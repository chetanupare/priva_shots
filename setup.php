<?php
/**
 * CloudPhoto Complete Setup Script
 * This script will create the database, import content, and set up everything needed
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

if ($_POST && $step == 1) {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? '';
    $db_name = $_POST['db_name'] ?? 'cloudphoto_db';

    try {
        // Connect to MySQL server
        $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db_name`");

        // Create tables
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            storage_quota BIGINT DEFAULT 5368709120,
            storage_used BIGINT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS media_files (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            filename VARCHAR(255) NOT NULL,
            original_filename VARCHAR(255) NOT NULL,
            filepath VARCHAR(500) NOT NULL,
            mimetype VARCHAR(100) NOT NULL,
            filesize BIGINT NOT NULL,
            width INT DEFAULT NULL,
            height INT DEFAULT NULL,
            duration INT DEFAULT NULL,
            capture_time TIMESTAMP NULL,
            device_id VARCHAR(100) DEFAULT NULL,
            upload_ip VARCHAR(45) DEFAULT NULL,
            has_exif BOOLEAN DEFAULT FALSE,
            date_taken DATETIME DEFAULT NULL,
            camera_model VARCHAR(100) DEFAULT NULL,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS media_exif (
            id INT AUTO_INCREMENT PRIMARY KEY,
            media_id INT NOT NULL,
            camera_make VARCHAR(100) DEFAULT NULL,
            camera_model VARCHAR(100) DEFAULT NULL,
            date_taken DATETIME DEFAULT NULL,
            gps_latitude DECIMAL(10,8) DEFAULT NULL,
            gps_longitude DECIMAL(11,8) DEFAULT NULL,
            orientation INT DEFAULT NULL,
            iso INT DEFAULT NULL,
            aperture VARCHAR(20) DEFAULT NULL,
            shutter_speed VARCHAR(20) DEFAULT NULL,
            focal_length VARCHAR(20) DEFAULT NULL,
            flash INT DEFAULT NULL,
            white_balance INT DEFAULT NULL,
            exposure_mode INT DEFAULT NULL,
            metering_mode INT DEFAULT NULL,
            software VARCHAR(200) DEFAULT NULL,
            copyright VARCHAR(200) DEFAULT NULL,
            artist VARCHAR(200) DEFAULT NULL,
            description TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (media_id) REFERENCES media_files(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS albums (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            description TEXT DEFAULT NULL,
            cover_image_id INT DEFAULT NULL,
            is_public BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS album_files (
            id INT AUTO_INCREMENT PRIMARY KEY,
            album_id INT NOT NULL,
            media_id INT NOT NULL,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE CASCADE,
            FOREIGN KEY (media_id) REFERENCES media_files(id) ON DELETE CASCADE,
            UNIQUE KEY unique_album_media (album_id, media_id)
        );

        CREATE TABLE IF NOT EXISTS user_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            device_info TEXT DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS api_keys (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            api_key VARCHAR(64) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            permissions JSON DEFAULT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            last_used_at TIMESTAMP NULL,
            expires_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            resource_type VARCHAR(50) DEFAULT NULL,
            resource_id INT DEFAULT NULL,
            details JSON DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );";

        $pdo->exec($sql);

        // Insert admin user
        $adminPassword = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password_hash, storage_quota) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@cloudphoto.local', $adminPassword, 107374182400]);

        // Create config file
        $config = "<?php
define('DB_HOST', '$db_host');
define('DB_NAME', '$db_name');
define('DB_USER', '$db_user');
define('DB_PASS', '$db_pass');
define('JWT_SECRET', '" . bin2hex(random_bytes(32)) . "');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRY', 86400);
define('UPLOAD_MAX_SIZE', 100 * 1024 * 1024);
define('MEDIA_PATH', __DIR__ . '/../media/');
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/quicktime', 'video/avi', 'video/mov']);
define('BCRYPT_COST', 12);
define('TOKEN_EXPIRY', 3600);
define('BASE_URL', 'http://localhost/Cloudphoto');
define('API_URL', BASE_URL . '/api');

if (!isset(\$pdo)) {
    try {
        \$pdo = new PDO(
            \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8mb4\",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException \$e) {
        error_log('Database connection failed: ' . \$e->getMessage());
    }
}";

        if (!file_exists(__DIR__ . '/config')) {
            mkdir(__DIR__ . '/config', 0755, true);
        }
        file_put_contents(__DIR__ . '/config/database.php', $config);

        // Create media directory
        if (!file_exists(__DIR__ . '/media')) {
            mkdir(__DIR__ . '/media', 0755, true);
        }

        $success = "✅ Database created, tables imported, config saved!";
        $step = 2;

    } catch (Exception $e) {
        $error = "Setup failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>CloudPhoto Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        button { width: 100%; padding: 12px; background: #007cba; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        button:hover { background: #005a8b; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .credentials { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .btn-link { display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>☁️ CloudPhoto Setup</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <form method="post">
                <div class="form-group">
                    <label>Database Host:</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>Database Name:</label>
                    <input type="text" name="db_name" value="cloudphoto_db" required>
                    <small>Will be created automatically</small>
                </div>
                <div class="form-group">
                    <label>MySQL Username:</label>
                    <input type="text" name="db_user" value="root" required>
                </div>
                <div class="form-group">
                    <label>MySQL Password:</label>
                    <input type="password" name="db_pass" placeholder="Leave empty if no password">
                </div>
                <button type="submit">🚀 Create Database & Setup CloudPhoto</button>
            </form>
        <?php elseif ($step == 2): ?>
            <div style="text-align: center;">
                <h2>🎉 Setup Complete!</h2>
                <div class="credentials">
                    <h3>Default Admin Login:</h3>
                    <p><strong>Email:</strong> admin@cloudphoto.local</p>
                    <p><strong>Password:</strong> admin123</p>
                    <p style="color: red;"><strong>⚠️ Change this password immediately!</strong></p>
                </div>
                <a href="index.php" class="btn-link">🏠 Go to CloudPhoto</a>
                <a href="dashboard.php" class="btn-link">📊 Dashboard</a>
                <p style="margin-top: 20px; font-size: 14px; color: #666;">
                    You can delete this setup.php file now.
                </p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 