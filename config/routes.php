<?php

/**
 * Route Definitions (Procedural)
 * 
 * All routes map to function names
 */

return [
    // Public/Auth routes
    'GET /' => 'dashboard_index',
    'GET /login' => 'auth_login',
    'POST /login' => 'auth_login',
    'GET /logout' => 'auth_logout',
    'GET /auth/callback' => 'auth_callback',
    'POST /auth/callback' => 'auth_callback',
    'GET /auth/saml' => 'auth_saml',
    'GET /auth/oauth' => 'auth_oauth',
    
    // Dashboard
    'GET /dashboard' => 'dashboard_index',
    'GET /api/dashboard/widgets' => 'dashboard_get_widgets',
    
    // Live Map
    'GET /map' => 'map_index',
    'GET /api/map/devices' => 'map_get_devices',
    'GET /api/map/geofences' => 'map_get_geofences',
    
    // Assets
    'GET /assets' => 'asset_index',
    'GET /api/assets' => 'asset_list',
    'GET /assets/:id' => 'asset_show',
    'GET /api/assets/:id' => 'asset_get',
    'POST /api/assets' => 'asset_create',
    'PUT /api/assets/:id' => 'asset_update',
    'DELETE /api/assets/:id' => 'asset_delete',
    
    // Devices
    'GET /devices' => 'device_index',
    'GET /api/devices' => 'device_list',
    'GET /devices/:id' => 'device_show',
    'GET /api/devices/:id' => 'device_get',
    'GET /api/devices/:id/telemetry' => 'device_get_telemetry',
    'GET /api/devices/:id/status' => 'device_get_status',
    'POST /api/devices/register-teltonika' => 'device_register_teltonika',
    
    // Alerts
    'GET /alerts' => 'alert_index',
    'GET /api/alerts' => 'alert_list',
    'GET /api/alerts/:id' => 'alert_get',
    'POST /api/alerts/:id/acknowledge' => 'alert_acknowledge',
    'POST /api/alerts/:id/assign' => 'alert_assign',
    'GET /api/alerts/export' => 'alert_export',
    
    // Trips
    'GET /assets/:id/trips' => 'trip_index',
    'GET /api/assets/:id/trips' => 'trip_list',
    'GET /api/trips/:id' => 'trip_get',
    'GET /api/trips/:id/playback' => 'trip_get_playback',
    'GET /api/trips/:id/export' => 'trip_export',
    
    // Video
    'GET /assets/:id/video' => 'video_index',
    'GET /api/assets/:id/video' => 'video_list',
    'GET /api/video/:id' => 'video_get',
    'GET /api/video/:id/stream' => 'video_stream',
    'GET /api/video/:id/download' => 'video_download',
    
    // Geofences
    'GET /geofences' => 'geofence_index',
    'GET /api/geofences' => 'geofence_list',
    'GET /api/geofences/:id' => 'geofence_get',
    'POST /api/geofences' => 'geofence_create',
    'PUT /api/geofences/:id' => 'geofence_update',
    'DELETE /api/geofences/:id' => 'geofence_delete',
    
    // Reports
    'GET /reports' => 'report_index',
    'GET /api/reports' => 'report_list',
    'GET /api/reports/:id' => 'report_get',
    'POST /api/reports/generate' => 'report_generate',
    
    // Administration
    'GET /admin' => 'admin_index',
    'GET /admin/users' => 'admin_users',
    'GET /api/admin/users' => 'admin_list_users',
    'POST /api/admin/users' => 'admin_create_user',
    'PUT /api/admin/users/:id' => 'admin_update_user',
    'DELETE /api/admin/users/:id' => 'admin_delete_user',
    'GET /api/admin/settings' => 'admin_get_settings',
    'PUT /api/admin/settings' => 'admin_update_settings',
    
    // Push Notifications
    'POST /api/push/subscribe' => 'push_subscribe',
    'DELETE /api/push/unsubscribe' => 'push_unsubscribe',
    
    // PWA
    'GET /manifest.json' => 'pwa_manifest',
    'GET /service-worker.js' => 'pwa_service_worker',
    
    // Teltonika Device Integration
    'POST /api/teltonika/receive' => 'teltonika_receive',
    'GET /api/teltonika/receive' => 'teltonika_receive',
];
