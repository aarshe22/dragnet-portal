<?php

/**
 * Route Definitions
 * 
 * All routes are explicitly mapped here for clarity and maintainability.
 * Routes are tenant-scoped by default.
 */

return [
    // Public/Auth routes
    'GET /' => 'DashboardController@index',
    'GET /login' => 'AuthController@login',
    'POST /login' => 'AuthController@login',
    'GET /logout' => 'AuthController@logout',
    'GET /auth/callback' => 'AuthController@callback',
    'POST /auth/callback' => 'AuthController@callback',
    'GET /auth/saml' => 'AuthController@saml',
    'GET /auth/oauth' => 'AuthController@oauth',
    
    // Dashboard
    'GET /dashboard' => 'DashboardController@index',
    'GET /api/dashboard/widgets' => 'DashboardController@getWidgets',
    
    // Live Map
    'GET /map' => 'MapController@index',
    'GET /api/map/devices' => 'MapController@getDevices',
    'GET /api/map/geofences' => 'MapController@getGeofences',
    
    // Assets
    'GET /assets' => 'AssetController@index',
    'GET /api/assets' => 'AssetController@list',
    'GET /assets/:id' => 'AssetController@show',
    'GET /api/assets/:id' => 'AssetController@get',
    'POST /api/assets' => 'AssetController@create',
    'PUT /api/assets/:id' => 'AssetController@update',
    'DELETE /api/assets/:id' => 'AssetController@delete',
    
    // Devices
    'GET /devices' => 'DeviceController@index',
    'GET /api/devices' => 'DeviceController@list',
    'GET /devices/:id' => 'DeviceController@show',
    'GET /api/devices/:id' => 'DeviceController@get',
    'GET /api/devices/:id/telemetry' => 'DeviceController@getTelemetry',
    'GET /api/devices/:id/status' => 'DeviceController@getStatus',
    'POST /api/devices/register-teltonika' => 'DeviceController@registerTeltonika',
    
    // Alerts
    'GET /alerts' => 'AlertController@index',
    'GET /api/alerts' => 'AlertController@list',
    'GET /api/alerts/:id' => 'AlertController@get',
    'POST /api/alerts/:id/acknowledge' => 'AlertController@acknowledge',
    'POST /api/alerts/:id/assign' => 'AlertController@assign',
    'GET /api/alerts/export' => 'AlertController@export',
    
    // Trips
    'GET /assets/:id/trips' => 'TripController@index',
    'GET /api/assets/:id/trips' => 'TripController@list',
    'GET /api/trips/:id' => 'TripController@get',
    'GET /api/trips/:id/playback' => 'TripController@getPlayback',
    'GET /api/trips/:id/export' => 'TripController@export',
    
    // Video
    'GET /assets/:id/video' => 'VideoController@index',
    'GET /api/assets/:id/video' => 'VideoController@list',
    'GET /api/video/:id' => 'VideoController@get',
    'GET /api/video/:id/stream' => 'VideoController@stream',
    'GET /api/video/:id/download' => 'VideoController@download',
    
    // Geofences
    'GET /geofences' => 'GeofenceController@index',
    'GET /api/geofences' => 'GeofenceController@list',
    'GET /api/geofences/:id' => 'GeofenceController@get',
    'POST /api/geofences' => 'GeofenceController@create',
    'PUT /api/geofences/:id' => 'GeofenceController@update',
    'DELETE /api/geofences/:id' => 'GeofenceController@delete',
    
    // Reports
    'GET /reports' => 'ReportController@index',
    'GET /api/reports' => 'ReportController@list',
    'GET /api/reports/:id' => 'ReportController@get',
    'POST /api/reports/generate' => 'ReportController@generate',
    
    // Administration
    'GET /admin' => 'AdminController@index',
    'GET /admin/users' => 'AdminController@users',
    'GET /api/admin/users' => 'AdminController@listUsers',
    'POST /api/admin/users' => 'AdminController@createUser',
    'PUT /api/admin/users/:id' => 'AdminController@updateUser',
    'DELETE /api/admin/users/:id' => 'AdminController@deleteUser',
    'GET /api/admin/settings' => 'AdminController@getSettings',
    'PUT /api/admin/settings' => 'AdminController@updateSettings',
    
    // Push Notifications
    'POST /api/push/subscribe' => 'PushController@subscribe',
    'DELETE /api/push/unsubscribe' => 'PushController@unsubscribe',
    
    // PWA
    'GET /manifest.json' => 'PwaController@manifest',
    'GET /service-worker.js' => 'PwaController@serviceWorker',
];

