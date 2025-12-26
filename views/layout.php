<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1a1a">
    <title><?= $title ?? 'Dragnet Intelematics' ?></title>
    <style>
        .intelematics-brand {
            color: inherit;
        }
        .intelematics-brand .intel-highlight {
            color: #d4af37;
            text-shadow: 0 0 8px rgba(212, 175, 55, 0.6);
            font-weight: 700;
        }
    </style>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/public/css/app.css">
    <!-- Dragnet 1950's Theme -->
    <link rel="stylesheet" href="/public/css/dragnet-theme.css">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/public/manifest.json">
    
    <!-- iOS PWA Support -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="DragNet">
    <link rel="apple-touch-icon" href="/public/icons/icon-192.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/public/icons/icon-192.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/public/icons/icon-512.png">
    
    <!-- Android/Chrome PWA Support -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="DragNet">
    
    <!-- Windows PWA Support -->
    <meta name="msapplication-TileColor" content="#1a1a1a">
    <meta name="msapplication-TileImage" content="/public/icons/icon-192.png">
    <meta name="msapplication-config" content="/browserconfig.xml">
    
    <!-- Theme Color -->
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)">
</head>
<body>
    <?php
    require_once __DIR__ . '/../includes/tenant.php';
    require_once __DIR__ . '/../includes/auth.php';
    $context = get_tenant_context();
    $isAuthenticated = is_authenticated();
    $isAdmin = $isAuthenticated && has_role('Administrator');
    // Show nav by default if authenticated, unless explicitly set to false
    $shouldShowNav = !isset($showNav) ? $isAuthenticated : ($showNav !== false && $isAuthenticated);
    ?>
    
    <?php if ($shouldShowNav): ?>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark dragnet-nav shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="/dashboard.php">
                <i class="fas fa-shield-alt me-2"></i>Dragnet <span style="color: #d4af37; text-shadow: 0 0 8px rgba(212, 175, 55, 0.6);">Intel</span>ematics
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto d-flex flex-row align-items-center navbar-nav-collapsed">
                    <li class="nav-item">
                        <a class="nav-link nav-icon-link <?= get_current_page() === 'dashboard' ? 'active' : '' ?>" href="/dashboard.php" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Dashboard - View overview and statistics">
                            <i class="fas fa-home fa-lg"></i>
                            <span class="nav-icon-label d-lg-none">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-icon-link <?= get_current_page() === 'map' ? 'active' : '' ?>" href="/map.php" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Live Map - View real-time device locations">
                            <i class="fas fa-map fa-lg"></i>
                            <span class="nav-icon-label d-lg-none">Live Map</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-icon-link dropdown-toggle <?= in_array(get_current_page(), ['assets', 'asset_detail']) ? 'active' : '' ?>" href="#" id="assetsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle-tooltip="tooltip" data-bs-placement="bottom" title="Assets - Manage vehicles and equipment">
                            <i class="fas fa-car fa-lg"></i>
                            <span class="nav-icon-label d-lg-none">Assets</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="assetsDropdown">
                            <li><a class="dropdown-item" href="/assets.php"><i class="fas fa-list me-2"></i>All Assets</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-icon-link dropdown-toggle <?= in_array(get_current_page(), ['devices', 'device_detail']) ? 'active' : '' ?>" href="#" id="devicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Devices - Manage telematics devices">
                            <i class="fas fa-microchip fa-lg"></i>
                            <span class="nav-icon-label d-lg-none">Devices</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="devicesDropdown">
                            <li><a class="dropdown-item" href="/devices.php"><i class="fas fa-list me-2"></i>All Devices</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-icon-link <?= get_current_page() === 'alerts' ? 'active' : '' ?>" href="/alerts.php" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Alerts - View and manage system alerts">
                            <i class="fas fa-bell fa-lg"></i>
                            <?php if ($isAuthenticated): ?>
                            <?php
                            try {
                                require_once __DIR__ . '/../includes/alerts.php';
                                $unreadCount = alert_count(['acknowledged' => false], $context['tenant_id']);
                                if ($unreadCount > 0):
                                ?>
                                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle" style="font-size: 0.6rem;"><?= $unreadCount ?></span>
                                <?php endif; ?>
                            <?php } catch (Exception $e) {
                                // Silently fail if alerts can't be loaded
                            } ?>
                            <?php endif; ?>
                            <span class="nav-icon-label d-lg-none">Alerts</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-icon-link <?= get_current_page() === 'geofences' ? 'active' : '' ?>" href="/geofences.php" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Geofences - Define geographic boundaries">
                            <i class="fas fa-draw-polygon fa-lg"></i>
                            <span class="nav-icon-label d-lg-none">Geofences</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-icon-link <?= get_current_page() === 'reports' ? 'active' : '' ?>" href="/reports.php" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Reports - View analytics and reports">
                            <i class="fas fa-chart-bar fa-lg"></i>
                            <span class="nav-icon-label d-lg-none">Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-icon-link <?= get_current_page() === 'help' ? 'active' : '' ?>" href="/help.php" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Help - Documentation and support">
                            <i class="fas fa-question-circle fa-lg"></i>
                            <span class="nav-icon-label d-lg-none">Help</span>
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav d-flex flex-row align-items-center">
                    <!-- PWA Install Button -->
                    <li class="nav-item" id="pwaInstallContainer">
                        <button class="btn btn-outline-light btn-sm ms-2 nav-icon-link" id="pwaInstallButton" data-bs-toggle="modal" data-bs-target="#pwaInstallModal" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Install App - Add to home screen">
                            <i class="fas fa-download fa-lg"></i>
                            <span class="nav-icon-label d-lg-none">Install</span>
                        </button>
                    </li>
                    
                    <?php if ($isAdmin): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-icon-link dropdown-toggle <?= get_current_page() === 'admin' ? 'active' : '' ?>" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Admin - System administration">
                            <i class="fas fa-cog fa-lg"></i>
                            <span class="nav-icon-label d-lg-none">Admin</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                            <li><a class="dropdown-item" href="/admin.php"><i class="fas fa-tachometer-alt me-2"></i>Admin Panel</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/admin.php#tenants"><i class="fas fa-building me-2"></i>Tenants</a></li>
                            <li><a class="dropdown-item" href="/admin.php#users"><i class="fas fa-users me-2"></i>Users</a></li>
                            <li><a class="dropdown-item" href="/admin.php#invites"><i class="fas fa-envelope me-2"></i>Invites</a></li>
                            <li><a class="dropdown-item" href="/admin.php#devices"><i class="fas fa-microchip me-2"></i>Devices</a></li>
                            <li><a class="dropdown-item" href="/admin.php#logs"><i class="fas fa-list-alt me-2"></i>Telematics Logs</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <button class="btn btn-link nav-link text-light nav-icon-link" id="themeToggle" onclick="toggleTheme()" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Toggle Dark/Light Mode">
                            <i class="fas fa-sun fa-lg" id="themeIcon"></i>
                            <span class="nav-icon-label d-lg-none">Theme</span>
                        </button>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-icon-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle-tooltip="tooltip" data-bs-placement="bottom" title="User Menu - <?= htmlspecialchars($context['user_email'] ?? 'User') ?>">
                            <i class="fas fa-user fa-lg"></i>
                            <span class="nav-icon-label d-lg-none"><?= htmlspecialchars($context['user_email'] ?? 'User') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li class="px-3 py-2 d-lg-none"><strong><?= htmlspecialchars($context['user_email'] ?? 'User') ?></strong></li>
                            <li class="px-3 py-2 d-lg-none"><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout.php" onclick="return checkSimulatorBeforeLogout(event)"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <main class="container-fluid py-3">
        <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?= $content ?? '' ?>
    </main>
    
    <!-- PWA & Push Notification Prompt Modal -->
    <?php if ($isAuthenticated): ?>
    <div class="modal fade" id="pwaPromptModal" tabindex="-1" aria-labelledby="pwaPromptModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(180deg, var(--dragnet-dark-gray) 0%, var(--dragnet-gray) 100%); border-bottom: 2px solid var(--dragnet-badge-gold);">
                    <h5 class="modal-title" id="pwaPromptModalLabel" style="font-family: var(--dragnet-typewriter); letter-spacing: 1px; text-transform: uppercase; color: var(--dragnet-white);">
                        <i class="fas fa-bell me-2" style="color: var(--dragnet-badge-gold);"></i>Stay Connected
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="dismissPwaPrompt"></button>
                </div>
                <div class="modal-body" style="background: var(--dragnet-white); padding: 2rem;">
                    <div id="installPromptSection" style="display: none;">
                        <div class="d-flex align-items-start mb-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-mobile-alt fa-2x" style="color: var(--dragnet-badge-gold);"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 style="font-family: var(--dragnet-typewriter); font-weight: 700; color: var(--dragnet-text-primary); margin-bottom: 0.5rem;">Install Dragnet Intelematics</h6>
                                <p style="color: var(--dragnet-text-secondary); margin-bottom: 0;">Get quick access from your home screen. Works offline and loads faster.</p>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary w-100 mb-3" id="installAppButton" style="font-family: var(--dragnet-typewriter); text-transform: uppercase; letter-spacing: 1px;">
                            <i class="fas fa-download me-2"></i>Install App
                        </button>
                    </div>
                    
                    <div id="pushPromptSection" style="display: none;">
                        <div class="d-flex align-items-start mb-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-bell fa-2x" style="color: var(--dragnet-badge-gold);"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 style="font-family: var(--dragnet-typewriter); font-weight: 700; color: var(--dragnet-text-primary); margin-bottom: 0.5rem;">Enable Push Notifications</h6>
                                <p style="color: var(--dragnet-text-secondary); margin-bottom: 0;">Get instant alerts for critical events, device status changes, and important updates.</p>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success w-100 mb-3" id="enablePushButton" style="font-family: var(--dragnet-typewriter); text-transform: uppercase; letter-spacing: 1px;">
                            <i class="fas fa-bell me-2"></i>Enable Notifications
                        </button>
                    </div>
                    
                    <div id="noPromptsMessage" style="display: none; text-align: center; padding: 1rem;">
                        <i class="fas fa-check-circle fa-2x mb-2" style="color: var(--dragnet-blue);"></i>
                        <p style="color: var(--dragnet-text-secondary); margin: 0;">You're all set! All features are enabled.</p>
                    </div>
                </div>
                <div class="modal-footer" style="background: var(--dragnet-cream); border-top: 2px solid var(--dragnet-gray);">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="maybeLaterButton" style="font-family: var(--dragnet-typewriter); text-transform: uppercase; letter-spacing: 1px;">
                        Maybe Later
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- PWA Install Modal with Platform-Specific Instructions -->
    <div class="modal fade" id="pwaInstallModal" tabindex="-1" aria-labelledby="pwaInstallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(180deg, var(--dragnet-dark-gray) 0%, var(--dragnet-gray) 100%); border-bottom: 2px solid var(--dragnet-badge-gold);">
                    <h5 class="modal-title" id="pwaInstallModalLabel" style="font-family: var(--dragnet-typewriter); letter-spacing: 1px; text-transform: uppercase; color: var(--dragnet-white);">
                        <i class="fas fa-mobile-alt me-2" style="color: var(--dragnet-badge-gold);"></i>Install Dragnet Intelematics
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="background: var(--dragnet-white); padding: 2rem;">
                    <!-- Auto-install section (Chrome/Edge/Android) -->
                    <div id="autoInstallSection" style="display: none;">
                        <div class="text-center mb-4">
                            <i class="fas fa-download fa-3x mb-3" style="color: var(--dragnet-badge-gold);"></i>
                            <h5 style="font-family: var(--dragnet-typewriter); font-weight: 700; color: var(--dragnet-text-primary);">Install Dragnet Intelematics</h5>
                            <p style="color: var(--dragnet-text-secondary);">Get quick access from your home screen. Works offline and loads faster.</p>
                        </div>
                        <button type="button" class="btn btn-primary w-100 mb-3" id="autoInstallButton" style="font-family: var(--dragnet-typewriter); text-transform: uppercase; letter-spacing: 1px; padding: 1rem;">
                            <i class="fas fa-download me-2"></i>Install Now
                        </button>
                    </div>
                    
                    <!-- Platform-specific instructions -->
                    <div id="platformInstructions" style="display: none;">
                        <div id="iosInstructions" style="display: none;">
                            <div class="alert alert-info mb-3">
                                <h6 class="alert-heading"><i class="fab fa-apple me-2"></i>Install on iOS (Safari)</h6>
                                <ol class="mb-0">
                                    <li>Tap the <strong>Share</strong> button <i class="fas fa-share-square"></i> at the bottom of the screen</li>
                                    <li>Scroll down and tap <strong>"Add to Home Screen"</strong></li>
                                    <li>Tap <strong>"Add"</strong> in the top right corner</li>
                                    <li>The app will appear on your home screen</li>
                                </ol>
                            </div>
                        </div>
                        
                        <div id="androidInstructions" style="display: none;">
                            <div class="alert alert-info mb-3">
                                <h6 class="alert-heading"><i class="fab fa-android me-2"></i>Install on Android (Chrome)</h6>
                                <ol class="mb-0">
                                    <li>Tap the <strong>Menu</strong> button <i class="fas fa-ellipsis-v"></i> (three dots) in the top right</li>
                                    <li>Tap <strong>"Add to Home screen"</strong> or <strong>"Install app"</strong></li>
                                    <li>Tap <strong>"Add"</strong> or <strong>"Install"</strong> in the popup</li>
                                    <li>The app will appear on your home screen</li>
                                </ol>
                            </div>
                        </div>
                        
                        <div id="windowsInstructions" style="display: none;">
                            <div class="alert alert-info mb-3">
                                <h6 class="alert-heading"><i class="fab fa-windows me-2"></i>Install on Windows (Edge/Chrome)</h6>
                                <ol class="mb-0">
                                    <li>Click the <strong>Install</strong> icon <i class="fas fa-plus-circle"></i> in the address bar</li>
                                    <li>Or click the <strong>Menu</strong> button <i class="fas fa-ellipsis-v"></i> and select <strong>"Apps"</strong> → <strong>"Install this site as an app"</strong></li>
                                    <li>Click <strong>"Install"</strong> in the dialog</li>
                                    <li>The app will open in its own window</li>
                                </ol>
                            </div>
                        </div>
                        
                        <div id="macosInstructions" style="display: none;">
                            <div class="alert alert-info mb-3">
                                <h6 class="alert-heading"><i class="fab fa-apple me-2"></i>Install on macOS (Safari/Chrome)</h6>
                                <h6 class="mt-3">Safari:</h6>
                                <ol class="mb-3">
                                    <li>Click <strong>File</strong> → <strong>"Add to Dock"</strong></li>
                                    <li>The app will appear in your Dock</li>
                                </ol>
                                <h6>Chrome/Edge:</h6>
                                <ol class="mb-0">
                                    <li>Click the <strong>Install</strong> icon <i class="fas fa-plus-circle"></i> in the address bar</li>
                                    <li>Or click the <strong>Menu</strong> button and select <strong>"Install Dragnet Intelematics..."</strong></li>
                                    <li>Click <strong>"Install"</strong> in the dialog</li>
                                    <li>The app will open in its own window</li>
                                </ol>
                            </div>
                        </div>
                        
                        <div id="chromeInstructions" style="display: none;">
                            <div class="alert alert-info mb-3">
                                <h6 class="alert-heading"><i class="fab fa-chrome me-2"></i>Install on Chrome/Chromium/Edge</h6>
                                <ol class="mb-0">
                                    <li>Look for the <strong>Install</strong> icon <i class="fas fa-plus-circle"></i> in the address bar</li>
                                    <li>Click it and select <strong>"Install"</strong></li>
                                    <li>Or go to <strong>Menu</strong> <i class="fas fa-ellipsis-v"></i> → <strong>"Install Dragnet Intelematics..."</strong></li>
                                    <li>The app will open in its own window</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Benefits section -->
                    <div class="mt-4 pt-4 border-top">
                        <h6 style="font-family: var(--dragnet-typewriter); font-weight: 700; color: var(--dragnet-text-primary); margin-bottom: 1rem;">
                            <i class="fas fa-star me-2" style="color: var(--dragnet-badge-gold);"></i>Benefits:
                        </h6>
                        <ul style="color: var(--dragnet-text-secondary);">
                            <li>Quick access from your home screen</li>
                            <li>Works offline with cached data</li>
                            <li>Faster loading times</li>
                            <li>GPS location tracking</li>
                            <li>Push notifications for alerts</li>
                            <li>Native app-like experience</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer" style="background: var(--dragnet-cream); border-top: 2px solid var(--dragnet-gray);">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="font-family: var(--dragnet-typewriter); text-transform: uppercase; letter-spacing: 1px;">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php if ($shouldShowNav): ?>
    <footer class="bg-light mt-5 py-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">&copy; <?= date('Y') ?> Dragnet <span style="color: #d4af37;">Intel</span>ematics. All rights reserved.</small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">Version 1.0.0</small>
                </div>
            </div>
        </div>
    </footer>
    <?php endif; ?>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/public/js/app.js"></script>
    
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Service Worker Registration & PWA Install -->
    <script>
        let deferredPrompt;
        let installButton;
        let installContainer;
        
        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(reg => {
                        console.log('Service Worker registered');
                        // Check for updates
                        reg.addEventListener('updatefound', () => {
                            const newWorker = reg.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New service worker available
                                    if (confirm('A new version is available. Reload to update?')) {
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    })
                    .catch(err => console.log('Service Worker registration failed:', err));
            });
        }
        
        // Platform detection
        function detectPlatform() {
            const userAgent = navigator.userAgent || navigator.vendor || window.opera;
            const isIOS = /iPad|iPhone|iPod/.test(userAgent) && !window.MSStream;
            const isAndroid = /android/i.test(userAgent);
            const isWindows = /windows/i.test(userAgent);
            const isMacOS = /macintosh|mac os x/i.test(userAgent);
            const isChrome = /chrome/i.test(userAgent) && !/edge/i.test(userAgent);
            const isEdge = /edge/i.test(userAgent);
            const isSafari = /safari/i.test(userAgent) && !/chrome/i.test(userAgent);
            
            return {
                isIOS,
                isAndroid,
                isWindows,
                isMacOS,
                isChrome,
                isEdge,
                isSafari,
                isMobile: isIOS || isAndroid,
                isDesktop: isWindows || isMacOS
            };
        }
        
        // Show platform-specific instructions
        function showPlatformInstructions() {
            const platform = detectPlatform();
            const instructionsDiv = document.getElementById('platformInstructions');
            const autoInstallDiv = document.getElementById('autoInstallSection');
            
            if (instructionsDiv) {
                instructionsDiv.style.display = 'block';
                autoInstallDiv.style.display = 'none';
                
                // Hide all instructions first
                document.getElementById('iosInstructions').style.display = 'none';
                document.getElementById('androidInstructions').style.display = 'none';
                document.getElementById('windowsInstructions').style.display = 'none';
                document.getElementById('macosInstructions').style.display = 'none';
                document.getElementById('chromeInstructions').style.display = 'none';
                
                // Show appropriate instructions
                if (platform.isIOS) {
                    document.getElementById('iosInstructions').style.display = 'block';
                } else if (platform.isAndroid) {
                    document.getElementById('androidInstructions').style.display = 'block';
                } else if (platform.isWindows) {
                    document.getElementById('windowsInstructions').style.display = 'block';
                } else if (platform.isMacOS) {
                    document.getElementById('macosInstructions').style.display = 'block';
                } else if (platform.isChrome || platform.isEdge) {
                    document.getElementById('chromeInstructions').style.display = 'block';
                }
            }
        }
        
        // Request permissions after installation
        async function requestPermissionsAfterInstall() {
            // Request GPS location permission
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    () => console.log('GPS permission granted'),
                    () => console.log('GPS permission denied'),
                    { timeout: 1000 }
                );
            }
            
            // Request push notification permission
            if ('Notification' in window && Notification.permission === 'default') {
                try {
                    const permission = await Notification.requestPermission();
                    if (permission === 'granted' && typeof DragNet !== 'undefined' && DragNet.subscribePush) {
                        // Get VAPID key
                        try {
                            const response = await fetch('/api/push/vapid-key.php');
                            const data = await response.json();
                            if (data.publicKey) {
                                DragNet.config.vapidPublicKey = data.publicKey;
                            }
                        } catch (e) {
                            console.log('Could not fetch VAPID key');
                        }
                        
                        // Subscribe to push notifications
                        DragNet.subscribePush().then(() => {
                            console.log('Push notifications enabled');
                        }).catch(err => {
                            console.error('Push subscription error:', err);
                        });
                    }
                } catch (err) {
                    console.error('Error requesting notification permission:', err);
                }
            }
        }
        
        // PWA Install Prompt Handler
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            // Stash the event so it can be triggered later
            deferredPrompt = e;
            // Show install button
            installContainer = document.getElementById('pwaInstallContainer');
            installButton = document.getElementById('pwaInstallButton');
            if (installContainer && installButton) {
                installContainer.style.display = 'block';
            }
        });
        
        // Handle install button click
        document.addEventListener('DOMContentLoaded', function() {
            const installButton = document.getElementById('pwaInstallButton');
            const autoInstallButton = document.getElementById('autoInstallButton');
            const installModal = document.getElementById('pwaInstallModal');
            
            if (installButton) {
                installButton.addEventListener('click', function(e) {
                    // If we have deferredPrompt, show auto-install section
                    if (deferredPrompt) {
                        document.getElementById('autoInstallSection').style.display = 'block';
                        document.getElementById('platformInstructions').style.display = 'none';
                    } else {
                        // Show platform-specific instructions
                        showPlatformInstructions();
                    }
                });
            }
            
            if (autoInstallButton) {
                autoInstallButton.addEventListener('click', async function() {
                    if (!deferredPrompt) {
                        showPlatformInstructions();
                        return;
                    }
                    
                    // Show the install prompt
                    deferredPrompt.prompt();
                    
                    // Wait for the user to respond to the prompt
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log(`User response to install prompt: ${outcome}`);
                    
                    if (outcome === 'accepted') {
                        // Request permissions after successful installation
                        setTimeout(() => {
                            requestPermissionsAfterInstall();
                        }, 1000);
                        
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(installModal);
                        if (modal) {
                            modal.hide();
                        }
                        
                        // Show success message
                        alert('App installed successfully! GPS and push notifications will be requested.');
                    }
                    
                    // Clear the deferredPrompt
                    deferredPrompt = null;
                });
            }
            
            // Show platform instructions when modal opens if no deferredPrompt
            if (installModal) {
                installModal.addEventListener('show.bs.modal', function() {
                    if (!deferredPrompt) {
                        showPlatformInstructions();
                    }
                });
            }
        });
        
        // Hide install button if app is already installed
        window.addEventListener('appinstalled', () => {
            console.log('PWA was installed');
            // Request permissions after installation
            requestPermissionsAfterInstall();
            
            if (installContainer) {
                installContainer.style.display = 'none';
            }
            deferredPrompt = null;
        });
        
        // Check if app is already installed
        const isInstalled = window.matchMedia('(display-mode: standalone)').matches || 
            window.navigator.standalone === true ||
            document.referrer.includes('android-app://');
        
        if (isInstalled) {
            // App is already installed
            if (installContainer) {
                installContainer.style.display = 'none';
            }
        } else {
            // Show install button even if beforeinstallprompt hasn't fired yet
            // (for platforms that don't support it, like iOS)
            installContainer = document.getElementById('pwaInstallContainer');
            if (installContainer) {
                installContainer.style.display = 'block';
            }
        }
        
        // PWA & Push Notification Prompt System
        (function() {
            // Check if user has dismissed prompts
            const pwaPromptDismissed = localStorage.getItem('pwaPromptDismissed');
            const pushPromptDismissed = localStorage.getItem('pushPromptDismissed');
            const promptShownToday = localStorage.getItem('promptShownDate') === new Date().toDateString();
            
            // Don't show if dismissed today
            if (pwaPromptDismissed && pushPromptDismissed && promptShownToday) {
                return;
            }
            
            // Wait a bit after page load before showing prompt
            setTimeout(() => {
                const modal = document.getElementById('pwaPromptModal');
                if (!modal) return;
                
                const installSection = document.getElementById('installPromptSection');
                const pushSection = document.getElementById('pushPromptSection');
                const noPromptsMessage = document.getElementById('noPromptsMessage');
                const installButton = document.getElementById('installAppButton');
                const enablePushButton = document.getElementById('enablePushButton');
                const dismissButton = document.getElementById('dismissPwaPrompt');
                const maybeLaterButton = document.getElementById('maybeLaterButton');
                
                let showInstall = false;
                let showPush = false;
                
                // Check if install prompt should be shown
                if (!isInstalled && deferredPrompt && !pwaPromptDismissed) {
                    showInstall = true;
                }
                
                // Check if push notification prompt should be shown
                if ('Notification' in window && Notification.permission === 'default' && !pushPromptDismissed) {
                    showPush = true;
                }
                
                // Show modal if there are prompts to show
                if (showInstall || showPush) {
                    if (showInstall) {
                        installSection.style.display = 'block';
                        installButton.addEventListener('click', async () => {
                            if (deferredPrompt) {
                                deferredPrompt.prompt();
                                const { outcome } = await deferredPrompt.userChoice;
                                console.log(`Install prompt: ${outcome}`);
                                deferredPrompt = null;
                                
                                if (outcome === 'accepted') {
                                    installSection.style.display = 'none';
                                    checkIfAllDone();
                                }
                            }
                        });
                    }
                    
                    if (showPush) {
                        pushSection.style.display = 'block';
                        enablePushButton.addEventListener('click', async () => {
                            try {
                                const permission = await Notification.requestPermission();
                                if (permission === 'granted') {
                                    // Subscribe to push notifications
                                    if (typeof DragNet !== 'undefined' && DragNet.subscribePush) {
                                        // Get VAPID key from config if available
                                        try {
                                            const response = await fetch('/api/push/vapid-key.php');
                                            const data = await response.json();
                                            if (data.publicKey) {
                                                DragNet.config.vapidPublicKey = data.publicKey;
                                            }
                                        } catch (e) {
                                            console.log('Could not fetch VAPID key, using default');
                                        }
                                        
                                        DragNet.subscribePush().then(() => {
                                            pushSection.style.display = 'none';
                                            checkIfAllDone();
                                        }).catch(err => {
                                            console.error('Push subscription error:', err);
                                            alert('Unable to complete push notification setup. Please try again later.');
                                        });
                                    } else {
                                        pushSection.style.display = 'none';
                                        checkIfAllDone();
                                    }
                                } else {
                                    alert('Notifications were blocked. You can enable them later in your browser settings.');
                                }
                            } catch (err) {
                                console.error('Error requesting notification permission:', err);
                                alert('Unable to enable notifications. Please check your browser settings.');
                            }
                        });
                    }
                    
                    // Show modal
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                    
                    // Handle dismiss buttons
                    dismissButton?.addEventListener('click', () => {
                        if (showInstall && !isInstalled) {
                            localStorage.setItem('pwaPromptDismissed', 'true');
                        }
                        if (showPush && Notification.permission === 'default') {
                            localStorage.setItem('pushPromptDismissed', 'true');
                        }
                        localStorage.setItem('promptShownDate', new Date().toDateString());
                    });
                    
                    maybeLaterButton?.addEventListener('click', () => {
                        if (showInstall && !isInstalled) {
                            localStorage.setItem('pwaPromptDismissed', 'true');
                        }
                        if (showPush && Notification.permission === 'default') {
                            localStorage.setItem('pushPromptDismissed', 'true');
                        }
                        localStorage.setItem('promptShownDate', new Date().toDateString());
                    });
                    
                    function checkIfAllDone() {
                        const installDone = isInstalled || !showInstall || installSection.style.display === 'none';
                        const pushDone = Notification.permission !== 'default' || pushSection.style.display === 'none';
                        
                        if (installDone && pushDone) {
                            installSection.style.display = 'none';
                            pushSection.style.display = 'none';
                            noPromptsMessage.style.display = 'block';
                            
                            setTimeout(() => {
                                bsModal.hide();
                            }, 2000);
                        }
                    }
                }
            }, 2000); // Show after 2 seconds
        })();
        
        // Show install instructions for browsers that don't support beforeinstallprompt
        function showInstallInstructions() {
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
            const isAndroid = /Android/.test(navigator.userAgent);
            const isChrome = /Chrome/.test(navigator.userAgent);
            const isEdge = /Edg/.test(navigator.userAgent);
            const isSafari = /Safari/.test(navigator.userAgent) && !isChrome;
            
            let instructions = '';
            
            if (isIOS && isSafari) {
                instructions = 'To install this app on your iOS device:\n\n' +
                    '1. Tap the Share button (square with arrow)\n' +
                    '2. Scroll down and tap "Add to Home Screen"\n' +
                    '3. Tap "Add" to confirm';
            } else if (isAndroid && isChrome) {
                instructions = 'To install this app on your Android device:\n\n' +
                    '1. Tap the menu (three dots) in the browser\n' +
                    '2. Tap "Install app" or "Add to Home screen"\n' +
                    '3. Tap "Install" to confirm';
            } else if (isChrome || isEdge) {
                instructions = 'To install this app:\n\n' +
                    '1. Click the install icon in the address bar\n' +
                    '2. Or go to Settings > Apps > Install this site as an app';
            } else {
                instructions = 'To install this app, look for an install option in your browser\'s menu or address bar.';
            }
            
            alert(instructions);
        }
        
        // Make toggleTheme available globally
        window.toggleTheme = function() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            const icon = document.getElementById('themeIcon');
            if (icon) {
                if (newTheme === 'dark') {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                } else {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                }
            }
        };
        
        // Initialize theme on page load
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            const icon = document.getElementById('themeIcon');
            if (icon) {
                if (savedTheme === 'dark') {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                } else {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                }
            }
        })();
        
        // Initialize Bootstrap tooltips
        $(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
        
        // Check simulator before logout
        function checkSimulatorBeforeLogout(e) {
            if (localStorage.getItem('simulatorStreaming')) {
                if (!confirm('A telemetry simulator is currently running. Stop it and logout?')) {
                    e.preventDefault();
                    return false;
                }
                // Stop simulator
                localStorage.removeItem('simulatorStreaming');
                localStorage.removeItem('simulatorPacketCount');
                localStorage.removeItem('simulatorFailedCount');
            }
            return true;
        }
        window.checkSimulatorBeforeLogout = checkSimulatorBeforeLogout;
    </script>
</body>
</html>
