/**
 * Dragnet Intelematics - Main Application JavaScript
 */

// Global app object
const DragNet = {
    config: {
        apiBase: '',
        refreshInterval: 30000
    },
    
    // Initialize push notifications
    initPush: function() {
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            navigator.serviceWorker.ready.then(function(registration) {
                registration.pushManager.getSubscription().then(function(subscription) {
                    if (!subscription) {
                        console.log('Push notifications available but not subscribed');
                    }
                });
            });
        }
    },
    
    // Subscribe to push notifications
    subscribePush: function() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.warn('Push notifications are not supported in this browser');
            return Promise.reject('Push notifications not supported');
        }
        
        // Get VAPID key if not already set
        const getVapidKey = () => {
            if (DragNet.config.vapidPublicKey) {
                return Promise.resolve(DragNet.config.vapidPublicKey);
            }
            
            return fetch('/api/push/vapid-key.php')
                .then(response => response.json())
                .then(data => {
                    if (data.publicKey) {
                        DragNet.config.vapidPublicKey = data.publicKey;
                        return data.publicKey;
                    }
                    throw new Error('VAPID key not available');
                });
        };
        
        return getVapidKey().then(vapidKey => {
            return navigator.serviceWorker.ready.then(function(registration) {
                return registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidKey)
                });
            });
        }).then(function(subscription) {
            return $.ajax({
                url: '/api/push/subscribe.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    endpoint: subscription.endpoint,
                    keys: {
                        p256dh: arrayBufferToBase64(subscription.getKey('p256dh')),
                        auth: arrayBufferToBase64(subscription.getKey('auth'))
                    },
                    platform: getPlatform()
                })
            }).then(function() {
                console.log('Subscribed to push notifications');
                return { success: true };
            });
        }).catch(function(err) {
            console.error('Push subscription failed:', err);
            throw err;
        });
    },
    
    // Show notification (enhanced for Windows/macOS)
    showNotification: function(title, body, url, options = {}) {
        if ('Notification' in window && Notification.permission === 'granted') {
            const notificationOptions = {
                body: body,
                icon: '/public/icons/icon-192.png',
                badge: '/public/icons/icon-192.png',
                data: url || '/',
                tag: options.tag || 'dragnet-alert',
                requireInteraction: options.requireInteraction || false,
                silent: options.silent || false,
                ...options
            };
            
            // Enhanced for Windows 10+ (Action Center)
            if (navigator.userAgent.indexOf('Windows') > -1) {
                notificationOptions.actions = options.actions || [
                    { action: 'view', title: 'View Details' },
                    { action: 'dismiss', title: 'Dismiss' }
                ];
            }
            
            const notification = new Notification(title, notificationOptions);
            
            // Handle notification click
            notification.onclick = function(event) {
                event.preventDefault();
                if (url) {
                    window.focus();
                    window.location.href = url;
                }
                notification.close();
            };
            
            // Auto-close after 5 seconds (unless requireInteraction is true)
            if (!notificationOptions.requireInteraction) {
                setTimeout(() => notification.close(), 5000);
            }
            
            return notification;
        } else if ('Notification' in window && Notification.permission === 'default') {
            // Request permission if not yet granted
            this.requestNotificationPermission().then(() => {
                this.showNotification(title, body, url, options);
            });
        }
    },
    
    // Request notification permission
    requestNotificationPermission: function() {
        if ('Notification' in window) {
            return Notification.requestPermission().then(function(permission) {
                if (permission === 'granted') {
                    // Subscribe to push notifications if supported
                    if ('serviceWorker' in navigator && 'PushManager' in window) {
                        DragNet.subscribePush().catch(err => {
                            console.log('Push subscription optional:', err);
                        });
                    }
                    return permission;
                }
                return permission;
            });
        }
        return Promise.resolve('unsupported');
    },
    
    // Enhanced notification for critical alerts (Windows/macOS native)
    showCriticalAlert: function(title, body, url, severity = 'warning') {
        const options = {
            requireInteraction: true,
            tag: 'critical-alert',
            actions: [
                { action: 'view', title: 'View Alert' },
                { action: 'acknowledge', title: 'Acknowledge' }
            ]
        };
        
        // Use different icons based on severity
        if (severity === 'critical') {
            options.icon = '/public/icons/icon-192.png';
            options.badge = '/public/icons/icon-192.png';
        }
        
        return this.showNotification(title, body, url, options);
    }
};

// Utility functions
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');
    
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function arrayBufferToBase64(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    for (let i = 0; i < bytes.byteLength; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    return window.btoa(binary);
}

function getPlatform() {
    const ua = navigator.userAgent;
    if (/Android/i.test(ua)) return 'android';
    if (/iPhone|iPad|iPod/i.test(ua)) return 'ios';
    if (/Windows/i.test(ua)) return 'windows';
    if (/Mac/i.test(ua)) return 'mac';
    return 'unknown';
}

// Theme Management
function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);
}

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    updateThemeIcon(theme);
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    setTheme(newTheme);
}

function updateThemeIcon(theme) {
    const icon = document.getElementById('themeIcon');
    if (icon) {
        if (theme === 'dark') {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        } else {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
    }
}

// Initialize on page load
$(document).ready(function() {
    // Initialize theme
    initTheme();
    
    // Request notification permission on first load
    if ('Notification' in window && Notification.permission === 'default') {
        // Could show a prompt here
    }
    
    // Initialize push if already granted
    if (Notification.permission === 'granted') {
        DragNet.initPush();
    }
    
    // Handle AJAX errors globally
    $(document).ajaxError(function(event, xhr) {
        if (xhr.status === 401) {
            window.location.href = '/login.php';
        } else if (xhr.status === 403) {
            alert('You do not have permission to perform this action');
        } else if (xhr.status >= 500) {
            alert('Server error. Please try again later.');
        }
    });
    
    // Setup AJAX defaults for PUT/DELETE
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            if (settings.type === 'PUT' || settings.type === 'DELETE') {
                xhr.setRequestHeader('X-HTTP-Method-Override', settings.type);
            }
        }
    });
});

