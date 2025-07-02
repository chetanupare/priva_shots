const CACHE_NAME = 'cloudphoto-v1';
const STATIC_CACHE = 'cloudphoto-static-v1';
const IMAGE_CACHE = 'cloudphoto-images-v1';

// Files to cache for offline functionality
const STATIC_FILES = [
    '/',
    '/index.php',
    '/dashboard.php',
    '/timeline.php',
    '/login.php',
    '/api/router.php',
    'https://cdn.tailwindcss.com',
    'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Install event - cache static files
self.addEventListener('install', event => {
    console.log('Service Worker installing...');
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('Caching static files');
                return cache.addAll(STATIC_FILES);
            })
            .then(() => {
                console.log('Static files cached successfully');
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('Failed to cache static files:', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    console.log('Service Worker activating...');
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== STATIC_CACHE && cacheName !== IMAGE_CACHE) {
                            console.log('Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker activated');
                return self.clients.claim();
            })
    );
});

// Fetch event - handle requests
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Handle API requests
    if (url.pathname.includes('/api/')) {
        event.respondWith(handleApiRequest(request));
        return;
    }

    // Handle image requests
    if (isImageRequest(request)) {
        event.respondWith(handleImageRequest(request));
        return;
    }

    // Handle static file requests
    if (isStaticRequest(request)) {
        event.respondWith(handleStaticRequest(request));
        return;
    }

    // Default: network first, fallback to cache
    event.respondWith(
        fetch(request)
            .then(response => {
                // Cache successful responses
                if (response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(STATIC_CACHE)
                        .then(cache => cache.put(request, responseClone));
                }
                return response;
            })
            .catch(() => {
                return caches.match(request);
            })
    );
});

// Handle API requests
async function handleApiRequest(request) {
    try {
        // Try network first for API requests
        const response = await fetch(request);
        
        // Cache successful API responses (except for large data)
        if (response.status === 200 && response.headers.get('content-type')?.includes('application/json')) {
            const responseClone = response.clone();
            const data = await responseClone.json();
            
            // Only cache small API responses
            if (JSON.stringify(data).length < 100000) {
                caches.open(STATIC_CACHE)
                    .then(cache => cache.put(request, responseClone));
            }
        }
        
        return response;
    } catch (error) {
        // Fallback to cached API response
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline response for API requests
        return new Response(JSON.stringify({
            success: false,
            message: 'You are offline. Please check your connection.',
            offline: true
        }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

// Handle image requests
async function handleImageRequest(request) {
    try {
        // Check cache first for images
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            // Return cached image immediately
            return cachedResponse;
        }

        // Try network
        const response = await fetch(request);
        
        if (response.status === 200) {
            // Cache the image
            const responseClone = response.clone();
            caches.open(IMAGE_CACHE)
                .then(cache => {
                    cache.put(request, responseClone);
                    
                    // Limit cache size (keep only 100 images)
                    cache.keys().then(keys => {
                        if (keys.length > 100) {
                            cache.delete(keys[0]);
                        }
                    });
                });
        }
        
        return response;
    } catch (error) {
        // Return placeholder image for offline
        return new Response(
            `<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
                <rect width="200" height="200" fill="#f3f4f6"/>
                <text x="100" y="100" text-anchor="middle" fill="#9ca3af" font-family="Arial" font-size="14">
                    Image not available offline
                </text>
            </svg>`,
            {
                headers: { 'Content-Type': 'image/svg+xml' }
            }
        );
    }
}

// Handle static file requests
async function handleStaticRequest(request) {
    try {
        // Check cache first
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }

        // Try network
        const response = await fetch(request);
        
        if (response.status === 200) {
            // Cache the response
            const responseClone = response.clone();
            caches.open(STATIC_CACHE)
                .then(cache => cache.put(request, responseClone));
        }
        
        return response;
    } catch (error) {
        // Return offline page
        if (request.destination === 'document') {
            return caches.match('/offline.html');
        }
        
        return new Response('Offline', { status: 503 });
    }
}

// Helper functions
function isImageRequest(request) {
    return request.destination === 'image' || 
           request.url.match(/\.(jpg|jpeg|png|gif|webp|svg|ico)$/i) ||
           request.url.includes('download.php');
}

function isStaticRequest(request) {
    return request.destination === 'document' ||
           request.destination === 'script' ||
           request.destination === 'style' ||
           request.url.includes('fonts.googleapis.com') ||
           request.url.includes('cdn.tailwindcss.com') ||
           request.url.includes('alpinejs') ||
           request.url.includes('font-awesome');
}

// Background sync for offline uploads
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

async function doBackgroundSync() {
    try {
        // Get stored uploads from IndexedDB
        const uploads = await getStoredUploads();
        
        for (const upload of uploads) {
            try {
                await uploadFile(upload);
                await removeStoredUpload(upload.id);
            } catch (error) {
                console.error('Background sync failed for upload:', upload.id, error);
            }
        }
    } catch (error) {
        console.error('Background sync failed:', error);
    }
}

// Push notifications
self.addEventListener('push', event => {
    const options = {
        body: event.data ? event.data.text() : 'New photo uploaded!',
        icon: '/icon-192x192.png',
        badge: '/badge-72x72.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'View Photos',
                icon: '/icon-192x192.png'
            },
            {
                action: 'close',
                title: 'Close',
                icon: '/icon-192x192.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification('CloudPhoto', options)
    );
});

// Notification click
self.addEventListener('notificationclick', event => {
    event.notification.close();

    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('/dashboard.php')
        );
    }
});

// IndexedDB helpers for offline uploads
async function getStoredUploads() {
    // Implementation would depend on your IndexedDB setup
    return [];
}

async function uploadFile(upload) {
    // Implementation for uploading stored files
    const formData = new FormData();
    formData.append('action', 'upload-media');
    formData.append('file', upload.file);
    formData.append('timestamp', upload.timestamp);
    
    const response = await fetch('/api/router.php', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${upload.token}`
        },
        body: formData
    });
    
    return response.json();
}

async function removeStoredUpload(id) {
    // Implementation for removing stored upload
}

// Message handling
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'CACHE_IMAGE') {
        event.waitUntil(
            caches.open(IMAGE_CACHE)
                .then(cache => cache.add(event.data.url))
        );
    }
}); 