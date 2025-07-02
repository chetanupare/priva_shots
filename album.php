<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/AlbumManager.php';

$token = $_GET['token'] ?? '';
$albumId = $_GET['id'] ?? '';

// Auth check
$auth = new Auth();
$user = $auth->getCurrentUser($token);
if (!$user) {
    header('Location: index.php');
    exit;
}

$albumManager = new AlbumManager();
$albumData = $albumManager->getAlbumDetails($albumId, $user['id']);

if (!$albumData['success']) {
    header('Location: albums.php?token=' . urlencode($token));
    exit;
}

$album = $albumData['album'];
$media = $albumData['media'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($album['name']) ?> - PrivaShots</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/header.css">
    <style>
        .album-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .album-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: translate(50px, -50px);
        }
        
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 8px;
        }
        
        .photo-item {
            aspect-ratio: 1;
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .photo-item:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s ease;
        }
        
        .photo-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
            color: white;
            padding: 8px;
            font-size: 0.75rem;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .photo-item:hover .photo-overlay {
            opacity: 1;
        }
        
        .modal-backdrop {
            backdrop-filter: blur(8px);
        }
        
        .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .photo-detail-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 2rem;
        }
        
        @media (max-width: 1024px) {
            .photo-detail-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #a0aec0;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>
</head>
<body class="bg-gray-50" x-data="albumApp()" x-init="init()">
    <?php include 'header.php'; ?>
    
    <!-- Album Header -->
    <div class="album-header px-6 py-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <a href="albums.php?token=<?= urlencode($token) ?>" class="text-white/80 hover:text-white transition-colors">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($album['name']) ?></h1>
                        <p class="text-white/80"><?= htmlspecialchars($album['description'] ?? 'No description') ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div class="text-2xl font-bold"><?= number_format($album['media_count']) ?></div>
                        <div class="text-white/80 text-sm">photos</div>
                    </div>
                    <div class="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-folder text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Album Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white/10 rounded-lg p-4">
                    <div class="text-sm text-white/80">Type</div>
                    <div class="font-semibold"><?= ucfirst($album['type']) ?> Album</div>
                </div>
                <div class="bg-white/10 rounded-lg p-4">
                    <div class="text-sm text-white/80">Created</div>
                    <div class="font-semibold"><?= date('M j, Y', strtotime($album['created_at'])) ?></div>
                </div>
                <div class="bg-white/10 rounded-lg p-4">
                    <div class="text-sm text-white/80">Size</div>
                    <div class="font-semibold"><?= number_format($album['total_size'] / (1024 * 1024), 1) ?> MB</div>
                </div>
                <div class="bg-white/10 rounded-lg p-4">
                    <div class="text-sm text-white/80">Date Range</div>
                    <div class="font-semibold">
                        <?php if ($album['earliest_date'] && $album['latest_date']): ?>
                            <?= date('M j', strtotime($album['earliest_date'])) ?> - <?= date('M j, Y', strtotime($album['latest_date'])) ?>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Album Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (empty($media)): ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-images"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No photos in this album</h3>
            <p class="text-gray-600 mb-6">This album is empty. Add some photos to get started.</p>
            <a href="dashboard.php?token=<?= urlencode($token) ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Add Photos
            </a>
        </div>
        <?php else: ?>
        <!-- Photo Grid -->
        <div class="photo-grid">
            <?php foreach ($media as $item): ?>
            <div class="photo-item" @click="openPhotoModal(<?= htmlspecialchars(json_encode($item)) ?>)">
                <img src="<?= htmlspecialchars($item['download_url']) ?>&token=<?= urlencode($token) ?>" 
                     alt="<?= htmlspecialchars($item['original_filename']) ?>"
                     loading="lazy"
                     onerror="this.style.display='none'">
                <div class="photo-overlay">
                    <div class="font-medium"><?= htmlspecialchars($item['original_filename']) ?></div>
                    <div class="text-xs opacity-80">
                        <?php if ($item['has_exif'] && $item['date_taken']): ?>
                            <?= date('M j, Y', strtotime($item['date_taken'])) ?>
                        <?php else: ?>
                            <?= date('M j, Y', strtotime($item['uploaded_at'])) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Photo Modal -->
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 modal-backdrop"
         @click="closeModal()">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl shadow-2xl modal-content max-w-6xl w-full"
                 @click.stop>
                
                <div class="photo-detail-grid">
                    <!-- Photo Display -->
                    <div class="p-6">
                        <img :src="selectedPhoto.download_url + '&token=' + encodeURIComponent(token)" 
                             :alt="selectedPhoto.original_filename"
                             class="w-full h-auto rounded-lg shadow-lg">
                    </div>
                    
                    <!-- Photo Details -->
                    <div class="p-6 bg-gray-50 rounded-r-2xl">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-900" x-text="selectedPhoto.original_filename"></h3>
                            <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="stats-card">
                                <h4 class="font-semibold text-gray-900 mb-3">File Information</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Size:</span>
                                        <span class="font-medium" x-text="formatFileSize(selectedPhoto.filesize)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Type:</span>
                                        <span class="font-medium" x-text="selectedPhoto.mimetype"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Uploaded:</span>
                                        <span class="font-medium" x-text="formatDate(selectedPhoto.uploaded_at)"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div x-show="selectedPhoto.has_exif" class="stats-card">
                                <h4 class="font-semibold text-gray-900 mb-3">Photo Details</h4>
                                <div class="space-y-2 text-sm">
                                    <div x-show="selectedPhoto.date_taken" class="flex justify-between">
                                        <span class="text-gray-600">Date Taken:</span>
                                        <span class="font-medium" x-text="formatDate(selectedPhoto.date_taken)"></span>
                                    </div>
                                    <div x-show="selectedPhoto.camera_make" class="flex justify-between">
                                        <span class="text-gray-600">Camera:</span>
                                        <span class="font-medium" x-text="selectedPhoto.camera_make + ' ' + (selectedPhoto.camera_model || '')"></span>
                                    </div>
                                    <div x-show="selectedPhoto.iso" class="flex justify-between">
                                        <span class="text-gray-600">ISO:</span>
                                        <span class="font-medium" x-text="selectedPhoto.iso"></span>
                                    </div>
                                    <div x-show="selectedPhoto.aperture" class="flex justify-between">
                                        <span class="text-gray-600">Aperture:</span>
                                        <span class="font-medium" x-text="selectedPhoto.aperture"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex space-x-2">
                                <a :href="selectedPhoto.download_url + '&token=' + encodeURIComponent(token)" 
                                   download
                                   class="btn btn-primary flex-1">
                                    <i class="fas fa-download"></i>
                                    Download
                                </a>
                                <button @click="removeFromAlbum(selectedPhoto.id)" 
                                        class="btn btn-secondary">
                                    <i class="fas fa-trash"></i>
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function albumApp() {
            return {
                token: localStorage.getItem('auth_token') || new URLSearchParams(window.location.search).get('token'),
                showModal: false,
                selectedPhoto: {},
                
                init() {
                    // Initialize the app
                },
                
                openPhotoModal(photo) {
                    this.selectedPhoto = photo;
                    this.showModal = true;
                    document.body.style.overflow = 'hidden';
                },
                
                closeModal() {
                    this.showModal = false;
                    document.body.style.overflow = 'auto';
                },
                
                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },
                
                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    return new Date(dateString).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                },
                
                removeFromAlbum(mediaId) {
                    if (confirm('Are you sure you want to remove this photo from the album?')) {
                        const token = localStorage.getItem('auth_token') || new URLSearchParams(window.location.search).get('token');
                        const albumId = new URLSearchParams(window.location.search).get('id');
                        
                        try {
                            fetch('api/index.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    action: 'remove_from_album',
                                    album_id: albumId,
                                    media_id: mediaId,
                                    token: token
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.closeModal();
                                    location.reload();
                                } else {
                                    alert('Failed to remove photo: ' + data.message);
                                }
                            })
                        } catch (error) {
                            // Error loading album data
                        }
                    }
                }
            }
        }
    </script>
</body>
</html> 