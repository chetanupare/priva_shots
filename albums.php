<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Albums - PrivaShots</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="icon" type="image/png" href="assets/images/privaShots-logo.png">
    <style>
        .album-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .album-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .album-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .album-cover {
            position: relative;
            aspect-ratio: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow: hidden;
        }
        
        .album-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .album-card:hover .album-cover img {
            transform: scale(1.05);
        }
        
        .album-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent, rgba(0, 0, 0, 0.6));
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: flex-end;
            padding: 1rem;
        }
        
        .album-card:hover .album-overlay {
            opacity: 1;
        }
        
        .album-info {
            color: white;
            font-size: 0.875rem;
        }
        
        .album-content {
            padding: 1.25rem;
        }
        
        .album-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .album-type-auto {
            background: #e6fffa;
            color: #38b2ac;
        }
        
        .album-type-manual {
            background: #fef5e7;
            color: #dd6b20;
        }
        
        .floating-action {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 100;
        }
        
        .modal-backdrop {
            backdrop-filter: blur(8px);
        }
        
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
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
        
        .search-bar {
            background: white;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }
        
        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .filter-chip.active {
            background: #667eea;
            color: white;
        }
        
        .filter-chip:not(.active) {
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }
        
        .filter-chip:not(.active):hover {
            background: #edf2f7;
        }
        
        @keyframes fadein { from { opacity: 0; } to { opacity: 1; } }
        .animate-fadein { animation: fadein 1.2s forwards; }
        .album-card, .album-grid .album-item, .album-action-btn {
          transition: box-shadow 0.3s cubic-bezier(.4,2,.6,1), transform 0.3s cubic-bezier(.4,2,.6,1), opacity 0.5s;
        }
        .album-card:hover, .album-grid .album-item:hover, .album-action-btn:hover {
          box-shadow: 0 8px 32px rgba(80, 72, 229, 0.15), 0 1.5px 6px rgba(80, 72, 229, 0.08);
          transform: translateY(-4px) scale(1.03);
          opacity: 0.97;
        }
    </style>
