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

$title = 'Live Map - Dragnet Intelematics';
$showNav = true;

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-map me-2"></i>Live Map</h1>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-primary me-2" onclick="centerOnLocation()" id="locationButton" title="Center on my location">
            <i class="fas fa-crosshairs me-1"></i>My Location
        </button>
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
// Wait for jQuery and Leaflet to be loaded
(function() {
    // Check if jQuery and Leaflet are loaded
    function waitForDependencies(callback) {
        if (typeof jQuery !== 'undefined' && typeof L !== 'undefined') {
            callback();
        } else {
            setTimeout(function() { waitForDependencies(callback); }, 50);
        }
    }
    
    waitForDependencies(function() {
        // Now jQuery is available, use $ safely
        const $ = jQuery;
        
        let map;
        let deviceMarkers = {};
        let userLocationMarker = null;
        let userLocation = null;
        
        $(document).ready(function() {
            initMap();
            
            // Refresh every 30 seconds
            setInterval(function() {
                if (map) {
                    loadDevices();
                }
            }, 30000);
        });
        
        // Get user's current location
        function getCurrentLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation is not supported by this browser'));
                    return;
                }
                
                const options = {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                };
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const location = {
                            lat: position.coords.latitude,
                            lon: position.coords.longitude,
                            accuracy: position.coords.accuracy
                        };
                        resolve(location);
                    },
                    function(error) {
                        let errorMsg = 'Unable to get your location';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMsg = 'Location access denied. Please enable location permissions.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMsg = 'Location information unavailable.';
                                break;
                            case error.TIMEOUT:
                                errorMsg = 'Location request timed out.';
                                break;
                        }
                        reject(new Error(errorMsg));
                    },
                    options
                );
            });
        }
        
        // Center map on user's location
        window.centerOnLocation = function() {
            const btn = $('#locationButton');
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Locating...');
            
            getCurrentLocation().then(function(location) {
                userLocation = location;
                
                // Update or create user location marker
                if (userLocationMarker) {
                    userLocationMarker.setLatLng([location.lat, location.lon]);
                } else {
                    const userIcon = L.divIcon({
                        className: 'user-location-marker',
                        html: '<i class="fas fa-map-marker-alt" style="color: #0066cc; font-size: 32px; text-shadow: 0 0 4px white;"></i>',
                        iconSize: [32, 32],
                        iconAnchor: [16, 32]
                    });
                    
                    userLocationMarker = L.marker([location.lat, location.lon], { icon: userIcon })
                        .bindPopup('<strong>Your Location</strong><br>Accuracy: ' + Math.round(location.accuracy) + 'm')
                        .addTo(map);
                }
                
                // Center map on user location with appropriate zoom
                const zoom = location.accuracy > 1000 ? 12 : location.accuracy > 500 ? 14 : 16;
                map.setView([location.lat, location.lon], zoom);
                
                btn.prop('disabled', false).html(originalHtml);
            }).catch(function(error) {
                alert(error.message);
                btn.prop('disabled', false).html(originalHtml);
            });
        };
        
        window.initMap = function() {
            // Get map settings
            $.get('/api/admin/settings.php', function(settings) {
                const defaultLat = settings.map_center_lat || 40.7128;
                const defaultLon = settings.map_center_lon || -74.0060;
                const defaultZoom = settings.map_zoom || 10;
                const provider = settings.map_provider || 'openstreetmap';
                
                // Try to get user's location first, fallback to default
                getCurrentLocation().then(function(location) {
                    userLocation = location;
                    
                    // Initialize map centered on user location
                    map = L.map('map').setView([location.lat, location.lon], 14);
                    
                    // Add user location marker
                    const userIcon = L.divIcon({
                        className: 'user-location-marker',
                        html: '<i class="fas fa-map-marker-alt" style="color: #0066cc; font-size: 32px; text-shadow: 0 0 4px white;"></i>',
                        iconSize: [32, 32],
                        iconAnchor: [16, 32]
                    });
                    
                    userLocationMarker = L.marker([location.lat, location.lon], { icon: userIcon })
                        .bindPopup('<strong>Your Location</strong><br>Accuracy: ' + Math.round(location.accuracy) + 'm')
                        .addTo(map);
                    
                    // Add tile layer
                    addTileLayer(provider);
                    
                    // Load devices after map is initialized
                    loadDevices();
                }).catch(function(error) {
                    // Fallback to default location if GPS fails
                    console.log('Using default location:', error.message);
                    map = L.map('map').setView([defaultLat, defaultLon], defaultZoom);
                    
                    // Add tile layer
                    addTileLayer(provider);
                    
                    // Load devices after map is initialized
                    loadDevices();
                });
            }).fail(function() {
                // Fallback to default if settings fail to load
                map = L.map('map').setView([40.7128, -74.0060], 10);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19,
                    subdomains: ['a', 'b', 'c']
                }).addTo(map);
                loadDevices();
            });
        };
        
        // Helper function to add tile layer
        function addTileLayer(provider) {
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
            
            // Only add subdomains if array exists and has items
            if (providerConfig.sub && Array.isArray(providerConfig.sub) && providerConfig.sub.length > 0) {
                tileOptions.subdomains = providerConfig.sub;
            }
            
            L.tileLayer(providerConfig.url, tileOptions).addTo(map);
        }
                
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
                
        
        window.loadDevices = function() {
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
        };
        
        window.refreshMap = function() {
            loadDevices();
        };
    });
})();
</script>

<style>
.device-marker {
    background: transparent;
    border: none;
}

.user-location-marker {
    background: transparent;
    border: none;
    cursor: pointer;
}

.user-location-marker i {
    filter: drop-shadow(0 0 2px rgba(255, 255, 255, 0.8));
}

#locationButton:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

