<?php
/**
 * Production Configuration for PrivaShots
 * 
 * This file contains production-ready settings for the PrivaShots photo server.
 * Make sure to update these values according to your production environment.
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'cloudphoto_db');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_secure_password');

// Security Configuration
define('JWT_SECRET', 'your_very_long_random_jwt_secret_key_here_minimum_32_characters');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRY', 86400); // 24 hours
define('BCRYPT_COST', 12);

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 100 * 1024 * 1024); // 100MB
define('MEDIA_PATH', __DIR__ . '/../media/');

// Allowed File Types
define('ALLOWED_IMAGE_TYPES', [
    // Common formats
    'image/jpeg',
    'image/jpg', 
    'image/png',
    'image/gif',
    'image/webp',
    
    // BMP and bitmap formats
    'image/bmp',
    'image/x-ms-bmp',
    'image/x-windows-bmp',
    
    // TIFF formats
    'image/tiff',
    'image/tif',
    
    // SVG vector format
    'image/svg+xml',
    
    // Icon formats
    'image/x-icon',
    'image/vnd.microsoft.icon',
    'image/ico',
    
    // Adobe formats
    'image/psd',
    'image/vnd.adobe.photoshop',
    
    // RAW camera formats
    'image/x-canon-cr2',
    'image/x-canon-crw',
    'image/x-nikon-nef',
    'image/x-sony-arw',
    'image/x-adobe-dng',
    'image/x-panasonic-raw',
    'image/x-olympus-orf',
    'image/x-fuji-raf',
    'image/x-kodak-dcr',
    'image/x-kodak-k25',
    'image/x-kodak-kdc',
    'image/x-minolta-mrw',
    'image/x-pentax-pef',
    'image/x-sigma-x3f',
    
    // Apple formats
    'image/heic',
    'image/heif',
    
    // Other formats
    'image/x-portable-pixmap',
    'image/x-portable-graymap',
    'image/x-portable-bitmap',
    'image/x-xbitmap',
    'image/x-xpixmap',
    'image/x-cmu-raster',
    'image/x-sun-raster',
    'image/x-rgb',
    'image/x-portable-anymap',
    'image/x-targa',
    'image/x-pcx',
    'image/avif',
    'image/jxl',     // JPEG XL
    'image/jp2',     // JPEG 2000
    'image/jpx',     // JPEG 2000 extended
    'image/jpm',     // JPEG 2000 compound
    
    // Legacy and specialized formats
    'image/x-cmx',
    'image/x-freehand',
    'image/x-wmf',
    'image/x-emf',
    'image/x-cgm',
    'image/x-eps',
    'image/x-dxf'
]);

define('ALLOWED_VIDEO_TYPES', [
    // Common formats
    'video/mp4',
    'video/quicktime',
    'video/x-msvideo',
    'video/avi',
    'video/webm',
    'video/ogg',
    
    // Mobile formats
    'video/3gpp',
    'video/3gpp2',
    
    // Flash formats
    'video/x-flv',
    
    // Windows formats
    'video/x-ms-wmv',
    'video/x-ms-asf',
    
    // Other formats
    'video/x-matroska',
    'video/mkv',
    'video/x-msvideo',
    'video/x-divx',
    'video/xvid',
    'video/m4v',
    'video/mp2t',
    'video/dv',
    'video/x-ms-wm',
    'video/x-ms-wmx',
    'video/x-ms-wvx'
]);

// Application Configuration
define('TOKEN_EXPIRY', 3600);
define('BASE_URL', 'https://your-domain.com/Cloudphoto'); // Update with your domain
define('API_URL', BASE_URL . '/api');

// Performance Configuration
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600); // 1 hour
define('THUMBNAIL_SIZE', 300);
define('MAX_THUMBNAILS', 1000);

// Error Reporting (Set to false in production)
define('DISPLAY_ERRORS', false);
define('LOG_ERRORS', true);
define('ERROR_LOG_PATH', __DIR__ . '/../logs/error.log');

// Security Headers
define('SECURITY_HEADERS', [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' data:;"
]);

// Rate Limiting
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_REQUESTS', 100); // requests per window
define('RATE_LIMIT_WINDOW', 3600); // 1 hour window

// Backup Configuration
define('BACKUP_ENABLED', true);
define('BACKUP_RETENTION_DAYS', 30);
define('BACKUP_PATH', __DIR__ . '/../backups/');

// Logging Configuration
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
define('LOG_PATH', __DIR__ . '/../logs/');
define('ACCESS_LOG', LOG_PATH . 'access.log');
define('ERROR_LOG', LOG_PATH . 'error.log');
define('UPLOAD_LOG', LOG_PATH . 'upload.log');

// Set error reporting based on configuration
if (DISPLAY_ERRORS) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

if (LOG_ERRORS) {
    ini_set('log_errors', 1);
    ini_set('error_log', ERROR_LOG);
}

// Create log directory if it doesn't exist
if (!is_dir(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

// Create backup directory if it doesn't exist
if (BACKUP_ENABLED && !is_dir(BACKUP_PATH)) {
    mkdir(BACKUP_PATH, 0755, true);
}

// Set security headers
if (function_exists('header')) {
    foreach (SECURITY_HEADERS as $header => $value) {
        header("$header: $value");
    }
}
?> 