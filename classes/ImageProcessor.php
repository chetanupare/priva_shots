<?php

class ImageProcessor {
    
    /**
     * Convert image to WebP format with compression
     */
    public static function convertToWebP($sourcePath, $destinationPath, $quality = 80) {
        try {
            // Check if GD extension is available
            if (!extension_loaded('gd')) {
                return ['success' => false, 'message' => 'GD extension not available'];
            }
            
            // Get image info
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return ['success' => false, 'message' => 'Invalid image file'];
            }
            
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $mimeType = $imageInfo['mime'];
            
            // Create image resource based on type
            $sourceImage = null;
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case 'image/png':
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                case 'image/gif':
                    $sourceImage = imagecreatefromgif($sourcePath);
                    break;
                case 'image/webp':
                    $sourceImage = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    return ['success' => false, 'message' => 'Unsupported image format'];
            }
            
            if (!$sourceImage) {
                return ['success' => false, 'message' => 'Failed to create image resource'];
            }
            
            // Create WebP image
            $result = imagewebp($sourceImage, $destinationPath, $quality);
            
            // Clean up
            imagedestroy($sourceImage);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'WebP conversion successful',
                    'original_size' => filesize($sourcePath),
                    'webp_size' => filesize($destinationPath),
                    'compression_ratio' => round((1 - filesize($destinationPath) / filesize($sourcePath)) * 100, 2)
                ];
            } else {
                return ['success' => false, 'message' => 'WebP conversion failed'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Image processing error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Create thumbnail with WebP format
     */
    public static function createThumbnail($sourcePath, $destinationPath, $maxWidth = 300, $maxHeight = 300, $quality = 80) {
        try {
            if (!extension_loaded('gd')) {
                return ['success' => false, 'message' => 'GD extension not available'];
            }
            
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return ['success' => false, 'message' => 'Invalid image file'];
            }
            
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $mimeType = $imageInfo['mime'];
            
            // Calculate new dimensions
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = round($originalWidth * $ratio);
            $newHeight = round($originalHeight * $ratio);
            
            // Create source image
            $sourceImage = null;
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case 'image/png':
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                case 'image/gif':
                    $sourceImage = imagecreatefromgif($sourcePath);
                    break;
                case 'image/webp':
                    $sourceImage = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    return ['success' => false, 'message' => 'Unsupported image format'];
            }
            
            if (!$sourceImage) {
                return ['success' => false, 'message' => 'Failed to create image resource'];
            }
            
            // Create thumbnail image
            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG and GIF
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
                imagefilledrectangle($thumbnail, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // Resize image
            imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            
            // Save as WebP
            $result = imagewebp($thumbnail, $destinationPath, $quality);
            
            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($thumbnail);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Thumbnail created successfully',
                    'original_size' => filesize($sourcePath),
                    'thumbnail_size' => filesize($destinationPath),
                    'dimensions' => ['width' => $newWidth, 'height' => $newHeight]
                ];
            } else {
                return ['success' => false, 'message' => 'Thumbnail creation failed'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Thumbnail creation error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Optimize image (resize if too large and convert to WebP)
     */
    public static function optimizeImage($sourcePath, $destinationPath, $maxWidth = 1920, $maxHeight = 1080, $quality = 85) {
        try {
            if (!extension_loaded('gd')) {
                return ['success' => false, 'message' => 'GD extension not available'];
            }
            
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return ['success' => false, 'message' => 'Invalid image file'];
            }
            
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $mimeType = $imageInfo['mime'];
            
            // Check if resizing is needed
            $needsResize = $originalWidth > $maxWidth || $originalHeight > $maxHeight;
            
            if ($needsResize) {
                // Calculate new dimensions
                $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
                $newWidth = round($originalWidth * $ratio);
                $newHeight = round($originalHeight * $ratio);
            } else {
                $newWidth = $originalWidth;
                $newHeight = $originalHeight;
            }
            
            // Create source image
            $sourceImage = null;
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case 'image/png':
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                case 'image/gif':
                    $sourceImage = imagecreatefromgif($sourcePath);
                    break;
                case 'image/webp':
                    $sourceImage = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    return ['success' => false, 'message' => 'Unsupported image format'];
            }
            
            if (!$sourceImage) {
                return ['success' => false, 'message' => 'Failed to create image resource'];
            }
            
            // Create optimized image
            $optimizedImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($optimizedImage, false);
                imagesavealpha($optimizedImage, true);
                $transparent = imagecolorallocatealpha($optimizedImage, 255, 255, 255, 127);
                imagefilledrectangle($optimizedImage, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // Resize if needed
            if ($needsResize) {
                imagecopyresampled($optimizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            } else {
                imagecopy($optimizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight);
            }
            
            // Save as WebP
            $result = imagewebp($optimizedImage, $destinationPath, $quality);
            
            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($optimizedImage);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Image optimized successfully',
                    'original_size' => filesize($sourcePath),
                    'optimized_size' => filesize($destinationPath),
                    'compression_ratio' => round((1 - filesize($destinationPath) / filesize($sourcePath)) * 100, 2),
                    'resized' => $needsResize,
                    'dimensions' => ['width' => $newWidth, 'height' => $newHeight]
                ];
            } else {
                return ['success' => false, 'message' => 'Image optimization failed'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Image optimization error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Check if WebP is supported
     */
    public static function isWebPSupported() {
        return extension_loaded('gd') && function_exists('imagewebp');
    }
    
    /**
     * Get supported image formats
     */
    public static function getSupportedFormats() {
        $formats = [];
        
        if (function_exists('imagecreatefromjpeg')) $formats[] = 'jpeg';
        if (function_exists('imagecreatefrompng')) $formats[] = 'png';
        if (function_exists('imagecreatefromgif')) $formats[] = 'gif';
        if (function_exists('imagecreatefromwebp')) $formats[] = 'webp';
        
        return $formats;
    }
}
?> 