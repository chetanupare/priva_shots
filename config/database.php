<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cloudphoto_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('JWT_SECRET', '54c6e923d8b29e7162a6f483d746f009c4a835a147ea0bb6dfe6b8be621444c3');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRY', 86400);
define('UPLOAD_MAX_SIZE', 100 * 1024 * 1024);
define('MEDIA_PATH', __DIR__ . '/../media/');
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
    'image/cgm',
    'image/x-eps',
    'image/vnd.dxf'
]);

define('ALLOWED_VIDEO_TYPES', [
    // Common video formats
    'video/mp4',
    'video/quicktime',
    'video/avi',
    'video/mov',
    'video/x-msvideo',
    
    // Additional video formats
    'video/webm',
    'video/ogg',
    'video/3gpp',
    'video/3gpp2',
    'video/x-flv',
    'video/x-ms-wmv',
    'video/x-ms-asf',
    'video/mkv',
    'video/x-matroska',
    'video/divx',
    'video/xvid',
    'video/x-m4v',
    'video/mp2t',
    'video/vnd.dlna.mpeg-tts',
    'video/x-dv'
]);
define('BCRYPT_COST', 12);
define('TOKEN_EXPIRY', 3600);
define('BASE_URL', 'http://localhost/Cloudphoto');
define('API_URL', BASE_URL . '/api');

if (!isset($pdo)) {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
    }
}