<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Auth.php';

// Get media ID from URL
$mediaId = $_GET['id'] ?? null;

if (!$mediaId) {
    http_response_code(400);
    echo 'Media ID required';
    exit();
}

// Get authorization token
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;

// Also check for token in URL parameter (for image/video display)
$tokenParam = $_GET['token'] ?? null;

if (!$authHeader && !$tokenParam) {
    http_response_code(401);
    echo 'Authorization required';
    exit();
}

$token = $authHeader ? str_replace('Bearer ', '', $authHeader) : $tokenParam;

// Verify token and get user
$auth = new Auth();
$user = $auth->getCurrentUser($token);

if (!$user) {
    http_response_code(401);
    echo 'Invalid token';
    exit();
}

// Get media file
try {
    $stmt = $pdo->prepare("SELECT * FROM media_files WHERE id = ? AND user_id = ?");
    $stmt->execute([$mediaId, $user['id']]);
    $media = $stmt->fetch();
    
    if (!$media) {
        http_response_code(404);
        echo 'Media not found';
        exit();
    }
    
    if (!file_exists($media['filepath'])) {
        http_response_code(404);
        echo 'File not found on disk';
        exit();
    }
    
    // Serve the file
    header('Content-Type: ' . $media['mimetype']);
    header('Content-Length: ' . $media['filesize']);
    header('Content-Disposition: inline; filename="' . $media['original_filename'] . '"');
    header('Cache-Control: public, max-age=31536000');
    
    readfile($media['filepath']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Download failed';
}
?> 