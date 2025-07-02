<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON content type
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $configPath = __DIR__ . '/../config/database.php';
    $authPath = __DIR__ . '/../classes/Auth.php';
    $mediaPath = __DIR__ . '/../classes/MediaManager.php';
    
    error_log("API Router - Loading files:");
    error_log("Config path: $configPath - " . (file_exists($configPath) ? "EXISTS" : "MISSING"));
    error_log("Auth path: $authPath - " . (file_exists($authPath) ? "EXISTS" : "MISSING"));
    error_log("Media path: $mediaPath - " . (file_exists($mediaPath) ? "EXISTS" : "MISSING"));
    
    require_once $configPath;
    require_once $authPath;
    require_once $mediaPath;
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading required files: ' . $e->getMessage(),
        'debug' => [
            'config_path' => __DIR__ . '/../config/database.php',
            'auth_path' => __DIR__ . '/../classes/Auth.php',
            'media_path' => __DIR__ . '/../classes/MediaManager.php',
            'current_dir' => __DIR__
        ]
    ]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/Cloudphoto/api', '', $path);

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';

// If no action in JSON, POST, or GET, try to get from URL path
if (!$action) {
    $action = trim($path, '/');
}

// Debug logging
error_log("API Router - Action: $action, Method: $method, Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
error_log("API Router - GET params: " . json_encode($_GET));
error_log("API Router - POST params: " . json_encode($_POST));

$auth = new Auth();
$mediaManager = new MediaManager();

function getJsonInput() {
    global $input;
    return $input;
}

function getBearerToken() {
    // Try multiple methods to get authorization header
    $authHeader = null;
    
    // Method 1: apache_request_headers() (may not work on all servers)
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        }
    }
    
    // Method 2: $_SERVER variables
    if (!$authHeader) {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
    }
    
    // Debug logging
    error_log("Auth Debug - Headers: " . json_encode(apache_request_headers()));
    error_log("Auth Debug - HTTP_AUTHORIZATION: " . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'not set'));
    error_log("Auth Debug - Final token: " . ($authHeader ? 'present' : 'missing'));
    
    if ($authHeader) {
        return str_replace('Bearer ', '', $authHeader);
    }
    
    return null;
}

function authenticateUser($auth) {
    $token = getBearerToken();
    if (!$token) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authorization token required']);
        exit();
    }
    
    $user = $auth->getCurrentUser($token);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
        exit();
    }
    
    return $user;
}

