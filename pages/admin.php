<?php

/**
 * Admin Dashboard Page
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Administrator');

$title = 'Administration - DragNet Portal';
$showNav = true;

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-cog me-2"></i>Administration</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tenants-tab" data-bs-toggle="tab" data-bs-target="#tenants" type="button">
                    <i class="fas fa-building me-1"></i>Tenants
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button">
                    <i class="fas fa-users me-1"></i>Users
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="devices-tab" data-bs-toggle="tab" data-bs-target="#devices" type="button">
                    <i class="fas fa-microchip me-1"></i>Devices
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button">
                    <i class="fas fa-list-alt me-1"></i>Telematics Logs
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button">
                    <i class="fas fa-cog me-1"></i>Settings
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="adminTabContent">
            <!-- Tenants Tab -->
            <div class="tab-pane fade show active" id="tenants" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Tenant Management</h5>
                        <button class="btn btn-primary btn-sm" onclick="showTenantModal()">
                            <i class="fas fa-plus me-1"></i>Add Tenant
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="tenantsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Region</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="tenantsTableBody">
                                    <!-- Loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Users Tab -->
            <div class="tab-pane fade" id="users" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">User Management</h5>
                        <button class="btn btn-primary btn-sm" onclick="showUserModal()">
                            <i class="fas fa-plus me-1"></i>Add User
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="userSearch" placeholder="Search by email or tenant..." onkeyup="loadUsers()">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Email</th>
                                        <th>Tenant</th>
                                        <th>Role</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <!-- Loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Devices Tab -->
            <div class="tab-pane fade" id="devices" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Device Management</h5>
                        <button class="btn btn-primary btn-sm" onclick="showDeviceModal()">
                            <i class="fas fa-plus me-1"></i>Add Device
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="deviceSearch" placeholder="Search by IMEI, device UID, or tenant..." onkeyup="loadDevices()">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="devicesTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Device UID</th>
                                        <th>IMEI</th>
                                        <th>Tenant</th>
                                        <th>Model</th>
                                        <th>Status</th>
                                        <th>Last Seen</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="devicesTableBody">
                                    <!-- Loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Settings Tab -->
            <div class="tab-pane fade" id="settings" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Application Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6><i class="fas fa-map me-2"></i>Map Provider Settings</h6>
                            <p class="text-muted">Select the default mapping provider for the Live Map view.</p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Map Provider</label>
                                    <select class="form-select" id="mapProviderSelect" onchange="updateMapPreview()">
                                        <option value="openstreetmap">OpenStreetMap</option>
                                        <option value="openstreetmap_fr">OpenStreetMap France</option>
                                        <option value="openstreetmap_de">OpenStreetMap DE</option>
                                        <option value="cartodb_positron">CartoDB Positron</option>
                                        <option value="cartodb_dark">CartoDB Dark Matter</option>
                                        <option value="stamen_terrain">Stamen Terrain</option>
                                        <option value="stamen_toner">Stamen Toner</option>
                                        <option value="stamen_watercolor">Stamen Watercolor</option>
                                        <option value="esri_worldstreetmap">Esri World Street Map</option>
                                        <option value="esri_worldtopomap">Esri World Topo Map</option>
                                        <option value="esri_worldimagery">Esri World Imagery</option>
                                        <option value="opentopomap">OpenTopoMap</option>
                                        <option value="cyclosm">CyclOSM</option>
                                        <option value="wikimedia">Wikimedia Maps</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Default Zoom Level</label>
                                    <input type="number" class="form-control" id="mapZoom" min="1" max="20" value="10">
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Default Center Latitude</label>
                                    <input type="number" class="form-control" id="mapCenterLat" step="0.000001" value="40.7128">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Default Center Longitude</label>
                                    <input type="number" class="form-control" id="mapCenterLon" step="0.000001" value="-74.0060">
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <div id="mapPreview" style="height: 300px; border: 1px solid #ddd; border-radius: 0.375rem;"></div>
                                <small class="text-muted">Preview of selected map provider</small>
                            </div>
                            
                            <div class="mt-3">
                                <button class="btn btn-primary" onclick="saveMapSettings()">
                                    <i class="fas fa-save me-1"></i>Save Map Settings
                                </button>
                                <button class="btn btn-secondary" onclick="loadMapSettings()">
                                    <i class="fas fa-sync me-1"></i>Reset to Defaults
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Telematics Logs Tab -->
            <div class="tab-pane fade" id="logs" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Telematics Logs</h5>
                        <div>
                            <button class="btn btn-sm btn-secondary" onclick="toggleAutoRefresh()" id="autoRefreshBtn">
                                <i class="fas fa-pause me-1"></i>Pause Auto-Refresh
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="clearLogs()">
                                <i class="fas fa-trash me-1"></i>Clear View
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select class="form-select" id="logTenantFilter" onchange="loadLogs()">
                                    <option value="">All Tenants</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="logDeviceFilter" onchange="loadLogs()">
                                    <option value="">All Devices</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="logSearch" placeholder="Type to search..." onkeyup="filterLogs()">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="logSort" onchange="loadLogs()">
                                    <option value="timestamp_desc">Newest First</option>
                                    <option value="timestamp_asc">Oldest First</option>
                                    <option value="device_asc">Device A-Z</option>
                                    <option value="device_desc">Device Z-A</option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-sm table-striped" id="logsTable">
                                <thead class="sticky-top bg-white">
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Device</th>
                                        <th>Tenant</th>
                                        <th>Lat</th>
                                        <th>Lon</th>
                                        <th>Speed</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody id="logsTableBody">
                                    <!-- Loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tenant Modal -->
<div class="modal fade" id="tenantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tenantModalTitle">Add Tenant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="tenantForm">
                    <input type="hidden" id="tenantId" name="id">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="tenantName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Region</label>
                        <input type="text" class="form-control" id="tenantRegion" name="region" value="us-east">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTenant()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="userId" name="id">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="userEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tenant</label>
                        <select class="form-select" id="userTenantId" name="tenant_id" required>
                            <option value="">Select Tenant</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" id="userRole" name="role" required>
                            <option value="Guest">Guest</option>
                            <option value="ReadOnly">ReadOnly</option>
                            <option value="Operator">Operator</option>
                            <option value="Administrator">Administrator</option>
                            <option value="TenantOwner">TenantOwner</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Device Modal -->
<div class="modal fade" id="deviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deviceModalTitle">Add Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="deviceForm">
                    <input type="hidden" id="deviceId" name="id">
                    <div class="mb-3">
                        <label class="form-label">Tenant</label>
                        <select class="form-select" id="deviceTenantId" name="tenant_id" required>
                            <option value="">Select Tenant</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Device UID</label>
                        <input type="text" class="form-control" id="deviceUid" name="device_uid" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">IMEI</label>
                        <input type="text" class="form-control" id="deviceImei" name="imei" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ICCID</label>
                        <input type="text" class="form-control" id="deviceIccid" name="iccid">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Model</label>
                        <input type="text" class="form-control" id="deviceModel" name="model" value="FMM13A">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Firmware Version</label>
                        <input type="text" class="form-control" id="deviceFirmware" name="firmware_version">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveDevice()">Save</button>
            </div>
        </div>
    </div>
</div>

<script src="/public/js/admin.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let mapPreview = null;

$(document).ready(function() {
    loadMapSettings();
    
    // Initialize map preview when settings tab is shown
    $('#settings-tab').on('shown.bs.tab', function() {
        setTimeout(function() {
            if (!mapPreview) {
                initMapPreview();
            }
        }, 100);
    });
});

function initMapPreview() {
    if (mapPreview) {
        mapPreview.remove();
    }
    
    mapPreview = L.map('mapPreview').setView([40.7128, -74.0060], 10);
    updateMapPreview();
}

function updateMapPreview() {
    if (!mapPreview) {
        initMapPreview();
        return;
    }
    
    const provider = $('#mapProviderSelect').val();
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
    
    const config = providers[provider] || providers['openstreetmap'];
    
    // Remove existing tiles
    mapPreview.eachLayer(function(layer) {
        if (layer instanceof L.TileLayer) {
            mapPreview.removeLayer(layer);
        }
    });
    
    // Add new tiles
    const tileLayer = L.tileLayer(config.url, {
        attribution: config.attr,
        maxZoom: 19,
        subdomains: config.sub.length > 0 ? config.sub : undefined
    });
    
    tileLayer.addTo(mapPreview);
}

function loadMapSettings() {
    $.get('/api/admin/settings.php', function(settings) {
        $('#mapProviderSelect').val(settings.map_provider || 'openstreetmap');
        $('#mapZoom').val(settings.map_zoom || 10);
        $('#mapCenterLat').val(settings.map_center_lat || 40.7128);
        $('#mapCenterLon').val(settings.map_center_lon || -74.0060);
        updateMapPreview();
    });
}

function saveMapSettings() {
    const settings = {
        map_provider: $('#mapProviderSelect').val(),
        map_zoom: parseInt($('#mapZoom').val()),
        map_center_lat: parseFloat($('#mapCenterLat').val()),
        map_center_lon: parseFloat($('#mapCenterLon').val())
    };
    
    $.ajax({
        url: '/api/admin/settings.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(settings),
        success: function() {
            alert('Map settings saved successfully!');
        },
        error: function(xhr) {
            alert('Error saving settings: ' + (xhr.responseJSON?.error || 'Unknown error'));
        }
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>
