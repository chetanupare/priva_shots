<?php
require_once __DIR__ . '/../config/database.php';

class ExifManager {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Extract EXIF data from image file
     */
    public function extractExifData($filepath) {
        if (!function_exists('exif_read_data')) {
            return ['success' => false, 'message' => 'EXIF extension not available'];
        }
        
        $exif = @exif_read_data($filepath, 'ANY_TAG', true);
        if (!$exif) {
            return ['success' => false, 'message' => 'No EXIF data found'];
        }
        
        $data = [
            'camera_make' => $this->getExifValue($exif, 'IFD0', 'Make'),
            'camera_model' => $this->getExifValue($exif, 'IFD0', 'Model'),
            'date_taken' => $this->getDateTaken($exif),
            'gps_latitude' => $this->getGpsCoordinate($exif, 'GPSLatitude', $this->getExifValue($exif, 'GPS', 'GPSLatitudeRef')),
            'gps_longitude' => $this->getGpsCoordinate($exif, 'GPSLongitude', $this->getExifValue($exif, 'GPS', 'GPSLongitudeRef')),
            'orientation' => $this->getExifValue($exif, 'IFD0', 'Orientation'),
            'iso' => $this->getExifValue($exif, 'EXIF', 'ISOSpeedRatings'),
            'aperture' => $this->getAperture($exif),
            'shutter_speed' => $this->getShutterSpeed($exif),
            'focal_length' => $this->getFocalLength($exif),
            'flash' => $this->getExifValue($exif, 'EXIF', 'Flash'),
            'white_balance' => $this->getExifValue($exif, 'EXIF', 'WhiteBalance'),
            'exposure_mode' => $this->getExifValue($exif, 'EXIF', 'ExposureMode'),
            'metering_mode' => $this->getExifValue($exif, 'EXIF', 'MeteringMode'),
            'software' => $this->getExifValue($exif, 'IFD0', 'Software'),
            'copyright' => $this->getExifValue($exif, 'IFD0', 'Copyright'),
            'artist' => $this->getExifValue($exif, 'IFD0', 'Artist'),
            'description' => $this->getExifValue($exif, 'IFD0', 'ImageDescription')
        ];
        
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Get EXIF value safely
     */
    private function getExifValue($exif, $section, $key) {
        return isset($exif[$section][$key]) ? trim($exif[$section][$key]) : null;
    }
    
    /**
     * Get date taken from EXIF
     */
    private function getDateTaken($exif) {
        // Try multiple date fields
        $dateFields = [
            'EXIF' => ['DateTimeOriginal', 'DateTimeDigitized'],
            'IFD0' => ['DateTime']
        ];
        
        foreach ($dateFields as $section => $fields) {
            foreach ($fields as $field) {
                $date = $this->getExifValue($exif, $section, $field);
                if ($date) {
                    $timestamp = strtotime($date);
                    if ($timestamp !== false) {
                        return date('Y-m-d H:i:s', $timestamp);
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get GPS coordinate
     */
    private function getGpsCoordinate($exif, $coordKey, $hemi) {
        if (!isset($exif['GPS'][$coordKey])) {
            return null;
        }
        
        $coord = $exif['GPS'][$coordKey];
        if (!is_array($coord) || count($coord) !== 3) {
            return null;
        }
        
        $degrees = $this->gps2Num($coord[0]);
        $minutes = $this->gps2Num($coord[1]);
        $seconds = $this->gps2Num($coord[2]);
        
        $flip = ($hemi == 'W' || $hemi == 'S') ? -1 : 1;
        return $flip * ($degrees + ($minutes / 60) + ($seconds / 3600));
    }
    
    /**
     * Convert GPS coordinate part to number
     */
    private function gps2Num($coordPart) {
        $parts = explode('/', $coordPart);
        return (count($parts) <= 1) ? $coordPart : floatval($parts[0]) / floatval($parts[1]);
    }
    
    /**
     * Get aperture value
     */
    private function getAperture($exif) {
        $aperture = $this->getExifValue($exif, 'EXIF', 'COMPUTED');
        if (isset($aperture['ApertureFNumber'])) {
            return $aperture['ApertureFNumber'];
        }
        return null;
    }
    
    /**
     * Get shutter speed
     */
    private function getShutterSpeed($exif) {
        $shutter = $this->getExifValue($exif, 'EXIF', 'ExposureTime');
        if ($shutter) {
            return $shutter;
        }
        return null;
    }
    
    /**
     * Get focal length
     */
    private function getFocalLength($exif) {
        $focal = $this->getExifValue($exif, 'EXIF', 'FocalLength');
        if ($focal) {
            return $focal;
        }
        return null;
    }
    
    /**
     * Save EXIF data to database
     */
    public function saveExifData($mediaId, $exifData) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO media_exif (
                    media_id, camera_make, camera_model, date_taken, 
                    gps_latitude, gps_longitude, orientation, iso, 
                    aperture, shutter_speed, focal_length, flash, 
                    white_balance, exposure_mode, metering_mode, 
                    software, copyright, artist, description
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");
            
            $stmt->execute([
                $mediaId,
                $exifData['camera_make'],
                $exifData['camera_model'],
                $exifData['date_taken'],
                $exifData['gps_latitude'],
                $exifData['gps_longitude'],
                $exifData['orientation'],
                $exifData['iso'],
                $exifData['aperture'],
                $exifData['shutter_speed'],
                $exifData['focal_length'],
                $exifData['flash'],
                $exifData['white_balance'],
                $exifData['exposure_mode'],
                $exifData['metering_mode'],
                $exifData['software'],
                $exifData['copyright'],
                $exifData['artist'],
                $exifData['description']
            ]);
            
            return ['success' => true, 'message' => 'EXIF data saved'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to save EXIF data'];
        }
    }
    
    /**
     * Get EXIF data for media
     */
    public function getExifData($mediaId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM media_exif WHERE media_id = ?
            ");
            $stmt->execute([$mediaId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get organized folder path based on date
     */
    public function getOrganizedPath($dateTaken, $userId) {
        if (!$dateTaken) {
            $dateTaken = date('Y-m-d');
        }
        
        $timestamp = strtotime($dateTaken);
        $year = date('Y', $timestamp);
        $month = date('m', $timestamp);
        $day = date('d', $timestamp);
        
        return MEDIA_PATH . $userId . '/' . $year . '/' . $month . '/' . $day;
    }
    
    /**
     * Detect duplicate files
     */
    public function detectDuplicate($userId, $exifData, $fileSize) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT m.id, m.original_filename, m.uploaded_at, e.date_taken, e.camera_model
                FROM media_files m
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE m.user_id = ? AND m.filesize = ?
                AND e.date_taken = ? AND e.camera_model = ?
            ");
            
            $stmt->execute([
                $userId, 
                $fileSize, 
                $exifData['date_taken'], 
                $exifData['camera_model']
            ]);
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get camera statistics
     */
    public function getCameraStats($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    e.camera_make,
                    e.camera_model,
                    COUNT(*) as photo_count,
                    MIN(m.uploaded_at) as first_photo,
                    MAX(m.uploaded_at) as last_photo
                FROM media_files m
                JOIN media_exif e ON m.id = e.media_id
                WHERE m.user_id = ? AND e.camera_model IS NOT NULL
                GROUP BY e.camera_make, e.camera_model
                ORDER BY photo_count DESC
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get photos by location (GPS)
     */
    public function getPhotosByLocation($userId, $lat, $lng, $radius = 1) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT m.*, e.gps_latitude, e.gps_longitude
                FROM media_files m
                JOIN media_exif e ON m.id = e.media_id
                WHERE m.user_id = ? 
                AND e.gps_latitude IS NOT NULL 
                AND e.gps_longitude IS NOT NULL
                AND (
                    (e.gps_latitude BETWEEN ? - ? AND ? + ?) AND
                    (e.gps_longitude BETWEEN ? - ? AND ? + ?)
                )
            ");
            
            $stmt->execute([
                $userId, $lat, $radius, $lat, $radius, 
                $lng, $radius, $lng, $radius
            ]);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Advanced EXIF search and filter
     */
    public function searchByExif($userId, $filters = []) {
        try {
            $whereConditions = ['m.user_id = ?'];
            $params = [$userId];
            
            // Date range filter
            if (!empty($filters['date_from'])) {
                $whereConditions[] = 'e.date_taken >= ?';
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereConditions[] = 'e.date_taken <= ?';
                $params[] = $filters['date_to'];
            }
            
            // Camera make/model filter
            if (!empty($filters['camera_make'])) {
                $whereConditions[] = 'e.camera_make LIKE ?';
                $params[] = '%' . $filters['camera_make'] . '%';
            }
            
            if (!empty($filters['camera_model'])) {
                $whereConditions[] = 'e.camera_model LIKE ?';
                $params[] = '%' . $filters['camera_model'] . '%';
            }
            
            // ISO range filter
            if (!empty($filters['iso_min'])) {
                $whereConditions[] = 'CAST(e.iso AS UNSIGNED) >= ?';
                $params[] = $filters['iso_min'];
            }
            
            if (!empty($filters['iso_max'])) {
                $whereConditions[] = 'CAST(e.iso AS UNSIGNED) <= ?';
                $params[] = $filters['iso_max'];
            }
            
            // Aperture filter
            if (!empty($filters['aperture_min'])) {
                $whereConditions[] = 'CAST(REPLACE(e.aperture, "f/", "") AS DECIMAL(4,2)) >= ?';
                $params[] = $filters['aperture_min'];
            }
            
            if (!empty($filters['aperture_max'])) {
                $whereConditions[] = 'CAST(REPLACE(e.aperture, "f/", "") AS DECIMAL(4,2)) <= ?';
                $params[] = $filters['aperture_max'];
            }
            
            // Focal length filter
            if (!empty($filters['focal_min'])) {
                $whereConditions[] = 'CAST(REPLACE(e.focal_length, "mm", "") AS DECIMAL(6,2)) >= ?';
                $params[] = $filters['focal_min'];
            }
            
            if (!empty($filters['focal_max'])) {
                $whereConditions[] = 'CAST(REPLACE(e.focal_length, "mm", "") AS DECIMAL(6,2)) <= ?';
                $params[] = $filters['focal_max'];
            }
            
            // Flash filter
            if (isset($filters['flash']) && $filters['flash'] !== '') {
                $whereConditions[] = 'e.flash = ?';
                $params[] = $filters['flash'];
            }
            
            // White balance filter
            if (!empty($filters['white_balance'])) {
                $whereConditions[] = 'e.white_balance LIKE ?';
                $params[] = '%' . $filters['white_balance'] . '%';
            }
            
            // Exposure mode filter
            if (!empty($filters['exposure_mode'])) {
                $whereConditions[] = 'e.exposure_mode LIKE ?';
                $params[] = '%' . $filters['exposure_mode'] . '%';
            }
            
            // Software filter
            if (!empty($filters['software'])) {
                $whereConditions[] = 'e.software LIKE ?';
                $params[] = '%' . $filters['software'] . '%';
            }
            
            // Description search
            if (!empty($filters['description'])) {
                $whereConditions[] = 'e.description LIKE ?';
                $params[] = '%' . $filters['description'] . '%';
            }
            
            // Artist/Copyright search
            if (!empty($filters['artist'])) {
                $whereConditions[] = '(e.artist LIKE ? OR e.copyright LIKE ?)';
                $params[] = '%' . $filters['artist'] . '%';
                $params[] = '%' . $filters['artist'] . '%';
            }
            
            // GPS location filter
            if (!empty($filters['location'])) {
                $whereConditions[] = '(e.gps_latitude IS NOT NULL AND e.gps_longitude IS NOT NULL)';
            }
            
            // Has EXIF data filter
            if (isset($filters['has_exif']) && $filters['has_exif'] !== '') {
                if ($filters['has_exif']) {
                    $whereConditions[] = 'e.media_id IS NOT NULL';
                } else {
                    $whereConditions[] = 'e.media_id IS NULL';
                }
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            $sql = "
                SELECT m.*, e.*, 
                       CASE WHEN e.media_id IS NOT NULL THEN 1 ELSE 0 END as has_exif
                FROM media_files m
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE $whereClause
                ORDER BY m.uploaded_at DESC
            ";
            
            // Add limit and offset
            if (isset($filters['limit'])) {
                $sql .= ' LIMIT ' . (int)$filters['limit'];
                if (isset($filters['offset'])) {
                    $sql .= ' OFFSET ' . (int)$filters['offset'];
                }
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("EXIF Search Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available filter options for dropdowns
     */
    public function getFilterOptions($userId) {
        try {
            $options = [];
            
            // Camera makes
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT camera_make 
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ? AND camera_make IS NOT NULL AND camera_make != ''
                ORDER BY camera_make
            ");
            $stmt->execute([$userId]);
            $options['camera_makes'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Camera models
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT camera_model 
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ? AND camera_model IS NOT NULL AND camera_model != ''
                ORDER BY camera_model
            ");
            $stmt->execute([$userId]);
            $options['camera_models'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // ISO ranges
            $stmt = $this->pdo->prepare("
                SELECT MIN(CAST(iso AS UNSIGNED)) as min_iso, 
                       MAX(CAST(iso AS UNSIGNED)) as max_iso
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ? AND iso IS NOT NULL AND iso != ''
            ");
            $stmt->execute([$userId]);
            $options['iso_range'] = $stmt->fetch();
            
            // Aperture ranges
            $stmt = $this->pdo->prepare("
                SELECT MIN(CAST(REPLACE(aperture, 'f/', '') AS DECIMAL(4,2))) as min_aperture,
                       MAX(CAST(REPLACE(aperture, 'f/', '') AS DECIMAL(4,2))) as max_aperture
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ? AND aperture IS NOT NULL AND aperture != ''
            ");
            $stmt->execute([$userId]);
            $options['aperture_range'] = $stmt->fetch();
            
            // Focal length ranges
            $stmt = $this->pdo->prepare("
                SELECT MIN(CAST(REPLACE(focal_length, 'mm', '') AS DECIMAL(6,2))) as min_focal,
                       MAX(CAST(REPLACE(focal_length, 'mm', '') AS DECIMAL(6,2))) as max_focal
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ? AND focal_length IS NOT NULL AND focal_length != ''
            ");
            $stmt->execute([$userId]);
            $options['focal_range'] = $stmt->fetch();
            
            // Date range
            $stmt = $this->pdo->prepare("
                SELECT MIN(date_taken) as min_date, MAX(date_taken) as max_date
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ? AND date_taken IS NOT NULL
            ");
            $stmt->execute([$userId]);
            $options['date_range'] = $stmt->fetch();
            
            // White balance options
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT white_balance 
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ? AND white_balance IS NOT NULL AND white_balance != ''
                ORDER BY white_balance
            ");
            $stmt->execute([$userId]);
            $options['white_balance_options'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Exposure mode options
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT exposure_mode 
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ? AND exposure_mode IS NOT NULL AND exposure_mode != ''
                ORDER BY exposure_mode
            ");
            $stmt->execute([$userId]);
            $options['exposure_mode_options'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Software options
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT software 
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ? AND software IS NOT NULL AND software != ''
                ORDER BY software
            ");
            $stmt->execute([$userId]);
            $options['software_options'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            return $options;
            
        } catch (PDOException $e) {
            error_log("Filter Options Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get EXIF statistics for dashboard
     */
    public function getExifStats($userId) {
        try {
            $stats = [];
            
            // Total photos with EXIF
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ?
            ");
            $stmt->execute([$userId]);
            $stats['photos_with_exif'] = $stmt->fetchColumn();
            
            // Total photos without EXIF
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count
                FROM media_files m
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE m.user_id = ? AND e.media_id IS NULL
            ");
            $stmt->execute([$userId]);
            $stats['photos_without_exif'] = $stmt->fetchColumn();
            
            // Photos with GPS
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ? AND e.gps_latitude IS NOT NULL AND e.gps_longitude IS NOT NULL
            ");
            $stmt->execute([$userId]);
            $stats['photos_with_gps'] = $stmt->fetchColumn();
            
            // Most used camera
            $stmt = $this->pdo->prepare("
                SELECT camera_make, camera_model, COUNT(*) as count
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ? AND camera_model IS NOT NULL
                GROUP BY camera_make, camera_model
                ORDER BY count DESC
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $stats['most_used_camera'] = $stmt->fetch();
            
            // Average ISO
            $stmt = $this->pdo->prepare("
                SELECT AVG(CAST(iso AS UNSIGNED)) as avg_iso
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ? AND iso IS NOT NULL AND iso != ''
            ");
            $stmt->execute([$userId]);
            $stats['average_iso'] = round($stmt->fetchColumn(), 0);
            
            // Most common aperture
            $stmt = $this->pdo->prepare("
                SELECT aperture, COUNT(*) as count
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ? AND aperture IS NOT NULL AND aperture != ''
                GROUP BY aperture
                ORDER BY count DESC
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $stats['most_common_aperture'] = $stmt->fetch();
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("EXIF Stats Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get photos by date range with EXIF data
     */
    public function getPhotosByDateRange($userId, $startDate, $endDate, $limit = 50, $offset = 0) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT m.*, e.*, 
                       CASE WHEN e.media_id IS NOT NULL THEN 1 ELSE 0 END as has_exif
                FROM media_files m
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE m.user_id = ? 
                AND (e.date_taken BETWEEN ? AND ? OR m.uploaded_at BETWEEN ? AND ?)
                ORDER BY COALESCE(e.date_taken, m.uploaded_at) DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$userId, $startDate, $endDate, $startDate, $endDate, $limit, $offset]);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Date Range Search Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get photos by camera model
     */
    public function getPhotosByCamera($userId, $cameraMake = null, $cameraModel = null, $limit = 50, $offset = 0) {
        try {
            $whereConditions = ['m.user_id = ?'];
            $params = [$userId];
            
            if ($cameraMake) {
                $whereConditions[] = 'e.camera_make LIKE ?';
                $params[] = '%' . $cameraMake . '%';
            }
            
            if ($cameraModel) {
                $whereConditions[] = 'e.camera_model LIKE ?';
                $params[] = '%' . $cameraModel . '%';
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            $stmt = $this->pdo->prepare("
                SELECT m.*, e.*, 
                       CASE WHEN e.media_id IS NOT NULL THEN 1 ELSE 0 END as has_exif
                FROM media_files m
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE $whereClause
                ORDER BY m.uploaded_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $params[] = $limit;
            $params[] = $offset;
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Camera Search Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get device statistics for analytics
     */
    public function getDeviceStats($userId) {
        try {
            // Get top devices by photo count
            $stmt = $this->pdo->prepare("
                SELECT 
                    camera_make,
                    camera_model,
                    COUNT(*) as photo_count,
                    MIN(m.uploaded_at) as first_photo,
                    MAX(m.uploaded_at) as last_photo
                FROM media_exif e
                JOIN media_files m ON e.media_id = m.id
                WHERE m.user_id = ? 
                AND (e.camera_make IS NOT NULL OR e.camera_model IS NOT NULL)
                GROUP BY e.camera_make, e.camera_model
                ORDER BY photo_count DESC
                LIMIT 10
            ");
            $stmt->execute([$userId]);
            $devices = $stmt->fetchAll();
            
            // Get file type distribution
            $stmt = $this->pdo->prepare("
                SELECT 
                    CASE 
                        WHEN mimetype LIKE 'image/jpeg' THEN 'JPEG'
                        WHEN mimetype LIKE 'image/png' THEN 'PNG'
                        WHEN mimetype LIKE 'image/webp' THEN 'WebP'
                        WHEN mimetype LIKE 'video/mp4' THEN 'MP4'
                        WHEN mimetype LIKE 'video/quicktime' THEN 'MOV'
                        ELSE 'Other'
                    END as file_type,
                    COUNT(*) as count
                FROM media_files 
                WHERE user_id = ?
                GROUP BY file_type
                ORDER BY count DESC
            ");
            $stmt->execute([$userId]);
            $fileTypes = $stmt->fetchAll();
            
            return [
                'devices' => $devices,
                'file_types' => $fileTypes
            ];
            
        } catch (PDOException $e) {
            error_log("Device Stats Error: " . $e->getMessage());
            return [
                'devices' => [],
                'file_types' => []
            ];
        }
    }
}
?> 