</head>
<body class="bg-gray-50" x-data="albumsApp()" x-init="init()">
<?php include 'header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Albums</p>
                        <p class="text-2xl font-bold text-gray-900" x-text="stats.total_albums || 0"></p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-folder text-indigo-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Auto Albums</p>
                        <p class="text-2xl font-bold text-gray-900" x-text="stats.auto_albums || 0"></p>
                    </div>
                    <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-magic text-teal-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Manual Albums</p>
                        <p class="text-2xl font-bold text-gray-900" x-text="stats.manual_albums || 0"></p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-edit text-orange-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Avg Photos/Album</p>
                        <p class="text-2xl font-bold text-gray-900" x-text="Math.round(stats.avg_photos_per_album || 0)"></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-images text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="mb-8">
            <div class="search-bar mb-4">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-search text-gray-400"></i>
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Search albums..."
                           class="flex-1 border-none outline-none text-gray-900 placeholder-gray-500">
                    <button @click="searchQuery = ''" 
                            x-show="searchQuery"
                            class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <button @click="activeFilter = 'all'" 
                        :class="activeFilter === 'all' ? 'filter-chip active' : 'filter-chip'">
                    <i class="fas fa-th-large"></i>
                    All Albums
                </button>
                <button @click="activeFilter = 'auto'" 
                        :class="activeFilter === 'auto' ? 'filter-chip active' : 'filter-chip'">
                    <i class="fas fa-magic"></i>
                    Auto Albums
                </button>
                <button @click="activeFilter = 'manual'" 
                        :class="activeFilter === 'manual' ? 'filter-chip active' : 'filter-chip'">
                    <i class="fas fa-edit"></i>
                    Manual Albums
                </button>
            </div>
        </div>

        <!-- Albums Grid -->
        <div x-show="loading" class="text-center py-12">
            <div class="loading-skeleton w-16 h-16 rounded-full mx-auto mb-4"></div>
            <p class="text-gray-600">Loading albums...</p>
        </div>

        <div x-show="!loading && filteredAlbums.length === 0" class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No albums found</h3>
            <p class="text-gray-600 mb-6">Create your first album or generate auto-albums from your photos</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button @click="showCreateAlbumModal = true" 
                        class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Create Album
                </button>
                <button @click="createAutoAlbums()" 
                        class="bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-magic mr-2"></i>
                    Generate Auto-Albums
                </button>
            </div>
        </div>

        <div x-show="!loading && filteredAlbums.length > 0" class="album-grid">
            <template x-for="album in filteredAlbums" :key="album.id">
                <div class="album-card" @click="openAlbum(album)">
                    <div class="album-cover">
                        <img x-show="album.cover_download_url" 
                             :src="album.cover_download_url + '&token=' + token" 
                             :alt="album.name"
                             loading="lazy">
                        <div x-show="!album.cover_download_url" 
                             class="w-full h-full flex items-center justify-center">
                            <i class="fas fa-images text-white text-4xl opacity-50"></i>
                        </div>
                        
                        <div class="album-overlay">
                            <div class="album-info">
                                <div class="font-medium" x-text="album.media_count + ' photos'"></div>
                                <div x-show="album.earliest_date && album.latest_date" 
                                     x-text="formatDateRange(album.earliest_date, album.latest_date)"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="album-content">
                        <div :class="album.type === 'auto' ? 'album-type-badge album-type-auto' : 'album-type-badge album-type-manual'">
                            <i :class="album.type === 'auto' ? 'fas fa-magic' : 'fas fa-edit'"></i>
                            <span x-text="album.type === 'auto' ? 'Auto' : 'Manual'"></span>
                        </div>
                        
                        <h3 class="font-semibold text-gray-900 mb-1" x-text="album.name"></h3>
                        
                        <p x-show="album.description" 
                           class="text-sm text-gray-600 mb-3" 
                           x-text="album.description"></p>
                        
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span x-text="formatFileSize(album.total_size)"></span>
                            <span x-text="formatDate(album.created_at)"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="floating-action">
        <button @click="showCreateAlbumModal = true" 
                class="bg-indigo-600 text-white p-4 rounded-full shadow-lg hover:bg-indigo-700 transition-colors">
            <i class="fas fa-plus text-xl"></i>
        </button>
    </div>

    <!-- Create Album Modal -->
    <div x-show="showCreateAlbumModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 modal-backdrop"
         @click="showCreateAlbumModal = false">
        
        <div class="modal-content bg-white rounded-lg shadow-2xl mx-4 my-8 max-w-md" @click.stop>
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Create New Album</h3>
                    <button @click="showCreateAlbumModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form @submit.prevent="createAlbum()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Album Name</label>
                            <input type="text" 
                                   x-model="newAlbum.name" 
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                            <textarea x-model="newAlbum.description" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" 
                                    @click="showCreateAlbumModal = false"
                                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                Create Album
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function albumsApp() {
            return {
                token: (() => {
                    let token = localStorage.getItem('cloudphoto_token');
                    
                    // Check if token is passed as URL parameter
                    const urlParams = new URLSearchParams(window.location.search);
                    const urlToken = urlParams.get('token');
                    
                    if (urlToken && !token) {
                        token = urlToken;
                        localStorage.setItem('cloudphoto_token', urlToken);
                    }
                    
                    return token;
                })(),
                albums: [],
                stats: {},
                loading: true,
                searchQuery: '',
                activeFilter: 'all',
                showCreateAlbumModal: false,
                creatingAutoAlbums: false,
                newAlbum: {
                    name: '',
                    description: ''
                },
                
                get filteredAlbums() {
                    let filtered = this.albums;
                    
                    // Apply type filter
                    if (this.activeFilter !== 'all') {
                        filtered = filtered.filter(album => album.type === this.activeFilter);
                    }
                    
                    // Apply search filter
                    if (this.searchQuery) {
                        const query = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(album => 
                            album.name.toLowerCase().includes(query) ||
                            (album.description && album.description.toLowerCase().includes(query))
                        );
                    }
                    
                    return filtered;
                },
                
                init() {
                    if (!this.token) {
                        window.location.href = 'index.php';
                        return;
                    }
                    
                    this.loadAlbums();
                    this.loadStats();
                    this.updateNavigationLinks();
                },
                
                updateNavigationLinks() {
                    if (this.token) {
                        // Update navigation links
                        const dashboardLink = document.getElementById('nav-dashboard');
                        const timelineLink = document.getElementById('nav-timeline');
                        const albumsLink = document.getElementById('nav-albums');
                        
                        if (dashboardLink) {
                            dashboardLink.href = `dashboard.php?token=${encodeURIComponent(this.token)}`;
                        }
                        if (timelineLink) {
                            timelineLink.href = `timeline.php?token=${encodeURIComponent(this.token)}`;
                        }
                        if (albumsLink) {
                            albumsLink.href = `albums.php?token=${encodeURIComponent(this.token)}`;
                        }
                    }
                },
                
                async loadAlbums() {
                    try {
                        this.loading = true;
                        const response = await fetch('api/router.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            },
                            body: JSON.stringify({
                                action: 'list-albums'
                            })
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            this.albums = result.albums;
                        }
                    } catch (error) {
                        // Failed to load albums
                    } finally {
                        this.loading = false;
                    }
                },
                
                async loadStats() {
                    try {
                        const response = await fetch('api/router.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            },
                            body: JSON.stringify({
                                action: 'album-stats'
                            })
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            this.stats = result.stats;
                        }
                    } catch (error) {
                        // Failed to load stats
                    }
                },
                
                async createAlbum() {
                    if (!this.newAlbum.name.trim()) return;
                    
                    try {
                        const response = await fetch('api/router.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            },
                            body: JSON.stringify({
                                action: 'create-album',
                                name: this.newAlbum.name,
                                description: this.newAlbum.description || null
                            })
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            this.showCreateAlbumModal = false;
                            this.newAlbum = { name: '', description: '' };
                            this.loadAlbums();
                            this.loadStats();
                        } else {
                            alert('Failed to create album: ' + result.message);
                        }
                    } catch (error) {
                        alert('Failed to create album');
                    }
                },
                
                async createAutoAlbums() {
                    try {
                        this.creatingAutoAlbums = true;
                        const response = await fetch('api/router.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            },
                            body: JSON.stringify({
                                action: 'create-auto-albums'
                            })
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            this.loadAlbums();
                            this.loadStats();
                            alert(`Created ${result.created_albums.length} auto-albums!`);
                        } else {
                            alert('Failed to create auto-albums: ' + result.message);
                        }
                    } catch (error) {
                        alert('Failed to create auto-albums');
                    } finally {
                        this.creatingAutoAlbums = false;
                    }
                },
                
                openAlbum(album) {
                    window.location.href = `album.php?id=${album.id}&token=${encodeURIComponent(this.token)}`;
                },
                
                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },
                
                formatDate(dateString) {
                    if (!dateString) return '';
                    return new Date(dateString).toLocaleDateString();
                },
                
                formatDateRange(startDate, endDate) {
                    if (!startDate || !endDate) return '';
                    
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    
                    if (start.toDateString() === end.toDateString()) {
                        return start.toLocaleDateString();
                    } else {
                        return start.toLocaleDateString() + ' - ' + end.toLocaleDateString();
                    }
                }
            }
        }
    </script>
</body>
</html> 