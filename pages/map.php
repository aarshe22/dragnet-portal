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
        <div class="input-group me-2" style="max-width: 300px;">
            <span class="input-group-text"><i class="fas fa-microchip"></i></span>
            <select class="form-select" id="devicePicker" onchange="centerOnDevice()">
                <option value="">Select Device...</option>
            </select>
        </div>
        <?php if (has_role('Operator')): ?>
        <button class="btn btn-success me-2" onclick="toggleGeofenceDrawing()" id="geofenceDrawButton" title="Draw Geofence">
            <i class="fas fa-draw-polygon me-1"></i>Draw Geofence
        </button>
        <?php endif; ?>
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

<!-- Geofence Creation Modal -->
<?php if (has_role('Operator')): ?>
<div class="modal fade" id="geofenceModal" tabindex="-1" aria-labelledby="geofenceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="geofenceModalLabel"><i class="fas fa-draw-polygon me-2"></i>Create Geofence</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="geofenceForm">
                    <input type="hidden" id="geofenceId" name="id">
                    <input type="hidden" id="geofenceCoordinates" name="coordinates">
                    
                    <div class="mb-3">
                        <label for="geofenceName" class="form-label">Geofence Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="geofenceName" name="name" required placeholder="Enter geofence name">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Associate with Devices</label>
                        <select class="form-select" id="geofenceDevices" name="device_ids[]" multiple size="5">
                            <option value="">Loading devices...</option>
                        </select>
                        <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple devices</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Associate with Device Groups</label>
                        <select class="form-select" id="geofenceGroups" name="group_ids[]" multiple size="5">
                            <option value="">Loading groups...</option>
                        </select>
                        <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple groups</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="geofenceActive" name="active" checked>
                            <label class="form-check-label" for="geofenceActive">
                                Active (Geofence is enabled)
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveGeofence()">
                    <i class="fas fa-save me-1"></i>Save Geofence
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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
        let drawnItems = new L.FeatureGroup();
        let drawControl = null;
        let isDrawingMode = false;
        let geofenceLayers = {};
        
        $(document).ready(function() {
            initMap();
            
            // Load geofences
            loadGeofences();
            
            // Check if viewing specific geofence
            const urlParams = new URLSearchParams(window.location.search);
            const geofenceId = urlParams.get('geofence');
            if (geofenceId) {
                setTimeout(function() {
                    centerOnGeofence(parseInt(geofenceId));
                }, 1000);
            }
            
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
                    
                    // Initialize drawing controls
                    initDrawingControls();
                    
                    // Load devices after map is initialized
                    loadDevices();
                }).catch(function(error) {
                    // Fallback to default location if GPS fails
                    console.log('Using default location:', error.message);
                    map = L.map('map').setView([defaultLat, defaultLon], defaultZoom);
                    
                    // Add tile layer
                    addTileLayer(provider);
                    
                    // Initialize drawing controls
                    initDrawingControls();
                    
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
                
                // Initialize drawing controls
                initDrawingControls();
                
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
        
        // Store all devices for picker
        let allDevices = {};
        
        window.loadDevices = function() {
            $.get('/api/devices/map', function(devices) {
                // Remove old markers
                Object.values(deviceMarkers).forEach(marker => map.removeLayer(marker));
                deviceMarkers = {};
                allDevices = {};
                
                // Populate device picker dropdown
                const picker = $('#devicePicker');
                picker.empty().append('<option value="">Select Device...</option>');
                
                devices.forEach(device => {
                    // Store device for later use
                    allDevices[device.id] = device;
                    
                    // Add to dropdown (show all devices, even without location)
                    const label = `${device.device_uid}${device.device_type_label ? ' (' + device.device_type_label + ')' : ''}${device.lat && device.lon ? ' ✓' : ' (No location)'}`;
                    picker.append(`<option value="${device.id}">${escapeHtml(label)}</option>`);
                    
                    // Add marker if device has location
                    if (device.lat && device.lon) {
                        // Get device type icon
                        const deviceTypeIcon = device.device_type_icon || 'fa-car';
                        const deviceTypeLabel = device.device_type_label || 'Vehicle';
                        
                        // Status-based color
                        const statusColors = {
                            'online': '#28a745',
                            'moving': '#28a745',
                            'idle': '#ffc107',
                            'parked': '#6c757d',
                            'offline': '#dc3545'
                        };
                        const color = statusColors[device.status] || '#999999';
                        
                        // Create icon with device type
                        const icon = L.divIcon({
                            className: 'device-marker',
                            html: `<i class="fas ${deviceTypeIcon}" style="color: ${color}; font-size: 28px; text-shadow: 0 0 4px rgba(255,255,255,0.9), 0 0 8px rgba(0,0,0,0.3);"></i>`,
                            iconSize: [32, 32],
                            iconAnchor: [16, 32]
                        });
                        
                        // Create popup with device info
                        const popupContent = `
                            <div style="min-width: 200px;">
                                <strong>${escapeHtml(device.device_uid)}</strong><br>
                                <small style="color: #666;">${escapeHtml(deviceTypeLabel)}</small><br>
                                <hr style="margin: 8px 0;">
                                <strong>Status:</strong> <span style="color: ${color};">${escapeHtml(device.status)}</span><br>
                                <strong>Speed:</strong> ${device.speed ? device.speed.toFixed(1) : '0'} km/h<br>
                                <strong>Last Seen:</strong> ${device.last_seen || 'N/A'}<br>
                                ${device.gsm_signal ? `<strong>GSM Signal:</strong> ${device.gsm_signal}%<br>` : ''}
                                ${device.external_voltage ? `<strong>Voltage:</strong> ${device.external_voltage}V<br>` : ''}
                            </div>
                        `;
                        
                        const marker = L.marker([device.lat, device.lon], { icon: icon })
                            .bindPopup(popupContent)
                            .addTo(map);
                        
                        deviceMarkers[device.id] = marker;
                    }
                });
            });
        };
        
        window.centerOnDevice = function() {
            const deviceId = $('#devicePicker').val();
            if (!deviceId) {
                return;
            }
            
            const device = allDevices[deviceId];
            if (!device) {
                alert('Device not found');
                return;
            }
            
            if (device.lat && device.lon) {
                // Center map on device
                map.setView([device.lat, device.lon], 15);
                
                // Open popup if marker exists
                if (deviceMarkers[deviceId]) {
                    deviceMarkers[deviceId].openPopup();
                }
            } else {
                alert('This device does not have a location yet.');
            }
        };
        
        window.refreshMap = function() {
            loadDevices();
            loadGeofences();
        };
        
        // Center map on specific geofence
        function centerOnGeofence(geofenceId) {
            const geofenceLayer = geofenceLayers[geofenceId];
            if (geofenceLayer) {
                map.fitBounds(geofenceLayer.getBounds());
                geofenceLayer.openPopup();
            }
        }
        
        // View geofence analytics
        window.viewGeofenceAnalytics = function(geofenceId) {
            window.location.href = '/geofences/analytics.php?id=' + geofenceId;
        };
        
        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Initialize drawing controls for geofences
        function initDrawingControls() {
            if (!map) return;
            
            // Add drawn items layer to map
            map.addLayer(drawnItems);
            
            // Configure draw control - only allow polygons
            drawControl = new L.Control.Draw({
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true,
                        drawError: {
                            color: '#e1e100',
                            message: '<strong>Oh snap!</strong> you can\'t draw that!'
                        },
                        shapeOptions: {
                            color: '#0066cc',
                            fillColor: '#0066cc',
                            fillOpacity: 0.2,
                            weight: 3
                        }
                    },
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    circlemarker: false,
                    marker: false
                },
                edit: {
                    featureGroup: drawnItems,
                    remove: true
                }
            });
            
            // Add draw control to map
            map.addControl(drawControl);
            
            // Handle draw events
            map.on(L.Draw.Event.CREATED, function(e) {
                const layer = e.layer;
                const type = e.layerType;
                
                if (type === 'polygon') {
                    // Get coordinates
                    const latlngs = layer.getLatLngs()[0];
                    const coordinates = latlngs.map(function(latlng) {
                        return [latlng.lat, latlng.lng];
                    });
                    
                    // Store coordinates for saving
                    $('#geofenceCoordinates').val(JSON.stringify(coordinates));
                    
                    // Add to drawn items
                    drawnItems.addLayer(layer);
                    
                    // Open modal to save geofence
                    openGeofenceModal();
                }
            });
            
            // Handle draw start/stop
            map.on(L.Draw.Event.DRAWSTART, function() {
                isDrawingMode = true;
                $('#geofenceDrawButton').addClass('active').html('<i class="fas fa-stop me-1"></i>Stop Drawing');
            });
            
            map.on(L.Draw.Event.DRAWSTOP, function() {
                isDrawingMode = false;
                $('#geofenceDrawButton').removeClass('active').html('<i class="fas fa-draw-polygon me-1"></i>Draw Geofence');
            });
        }
        
        // Toggle geofence drawing mode
        window.toggleGeofenceDrawing = function() {
            if (!drawControl) {
                alert('Drawing controls not initialized');
                return;
            }
            
            if (isDrawingMode) {
                // Stop drawing
                map.removeControl(drawControl);
                initDrawingControls();
                isDrawingMode = false;
                $('#geofenceDrawButton').removeClass('active').html('<i class="fas fa-draw-polygon me-1"></i>Draw Geofence');
            } else {
                // Start drawing - trigger polygon draw
                const drawPolygon = new L.Draw.Polygon(map, {
                    allowIntersection: false,
                    showArea: true,
                    shapeOptions: {
                        color: '#0066cc',
                        fillColor: '#0066cc',
                        fillOpacity: 0.2,
                        weight: 3
                    }
                });
                drawPolygon.enable();
            }
        };
        
        // Load geofences and display on map
        function loadGeofences() {
            $.get('/api/geofences.php', function(geofences) {
                // Remove existing geofence layers
                Object.values(geofenceLayers).forEach(layer => {
                    map.removeLayer(layer);
                });
                geofenceLayers = {};
                
                // Add each geofence to map
                geofences.forEach(function(geofence) {
                    if (!geofence.active) return;
                    
                    let coordinates = geofence.coordinates;
                    if (typeof coordinates === 'string') {
                        try {
                            coordinates = JSON.parse(coordinates);
                        } catch (e) {
                            console.error('Failed to parse geofence coordinates:', e);
                            return;
                        }
                    }
                    
                    if (!coordinates || (Array.isArray(coordinates) && coordinates.length === 0)) return;
                    
                    let polygon;
                    if (geofence.type === 'polygon') {
                        // Polygon: coordinates = [[lat, lon], [lat, lon], ...]
                        polygon = L.polygon(coordinates, {
                            color: '#0066cc',
                            fillColor: '#0066cc',
                            fillOpacity: 0.2,
                            weight: 3
                        });
                    } else if (geofence.type === 'circle') {
                        // Circle: coordinates = [centerLat, centerLon, radiusKm]
                        const center = [coordinates[0], coordinates[1]];
                        const radiusM = coordinates[2] * 1000; // Convert km to meters
                        polygon = L.circle(center, {
                            radius: radiusM,
                            color: '#0066cc',
                            fillColor: '#0066cc',
                            fillOpacity: 0.2,
                            weight: 3
                        });
                    } else if (geofence.type === 'rectangle') {
                        // Rectangle: coordinates = [[minLat, minLon], [maxLat, maxLon]]
                        const bounds = [coordinates[0], coordinates[1]];
                        polygon = L.rectangle(bounds, {
                            color: '#0066cc',
                            fillColor: '#0066cc',
                            fillOpacity: 0.2,
                            weight: 3
                        });
                    }
                    
                    if (polygon) {
                        const popupContent = `
                            <div style="min-width: 200px;">
                                <strong>${escapeHtml(geofence.name)}</strong><br>
                                <small>Type: ${escapeHtml(geofence.type)}</small><br>
                                <small>Status: ${geofence.active ? 'Active' : 'Inactive'}</small><br>
                                <hr style="margin: 8px 0;">
                                <button class="btn btn-sm btn-primary w-100" onclick="viewGeofenceAnalytics(${geofence.id})">
                                    <i class="fas fa-chart-line me-1"></i>View Analytics
                                </button>
                            </div>
                        `;
                        polygon.bindPopup(popupContent);
                        polygon.addTo(map);
                        geofenceLayers[geofence.id] = polygon;
                    }
                });
            }).fail(function() {
                console.error('Failed to load geofences');
            });
        }
        
        // Open geofence modal
        function openGeofenceModal() {
            // Load devices and groups
            loadGeofenceDevices();
            loadGeofenceGroups();
            
            // Reset form
            $('#geofenceForm')[0].reset();
            $('#geofenceId').val('');
            $('#geofenceModalLabel').html('<i class="fas fa-draw-polygon me-2"></i>Create Geofence');
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('geofenceModal'));
            modal.show();
        }
        
        // Load devices for geofence association
        function loadGeofenceDevices() {
            $.get('/api/devices/map', function(devices) {
                const select = $('#geofenceDevices');
                select.empty();
                devices.forEach(function(device) {
                    select.append(`<option value="${device.id}">${escapeHtml(device.device_uid)}${device.device_type_label ? ' (' + escapeHtml(device.device_type_label) + ')' : ''}</option>`);
                });
            });
        }
        
        // Load device groups for geofence association
        function loadGeofenceGroups() {
            $.get('/api/device_groups.php', function(groups) {
                const select = $('#geofenceGroups');
                select.empty();
                groups.forEach(function(group) {
                    select.append(`<option value="${group.id}">${escapeHtml(group.name)}</option>`);
                });
            });
        }
        
        // Save geofence
        window.saveGeofence = function() {
            const form = $('#geofenceForm')[0];
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            const name = $('#geofenceName').val();
            const coordinates = $('#geofenceCoordinates').val();
            
            if (!name || !coordinates) {
                alert('Please provide a name and draw a polygon on the map');
                return;
            }
            
            // Get selected devices and groups
            const deviceIds = Array.from($('#geofenceDevices option:selected')).map(opt => opt.value).filter(v => v);
            const groupIds = Array.from($('#geofenceGroups option:selected')).map(opt => opt.value).filter(v => v);
            
            const data = {
                name: name,
                type: 'polygon',
                coordinates: JSON.parse(coordinates),
                device_ids: deviceIds,
                group_ids: groupIds,
                active: $('#geofenceActive').is(':checked') ? 1 : 0
            };
            
            $.ajax({
                url: '/api/geofences.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    if (response.success) {
                        // Close modal
                        bootstrap.Modal.getInstance(document.getElementById('geofenceModal')).hide();
                        
                        // Clear drawn items
                        drawnItems.clearLayers();
                        
                        // Reload geofences
                        loadGeofences();
                        
                        alert('Geofence created successfully!');
                    } else {
                        alert('Failed to create geofence: ' + (response.error || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.error || 'Failed to create geofence';
                    alert(error);
                }
            });
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

#geofenceDrawButton.active {
    background-color: #dc3545;
    border-color: #dc3545;
}

/* Leaflet Draw styling */
.leaflet-draw-toolbar {
    margin-top: 10px;
}

.leaflet-draw-toolbar a {
    background-color: var(--enterprise-dark-grey);
    color: var(--enterprise-blue);
    border: 1px solid var(--enterprise-blue);
}

.leaflet-draw-toolbar a:hover {
    background-color: var(--enterprise-blue);
    color: var(--enterprise-white);
}

.leaflet-draw-actions {
    background-color: var(--enterprise-dark-grey);
    border: 1px solid var(--enterprise-blue);
}

.leaflet-draw-actions a {
    color: var(--enterprise-blue);
}

.leaflet-draw-actions a:hover {
    background-color: var(--enterprise-blue);
    color: var(--enterprise-white);
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

