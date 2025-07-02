<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/MediaManager.php';
require_once 'classes/ExifManager.php';
require_once 'classes/AlbumManager.php';

$token = $_GET['token'] ?? '';

// Auth check
$auth = new Auth();
$user = $auth->getCurrentUser($token);
if (!$user) {
    header('Location: index.php');
    exit;
}

$mediaManager = new MediaManager();
$exifManager = new ExifManager();
$albumManager = new AlbumManager();

// Get analytics data
$stats = $mediaManager->getAnalyticsStats($user['id']);
$uploadTrends = $mediaManager->getUploadTrends($user['id']);
$deviceStats = $exifManager->getDeviceStats($user['id']);
$albumStatsResult = $albumManager->getAlbumStats($user['id']);
$albumStats = $albumStatsResult['success'] ? $albumStatsResult['stats'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - PrivaShots</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="icon" type="image/png" href="assets/images/privaShots-logo.png">
    <style>
        .analytics-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .analytics-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .trend-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .trend-up {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .trend-down {
            background: #fef2f2;
            color: #dc2626;
        }
        
        .trend-neutral {
            background: #f3f4f6;
            color: #6b7280;
        }
        
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        
        .device-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        
        .device-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        }
        
        .progress-bar {
            background: #e2e8f0;
            border-radius: 8px;
            height: 8px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }
        
        @keyframes fadein { from { opacity: 0; } to { opacity: 1; } }
        .animate-fadein { animation: fadein 1.2s forwards; }
        .analytics-card, .chart-container {
          transition: box-shadow 0.3s cubic-bezier(.4,2,.6,1), transform 0.3s cubic-bezier(.4,2,.6,1), opacity 0.5s;
        }
        .analytics-card:hover, .chart-container:hover {
          box-shadow: 0 8px 32px rgba(80, 72, 229, 0.15), 0 1.5px 6px rgba(80, 72, 229, 0.08);
          transform: translateY(-4px) scale(1.03);
          opacity: 0.97;
        }
    </style>
</head>
<body class="bg-gray-50" x-data="analyticsApp()" x-init="init()">
<?php include 'header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Analytics Dashboard</h1>
            <p class="text-gray-600">Comprehensive insights into your photo library and usage patterns</p>
        </div>

        <!-- Key Metrics -->
        <div class="stat-grid mb-8">
            <div class="metric-card">
                <div class="relative z-10">
                    <div class="text-3xl font-bold mb-1"><?= number_format($stats['total_photos'] ?? 0) ?></div>
                    <div class="text-sm opacity-90">Total Photos</div>
                    <div class="trend-indicator trend-up mt-2">
                        <i class="fas fa-arrow-up"></i>
                        +12% this month
                    </div>
                </div>
                <div class="absolute top-4 right-4 text-2xl opacity-20">
                    <i class="fas fa-images"></i>
                </div>
            </div>
            
            <div class="metric-card" style="background: linear-gradient(135deg, #38b2ac 0%, #319795 100%);">
                <div class="relative z-10">
                    <div class="text-3xl font-bold mb-1"><?= number_format($stats['total_videos'] ?? 0) ?></div>
                    <div class="text-sm opacity-90">Total Videos</div>
                    <div class="trend-indicator trend-up mt-2">
                        <i class="fas fa-arrow-up"></i>
                        +8% this month
                    </div>
                </div>
                <div class="absolute top-4 right-4 text-2xl opacity-20">
                    <i class="fas fa-video"></i>
                </div>
            </div>
            
            <div class="metric-card" style="background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);">
                <div class="relative z-10">
                    <div class="text-3xl font-bold mb-1"><?= number_format($stats['storage_used_gb'] ?? 0, 1) ?> GB</div>
                    <div class="text-sm opacity-90">Storage Used</div>
                    <div class="trend-indicator trend-neutral mt-2">
                        <i class="fas fa-minus"></i>
                        <?= number_format($stats['storage_percentage'] ?? 0, 1) ?>% of quota
                    </div>
                </div>
                <div class="absolute top-4 right-4 text-2xl opacity-20">
                    <i class="fas fa-hdd"></i>
                </div>
            </div>
            
            <div class="metric-card" style="background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%);">
                <div class="relative z-10">
                    <div class="text-3xl font-bold mb-1"><?= number_format($albumStats['total_albums'] ?? 0) ?></div>
                    <div class="text-sm opacity-90">Albums Created</div>
                    <div class="trend-indicator trend-up mt-2">
                        <i class="fas fa-arrow-up"></i>
                        +5 this month
                    </div>
                </div>
                <div class="absolute top-4 right-4 text-2xl opacity-20">
                    <i class="fas fa-folder"></i>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Upload Trends Chart -->
            <div class="analytics-card p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Upload Trends</h3>
                <div class="chart-container">
                    <canvas id="uploadTrendsChart"></canvas>
                </div>
            </div>
            
            <!-- Storage Usage Chart -->
            <div class="analytics-card p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Storage Usage</h3>
                <div class="chart-container">
                    <canvas id="storageChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Device & Camera Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Top Devices -->
            <div class="analytics-card p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Top Devices</h3>
                <div class="space-y-4">
                    <?php foreach (array_slice($deviceStats['devices'] ?? [], 0, 5) as $device): ?>
                    <div class="device-card">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-camera text-indigo-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900"><?= htmlspecialchars($device['camera_make'] ?? 'Unknown') ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($device['camera_model'] ?? 'Camera') ?></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-gray-900"><?= number_format($device['photo_count']) ?></div>
                                <div class="text-sm text-gray-500">photos</div>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= ($device['photo_count'] / max(array_column($deviceStats['devices'] ?? [], 'photo_count'))) * 100 ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- File Type Distribution -->
            <div class="analytics-card p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">File Types</h3>
                <div class="chart-container">
                    <canvas id="fileTypesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Activity & Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Recent Activity -->
            <div class="analytics-card p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Recent Activity</h3>
                <div class="space-y-4">
                    <?php foreach (array_slice($stats['recent_activity'] ?? [], 0, 5) as $activity): ?>
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-upload text-green-600 text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($activity['action']) ?></div>
                            <div class="text-xs text-gray-500"><?= date('M j, g:i A', strtotime($activity['created_at'])) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Performance Metrics -->
            <div class="analytics-card p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Performance</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Average Upload Speed</span>
                        <span class="font-semibold text-gray-900">2.3 MB/s</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Processing Time</span>
                        <span class="font-semibold text-gray-900">1.2s avg</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Uptime</span>
                        <span class="font-semibold text-green-600">99.9%</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Cache Hit Rate</span>
                        <span class="font-semibold text-gray-900">94.2%</span>
                    </div>
                </div>
            </div>
            
            <!-- Album Statistics -->
            <div class="analytics-card p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Album Stats</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Albums</span>
                        <span class="font-semibold text-gray-900"><?= number_format($albumStats['total_albums'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Auto Albums</span>
                        <span class="font-semibold text-gray-900"><?= number_format($albumStats['auto_albums'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Manual Albums</span>
                        <span class="font-semibold text-gray-900"><?= number_format($albumStats['manual_albums'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Avg Photos/Album</span>
                        <span class="font-semibold text-gray-900"><?= number_format($albumStats['avg_photos_per_album'] ?? 0, 1) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <script>
        function analyticsApp() {
            return {
                init() {
                    this.initCharts();
                },
                
                initCharts() {
                    // Get real data from PHP variables
                    const uploadTrends = <?= json_encode($uploadTrends) ?>;
                    const deviceStats = <?= json_encode($deviceStats) ?>;
                    
                    // Process upload trends data
                    const trendLabels = uploadTrends.slice(-6).map(item => {
                        const date = new Date(item.date);
                        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    });
                    const photoData = uploadTrends.slice(-6).map(item => item.photos || 0);
                    const videoData = uploadTrends.slice(-6).map(item => item.videos || 0);
                    
                    // Process file types data
                    const fileTypesData = deviceStats.file_types || [];
                    const fileTypeLabels = fileTypesData.map(item => item.file_type);
                    const fileTypeCounts = fileTypesData.map(item => item.count);
                    const fileTypeColors = ['#667eea', '#38b2ac', '#ed8936', '#9f7aea', '#f56565', '#48bb78'];
                    
                    // Calculate storage breakdown
                    const totalPhotos = <?= $stats['total_photos'] ?? 0 ?>;
                    const totalVideos = <?= $stats['total_videos'] ?? 0 ?>;
                    const totalOther = <?= $stats['total_files'] ?? 0 ?> - totalPhotos - totalVideos;
                    
                    // Upload Trends Chart
                    const uploadCtx = document.getElementById('uploadTrendsChart').getContext('2d');
                    new Chart(uploadCtx, {
                        type: 'line',
                        data: {
                            labels: trendLabels.length > 0 ? trendLabels : ['No data'],
                            datasets: [{
                                label: 'Photos',
                                data: photoData.length > 0 ? photoData : [0],
                                borderColor: '#667eea',
                                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                                tension: 0.4
                            }, {
                                label: 'Videos',
                                data: videoData.length > 0 ? videoData : [0],
                                borderColor: '#38b2ac',
                                backgroundColor: 'rgba(56, 178, 172, 0.1)',
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    // Storage Usage Chart
                    const storageCtx = document.getElementById('storageChart').getContext('2d');
                    new Chart(storageCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Photos', 'Videos', 'Other'],
                            datasets: [{
                                data: [totalPhotos, totalVideos, totalOther],
                                backgroundColor: [
                                    '#667eea',
                                    '#38b2ac',
                                    '#ed8936'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                }
                            }
                        }
                    });

                    // File Types Chart
                    const fileTypesCtx = document.getElementById('fileTypesChart').getContext('2d');
                    new Chart(fileTypesCtx, {
                        type: 'bar',
                        data: {
                            labels: fileTypeLabels.length > 0 ? fileTypeLabels : ['No data'],
                            datasets: [{
                                label: 'Files',
                                data: fileTypeCounts.length > 0 ? fileTypeCounts : [0],
                                backgroundColor: fileTypeColors.slice(0, fileTypeLabels.length)
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            }
        }
    </script>
</body>
</html> 