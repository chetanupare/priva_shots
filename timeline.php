<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline - PrivaShots</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/header.css">

    <style>
        .timeline-container {
            scroll-behavior: smooth;
        }
        
        .timeline-year {
            scroll-margin-top: 100px;
        }
        
        .timeline-month {
            scroll-margin-top: 80px;
        }
        
        .timeline-day {
            scroll-margin-top: 60px;
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
        
        .photo-item.loading img {
            opacity: 0;
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
        
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        .navigation-sidebar {
            width: 280px;
            transition: transform 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .navigation-sidebar {
                transform: translateX(-100%);
            }
            
            .navigation-sidebar.open {
                transform: translateX(0);
            }
        }
        
        .scroll-indicator {
            position: fixed;
            top: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            z-index: 1000;
            transition: width 0.1s ease;
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
        
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        
        @keyframes fadein {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-fadein {
            animation: fadein 1.2s forwards;
        }
        .photo-grid .photo-item, .space-y-3 > .flex.items-center, .w-full.text-left.p-4.rounded-xl.border {
            transition: box-shadow 0.3s cubic-bezier(.4,2,.6,1), transform 0.3s cubic-bezier(.4,2,.6,1), opacity 0.5s;
        }
        .photo-grid .photo-item:hover, .space-y-3 > .flex.items-center:hover, .w-full.text-left.p-4.rounded-xl.border:hover {
            box-shadow: 0 8px 32px rgba(80, 72, 229, 0.15), 0 1.5px 6px rgba(80, 72, 229, 0.08);
            transform: translateY(-4px) scale(1.03);
            opacity: 0.97;
        }
    </style>
</head>
<body class="bg-gray-50" x-data="timelineApp()" x-init="init()">
    <?php include 'header.php'; ?>
    
    <!-- Scroll Progress Indicator -->
    <div class="scroll-indicator" :style="`width: ${scrollProgress}%`"></div>
    
    <!-- Google Photos Style Timeline -->
    <div class="flex h-screen">
        <!-- Timeline Sidebar -->
        <div class="w-80 bg-white border-r border-gray-200 flex flex-col hidden lg:flex">
            <!-- Sidebar Header -->
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50 flex items-center space-x-3">
                <h3 class="text-xl font-bold text-gray-900">Timeline</h3>
            </div>

            <!-- Search and Filters -->
            <div class="p-4 border-b border-gray-100">
                <div class="relative mb-4">
                    <input type="text" 
                           x-model="searchQuery" 
                           @input="filterTimeline"
                           placeholder="Search photos..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                
                <!-- View Options -->
                <div class="flex space-x-2 mb-4">
                    <button @click="setViewMode('grid')" 
                            :class="viewMode === 'grid' ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-700'"
                            class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-th-large mr-1"></i>
                        Grid
                    </button>
                    <button @click="setViewMode('list')" 
                            :class="viewMode === 'list' ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-700'"
                            class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-list mr-1"></i>
                        List
                    </button>
                </div>

                <!-- Quick Filters -->
                <div class="space-y-2">
                    <button @click="filterByType('all')" 
                            :class="activeFilter === 'all' ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-gray-50 text-gray-700 border-gray-200'"
                            class="w-full text-left p-3 rounded-lg border transition-colors">
                        <div class="flex items-center justify-between">
                            <span class="font-medium">All Media</span>
                            <span class="text-sm" x-text="`${totalPhotos}`"></span>
                        </div>
                    </button>
                    <button @click="filterByType('photos')" 
                            :class="activeFilter === 'photos' ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-gray-50 text-gray-700 border-gray-200'"
                            class="w-full text-left p-3 rounded-lg border transition-colors">
                        <div class="flex items-center justify-between">
                            <span class="font-medium">Photos Only</span>
                            <span class="text-sm" x-text="`${photoCount}`"></span>
                        </div>
                    </button>
                    <button @click="filterByType('videos')" 
                            :class="activeFilter === 'videos' ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-gray-50 text-gray-700 border-gray-200'"
                            class="w-full text-left p-3 rounded-lg border transition-colors">
                        <div class="flex items-center justify-between">
                            <span class="font-medium">Videos Only</span>
                            <span class="text-sm" x-text="`${videoCount}`"></span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Year Navigation -->
            <div class="flex-1 overflow-y-auto p-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Years</h4>
                <div class="space-y-2">
                    <template x-for="year in timelineNavigation" :key="year.year">
                        <button @click="scrollToYear(year.year)" 
                                :class="currentYear === year.year ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                                class="w-full text-left p-4 rounded-xl border transition-all duration-200 group">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-lg font-bold" x-text="year.year"></div>
                                <div class="text-sm opacity-75" x-text="`${year.total_photos} photos`"></div>
                            </div>
                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                <i class="fas fa-calendar"></i>
                                <span x-text="`${year.months?.length || 0} months`"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1 mt-2">
                                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-1 rounded-full transition-all duration-300"
                                     :style="`width: ${(year.total_photos / Math.max(...timelineNavigation.map(y => y.total_photos))) * 100}%`"></div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="p-4 border-t border-gray-100 bg-gray-50">
                <button @click="goToToday" 
                        class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 text-white py-3 px-4 rounded-xl hover:from-indigo-600 hover:to-purple-600 transition-all duration-300 font-medium mb-3">
                    <i class="fas fa-calendar-day mr-2"></i>
                    Go to Today
                </button>
                <button @click="exportTimeline" 
                        class="w-full bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-xl hover:bg-gray-50 transition-colors text-sm">
                    <i class="fas fa-download mr-2"></i>
                    Export Timeline
                </button>
            </div>
        </div>

        <!-- Main Timeline Content -->
        <div class="flex-1 overflow-y-auto timeline-container" @scroll="updateScrollProgress">
            <!-- Mobile Header with Toggle -->
            <div class="sticky top-0 z-40 bg-white/95 backdrop-blur-sm border-b border-gray-200 lg:hidden">
                <div class="flex items-center justify-between p-4">
                    <button @click="toggleSidebar" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-xl font-bold text-gray-900">Timeline</h1>
                    <div class="w-8"></div> <!-- Spacer for centering -->
                </div>
            </div>

            <!-- Mobile Sidebar Overlay -->
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black bg-opacity-50 z-50 lg:hidden"
                 @click="sidebarOpen = false">
            </div>

            <!-- Mobile Sidebar -->
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="fixed left-0 top-0 h-full w-80 bg-white z-50 lg:hidden overflow-y-auto">
                <!-- Mobile Sidebar Content (same as desktop but with close button) -->
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-900">Timeline</h3>
                        <button @click="sidebarOpen = false" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <i class="fas fa-images"></i>
                        <span x-text="`${totalPhotos} photos`"></span>
                        <span>•</span>
                        <span x-text="`${totalYears} years`"></span>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="p-4 border-b border-gray-100">
                    <div class="relative mb-4">
                        <input type="text" 
                               x-model="searchQuery" 
                               @input="filterTimeline"
                               placeholder="Search photos..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    
                    <!-- View Options -->
                    <div class="flex space-x-2 mb-4">
                        <button @click="setViewMode('grid')" 
                                :class="viewMode === 'grid' ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-700'"
                                class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-th-large mr-1"></i>
                            Grid
                        </button>
                        <button @click="setViewMode('list')" 
                                :class="viewMode === 'list' ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-700'"
                                class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-list mr-1"></i>
                            List
                        </button>
                    </div>

                    <!-- Quick Filters -->
                    <div class="space-y-2">
                        <button @click="filterByType('all')" 
                                :class="activeFilter === 'all' ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-gray-50 text-gray-700 border-gray-200'"
                                class="w-full text-left p-3 rounded-lg border transition-colors">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">All Media</span>
                                <span class="text-sm" x-text="`${totalPhotos}`"></span>
                            </div>
                        </button>
                        <button @click="filterByType('photos')" 
                                :class="activeFilter === 'photos' ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-gray-50 text-gray-700 border-gray-200'"
                                class="w-full text-left p-3 rounded-lg border transition-colors">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">Photos Only</span>
                                <span class="text-sm" x-text="`${photoCount}`"></span>
                            </div>
                        </button>
                        <button @click="filterByType('videos')" 
                                :class="activeFilter === 'videos' ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-gray-50 text-gray-700 border-gray-200'"
                                class="w-full text-left p-3 rounded-lg border transition-colors">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">Videos Only</span>
                                <span class="text-sm" x-text="`${videoCount}`"></span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Year Navigation -->
                <div class="flex-1 overflow-y-auto p-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Years</h4>
                    <div class="space-y-2">
                        <template x-for="year in timelineNavigation" :key="year.year">
                            <button @click="scrollToYear(year.year)" 
                                    :class="currentYear === year.year ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                                    class="w-full text-left p-4 rounded-xl border transition-all duration-200 group">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-lg font-bold" x-text="year.year"></div>
                                    <div class="text-sm opacity-75" x-text="`${year.total_photos} photos`"></div>
                                </div>
                                <div class="flex items-center space-x-2 text-xs text-gray-500">
                                    <i class="fas fa-calendar"></i>
                                    <span x-text="`${year.months?.length || 0} months`"></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1 mt-2">
                                    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-1 rounded-full transition-all duration-300"
                                         :style="`width: ${(year.total_photos / Math.max(...timelineNavigation.map(y => y.total_photos))) * 100}%`"></div>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    <button @click="goToToday" 
                            class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 text-white py-3 px-4 rounded-xl hover:from-indigo-600 hover:to-purple-600 transition-all duration-300 font-medium mb-3">
                        <i class="fas fa-calendar-day mr-2"></i>
                        Go to Today
                    </button>
                    <button @click="exportTimeline" 
                            class="w-full bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-xl hover:bg-gray-50 transition-colors text-sm">
                        <i class="fas fa-download mr-2"></i>
                        Export Timeline
                    </button>
                </div>
            </div>

            <!-- Timeline Header -->
            <div class="sticky top-0 bg-white/95 backdrop-blur-sm border-b border-gray-200 z-40 px-6 py-4 lg:block hidden">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-2xl font-bold text-gray-900">Timeline</h1>
                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                            <span x-text="`${totalPhotos} photos`"></span>
                            <span>•</span>
                            <span x-text="`${totalDays} days`"></span>
                            <span>•</span>
                            <span x-text="`${totalYears} years`"></span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button @click="goToToday" 
                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center space-x-2">
                            <i class="fas fa-calendar-day"></i>
                            <span>Today</span>
                        </button>
                        <button @click="toggleViewMode" 
                                class="bg-gray-100 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                            <i class="fas fa-th-large"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Timeline Content -->
            <div class="px-6 py-8">
                <!-- Loading State -->
                <div x-show="loading" class="space-y-8">
                    <template x-for="i in 3" :key="i">
                        <div class="space-y-4">
                            <div class="h-8 bg-gray-200 rounded loading-skeleton"></div>
                            <div class="space-y-2">
                                <template x-for="j in 2" :key="j">
                                    <div class="space-y-2">
                                        <div class="h-6 bg-gray-200 rounded loading-skeleton w-32"></div>
                                        <div class="photo-grid">
                                            <template x-for="k in 6" :key="k">
                                                <div class="photo-item loading-skeleton"></div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Timeline Content -->
                <div x-show="!loading" class="space-y-12">
                    <template x-for="year in (filteredTimelineData.length > 0 ? filteredTimelineData : timelineData)" :key="year.year">
                        <div class="timeline-year" :id="`year-${year.year}`">
                            <h2 class="text-3xl font-bold text-gray-900 mb-8" x-text="year.year"></h2>
                            
                            <div class="space-y-8">
                                <template x-for="month in year.months" :key="`${year.year}-${month.month}`">
                                    <div class="timeline-month" :id="`month-${year.year}-${month.month}`">
                                        <h3 class="text-xl font-semibold text-gray-800 mb-4" x-text="month.month_name"></h3>
                                        
                                        <div class="space-y-6">
                                            <template x-for="day in month.days" :key="`${year.year}-${month.month}-${day.day}`">
                                                <div class="timeline-day" :id="`day-${day.date}`">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <h4 class="text-lg font-medium text-gray-700" x-text="day.date_formatted"></h4>
                                                        <span class="text-sm text-gray-500" x-text="`${day.media.length} photos`"></span>
                                                    </div>
                                                    
                                                    <!-- Grid View -->
                                                    <div x-show="viewMode === 'grid'" class="photo-grid">
                                                        <template x-for="photo in day.media" :key="photo.id">
                                                            <div class="photo-item" 
                                                                 @click="openPhotoDetail(photo)"
                                                                 @mouseenter="preloadImage(photo)"
                                                                 :class="{ 'loading': !photo.loaded }">
                                                                <img :src="photo.download_url ? photo.download_url + '&token=' + token : ''" 
                                                                     :alt="photo.original_filename"
                                                                 @load="photo.loaded = true"
                                                                 loading="lazy">
                                                                
                                                                <div class="photo-overlay">
                                                                    <div x-text="photo.original_filename"></div>
                                                                    <div x-show="photo.has_exif" class="text-xs opacity-75">
                                                                        <span x-show="photo.camera_make" x-text="photo.camera_make"></span>
                                                                        <span x-show="photo.camera_model" x-text="' ' + photo.camera_model"></span>
                                                                        <span x-show="photo.iso" x-text="' • ISO ' + photo.iso"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>

                                                    <!-- List View -->
                                                    <div x-show="viewMode === 'list'" class="space-y-3">
                                                        <template x-for="photo in day.media" :key="photo.id">
                                                            <div class="flex items-center space-x-4 p-4 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer"
                                                                 @click="openPhotoDetail(photo)">
                                                                <div class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0">
                                                                    <img :src="photo.download_url ? photo.download_url + '&token=' + token : ''" 
                                                                         :alt="photo.original_filename"
                                                                         class="w-full h-full object-cover">
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <div class="font-medium text-gray-900 truncate" x-text="photo.original_filename"></div>
                                                                    <div class="text-sm text-gray-500">
                                                                        <span x-text="formatFileSize(photo.file_size || 0)"></span>
                                                                        <span x-show="photo.has_exif" class="ml-2">
                                                                            <span x-show="photo.camera_make" x-text="photo.camera_make"></span>
                                                                            <span x-show="photo.camera_model" x-text="' ' + photo.camera_model"></span>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="flex items-center space-x-2 text-gray-400">
                                                                    <i class="fas fa-image" x-show="photo.mime_type && photo.mime_type.startsWith('image/')"></i>
                                                                    <i class="fas fa-video" x-show="photo.mime_type && photo.mime_type.startsWith('video/')"></i>
                                                                    <i class="fas fa-chevron-right"></i>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Load More Button -->
                <div x-show="hasMoreData && !loading" class="text-center mt-8">
                    <button @click="loadMoreData" 
                            class="bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Load More
                    </button>
                </div>
                
                <!-- Empty State -->
                <div x-show="!loading && timelineData.length === 0" class="text-center py-12">
                    <div class="text-6xl text-gray-300 mb-4">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No photos yet</h3>
                    <p class="text-gray-600">Upload your first photo to start building your timeline</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Floating Action Button -->
    <div class="floating-action">
        <button @click="scrollToTop" 
                x-show="showScrollToTop"
                class="bg-white shadow-lg border border-gray-200 p-3 rounded-full hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-up text-gray-600"></i>
        </button>
    </div>
    
    <!-- Photo Detail Modal -->
    <div x-show="selectedPhoto" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 modal-backdrop"
         @click="selectedPhoto = null">
        
        <div class="modal-content bg-white rounded-lg shadow-2xl mx-4 my-8 max-w-6xl" @click.stop>
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900" x-text="selectedPhoto?.original_filename"></h3>
                    <button @click="selectedPhoto = null" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="photo-detail-grid">
                    <div>
                        <img :src="selectedPhoto?.download_url ? selectedPhoto.download_url + '&token=' + token : ''" 
                             :alt="selectedPhoto?.original_filename"
                             class="w-full h-auto rounded-lg">
                    </div>
                    
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">Photo Details</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">File Size:</span>
                                    <span x-text="formatFileSize(selectedPhoto?.filesize)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Uploaded:</span>
                                    <span x-text="formatDate(selectedPhoto?.uploaded_at)"></span>
                                </div>
                                <div x-show="selectedPhoto?.date_taken" class="flex justify-between">
                                    <span class="text-gray-600">Date Taken:</span>
                                    <span x-text="formatDate(selectedPhoto?.date_taken)"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div x-show="selectedPhoto?.has_exif" class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">EXIF Data</h4>
                            <div class="space-y-2 text-sm">
                                <div x-show="selectedPhoto?.camera_make || selectedPhoto?.camera_model" class="flex justify-between">
                                    <span class="text-gray-600">Camera:</span>
                                    <span x-text="`${selectedPhoto?.camera_make || ''} ${selectedPhoto?.camera_model || ''}`"></span>
                                </div>
                                <div x-show="selectedPhoto?.iso" class="flex justify-between">
                                    <span class="text-gray-600">ISO:</span>
                                    <span x-text="selectedPhoto?.iso"></span>
                                </div>
                                <div x-show="selectedPhoto?.aperture" class="flex justify-between">
                                    <span class="text-gray-600">Aperture:</span>
                                    <span x-text="'f/' + selectedPhoto?.aperture"></span>
                                </div>
                                <div x-show="selectedPhoto?.focal_length" class="flex justify-between">
                                    <span class="text-gray-600">Focal Length:</span>
                                    <span x-text="selectedPhoto?.focal_length"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button @click="downloadPhoto(selectedPhoto)" 
                                    class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-download mr-2"></i>
                                Download
                            </button>
                            <button @click="sharePhoto(selectedPhoto)" 
                                    class="flex-1 bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700 transition-colors">
                                <i class="fas fa-share mr-2"></i>
                                Share
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function timelineApp() {
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
                timelineData: [],
                timelineNavigation: [],
                selectedPhoto: null,
                loading: true,
                hasMoreData: true,
                navigationOpen: false,
                sidebarOpen: false,
                scrollProgress: 0,
                showScrollToTop: false,
                totalPhotos: 0,
                totalDays: 0,
                totalYears: 0,
                photoCount: 0,
                videoCount: 0,
                offset: 0,
                limit: 100,
                searchQuery: '',
                viewMode: 'grid',
                activeFilter: 'all',
                currentYear: null,
                filteredTimelineData: [],
                
                init() {
                    if (!this.token) {
                        window.location.href = 'index.php';
                        return;
                    }
                    
                    this.loadTimelineData();
                    this.loadTimelineNavigation();
                    this.loadTimelineStats();
                    
                    // Update navigation links with token
                    this.updateNavigationLinks();
                    
                    // Intersection Observer for lazy loading
                    this.setupIntersectionObserver();
                },
                
                updateNavigationLinks() {
                    if (this.token) {
                        // Update dashboard links
                        const dashboardLink = document.getElementById('nav-dashboard');
                        const dashboardHeaderLink = document.getElementById('nav-dashboard-header');
                        if (dashboardLink) {
                            dashboardLink.href = `dashboard.php?token=${encodeURIComponent(this.token)}`;
                        }
                        if (dashboardHeaderLink) {
                            dashboardHeaderLink.href = `dashboard.php?token=${encodeURIComponent(this.token)}`;
                        }

                        // Update timeline link
                        const timelineLink = document.getElementById('nav-timeline');
                        if (timelineLink) {
                            timelineLink.href = `timeline.php?token=${encodeURIComponent(this.token)}`;
                        }

                        // Update albums link
                        const albumsLink = document.getElementById('nav-albums');
                        if (albumsLink) {
                            albumsLink.href = `albums.php?token=${encodeURIComponent(this.token)}`;
                        }
                    }
                },
                
                async loadTimelineData() {
                    try {
                        this.loading = true;
                        const response = await fetch('api/router.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            },
                            body: JSON.stringify({
                                action: 'timeline',
                                limit: this.limit,
                                offset: this.offset
                            })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            if (this.offset === 0) {
                                this.timelineData = result.data;
                            } else {
                                this.timelineData = [...this.timelineData, ...result.data];
                            }
                            
                            this.hasMoreData = result.data.length === this.limit;
                            this.offset += this.limit;
                        }
                    } catch (error) {
                        // Failed to load timeline data
                    } finally {
                        this.loading = false;
                    }
                },
                
                async loadTimelineNavigation() {
                    try {
                        const response = await fetch('api/router.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            },
                            body: JSON.stringify({
                                action: 'timeline-navigation'
                            })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            this.timelineNavigation = result.data;
                        }
                    } catch (error) {
                        // Failed to load timeline navigation
                    }
                },
                
                async loadTimelineStats() {
                    try {
                        const response = await fetch('api/router.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            },
                            body: JSON.stringify({
                                action: 'timeline-stats'
                            })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            this.totalPhotos = result.data.total_photos || 0;
                            this.totalDays = result.data.total_days || 0;
                            this.totalYears = result.data.total_years || 0;
                            this.photoCount = result.data.photo_count || 0;
                            this.videoCount = result.data.video_count || 0;
                        }
                    } catch (error) {
                        // Failed to load timeline stats
                    }
                },
                
                loadMoreData() {
                    this.loadTimelineData();
                },
                
                scrollToYear(year) {
                    const element = document.getElementById(`year-${year}`);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth' });
                    }
                },
                
                scrollToMonth(year, month) {
                    const element = document.getElementById(`month-${year}-${month}`);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth' });
                    }
                },
                
                goToToday() {
                    const today = new Date().toISOString().split('T')[0];
                    const element = document.getElementById(`day-${today}`);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth' });
                    }
                },
                
                scrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                
                toggleNavigation() {
                    this.navigationOpen = !this.navigationOpen;
                },
                
                updateScrollProgress() {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
                    this.scrollProgress = (scrollTop / scrollHeight) * 100;
                    this.showScrollToTop = scrollTop > 500;
                },
                
                openPhotoDetail(photo) {
                    this.selectedPhoto = photo;
                },
                
                preloadImage(photo) {
                    if (!photo.loaded && photo.download_url) {
                        const img = new Image();
                        img.src = photo.download_url + '&token=' + this.token;
                        img.onload = () => {
                            photo.loaded = true;
                        };
                        img.onerror = () => {
                            console.warn('Failed to preload image:', photo.download_url);
                        };
                    }
                },
                
                setupIntersectionObserver() {
                    const options = {
                        root: null,
                        rootMargin: '50px',
                        threshold: 0.1
                    };
                    
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const img = entry.target;
                                if (img.dataset.src) {
                                    img.src = img.dataset.src;
                                    img.removeAttribute('data-src');
                                    observer.unobserve(img);
                                }
                            }
                        });
                    }, options);
                    
                    // Observe all images with data-src
                    document.querySelectorAll('img[data-src]').forEach(img => {
                        observer.observe(img);
                    });
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
                
                downloadPhoto(photo) {
                    if (photo && photo.download_url) {
                        const link = document.createElement('a');
                        link.href = photo.download_url + '&token=' + this.token;
                        link.download = photo.original_filename;
                        link.click();
                    }
                },
                
                sharePhoto(photo) {
                    if (navigator.share && photo && photo.download_url) {
                        navigator.share({
                            title: photo.original_filename,
                            url: window.location.origin + photo.download_url + '&token=' + this.token
                        });
                    } else if (photo && photo.download_url) {
                        // Fallback: copy to clipboard
                        const url = window.location.origin + photo.download_url + '&token=' + this.token;
                        navigator.clipboard.writeText(url).then(() => {
                            alert('Photo URL copied to clipboard!');
                        });
                    }
                },

                // Sidebar and Navigation Methods
                toggleSidebar() {
                    this.sidebarOpen = !this.sidebarOpen;
                },

                setViewMode(mode) {
                    this.viewMode = mode;
                    this.applyFilters();
                },

                filterByType(type) {
                    this.activeFilter = type;
                    this.applyFilters();
                },

                filterTimeline() {
                    this.applyFilters();
                },

                applyFilters() {
                    let filtered = [...this.timelineData];

                    // Apply search filter
                    if (this.searchQuery.trim()) {
                        const query = this.searchQuery.toLowerCase();
                        filtered = filtered.map(year => ({
                            ...year,
                            months: year.months.map(month => ({
                                ...month,
                                days: month.days.map(day => ({
                                    ...day,
                                    media: day.media.filter(photo => 
                                        photo.original_filename.toLowerCase().includes(query) ||
                                        (photo.camera_make && photo.camera_make.toLowerCase().includes(query)) ||
                                        (photo.camera_model && photo.camera_model.toLowerCase().includes(query))
                                    )
                                })).filter(day => day.media.length > 0)
                            })).filter(month => month.days.length > 0)
                        })).filter(year => year.months.length > 0);
                    }

                    // Apply type filter
                    if (this.activeFilter !== 'all') {
                        filtered = filtered.map(year => ({
                            ...year,
                            months: year.months.map(month => ({
                                ...month,
                                days: month.days.map(day => ({
                                    ...day,
                                    media: day.media.filter(photo => {
                                        if (this.activeFilter === 'photos') {
                                            return photo.mime_type && photo.mime_type.startsWith('image/');
                                        } else if (this.activeFilter === 'videos') {
                                            return photo.mime_type && photo.mime_type.startsWith('video/');
                                        }
                                        return true;
                                    })
                                })).filter(day => day.media.length > 0)
                            })).filter(month => month.days.length > 0)
                        })).filter(year => year.months.length > 0);
                    }

                    this.filteredTimelineData = filtered;
                },

                updateCurrentYear() {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    const yearElements = document.querySelectorAll('.timeline-year');
                    
                    for (let element of yearElements) {
                        const rect = element.getBoundingClientRect();
                        if (rect.top <= 100 && rect.bottom >= 100) {
                            const year = element.id.replace('year-', '');
                            this.currentYear = year;
                            break;
                        }
                    }
                },

                exportTimeline() {
                    const data = {
                        timeline: this.timelineData,
                        stats: {
                            totalPhotos: this.totalPhotos,
                            totalDays: this.totalDays,
                            totalYears: this.totalYears,
                            photoCount: this.photoCount,
                            videoCount: this.videoCount
                        },
                        exportDate: new Date().toISOString()
                    };

                    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `timeline-export-${new Date().toISOString().split('T')[0]}.json`;
                    link.click();
                    URL.revokeObjectURL(url);
                },

                // Enhanced scroll progress with year tracking
                updateScrollProgress() {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
                    this.scrollProgress = (scrollTop / scrollHeight) * 100;
                    this.showScrollToTop = scrollTop > 500;
                    this.updateCurrentYear();
                }
            }
        }
    </script>
</body>
</html> 