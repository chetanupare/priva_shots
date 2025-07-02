<?php
require_once __DIR__ . '/../config/database.php';

class TimelineManager {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Get timeline data grouped by year, month, and day
     */
    public function getTimelineData($userId, $limit = 1000, $offset = 0) {
        try {
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
                    CASE WHEN e.media_id IS NOT NULL THEN 1 ELSE 0 END as has_exif,
                    YEAR(COALESCE(e.date_taken, m.uploaded_at)) as year,
                    MONTH(COALESCE(e.date_taken, m.uploaded_at)) as month,
                    DAY(COALESCE(e.date_taken, m.uploaded_at)) as day,
                    DATE(COALESCE(e.date_taken, m.uploaded_at)) as date
                FROM media_files m
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE m.user_id = ?
                ORDER BY COALESCE(e.date_taken, m.uploaded_at) DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$userId, $limit, $offset]);
            $results = $stmt->fetchAll();
            
            return $this->groupByTimeline($results);
            
        } catch (PDOException $e) {
            error_log("Timeline Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Group media by timeline structure
     */
    private function groupByTimeline($media) {
        $timeline = [];
        
        foreach ($media as $item) {
            $year = $item['year'];
            $month = $item['month'];
            $day = $item['day'];
            $date = $item['date'];
            
            if (!isset($timeline[$year])) {
                $timeline[$year] = [
                    'year' => $year,
                    'months' => []
                ];
            }
            
            if (!isset($timeline[$year]['months'][$month])) {
                $timeline[$year]['months'][$month] = [
                    'month' => $month,
                    'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                    'days' => []
                ];
            }
            
            if (!isset($timeline[$year]['months'][$month]['days'][$day])) {
                $timeline[$year]['months'][$month]['days'][$day] = [
                    'day' => $day,
                    'date' => $date,
                    'date_formatted' => date('l, F j, Y', strtotime($date)),
                    'media' => []
                ];
            }
            
            // Add download_url to the media item
            $item['download_url'] = BASE_URL . '/download.php?id=' . $item['id'];
            $timeline[$year]['months'][$month]['days'][$day]['media'][] = $item;
        }
        
        // Convert to indexed arrays for JSON
        return $this->convertToIndexedArrays($timeline);
    }
    
    /**
     * Convert associative arrays to indexed arrays for JSON
     */
    private function convertToIndexedArrays($timeline) {
        $result = [];
        
        foreach ($timeline as $year => $yearData) {
            $yearArray = [
                'year' => $yearData['year'],
                'months' => []
            ];
            
            foreach ($yearData['months'] as $month => $monthData) {
                $monthArray = [
                    'month' => $monthData['month'],
                    'month_name' => $monthData['month_name'],
                    'days' => []
                ];
                
                foreach ($monthData['days'] as $day => $dayData) {
                    $monthArray['days'][] = $dayData;
                }
                
                $yearArray['months'][] = $monthArray;
            }
            
            $result[] = $yearArray;
        }
        
        return $result;
    }
    
    /**
     * Get timeline data for a specific date range
     */
    public function getTimelineByDateRange($userId, $startDate, $endDate, $limit = 1000) {
        try {
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
                    CASE WHEN e.media_id IS NOT NULL THEN 1 ELSE 0 END as has_exif,
                    YEAR(COALESCE(e.date_taken, m.uploaded_at)) as year,
                    MONTH(COALESCE(e.date_taken, m.uploaded_at)) as month,
                    DAY(COALESCE(e.date_taken, m.uploaded_at)) as day,
                    DATE(COALESCE(e.date_taken, m.uploaded_at)) as date
                FROM media_files m
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE m.user_id = ? 
                AND COALESCE(e.date_taken, m.uploaded_at) BETWEEN ? AND ?
                ORDER BY COALESCE(e.date_taken, m.uploaded_at) DESC
                LIMIT ?
            ");
            
            $stmt->execute([$userId, $startDate, $endDate, $limit]);
            $results = $stmt->fetchAll();
            
            return $this->groupByTimeline($results);
            
        } catch (PDOException $e) {
            error_log("Timeline Date Range Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get timeline statistics
     */
    public function getTimelineStats($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_photos,
                    COUNT(DISTINCT DATE(COALESCE(e.date_taken, m.uploaded_at))) as total_days,
                    COUNT(DISTINCT YEAR(COALESCE(e.date_taken, m.uploaded_at))) as total_years,
                    MIN(COALESCE(e.date_taken, m.uploaded_at)) as earliest_date,
                    MAX(COALESCE(e.date_taken, m.uploaded_at)) as latest_date
                FROM media_files m
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE m.user_id = ?
            ");
            
            $stmt->execute([$userId]);
            $stats = $stmt->fetch();
            
            // Get photo and video counts
            $stmt = $this->pdo->prepare("
                SELECT 
                    mime_type,
                    COUNT(*) as count
                FROM media_files m
                WHERE m.user_id = ?
                GROUP BY mime_type
            ");
            
            $stmt->execute([$userId]);
            $typeCounts = $stmt->fetchAll();
            
            $photoCount = 0;
            $videoCount = 0;
            
            foreach ($typeCounts as $type) {
                if (strpos($type['mime_type'], 'image/') === 0) {
                    $photoCount += $type['count'];
                } elseif (strpos($type['mime_type'], 'video/') === 0) {
                    $videoCount += $type['count'];
                }
            }
            
            $stats['photo_count'] = $photoCount;
            $stats['video_count'] = $videoCount;
            
            // Get most active day
            $stmt = $this->pdo->prepare("
                SELECT 
                    DATE(COALESCE(e.date_taken, m.uploaded_at)) as date,
                    COUNT(*) as photo_count
                FROM media_files m
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE m.user_id = ?
                GROUP BY DATE(COALESCE(e.date_taken, m.uploaded_at))
                ORDER BY photo_count DESC
                LIMIT 1
            ");
            
            $stmt->execute([$userId]);
            $mostActiveDay = $stmt->fetch();
            
            $stats['most_active_day'] = $mostActiveDay;
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Timeline Stats Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get photos for a specific date
     */
    public function getPhotosByDate($userId, $date, $limit = 50) {
        try {
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
                FROM media_files m
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE m.user_id = ? 
                AND DATE(COALESCE(e.date_taken, m.uploaded_at)) = ?
                ORDER BY COALESCE(e.date_taken, m.uploaded_at) DESC
                LIMIT ?
            ");
            
            $stmt->execute([$userId, $date, $limit]);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Photos By Date Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get timeline navigation data (years and months with photo counts)
     */
    public function getTimelineNavigation($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    YEAR(COALESCE(e.date_taken, m.uploaded_at)) as year,
                    MONTH(COALESCE(e.date_taken, m.uploaded_at)) as month,
                    COUNT(*) as photo_count
                FROM media_files m
                LEFT JOIN media_exif e ON m.id = e.media_id
                WHERE m.user_id = ?
                GROUP BY YEAR(COALESCE(e.date_taken, m.uploaded_at)), MONTH(COALESCE(e.date_taken, m.uploaded_at))
                ORDER BY year DESC, month DESC
            ");
            
            $stmt->execute([$userId]);
            $results = $stmt->fetchAll();
            
            $navigation = [];
            foreach ($results as $row) {
                $year = $row['year'];
                $month = $row['month'];
                
                if (!isset($navigation[$year])) {
                    $navigation[$year] = [
                        'year' => $year,
                        'total_photos' => 0,
                        'months' => []
                    ];
                }
                
                $navigation[$year]['months'][] = [
                    'month' => $month,
                    'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                    'photo_count' => $row['photo_count']
                ];
                
                $navigation[$year]['total_photos'] += $row['photo_count'];
            }
            
            return array_values($navigation);
            
        } catch (PDOException $e) {
            error_log("Timeline Navigation Error: " . $e->getMessage());
            return [];
        }
    }
}
?> 