try {
    switch ($action) {
        case 'register':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $input = getJsonInput();
            if (!isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                break;
            }
            
            $result = $auth->register($input['username'], $input['email'], $input['password']);
            echo json_encode($result);
            break;
        
        case 'login':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $input = getJsonInput();
            if (!isset($input['email']) || !isset($input['password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing email or password']);
                break;
            }
            
            $result = $auth->login($input['email'], $input['password']);
            echo json_encode($result);
            break;
        
        case 'upload-media':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            if (!isset($_FILES['file'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No file uploaded']);
                break;
            }
            
            $timestamp = $_POST['timestamp'] ?? null;
            $deviceId = $_POST['device_id'] ?? null;
            
            $result = $mediaManager->uploadMedia($user['id'], $_FILES['file'], $timestamp, $deviceId);
            echo json_encode($result);
            break;
        
        case 'list-media':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            $from = $input['from'] ?? null;
            $to = $input['to'] ?? null;
            $type = $input['type'] ?? null;
            $albumId = $input['album'] ?? null;
            $limit = (int)($input['limit'] ?? 50);
            $offset = (int)($input['offset'] ?? 0);
            $includeExif = $input['include_exif'] ?? false;
            
            $result = $mediaManager->listMedia($user['id'], $from, $to, $type, $albumId, $limit, $offset);
            
            // Add EXIF data if requested and available
            if ($includeExif && function_exists('exif_read_data') && $result['success']) {
                require_once __DIR__ . '/../classes/ExifManager.php';
                $exifManager = new ExifManager();
                
                foreach ($result['data'] as &$item) {
                    if ($item['has_exif']) {
                        $exifData = $exifManager->getExifData($item['id']);
                        if ($exifData) {
                            $item['exif'] = $exifData;
                        }
                    }
                }
            }
            
            echo json_encode($result);
            break;
        
        case 'download-media':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Media ID required']);
                break;
            }
            
            $result = $mediaManager->downloadMedia($input['id'], $user['id']);
            
            if ($result['success']) {
                header('Content-Type: ' . $result['mimetype']);
                header('Content-Length: ' . $result['filesize']);
                header('Content-Disposition: inline; filename="' . $result['filename'] . '"');
                readfile($result['filepath']);
                exit();
            } else {
                http_response_code(404);
                echo json_encode($result);
            }
            break;
        
        case 'profile':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'storage_quota' => $user['storage_quota'],
                    'storage_used' => $user['storage_used'],
                    'storage_percentage' => round(($user['storage_used'] / $user['storage_quota']) * 100, 2)
                ]
            ]);
            break;
        
        case 'exif-data':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['media_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Media ID required']);
                break;
            }
            
            if (!function_exists('exif_read_data')) {
                http_response_code(503);
                echo json_encode(['success' => false, 'message' => 'EXIF extension not available']);
                break;
            }
            
            require_once __DIR__ . '/../classes/ExifManager.php';
            $exifManager = new ExifManager();
            
            // Verify user owns this media
            $media = $mediaManager->getMediaById($input['media_id']);
            
            if (!$media || $media['user_id'] != $user['id']) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Media not found']);
                break;
            }
            
            $exifData = $exifManager->getExifData($input['media_id']);
            echo json_encode([
                'success' => true,
                'data' => $exifData ?: null
            ]);
            break;
        
        case 'camera-stats':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            if (!function_exists('exif_read_data')) {
                http_response_code(503);
                echo json_encode(['success' => false, 'message' => 'EXIF extension not available']);
                break;
            }
            
            require_once __DIR__ . '/../classes/ExifManager.php';
            $exifManager = new ExifManager();
            
            $stats = $exifManager->getCameraStats($user['id']);
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;

        case 'search-exif':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!function_exists('exif_read_data')) {
                http_response_code(503);
                echo json_encode(['success' => false, 'message' => 'EXIF extension not available']);
                break;
            }
            
            require_once __DIR__ . '/../classes/ExifManager.php';
            $exifManager = new ExifManager();
            
            $filters = $input['filters'] ?? [];
            $limit = (int)($input['limit'] ?? 50);
            $offset = (int)($input['offset'] ?? 0);
            
            $filters['limit'] = $limit;
            $filters['offset'] = $offset;
            
            $results = $exifManager->searchByExif($user['id'], $filters);
            
            echo json_encode([
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ]);
            break;

        case 'filter-options':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            if (!function_exists('exif_read_data')) {
                http_response_code(503);
                echo json_encode(['success' => false, 'message' => 'EXIF extension not available']);
                break;
            }
            
            require_once __DIR__ . '/../classes/ExifManager.php';
            $exifManager = new ExifManager();
            
            $options = $exifManager->getFilterOptions($user['id']);
            echo json_encode([
                'success' => true,
                'data' => $options
            ]);
            break;

        case 'exif-stats':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            if (!function_exists('exif_read_data')) {
                http_response_code(503);
                echo json_encode(['success' => false, 'message' => 'EXIF extension not available']);
                break;
            }
            
            require_once __DIR__ . '/../classes/ExifManager.php';
            $exifManager = new ExifManager();
            
            $stats = $exifManager->getExifStats($user['id']);
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;

        case 'photos-by-date':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['start_date']) || !isset($input['end_date'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Start date and end date required']);
                break;
            }
            
            if (!function_exists('exif_read_data')) {
                http_response_code(503);
                echo json_encode(['success' => false, 'message' => 'EXIF extension not available']);
                break;
            }
            
            require_once __DIR__ . '/../classes/ExifManager.php';
            $exifManager = new ExifManager();
            
            $limit = (int)($input['limit'] ?? 50);
            $offset = (int)($input['offset'] ?? 0);
            
            $results = $exifManager->getPhotosByDateRange($user['id'], $input['start_date'], $input['end_date'], $limit, $offset);
            
            echo json_encode([
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ]);
            break;

        case 'photos-by-camera':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!function_exists('exif_read_data')) {
                http_response_code(503);
                echo json_encode(['success' => false, 'message' => 'EXIF extension not available']);
                break;
            }
            
            require_once __DIR__ . '/../classes/ExifManager.php';
            $exifManager = new ExifManager();
            
            $cameraMake = $input['camera_make'] ?? null;
            $cameraModel = $input['camera_model'] ?? null;
            $limit = (int)($input['limit'] ?? 50);
            $offset = (int)($input['offset'] ?? 0);
            
            $results = $exifManager->getPhotosByCamera($user['id'], $cameraMake, $cameraModel, $limit, $offset);
            
            echo json_encode([
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ]);
            break;

        case 'photos-by-location':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['latitude']) || !isset($input['longitude'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Latitude and longitude required']);
                break;
            }
            
            if (!function_exists('exif_read_data')) {
                http_response_code(503);
                echo json_encode(['success' => false, 'message' => 'EXIF extension not available']);
                break;
            }
            
            require_once __DIR__ . '/../classes/ExifManager.php';
            $exifManager = new ExifManager();
            
            $radius = (float)($input['radius'] ?? 1);
            
            $results = $exifManager->getPhotosByLocation($user['id'], $input['latitude'], $input['longitude'], $radius);
            
            echo json_encode([
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ]);
            break;

        case 'timeline':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            require_once __DIR__ . '/../classes/TimelineManager.php';
            $timelineManager = new TimelineManager();
            
            $limit = (int)($input['limit'] ?? 1000);
            $offset = (int)($input['offset'] ?? 0);
            
            $timelineData = $timelineManager->getTimelineData($user['id'], $limit, $offset);
            
            echo json_encode([
                'success' => true,
                'data' => $timelineData
            ]);
            break;

        case 'timeline-stats':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            require_once __DIR__ . '/../classes/TimelineManager.php';
            $timelineManager = new TimelineManager();
            
            $stats = $timelineManager->getTimelineStats($user['id']);
            
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;

        case 'timeline-navigation':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            require_once __DIR__ . '/../classes/TimelineManager.php';
            $timelineManager = new TimelineManager();
            
            $navigation = $timelineManager->getTimelineNavigation($user['id']);
            
            echo json_encode([
                'success' => true,
                'data' => $navigation
            ]);
            break;

        case 'photos-by-specific-date':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['date'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Date required']);
                break;
            }
            
            require_once __DIR__ . '/../classes/TimelineManager.php';
            $timelineManager = new TimelineManager();
            
            $limit = (int)($input['limit'] ?? 50);
            $photos = $timelineManager->getPhotosByDate($user['id'], $input['date'], $limit);
            
            echo json_encode([
                'success' => true,
                'data' => $photos,
                'count' => count($photos)
            ]);
            break;

        case 'list-albums':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            require_once __DIR__ . '/../classes/AlbumManager.php';
            $albumManager = new AlbumManager();
            
            $result = $albumManager->listAlbums($user['id']);
            
            echo json_encode($result);
            break;

        case 'album-details':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['album_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Album ID required']);
                break;
            }
            
            require_once __DIR__ . '/../classes/AlbumManager.php';
            $albumManager = new AlbumManager();
            
            $limit = (int)($input['limit'] ?? 50);
            $offset = (int)($input['offset'] ?? 0);
            
            $result = $albumManager->getAlbumDetails($input['album_id'], $user['id'], $limit, $offset);
            
            echo json_encode($result);
            break;

        case 'create-album':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Album name required']);
                break;
            }
            
            require_once __DIR__ . '/../classes/AlbumManager.php';
            $albumManager = new AlbumManager();
            
            $result = $albumManager->createAlbum(
                $user['id'],
                $input['name'],
                $input['description'] ?? null,
                $input['type'] ?? 'manual',
                $input['cover_image_id'] ?? null
            );
            
            echo json_encode($result);
            break;

        case 'update-album':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['album_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Album ID required']);
                break;
            }
            
            require_once __DIR__ . '/../classes/AlbumManager.php';
            $albumManager = new AlbumManager();
            
            $result = $albumManager->updateAlbum(
                $input['album_id'],
                $user['id'],
                $input['name'] ?? null,
                $input['description'] ?? null,
                $input['cover_image_id'] ?? null
            );
            
            echo json_encode($result);
            break;

        case 'delete-album':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['album_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Album ID required']);
                break;
            }
            
            require_once __DIR__ . '/../classes/AlbumManager.php';
            $albumManager = new AlbumManager();
            
            $result = $albumManager->deleteAlbum($input['album_id'], $user['id']);
            
            echo json_encode($result);
            break;

        case 'add-to-album':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['album_id']) || !isset($input['media_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Album ID and Media ID required']);
                break;
            }
            
            require_once __DIR__ . '/../classes/AlbumManager.php';
            $albumManager = new AlbumManager();
            
            $result = $albumManager->addToAlbum($input['album_id'], $input['media_id'], $user['id']);
            
            echo json_encode($result);
            break;

        case 'remove-from-album':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['album_id']) || !isset($input['media_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Album ID and Media ID required']);
                break;
            }
            
            require_once __DIR__ . '/../classes/AlbumManager.php';
            $albumManager = new AlbumManager();
            
            $result = $albumManager->removeFromAlbum($input['album_id'], $input['media_id'], $user['id']);
            
            echo json_encode($result);
            break;

        case 'create-auto-albums':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            require_once __DIR__ . '/../classes/AlbumManager.php';
            $albumManager = new AlbumManager();
            
            $result = $albumManager->createAutoAlbums($user['id']);
            
            echo json_encode($result);
            break;

        case 'album-stats':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            require_once __DIR__ . '/../classes/AlbumManager.php';
            $albumManager = new AlbumManager();
            
            $result = $albumManager->getAlbumStats($user['id']);
            
            echo json_encode($result);
            break;

        case 'getDashboardStats':
            if ($method !== 'GET' && $method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            // Get stats
            $totalPhotos = $mediaManager->getMediaCount($user['id'], null, null, 'image');
            $totalVideos = $mediaManager->getMediaCount($user['id'], null, null, 'video');
            $totalMedia = $totalPhotos + $totalVideos;
            
            // Get album count
            require_once __DIR__ . '/../classes/AlbumManager.php';
            $albumManager = new AlbumManager();
            $albumStats = $albumManager->getAlbumStats($user['id']);
            $totalAlbums = $albumStats['success'] ? $albumStats['stats']['total_albums'] : 0;
            
            // Calculate actual storage used
            $storageUsedBytes = $mediaManager->getTotalStorageUsed($user['id']);
            $storageUsed = $storageUsedBytes;
            $storageUnit = 'B';
            
            if ($storageUsedBytes >= 1024 * 1024 * 1024) {
                $storageUsed = round($storageUsedBytes / (1024 * 1024 * 1024), 1);
                $storageUnit = 'GB';
            } elseif ($storageUsedBytes >= 1024 * 1024) {
                $storageUsed = round($storageUsedBytes / (1024 * 1024), 1);
                $storageUnit = 'MB';
            } elseif ($storageUsedBytes >= 1024) {
                $storageUsed = round($storageUsedBytes / 1024, 1);
                $storageUnit = 'KB';
            }
            
            // Get EXIF files count
            $exifFiles = $mediaManager->getExifFilesCount($user['id']);
            
            echo json_encode([
                'success' => true,
                'stats' => [
                    'totalPhotos' => $totalPhotos,
                    'totalVideos' => $totalVideos,
                    'totalMedia' => $totalMedia,
                    'totalAlbums' => $totalAlbums,
                    'storageUsed' => $storageUsed,
                    'storageUnit' => $storageUnit,
                    'exifFiles' => $exifFiles
                ]
            ]);
            break;

        case 'getRecentPhotos':
            if ($method !== 'GET' && $method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            // Get recent photos
            $result = $mediaManager->listMedia($user['id'], null, null, 'image', null, 10, 0);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'photos' => $result['media']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to load recent photos',
                    'photos' => []
                ]);
            }
            break;

        case 'getMedia':
            if ($method !== 'GET' && $method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            // Get all media
            $result = $mediaManager->listMedia($user['id'], null, null, null, null, 50, 0);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'media' => $result['media']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to load media',
                    'media' => []
                ]);
            }
            break;

        case 'delete-media':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Media ID required']);
                break;
            }
            
            $result = $mediaManager->deleteMedia($input['id'], $user['id']);
            echo json_encode($result);
            break;

        case 'deleteMedia':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Media ID required']);
                break;
            }
            
            $result = $mediaManager->deleteMedia($input['id'], $user['id']);
            echo json_encode($result);
            break;

        case 'exifData':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['media_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Media ID required']);
                break;
            }
            
            if (!function_exists('exif_read_data')) {
                http_response_code(503);
                echo json_encode(['success' => false, 'message' => 'EXIF extension not available']);
                break;
            }
            
            require_once __DIR__ . '/../classes/ExifManager.php';
            $exifManager = new ExifManager();
            
            // Verify user owns this media
            $media = $mediaManager->getMediaById($input['media_id']);
            
            if (!$media || $media['user_id'] != $user['id']) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Media not found']);
                break;
            }
            
            $exifData = $exifManager->getExifData($input['media_id']);
            echo json_encode([
                'success' => true,
                'data' => $exifData ?: null
            ]);
            break;

        case 'createAlbum':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Album name required']);
                break;
            }
            
            require_once __DIR__ . '/../classes/AlbumManager.php';
            $albumManager = new AlbumManager();
            
            $result = $albumManager->createAlbum(
                $user['id'],
                $input['name'],
                $input['description'] ?? null,
                $input['type'] ?? 'manual',
                $input['cover_image_id'] ?? null
            );
            
            echo json_encode($result);
            break;

        case 'addToAlbum':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!isset($input['album_id']) || !isset($input['media_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Album ID and Media ID required']);
                break;
            }
            
            require_once __DIR__ . '/../classes/AlbumManager.php';
            $albumManager = new AlbumManager();
            
            $result = $albumManager->addToAlbum($input['album_id'], $input['media_id'], $user['id']);
            
            echo json_encode($result);
            break;

        case 'timelineData':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            require_once __DIR__ . '/../classes/TimelineManager.php';
            $timelineManager = new TimelineManager();
            
            $limit = (int)($input['limit'] ?? 1000);
            $offset = (int)($input['offset'] ?? 0);
            
            $timelineData = $timelineManager->getTimelineData($user['id'], $limit, $offset);
            
            echo json_encode([
                'success' => true,
                'data' => $timelineData
            ]);
            break;

        case 'timelineNavigation':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            require_once __DIR__ . '/../classes/TimelineManager.php';
            $timelineManager = new TimelineManager();
            
            $navigation = $timelineManager->getTimelineNavigation($user['id']);
            
            echo json_encode([
                'success' => true,
                'data' => $navigation
            ]);
            break;

        case 'timelineStats':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            require_once __DIR__ . '/../classes/TimelineManager.php';
            $timelineManager = new TimelineManager();
            
            $stats = $timelineManager->getTimelineStats($user['id']);
            
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;

        case 'exifSearch':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            $input = getJsonInput();
            
            if (!function_exists('exif_read_data')) {
                http_response_code(503);
                echo json_encode(['success' => false, 'message' => 'EXIF extension not available']);
                break;
            }
            
            require_once __DIR__ . '/../classes/ExifManager.php';
            $exifManager = new ExifManager();
            
            $query = $input['query'] ?? '';
            $limit = (int)($input['limit'] ?? 50);
            $offset = (int)($input['offset'] ?? 0);
            
            $results = $exifManager->searchByExif($user['id'], ['query' => $query, 'limit' => $limit, 'offset' => $offset]);
            
            echo json_encode([
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ]);
            break;

        case 'list-albums':
            if ($method !== 'GET' && $method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $user = authenticateUser($auth);
            
            require_once __DIR__ . '/../classes/AlbumManager.php';
            $albumManager = new AlbumManager();
            $result = $albumManager->listAlbums($user['id']);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'albums' => $result['albums']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to load albums',
                    'albums' => []
                ]);
            }
            break;
        
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false, 
                'message' => 'Endpoint not found',
                'debug' => [
                    'action' => $action,
                    'method' => $method,
                    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
                    'post_data' => $_POST,
                    'files' => isset($_FILES) ? array_keys($_FILES) : 'no files'
                ]
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'debug' => [
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
} 