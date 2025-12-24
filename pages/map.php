<?php

/**
 * Live Map Page
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/devices.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$title = 'Live Map - DragNet Portal';
$showNav = true;

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-map me-2"></i>Live Map</h1>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" onclick="refreshMap()">
            <i class="fas fa-sync-alt me-1"></i>Refresh
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div id="map" style="height: 600px;"></div>
    </div>
</div>

<script>
let map;
let deviceMarkers = {};

$(document).ready(function() {
    initMap();
    loadDevices();
    
    // Refresh every 30 seconds
    setInterval(loadDevices, 30000);
});

function initMap() {
    map = L.map('map').setView([40.7128, -74.0060], 10);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
}

function loadDevices() {
    $.get('/api/devices/map', function(devices) {
        // Remove old markers
        Object.values(deviceMarkers).forEach(marker => map.removeLayer(marker));
        deviceMarkers = {};
        
        devices.forEach(device => {
            if (device.lat && device.lon) {
                const color = device.status === 'online' || device.status === 'moving' ? 'green' : 
                             device.status === 'idle' ? 'orange' : 'red';
                const icon = L.divIcon({
                    className: 'device-marker',
                    html: `<i class="fas fa-circle" style="color: ${color}; font-size: 20px;"></i>`,
                    iconSize: [20, 20]
                });
                
                const marker = L.marker([device.lat, device.lon], { icon: icon })
                    .bindPopup(`
                        <strong>${device.device_uid}</strong><br>
                        Status: ${device.status}<br>
                        Speed: ${device.speed || 0} km/h<br>
                        Last: ${device.last_seen || 'N/A'}
                    `)
                    .addTo(map);
                
                deviceMarkers[device.id] = marker;
            }
        });
    });
}

function refreshMap() {
    loadDevices();
}
</script>

<style>
.device-marker {
    background: transparent;
    border: none;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

