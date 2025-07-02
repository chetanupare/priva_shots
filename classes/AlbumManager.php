<?php
require_once __DIR__ . '/../config/database.php';

class AlbumManager {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Create a new album
     */
    public function createAlbum($userId, $name, $description = null, $type = 'manual', $coverImageId = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO albums (user_id, name, description, type, cover_image_id, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $name, $description, $type, $coverImageId]);
            
            return [
                'success' => true,
                'message' => 'Album created successfully',
                'album_id' => $this->pdo->lastInsertId()
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create album'];
        }
    }
    
    /**
     * List all albums with enhanced data
     */
    public function listAlbums($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    a.*,
                    COUNT(af.media_id) as media_count,
                    COALESCE(SUM(m.filesize), 0) as total_size,
                    m.filepath as cover_image_path,
                    m.original_filename as cover_filename,
                    m.mimetype as cover_mimetype,
                    MIN(COALESCE(e.date_taken, m.uploaded_at)) as earliest_date,
                    MAX(COALESCE(e.date_taken, m.uploaded_at)) as latest_date
                FROM albums a 
                LEFT JOIN album_files af ON a.id = af.album_id 
                LEFT JOIN media_files m ON a.cover_image_id = m.id
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE a.user_id = ? 
                GROUP BY a.id 
                ORDER BY a.created_at DESC
            ");
            $stmt->execute([$userId]);
            
            $albums = $stmt->fetchAll();
            
            // Add download_url to cover images
            foreach ($albums as &$album) {
                if ($album['cover_image_path']) {
                    $album['cover_download_url'] = BASE_URL . '/download.php?id=' . $album['cover_image_id'];
                }
            }
            
            return [
                'success' => true,
                'albums' => $albums
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to list albums'];
        }
    }
    
    /**
     * Get album details with media
     */
    public function getAlbumDetails($albumId, $userId, $limit = 50, $offset = 0) {
        try {
            // Get album info
            $stmt = $this->pdo->prepare("
                SELECT 
                    a.*,
                    COUNT(af.media_id) as media_count,
                    COALESCE(SUM(m.filesize), 0) as total_size,
                    MIN(COALESCE(e.date_taken, m.uploaded_at)) as earliest_date,
                    MAX(COALESCE(e.date_taken, m.uploaded_at)) as latest_date
                FROM albums a 
                LEFT JOIN album_files af ON a.id = af.album_id 
                LEFT JOIN media_files m ON af.media_id = m.id
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE a.id = ? AND a.user_id = ?
                GROUP BY a.id
            ");
            $stmt->execute([$albumId, $userId]);
            $album = $stmt->fetch();
            
            if (!$album) {
                return ['success' => false, 'message' => 'Album not found'];
            }
            
            // Get album media
            $stmt = $this->pdo->prepare("
                SELECT 
                    m.*,
                    e.date_taken,
                    e.camera_make,
                    e.camera_model,
                    e.iso,
                    e.aperture,
                    e.focal_length,
                    e.gps_latitude,
                    e.gps_longitude,
                    CASE WHEN e.media_id IS NOT NULL THEN 1 ELSE 0 END as has_exif
                FROM album_files af
                JOIN media_files m ON af.media_id = m.id
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE af.album_id = ?
                ORDER BY COALESCE(e.date_taken, m.uploaded_at) DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$albumId, $limit, $offset]);
            $media = $stmt->fetchAll();
            
            // Add download_url to media
            foreach ($media as &$item) {
                $item['download_url'] = BASE_URL . '/download.php?id=' . $item['id'];
            }
            
            return [
                'success' => true,
                'album' => $album,
                'media' => $media,
                'has_more' => count($media) === $limit
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to get album details'];
        }
    }
    
    /**
     * Add media to album
     */
    public function addToAlbum($albumId, $mediaId, $userId) {
        try {
            // Verify album belongs to user
            $stmt = $this->pdo->prepare("SELECT id FROM albums WHERE id = ? AND user_id = ?");
            $stmt->execute([$albumId, $userId]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Album not found'];
            }
            
            // Verify media belongs to user
            $stmt = $this->pdo->prepare("SELECT id FROM media_files WHERE id = ? AND user_id = ?");
            $stmt->execute([$mediaId, $userId]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Media not found'];
            }
            
            // Add to album
            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO album_files (album_id, media_id) 
                VALUES (?, ?)
            ");
            $stmt->execute([$albumId, $mediaId]);
            
            return ['success' => true, 'message' => 'Media added to album'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to add to album'];
        }
    }
    
    /**
     * Remove media from album
     */
    public function removeFromAlbum($albumId, $mediaId, $userId) {
        try {
            // Verify album belongs to user
            $stmt = $this->pdo->prepare("SELECT id FROM albums WHERE id = ? AND user_id = ?");
            $stmt->execute([$albumId, $userId]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Album not found'];
            }
            
            // Remove from album
            $stmt = $this->pdo->prepare("DELETE FROM album_files WHERE album_id = ? AND media_id = ?");
            $stmt->execute([$albumId, $mediaId]);
            
            return ['success' => true, 'message' => 'Media removed from album'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to remove from album'];
        }
    }
    
    /**
     * Update album details
     */
    public function updateAlbum($albumId, $userId, $name = null, $description = null, $coverImageId = null) {
        try {
            $updates = [];
            $params = [];
            
            if ($name !== null) {
                $updates[] = "name = ?";
                $params[] = $name;
            }
            
            if ($description !== null) {
                $updates[] = "description = ?";
                $params[] = $description;
            }
            
            if ($coverImageId !== null) {
                $updates[] = "cover_image_id = ?";
                $params[] = $coverImageId;
            }
            
            if (empty($updates)) {
                return ['success' => false, 'message' => 'No updates provided'];
            }
            
            $params[] = $albumId;
            $params[] = $userId;
            
            $stmt = $this->pdo->prepare("
                UPDATE albums 
                SET " . implode(', ', $updates) . "
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute($params);
            
            return ['success' => true, 'message' => 'Album updated successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update album'];
        }
    }
    
    /**
     * Delete album
     */
    public function deleteAlbum($albumId, $userId) {
        try {
            // Verify album belongs to user
            $stmt = $this->pdo->prepare("SELECT id FROM albums WHERE id = ? AND user_id = ?");
            $stmt->execute([$albumId, $userId]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Album not found'];
            }
            
            // Delete album files first
            $stmt = $this->pdo->prepare("DELETE FROM album_files WHERE album_id = ?");
            $stmt->execute([$albumId]);
            
            // Delete album
            $stmt = $this->pdo->prepare("DELETE FROM albums WHERE id = ?");
            $stmt->execute([$albumId]);
            
            return ['success' => true, 'message' => 'Album deleted successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to delete album'];
        }
    }
    
    /**
     * Create auto-albums based on date ranges (Google Photos style)
     */
    public function createAutoAlbums($userId) {
        try {
            // Get media grouped by date ranges
            $stmt = $this->pdo->prepare("
                SELECT 
                    DATE(COALESCE(e.date_taken, m.uploaded_at)) as date,
                    COUNT(*) as photo_count,
                    MIN(COALESCE(e.date_taken, m.uploaded_at)) as start_date,
                    MAX(COALESCE(e.date_taken, m.uploaded_at)) as end_date
                FROM media_files m
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE m.user_id = ?
                GROUP BY DATE(COALESCE(e.date_taken, m.uploaded_at))
                HAVING photo_count >= 3
                ORDER BY date DESC
            ");
            $stmt->execute([$userId]);
            $dateGroups = $stmt->fetchAll();
            
            $createdAlbums = [];
            
            foreach ($dateGroups as $group) {
                $albumName = $this->generateAlbumName($group['start_date'], $group['end_date'], $group['photo_count']);
                
                // Check if album already exists
                $stmt = $this->pdo->prepare("SELECT id FROM albums WHERE user_id = ? AND name = ? AND type = 'auto'");
                $stmt->execute([$userId, $albumName]);
                if ($stmt->fetch()) {
                    continue; // Album already exists
                }
                
                // Create album
                $result = $this->createAlbum($userId, $albumName, "Auto-generated album with {$group['photo_count']} photos", 'auto');
                
                if ($result['success']) {
                    $albumId = $result['album_id'];
                    
                    // Add media to album
                    $stmt = $this->pdo->prepare("
                        INSERT INTO album_files (album_id, media_id)
                        SELECT ?, m.id
                        FROM media_files m
                        LEFT JOIN media_exif e ON m.id = e.media_id
                        WHERE m.user_id = ? 
                        AND DATE(COALESCE(e.date_taken, m.uploaded_at)) = ?
                    ");
                    $stmt->execute([$albumId, $userId, $group['date']]);
                    
                    // Set cover image
                    $stmt = $this->pdo->prepare("
                        UPDATE albums 
                        SET cover_image_id = (
                            SELECT m.id 
                            FROM media_files m
                            LEFT JOIN media_exif e ON m.id = e.media_id
                            WHERE m.user_id = ? 
                            AND DATE(COALESCE(e.date_taken, m.uploaded_at)) = ?
                            ORDER BY COALESCE(e.date_taken, m.uploaded_at) DESC
                            LIMIT 1
                        )
                        WHERE id = ?
                    ");
                    $stmt->execute([$userId, $group['date'], $albumId]);
                    
                    $createdAlbums[] = $albumName;
                }
            }
            
            return [
                'success' => true,
                'message' => 'Auto-albums created successfully',
                'created_albums' => $createdAlbums
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create auto-albums'];
        }
    }
    
    /**
     * Generate album name based on date range
     */
    private function generateAlbumName($startDate, $endDate, $photoCount) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        
        if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
            // Same day
            return $start->format('F j, Y') . " ({$photoCount} photos)";
        } else {
            // Date range
            if ($start->format('Y') === $end->format('Y')) {
                if ($start->format('m') === $end->format('m')) {
                    // Same month
                    return $start->format('F j') . ' - ' . $end->format('j, Y') . " ({$photoCount} photos)";
                } else {
                    // Different months, same year
                    return $start->format('F j') . ' - ' . $end->format('F j, Y') . " ({$photoCount} photos)";
                }
            } else {
                // Different years
                return $start->format('F j, Y') . ' - ' . $end->format('F j, Y') . " ({$photoCount} photos)";
            }
        }
    }
    
    /**
     * Get album statistics
     */
    public function getAlbumStats($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_albums,
                    COUNT(CASE WHEN type = 'auto' THEN 1 END) as auto_albums,
                    COUNT(CASE WHEN type = 'manual' THEN 1 END) as manual_albums,
                    SUM(media_count) as total_photos_in_albums,
                    AVG(media_count) as avg_photos_per_album
                FROM (
                    SELECT 
                        a.id,
                        a.type,
                        COUNT(af.media_id) as media_count
                    FROM albums a
                    LEFT JOIN album_files af ON a.id = af.album_id
                    WHERE a.user_id = ?
                    GROUP BY a.id
                ) as album_stats
            ");
            $stmt->execute([$userId]);
            
            return [
                'success' => true,
                'stats' => $stmt->fetch()
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to get album stats'];
        }
    }
} 