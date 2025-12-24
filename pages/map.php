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
require_once __DIR__ . '/../includes/settings.php';

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
    
    // Refresh every 30 seconds
    setInterval(function() {
        if (map) {
            loadDevices();
        }
    }, 30000);
});

function initMap() {
    // Get map settings
    $.get('/api/admin/settings.php', function(settings) {
        const centerLat = settings.map_center_lat || 40.7128;
        const centerLon = settings.map_center_lon || -74.0060;
        const zoom = settings.map_zoom || 10;
        const provider = settings.map_provider || 'openstreetmap';
        
        map = L.map('map').setView([centerLat, centerLon], zoom);
        
        // Get provider configuration
        const providers = {
            'openstreetmap': { url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', attr: '© OpenStreetMap contributors', sub: ['a', 'b', 'c'] },
            'openstreetmap_fr': { url: 'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', attr: '© OpenStreetMap France | © OpenStreetMap contributors', sub: ['a', 'b', 'c'] },
            'openstreetmap_de': { url: 'https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png', attr: '© OpenStreetMap DE | © OpenStreetMap contributors', sub: ['a', 'b', 'c'] },
            'cartodb_positron': { url: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', attr: '© OpenStreetMap contributors © CARTO', sub: ['a', 'b', 'c', 'd'] },
            'cartodb_dark': { url: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', attr: '© OpenStreetMap contributors © CARTO', sub: ['a', 'b', 'c', 'd'] },
            'stamen_terrain': { url: 'https://stamen-tiles-{s}.a.ssl.fastly.net/terrain/{z}/{x}/{y}{r}.png', attr: 'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL.', sub: ['a', 'b', 'c', 'd'] },
            'stamen_toner': { url: 'https://stamen-tiles-{s}.a.ssl.fastly.net/toner/{z}/{x}/{y}{r}.png', attr: 'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL.', sub: ['a', 'b', 'c', 'd'] },
            'stamen_watercolor': { url: 'https://stamen-tiles-{s}.a.ssl.fastly.net/watercolor/{z}/{x}/{y}.jpg', attr: 'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL.', sub: ['a', 'b', 'c', 'd'] },
            'esri_worldstreetmap': { url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', attr: 'Tiles © Esri', sub: [] },
            'esri_worldtopomap': { url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', attr: 'Tiles © Esri', sub: [] },
            'esri_worldimagery': { url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', attr: 'Tiles © Esri', sub: [] },
            'opentopomap': { url: 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', attr: 'Map data: &copy; OpenStreetMap contributors, SRTM | Map style: &copy; OpenTopoMap (CC-BY-SA)', sub: ['a', 'b', 'c'] },
            'cyclosm': { url: 'https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', attr: '© OpenStreetMap contributors, style by CyclOSM', sub: ['a', 'b', 'c'] },
            'wikimedia': { url: 'https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', attr: '© OpenStreetMap contributors', sub: [] }
        };
        
        const providerConfig = providers[provider] || providers['openstreetmap'];
        
        const tileOptions = {
            attribution: providerConfig.attr,
            maxZoom: 19
        };
        
        if (providerConfig.sub && providerConfig.sub.length > 0) {
            tileOptions.subdomains = providerConfig.sub;
        }
        
        L.tileLayer(providerConfig.url, tileOptions).addTo(map);
        
        // Load devices after map is initialized
        loadDevices();
    }).fail(function() {
        // Fallback to default if settings fail to load
        map = L.map('map').setView([40.7128, -74.0060], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        loadDevices();
    });
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

