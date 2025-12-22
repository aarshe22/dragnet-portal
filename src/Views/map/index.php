<?php
$title = 'Live Map - DragNet Portal';
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
let geofenceLayers = [];

$(document).ready(function() {
    initMap();
    loadDevices();
    loadGeofences();
    
    // Refresh every 30 seconds
    setInterval(function() {
        loadDevices();
    }, 30000);
});

function initMap() {
    map = L.map('map').setView([40.7128, -74.0060], 10);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
}

function loadDevices() {
    $.get('/api/map/devices', function(devices) {
        // Remove old markers
        Object.values(deviceMarkers).forEach(marker => map.removeLayer(marker));
        deviceMarkers = {};
        
        devices.forEach(device => {
            if (device.lat && device.lon) {
                const color = device.status === 'online' ? 'green' : 'red';
                const icon = L.divIcon({
                    className: 'device-marker',
                    html: `<i class="fas fa-circle" style="color: ${color}; font-size: 20px;"></i>`,
                    iconSize: [20, 20]
                });
                
                const marker = L.marker([device.lat, device.lon], { icon: icon })
                    .bindPopup(`
                        <strong>${device.device_uid}</strong><br>
                        Status: ${device.status}<br>
                        Speed: ${device.speed || 0} mph<br>
                        Last: ${device.last_checkin || 'N/A'}
                    `)
                    .addTo(map);
                
                deviceMarkers[device.id] = marker;
            }
        });
    });
}

function loadGeofences() {
    $.get('/api/map/geofences', function(geofences) {
        geofenceLayers.forEach(layer => map.removeLayer(layer));
        geofenceLayers = [];
        
        geofences.forEach(geofence => {
            let layer;
            const coords = geofence.coordinates;
            
            if (geofence.type === 'circle') {
                layer = L.circle([coords.lat, coords.lon], {
                    radius: coords.radius
                });
            } else if (geofence.type === 'polygon') {
                layer = L.polygon(coords.points);
            } else if (geofence.type === 'rectangle') {
                layer = L.rectangle([
                    [coords.south, coords.west],
                    [coords.north, coords.east]
                ]);
            }
            
            if (layer) {
                layer.bindPopup(`<strong>${geofence.name}</strong>`).addTo(map);
                geofenceLayers.push(layer);
            }
        });
    });
}

function refreshMap() {
    loadDevices();
    loadGeofences();
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
include __DIR__ . '/../layout.php';
?>

