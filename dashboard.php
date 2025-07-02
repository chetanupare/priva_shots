<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrivaShots Dashboard</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/dashboard-metrics.css">
    <link rel="stylesheet" href="assets/css/dashboard-actions.css">
    <link rel="stylesheet" href="assets/css/dashboard-recent.css">
    <link rel="stylesheet" href="assets/css/dashboard-upload.css">
    <link rel="stylesheet" href="assets/css/dashboard-gallery.css">
    
    <!-- Base Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #1a202c;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .search-section {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }
        
        .search-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .search-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a202c;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .search-icon {
            color: #667eea;
        }
        
        .search-input-container {
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .search-input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }
        
        .filter-toggle-btn {
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-toggle-btn:hover {
            background: #edf2f7;
            border-color: #cbd5e0;
        }
        
        .hidden {
            display: none;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="dashboard-container">
        <!-- Metrics Grid -->
        <div class="metrics-grid">
            <div class="metric-card photo">
                <div class="metric-value" id="dashboardTotalPhotos">0</div>
                <div class="metric-label">Total Photos</div>
                <div class="metric-icon"><i class="fas fa-image"></i></div>
            </div>
            <div class="metric-card album">
                <div class="metric-value" id="dashboardTotalAlbums">0</div>
                <div class="metric-label">Total Albums</div>
                <div class="metric-icon"><i class="fas fa-folder"></i></div>
            </div>
            <div class="metric-card storage">
                <div class="metric-value" id="dashboardStorageUsed">0 GB</div>
                <div class="metric-label">Storage Used</div>
                <div class="metric-icon"><i class="fas fa-database"></i></div>
            </div>
            <div class="metric-card exif">
                <div class="metric-value" id="dashboardExifFiles">0</div>
                <div class="metric-label">With EXIF Data</div>
                <div class="metric-icon"><i class="fas fa-camera"></i></div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <button class="action-btn upload" onclick="document.getElementById('fileInput').click()">
                <i class="fas fa-cloud-upload-alt"></i>
                Upload Photos
            </button>
            <a href="albums.php" class="action-btn albums">
                <i class="fas fa-images"></i>
                View Albums
            </a>
            <a href="timeline.php" class="action-btn timeline">
                <i class="fas fa-calendar-alt"></i>
                Timeline View
            </a>
        </div>
        
        <!-- Recent Photos -->
        <div class="recent-photos-section">
            <div class="recent-photos-header">
                <h2 class="recent-photos-title">Recent Photos</h2>
                <a href="timeline.php?token=" class="view-all-link" onclick="addTokenToLink(this)">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="recent-photos-grid" id="recentPhotosGrid">
                <!-- Photo cards will be loaded here -->
            </div>
        </div>
        
        <!-- Upload Section -->
        <div class="upload-section">
            <h2 class="upload-title">Upload Photos & Videos</h2>
            <div class="upload-zone" onclick="document.getElementById('fileInput').click()">
                <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="upload-text">Click to upload or drag and drop</div>
                <div class="upload-subtext">
                    <div style="margin-bottom: 0.25rem;">📸 <strong>Images:</strong> JPG, PNG, GIF, WebP, BMP, TIFF, SVG, HEIC, AVIF, RAW formats (CR2, NEF, ARW, etc.)</div>
                    <div style="margin-bottom: 0.25rem;">🎥 <strong>Videos:</strong> MP4, MOV, AVI, WebM, MKV, FLV, WMV and more</div>
                    <div style="font-size: 0.75rem; opacity: 0.7;">Maximum file size: 100MB • <a href="#" onclick="showSupportedFormats()">View all supported formats</a></div>
                </div>
            </div>
            <input type="file" id="fileInput" style="display: none;" multiple accept="image/*,video/*">
        </div>
        
        <!-- Search & Filters -->
        <div class="search-section">
            <div class="search-header">
                <h2 class="search-title">
                    <i class="fas fa-search search-icon"></i>
                    Search & Filters
                </h2>
                <button class="filter-toggle-btn" onclick="toggleAdvancedSearch()" id="toggleSearchBtn">
                    <i class="fas fa-filter"></i>
                    Show Filters
                </button>
            </div>
            <div class="search-input-container">
                <input type="text" id="quickSearch" placeholder="Search by camera, description, artist, or any EXIF data..." class="search-input" onkeyup="handleQuickSearch(event)">
                <i class="fas fa-search search-input-icon"></i>
            </div>
            <div id="advancedFilters" class="hidden">
                <!-- Advanced filters content -->
            </div>
        </div>
        
        <!-- Gallery Section -->
        <div class="gallery-section">
            <div class="gallery-header">
                <h2 class="gallery-title">Your Media</h2>
                <div class="gallery-controls">
                    <button class="gallery-filter-btn active" data-filter="all">All</button>
                    <button class="gallery-filter-btn" data-filter="image">Photos</button>
                    <button class="gallery-filter-btn" data-filter="video">Videos</button>
                    <button class="gallery-filter-btn special" onclick="showExifStats()">
                        <i class="fas fa-chart-bar"></i>
                        EXIF Stats
                    </button>
                    <a href="timeline.php" class="gallery-filter-btn timeline" onclick="addTokenToLink(this)">
                        <i class="fas fa-calendar-alt"></i>
                        Timeline
                    </a>
                </div>
            </div>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Loading your media...</p>
            </div>
            
            <div class="gallery-grid" id="gallery">
                <!-- Media items will be loaded here -->
            </div>
            
            <div class="empty-state" id="emptyState" style="display: none;">
                <div class="empty-icon">
                    <i class="fas fa-images"></i>
                </div>
                <h3>No media files yet</h3>
                <p>Upload your first photo or video to get started</p>
            </div>
        </div>
    </div>

    <!-- Image Details Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Image Details</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="modal-image-container">
                    <img id="modalImage" src="" alt="Selected image">
                </div>
                <div class="modal-details">
                    <div class="detail-row">
                        <strong>Original Name:</strong>
                        <span id="modalOriginalName"></span>
                    </div>
                    <div class="detail-row">
                        <strong>File Size:</strong>
                        <span id="modalFileSize"></span>
                    </div>
                    <div class="detail-row">
                        <strong>Dimensions:</strong>
                        <span id="modalDimensions"></span>
                    </div>
                    <div class="detail-row">
                        <strong>Upload Date:</strong>
                        <span id="modalUploadDate"></span>
                    </div>
                    <div class="detail-row">
                        <strong>Camera:</strong>
                        <span id="modalCamera"></span>
                    </div>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-primary" onclick="downloadImage()">
                        <i class="fas fa-download"></i> Download
                    </button>
                    <button class="btn btn-secondary" onclick="addToAlbum()">
                        <i class="fas fa-folder-plus"></i> Add to Album
                    </button>
                    <button class="btn btn-info" onclick="showExifDetails()">
                        <i class="fas fa-camera"></i> EXIF Info
                    </button>
                    <button class="btn btn-danger" onclick="deleteImage()">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- EXIF Details Modal -->
    <div id="exifModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>EXIF Information</h3>
                <span class="close" onclick="closeExifModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="exifDetails"></div>
            </div>
        </div>
    </div>

    <!-- Album Selection Modal -->
    <div id="albumModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add to Album</h3>
                <span class="close" onclick="closeAlbumModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="albumList"></div>
                <div class="modal-actions">
                    <button class="btn btn-primary" onclick="confirmAddToAlbum()">Add to Album</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Token management
        let userToken = localStorage.getItem('auth_token') || new URLSearchParams(window.location.search).get('token');
        
        function addTokenToLink(link) {
            if (userToken) {
                const url = new URL(link.href, window.location.origin);
                url.searchParams.set('token', userToken);
                link.href = url.toString();
            }
        }
        
        // Initialize token management
        document.addEventListener('DOMContentLoaded', function() {
            // Update all links with token
            const links = document.querySelectorAll('a[href*="timeline.php"], a[href*="albums.php"]');
            links.forEach(link => addTokenToLink(link));
            
            // Load dashboard data
            loadDashboardData();
            loadRecentPhotos();
            loadGallery();
        });
        
        // Load dashboard metrics
        function loadDashboardData() {
            const headers = {
                'Authorization': 'Bearer ' + userToken,
                'Content-Type': 'application/json'
            };
            
            fetch('api/index.php?action=getDashboardStats', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({})
            })
            .then(response => {
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('dashboardTotalPhotos').textContent = data.stats.totalPhotos || 0;
                    document.getElementById('dashboardTotalAlbums').textContent = data.stats.totalAlbums || 0;
                    document.getElementById('dashboardStorageUsed').textContent = (data.stats.storageUsed || 0) + ' GB';
                    document.getElementById('dashboardExifFiles').textContent = data.stats.exifFiles || 0;
                } else {
                    // Dashboard stats error
                }
            })
            .catch(error => {
                // Error loading dashboard data
            });
        }
        
        // Load recent photos
        function loadRecentPhotos() {
            const headers = {
                'Authorization': 'Bearer ' + userToken,
                'Content-Type': 'application/json'
            };
            
            fetch('api/index.php?action=getRecentPhotos', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({})
            })
            .then(response => {
                return response.json();
            })
            .then(data => {
                if (data.success && data.photos.length > 0) {
                    const grid = document.getElementById('recentPhotosGrid');
                    grid.innerHTML = '';
                    
                    data.photos.slice(0, 10).forEach(photo => {
                        const card = document.createElement('div');
                        card.className = 'photo-card';
                        const imgUrl = photo.download_url + '&token=' + encodeURIComponent(userToken);
                        card.innerHTML = `<img src="${imgUrl}" alt="Recent photo" loading="lazy" style="cursor: pointer;">`;
                        
                        card.addEventListener('click', function(e) {
                            openImageModal(photo);
                        });
                        
                        grid.appendChild(card);
                    });
                } else {
                    // No recent photos found
                }
            })
            .catch(error => {
                // Error loading recent photos
            });
        }
        
        // Load gallery
        function loadGallery() {
            const loading = document.getElementById('loading');
            const gallery = document.getElementById('gallery');
            const emptyState = document.getElementById('emptyState');
            
            loading.style.display = 'block';
            gallery.style.display = 'none';
            emptyState.style.display = 'none';
            
            const headers = {
                'Authorization': 'Bearer ' + userToken,
                'Content-Type': 'application/json'
            };
            
            fetch('api/index.php?action=getMedia', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({})
            })
            .then(response => {
                return response.json();
            })
            .then(data => {
                loading.style.display = 'none';
                
                if (data.success && data.media.length > 0) {
                    gallery.style.display = 'grid';
                    gallery.innerHTML = '';
                    
                    data.media.forEach(item => {
                        const mediaItem = document.createElement('div');
                        mediaItem.className = 'media-item';
                        const imgUrl = item.download_url + '&token=' + encodeURIComponent(userToken);
                        mediaItem.innerHTML = `
                            <img src="${imgUrl}" alt="Media item" class="media-image" loading="lazy" style="cursor: pointer; pointer-events: auto;">
                            <div class="media-overlay" style="pointer-events: none;">
                                <div class="media-info">${item.original_filename}</div>
                            </div>
                        `;
                        
                        mediaItem.addEventListener('click', function(e) {
                            openImageModal(item);
                        });
                        
                        gallery.appendChild(mediaItem);
                    });
                } else {
                    emptyState.style.display = 'block';
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                emptyState.style.display = 'block';
            });
        }
        
        // File upload handling
        document.getElementById('fileInput').addEventListener('change', function(e) {
            const files = e.target.files;
            if (files.length > 0) {
                uploadFiles(files);
            }
        });
        
        function uploadFiles(files) {
            for (let i = 0; i < files.length; i++) {
                const formData = new FormData();
                formData.append('file', files[i]);
                fetch('api/router.php?action=upload-media', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + userToken
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadDashboardData();
                        loadRecentPhotos();
                        loadGallery();
                    } else {
                        alert('Upload failed: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Upload failed. Please try again.');
                });
            }
        }
        
        // Search functionality
        function handleQuickSearch(event) {
            const query = event.target.value;
            if (query.length > 2) {
            }
        }
        
        function toggleAdvancedSearch() {
            const filters = document.getElementById('advancedFilters');
            const btn = document.getElementById('toggleSearchBtn');
            
            if (filters.classList.contains('hidden')) {
                filters.classList.remove('hidden');
                btn.innerHTML = '<i class="fas fa-filter"></i> Hide Filters';
            } else {
                filters.classList.add('hidden');
                btn.innerHTML = '<i class="fas fa-filter"></i> Show Filters';
            }
        }
        
        // Gallery filter functionality
        document.querySelectorAll('.gallery-filter-btn[data-filter]').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.gallery-filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        function showExifStats() {
        }

        function showSupportedFormats() {
            alert('Supported formats:\n\nImages: JPG, PNG, GIF, WebP, BMP, TIFF, SVG, HEIC, AVIF, RAW (CR2, NEF, ARW, etc.)\nVideos: MP4, MOV, AVI, WebM, MKV, FLV, WMV');
        }

        // Modal functionality
        let currentImage = null;
        let selectedAlbumId = null;

        function openImageModal(imageData) {
            try {
                currentImage = imageData;
                const modal = document.getElementById('imageModal');
                
                if (!modal) {
                    alert('Modal element not found!');
                    return;
                }
                
                const modalImage = document.getElementById('modalImage');
                const modalTitle = document.getElementById('modalTitle');
                const modalOriginalName = document.getElementById('modalOriginalName');
                const modalFileSize = document.getElementById('modalFileSize');
                const modalDimensions = document.getElementById('modalDimensions');
                const modalUploadDate = document.getElementById('modalUploadDate');
                const modalCamera = document.getElementById('modalCamera');

                if (modalImage && imageData.download_url) {
                    const imgUrl = imageData.download_url + '&token=' + encodeURIComponent(userToken);
                    modalImage.src = imgUrl;
                }

                if (modalTitle) modalTitle.textContent = imageData.original_filename || 'Unknown';
                if (modalOriginalName) modalOriginalName.textContent = imageData.original_filename || 'Unknown';
                if (modalFileSize) modalFileSize.textContent = formatFileSize(imageData.filesize || 0);
                if (modalDimensions) modalDimensions.textContent = `${imageData.width || 0} × ${imageData.height || 0}`;
                if (modalUploadDate) modalUploadDate.textContent = imageData.uploaded_at ? new Date(imageData.uploaded_at).toLocaleDateString() : 'Unknown';
                if (modalCamera) modalCamera.textContent = imageData.camera_model || 'Unknown';

                modal.style.display = 'block';
            } catch (error) {
                alert('Error opening image modal: ' + error.message);
            }
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
            document.getElementById('exifModal').style.display = 'none';
            document.getElementById('albumModal').style.display = 'none';
            currentImage = null;
            selectedAlbumId = null;
        }

        function closeExifModal() {
            document.getElementById('exifModal').style.display = 'none';
        }

        function closeAlbumModal() {
            document.getElementById('albumModal').style.display = 'none';
            selectedAlbumId = null;
        }

        function downloadImage() {
            if (!currentImage) return;
            
            const link = document.createElement('a');
            link.href = currentImage.download_url + '&token=' + encodeURIComponent(userToken);
            link.download = currentImage.original_filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function deleteImage() {
            if (!currentImage) return;
            
            if (confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
                const headers = {
                    'Authorization': 'Bearer ' + userToken,
                    'Content-Type': 'application/json'
                };

                fetch('api/router.php?action=delete-media', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ id: currentImage.id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        loadDashboardData();
                        loadRecentPhotos();
                        loadGallery();
                        alert('Image deleted successfully');
                    } else {
                        alert('Failed to delete image: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    alert('Failed to delete image. Please try again.');
                });
            }
        }

        function addToAlbum() {
            if (!currentImage) return;
            
            fetch('api/router.php?action=list-albums', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + userToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const albumList = document.getElementById('albumList');
                    albumList.innerHTML = '';
                    
                    data.albums.forEach(album => {
                        const albumItem = document.createElement('div');
                        albumItem.className = 'album-item';
                        albumItem.innerHTML = `
                            <input type="radio" name="album" value="${album.id}" id="album_${album.id}">
                            <div class="album-info">
                                <div class="album-name">${album.name}</div>
                                <div class="album-count">${album.media_count} photos</div>
                            </div>
                        `;
                        albumItem.onclick = () => {
                            selectedAlbumId = album.id;
                            document.querySelectorAll('.album-item').forEach(item => item.classList.remove('selected'));
                            albumItem.classList.add('selected');
                        };
                        albumList.appendChild(albumItem);
                    });
                    
                    document.getElementById('albumModal').style.display = 'block';
                } else {
                    alert('Failed to load albums: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Load albums error:', error);
                alert('Failed to load albums. Please try again.');
            });
        }

        function confirmAddToAlbum() {
            if (!currentImage || !selectedAlbumId) {
                alert('Please select an album');
                return;
            }

            const headers = {
                'Authorization': 'Bearer ' + userToken,
                'Content-Type': 'application/json'
            };

            fetch('api/router.php?action=add-to-album', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({
                    album_id: selectedAlbumId,
                    media_id: currentImage.id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAlbumModal();
                    alert('Image added to album successfully');
                } else {
                    alert('Failed to add to album: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Add to album error:', error);
                alert('Failed to add to album. Please try again.');
            });
        }

        function showExifDetails() {
            if (!currentImage) return;
            
            const headers = {
                'Authorization': 'Bearer ' + userToken,
                'Content-Type': 'application/json'
            };

            fetch('api/router.php?action=exif-data', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({ media_id: currentImage.id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    const exifDetails = document.getElementById('exifDetails');
                    exifDetails.innerHTML = '';
                    
                    const categories = {
                        'Camera Info': ['Make', 'Model', 'Software', 'Artist'],
                        'Image Details': ['ImageWidth', 'ImageLength', 'Orientation', 'XResolution', 'YResolution'],
                        'Shooting Info': ['DateTime', 'DateTimeOriginal', 'ExposureTime', 'FNumber', 'ISO', 'FocalLength'],
                        'Location': ['GPSLatitude', 'GPSLongitude', 'GPSAltitude']
                    };
                    
                    Object.keys(categories).forEach(category => {
                        const section = document.createElement('div');
                        section.className = 'exif-section';
                        section.innerHTML = `<h4>${category}</h4>`;
                        
                        let hasData = false;
                        categories[category].forEach(key => {
                            if (data.data[key]) {
                                const item = document.createElement('div');
                                item.className = 'exif-item';
                                item.innerHTML = `
                                    <span class="exif-label">${key}:</span>
                                    <span class="exif-value">${data.data[key]}</span>
                                `;
                                section.appendChild(item);
                                hasData = true;
                            }
                        });
                        
                        if (hasData) {
                            exifDetails.appendChild(section);
                        }
                    });
                    
                    if (exifDetails.children.length === 0) {
                        exifDetails.innerHTML = '<p>No EXIF data available for this image.</p>';
                    }
                    
                    document.getElementById('exifModal').style.display = 'block';
                } else {
                    alert('No EXIF data available for this image');
                }
            })
            .catch(error => {
                console.error('EXIF error:', error);
                alert('Failed to load EXIF data. Please try again.');
            });
        }

        function formatFileSize(bytes) {
            try {
                if (bytes === 0 || !bytes) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            } catch (error) {
                return 'Unknown size';
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    closeModal();
                }
            });
        }

        // Close modals with X button
        document.querySelectorAll('.close').forEach(closeBtn => {
            closeBtn.onclick = closeModal;
        });

        // Fix tabs functionality
        function switchTab(tabName) {
            // Hide all tab content
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.style.display = 'none';
            });
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab content
            const selectedTab = document.getElementById(tabName);
            if (selectedTab) {
                selectedTab.style.display = 'block';
            }
            
            // Add active class to clicked button
            const activeButton = document.querySelector(`[onclick="switchTab('${tabName}')"]`);
            if (activeButton) {
                activeButton.classList.add('active');
            }
        }
    </script>
</body>
</html> 