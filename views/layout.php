<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0d6efd">
    <title><?= $title ?? 'DragNet Portal' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/public/css/app.css">
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="DragNet">
</head>
<body>
    <?php
    require_once __DIR__ . '/../includes/tenant.php';
    require_once __DIR__ . '/../includes/auth.php';
    $context = get_tenant_context();
    $isAuthenticated = is_authenticated();
    $isAdmin = $isAuthenticated && has_role('Administrator');
    ?>
    
    <?php if (isset($showNav) && $showNav !== false && $isAuthenticated): ?>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="/dashboard.php">
                <i class="fas fa-satellite-dish me-2"></i>DragNet Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= get_current_page() === 'dashboard' ? 'active' : '' ?>" href="/dashboard.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= get_current_page() === 'map' ? 'active' : '' ?>" href="/map.php">
                            <i class="fas fa-map me-1"></i>Live Map
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array(get_current_page(), ['assets', 'asset_detail']) ? 'active' : '' ?>" href="#" id="assetsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-car me-1"></i>Assets
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="assetsDropdown">
                            <li><a class="dropdown-item" href="/assets.php"><i class="fas fa-list me-2"></i>All Assets</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array(get_current_page(), ['devices', 'device_detail']) ? 'active' : '' ?>" href="#" id="devicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-microchip me-1"></i>Devices
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="devicesDropdown">
                            <li><a class="dropdown-item" href="/devices.php"><i class="fas fa-list me-2"></i>All Devices</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= get_current_page() === 'alerts' ? 'active' : '' ?>" href="/alerts.php">
                            <i class="fas fa-bell me-1"></i>Alerts
                            <?php if ($isAuthenticated): ?>
                            <?php
                            try {
                                require_once __DIR__ . '/../includes/alerts.php';
                                $unreadCount = alert_count(['acknowledged' => false], $context['tenant_id']);
                                if ($unreadCount > 0):
                                ?>
                                <span class="badge bg-danger ms-1"><?= $unreadCount ?></span>
                                <?php endif; ?>
                            <?php } catch (Exception $e) {
                                // Silently fail if alerts can't be loaded
                            } ?>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= get_current_page() === 'geofences' ? 'active' : '' ?>" href="/geofences.php">
                            <i class="fas fa-draw-polygon me-1"></i>Geofences
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= get_current_page() === 'reports' ? 'active' : '' ?>" href="/reports.php">
                            <i class="fas fa-chart-bar me-1"></i>Reports
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($isAdmin): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= get_current_page() === 'admin' ? 'active' : '' ?>" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog me-1"></i>Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                            <li><a class="dropdown-item" href="/admin.php"><i class="fas fa-tachometer-alt me-2"></i>Admin Panel</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/admin.php#tenants"><i class="fas fa-building me-2"></i>Tenants</a></li>
                            <li><a class="dropdown-item" href="/admin.php#users"><i class="fas fa-users me-2"></i>Users</a></li>
                            <li><a class="dropdown-item" href="/admin.php#devices"><i class="fas fa-microchip me-2"></i>Devices</a></li>
                            <li><a class="dropdown-item" href="/admin.php#logs"><i class="fas fa-list-alt me-2"></i>Telematics Logs</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-1"></i>
                            <?= htmlspecialchars($context['user_email'] ?? 'User') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="/profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
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
    
    <!-- Footer -->
    <?php if (isset($showNav) && $showNav !== false && $isAuthenticated): ?>
    <footer class="bg-light mt-5 py-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">&copy; <?= date('Y') ?> DragNet Portal. All rights reserved.</small>
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
    
    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(reg => console.log('Service Worker registered'))
                    .catch(err => console.log('Service Worker registration failed'));
            });
        }
    </script>
</body>
</html>
