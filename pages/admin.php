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

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
        
        let mapPreview = null;
        let autoRefreshEnabled = true;
        let autoRefreshInterval = null;
        let currentLogSearch = '';
        
        $(document).ready(function() {
            loadTenants();
            loadUsers();
            loadDevices();
            loadTenantOptions();
            loadLogFilters();
            loadLogs();
            loadMapSettings();
            
            // Auto-refresh logs every 5 seconds
            autoRefreshInterval = setInterval(function() {
                if (autoRefreshEnabled) {
                    loadLogs();
                }
            }, 5000);
            
            // Switch tabs
            $('#adminTabs button').on('shown.bs.tab', function(e) {
                const target = $(e.target).data('bs-target');
                if (target === '#tenants') {
                    loadTenants();
                } else if (target === '#users') {
                    loadUsers();
                } else if (target === '#devices') {
                    loadDevices();
                } else if (target === '#logs') {
                    loadLogs();
                } else if (target === '#settings') {
                    setTimeout(function() {
                        if (!mapPreview && typeof L !== 'undefined') {
                            initMapPreview();
                        }
                    }, 200);
                }
            });
        });
        
        // Make functions available globally
        window.loadTenants = function() {
            $.get('/api/admin/tenants.php', function(tenants) {
                const tbody = $('#tenantsTableBody');
                tbody.empty();
                
                if (tenants.length === 0) {
                    tbody.append('<tr><td colspan="5" class="text-center">No tenants found</td></tr>');
                    return;
                }
                
                tenants.forEach(tenant => {
                    const row = `
                        <tr>
                            <td>${tenant.id}</td>
                            <td>${escapeHtml(tenant.name)}</td>
                            <td>${escapeHtml(tenant.region)}</td>
                            <td>${formatDate(tenant.created_at)}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editTenant(${tenant.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteTenant(${tenant.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            });
        };
        
        window.showTenantModal = function(id = null) {
            $('#tenantModalTitle').text(id ? 'Edit Tenant' : 'Add Tenant');
            $('#tenantForm')[0].reset();
            $('#tenantId').val(id || '');
            
            if (id) {
                $.get('/api/admin/tenants.php', function(tenants) {
                    const tenant = tenants.find(t => t.id == id);
                    if (tenant) {
                        $('#tenantName').val(tenant.name);
                        $('#tenantRegion').val(tenant.region);
                    }
                });
            }
            
            new bootstrap.Modal(document.getElementById('tenantModal')).show();
        };
        
        window.saveTenant = function() {
            const data = {
                name: $('#tenantName').val(),
                region: $('#tenantRegion').val()
            };
            
            const id = $('#tenantId').val();
            const url = '/api/admin/tenants.php';
            const method = id ? 'PUT' : 'POST';
            
            if (id) {
                data.id = id;
            }
            
            $.ajax({
                url: url,
                method: method,
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function() {
                    bootstrap.Modal.getInstance(document.getElementById('tenantModal')).hide();
                    loadTenants();
                    loadTenantOptions();
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        };
        
        window.editTenant = function(id) {
            showTenantModal(id);
        };
        
        window.deleteTenant = function(id) {
            if (!confirm('Are you sure you want to delete this tenant? This will also delete all associated users, devices, and data.')) {
                return;
            }
            
            $.ajax({
                url: '/api/admin/tenants.php',
                method: 'DELETE',
                data: { id: id },
                success: function() {
                    loadTenants();
                    loadTenantOptions();
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        };
        
        window.loadUsers = function() {
            const search = $('#userSearch').val();
            const url = '/api/admin/users.php' + (search ? '?email=' + encodeURIComponent(search) : '');
            
            $.get(url, function(users) {
                const tbody = $('#usersTableBody');
                tbody.empty();
                
                if (users.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center">No users found</td></tr>');
                    return;
                }
                
                users.forEach(user => {
                    const row = `
                        <tr>
                            <td>${user.id}</td>
                            <td>${escapeHtml(user.email)}</td>
                            <td>${escapeHtml(user.tenant_name || 'N/A')}</td>
                            <td><span class="badge bg-info">${escapeHtml(user.role)}</span></td>
                            <td>${user.last_login ? formatDate(user.last_login) : 'Never'}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editUser(${user.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            });
        };
        
        window.showUserModal = function(id = null) {
            $('#userModalTitle').text(id ? 'Edit User' : 'Add User');
            $('#userForm')[0].reset();
            $('#userId').val(id || '');
            
            if (id) {
                $.get('/api/admin/users.php', function(users) {
                    const user = users.find(u => u.id == id);
                    if (user) {
                        $('#userEmail').val(user.email);
                        $('#userTenantId').val(user.tenant_id);
                        $('#userRole').val(user.role);
                    }
                });
            }
            
            new bootstrap.Modal(document.getElementById('userModal')).show();
        };
        
        window.saveUser = function() {
            const data = {
                email: $('#userEmail').val(),
                tenant_id: parseInt($('#userTenantId').val()),
                role: $('#userRole').val()
            };
            
            const id = $('#userId').val();
            const url = '/api/admin/users.php';
            const method = id ? 'PUT' : 'POST';
            
            if (id) {
                data.id = id;
            }
            
            $.ajax({
                url: url,
                method: method,
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function() {
                    bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
                    loadUsers();
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        };
        
        window.editUser = function(id) {
            showUserModal(id);
        };
        
        window.deleteUser = function(id) {
            if (!confirm('Are you sure you want to delete this user?')) {
                return;
            }
            
            $.ajax({
                url: '/api/admin/users.php',
                method: 'DELETE',
                data: { id: id },
                success: function() {
                    loadUsers();
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        };
        
        window.loadDevices = function() {
            const search = $('#deviceSearch').val();
            const url = '/api/admin/devices.php' + (search ? '?imei=' + encodeURIComponent(search) : '');
            
            $.get(url, function(devices) {
                const tbody = $('#devicesTableBody');
                tbody.empty();
                
                if (devices.length === 0) {
                    tbody.append('<tr><td colspan="8" class="text-center">No devices found</td></tr>');
                    return;
                }
                
                devices.forEach(device => {
                    const statusBadge = getStatusBadge(device.status);
                    const row = `
                        <tr>
                            <td>${device.id}</td>
                            <td>${escapeHtml(device.device_uid)}</td>
                            <td>${escapeHtml(device.imei)}</td>
                            <td>${escapeHtml(device.tenant_name || 'N/A')}</td>
                            <td>${escapeHtml(device.model || 'FMM13A')}</td>
                            <td>${statusBadge}</td>
                            <td>${device.last_seen ? formatDate(device.last_seen) : 'Never'}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editDevice(${device.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteDevice(${device.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            });
        };
        
        window.showDeviceModal = function(id = null) {
            $('#deviceModalTitle').text(id ? 'Edit Device' : 'Add Device');
            $('#deviceForm')[0].reset();
            $('#deviceId').val(id || '');
            
            if (id) {
                $.get('/api/admin/devices.php', function(devices) {
                    const device = devices.find(d => d.id == id);
                    if (device) {
                        $('#deviceTenantId').val(device.tenant_id);
                        $('#deviceUid').val(device.device_uid);
                        $('#deviceImei').val(device.imei);
                        $('#deviceIccid').val(device.iccid || '');
                        $('#deviceModel').val(device.model || 'FMM13A');
                        $('#deviceFirmware').val(device.firmware_version || '');
                    }
                });
            }
            
            new bootstrap.Modal(document.getElementById('deviceModal')).show();
        };
        
        window.saveDevice = function() {
            const data = {
                tenant_id: parseInt($('#deviceTenantId').val()),
                device_uid: $('#deviceUid').val(),
                imei: $('#deviceImei').val(),
                iccid: $('#deviceIccid').val() || null,
                model: $('#deviceModel').val() || 'FMM13A',
                firmware_version: $('#deviceFirmware').val() || null
            };
            
            const id = $('#deviceId').val();
            const url = '/api/admin/devices.php';
            const method = id ? 'PUT' : 'POST';
            
            if (id) {
                data.id = id;
            }
            
            $.ajax({
                url: url,
                method: method,
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function() {
                    bootstrap.Modal.getInstance(document.getElementById('deviceModal')).hide();
                    loadDevices();
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        };
        
        window.editDevice = function(id) {
            showDeviceModal(id);
        };
        
        window.deleteDevice = function(id) {
            if (!confirm('Are you sure you want to delete this device? This will also delete all associated telemetry data.')) {
                return;
            }
            
            $.ajax({
                url: '/api/admin/devices.php',
                method: 'DELETE',
                data: { id: id },
                success: function() {
                    loadDevices();
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        };
        
        window.loadLogFilters = function() {
            // Load tenants for filter
            $.get('/api/admin/tenants.php', function(tenants) {
                const select = $('#logTenantFilter');
                select.empty().append('<option value="">All Tenants</option>');
                tenants.forEach(tenant => {
                    select.append(`<option value="${tenant.id}">${escapeHtml(tenant.name)}</option>`);
                });
            });
            
            // Load devices for filter
            $.get('/api/admin/devices.php', function(devices) {
                const select = $('#logDeviceFilter');
                select.empty().append('<option value="">All Devices</option>');
                const uniqueDevices = [...new Map(devices.map(d => [d.id, d])).values()];
                uniqueDevices.forEach(device => {
                    select.append(`<option value="${device.id}">${escapeHtml(device.device_uid)} (${escapeHtml(device.imei)})</option>`);
                });
            });
        };
        
        window.loadLogs = function() {
            const tenantId = $('#logTenantFilter').val();
            const deviceId = $('#logDeviceFilter').val();
            const sort = $('#logSort').val();
            const search = $('#logSearch').val();
            
            let url = '/api/admin/logs.php?limit=500';
            if (tenantId) url += '&tenant_id=' + tenantId;
            if (deviceId) url += '&device_id=' + deviceId;
            if (sort) url += '&sort=' + sort;
            if (search) url += '&search=' + encodeURIComponent(search);
            
            $.get(url, function(logs) {
                const tbody = $('#logsTableBody');
                tbody.empty();
                
                if (logs.length === 0) {
                    tbody.append('<tr><td colspan="8" class="text-center">No logs found</td></tr>');
                    return;
                }
                
                logs.forEach(log => {
                    const ioData = log.io_payload ? JSON.parse(log.io_payload) : {};
                    const ioHtml = Object.keys(ioData).map(key => 
                        `<span class="badge bg-secondary me-1">${escapeHtml(key)}: ${escapeHtml(ioData[key])}</span>`
                    ).join('');
                    
                    // Highlight search terms
                    let deviceUid = escapeHtml(log.device_uid);
                    if (search) {
                        const regex = new RegExp(`(${escapeRegex(search)})`, 'gi');
                        deviceUid = deviceUid.replace(regex, '<mark>$1</mark>');
                    }
                    
                    const row = `
                        <tr>
                            <td>${formatDateTime(log.timestamp)}</td>
                            <td>${deviceUid}</td>
                            <td>${escapeHtml(log.tenant_name || 'N/A')}</td>
                            <td>${log.lat ? parseFloat(log.lat).toFixed(6) : '-'}</td>
                            <td>${log.lon ? parseFloat(log.lon).toFixed(6) : '-'}</td>
                            <td>${log.speed ? parseFloat(log.speed).toFixed(1) + ' km/h' : '-'}</td>
                            <td>
                                <span class="badge bg-${log.ignition ? 'success' : 'secondary'}">
                                    ${log.ignition ? 'On' : 'Off'}
                                </span>
                            </td>
                            <td>${ioHtml || '-'}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            });
        };
        
        window.filterLogs = function() {
            currentLogSearch = $('#logSearch').val();
            loadLogs();
        };
        
        window.toggleAutoRefresh = function() {
            autoRefreshEnabled = !autoRefreshEnabled;
            const btn = $('#autoRefreshBtn');
            if (autoRefreshEnabled) {
                btn.html('<i class="fas fa-pause me-1"></i>Pause Auto-Refresh');
            } else {
                btn.html('<i class="fas fa-play me-1"></i>Resume Auto-Refresh');
            }
        };
        
        window.clearLogs = function() {
            $('#logsTableBody').empty();
        };
        
        window.loadTenantOptions = function() {
            $.get('/api/admin/tenants.php', function(tenants) {
                $('#userTenantId, #deviceTenantId').each(function() {
                    const select = $(this);
                    const currentVal = select.val();
                    select.empty().append('<option value="">Select Tenant</option>');
                    tenants.forEach(tenant => {
                        select.append(`<option value="${tenant.id}">${escapeHtml(tenant.name)}</option>`);
                    });
                    if (currentVal) {
                        select.val(currentVal);
                    }
                });
            });
        };
        
        window.initMapPreview = function() {
            if (typeof L === 'undefined') {
                console.error('Leaflet not loaded');
                return;
            }
            
            if (mapPreview) {
                mapPreview.remove();
            }
            
            const lat = parseFloat($('#mapCenterLat').val()) || 40.7128;
            const lon = parseFloat($('#mapCenterLon').val()) || -74.0060;
            const zoom = parseInt($('#mapZoom').val()) || 10;
            
            mapPreview = L.map('mapPreview').setView([lat, lon], zoom);
            updateMapPreview();
        };
        
        window.updateMapPreview = function() {
            if (typeof L === 'undefined') {
                console.error('Leaflet not loaded');
                return;
            }
            
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
            
            // Add new tiles with proper subdomain handling
            const tileOptions = {
                attribution: config.attr,
                maxZoom: 19
            };
            
            // Only add subdomains if array exists and has items
            if (config.sub && Array.isArray(config.sub) && config.sub.length > 0) {
                tileOptions.subdomains = config.sub;
            }
            
            const tileLayer = L.tileLayer(config.url, tileOptions);
            tileLayer.addTo(mapPreview);
        };
        
        window.loadMapSettings = function() {
            $.ajax({
                url: '/api/admin/settings.php',
                method: 'GET',
                dataType: 'json',
                success: function(settings) {
                    $('#mapProviderSelect').val(settings.map_provider || 'openstreetmap');
                    $('#mapZoom').val(settings.map_zoom || 10);
                    $('#mapCenterLat').val(settings.map_center_lat || 40.7128);
                    $('#mapCenterLon').val(settings.map_center_lon || -74.0060);
                    if (mapPreview) {
                        updateMapPreview();
                    }
                },
                error: function(xhr) {
                    console.error('Failed to load map settings:', xhr);
                    // Use defaults
                    $('#mapProviderSelect').val('openstreetmap');
                    $('#mapZoom').val(10);
                    $('#mapCenterLat').val(40.7128);
                    $('#mapCenterLon').val(-74.0060);
                }
            });
        };
        
        window.saveMapSettings = function() {
            // Validate inputs
            const provider = $('#mapProviderSelect').val();
            const zoom = parseInt($('#mapZoom').val());
            const lat = parseFloat($('#mapCenterLat').val());
            const lon = parseFloat($('#mapCenterLon').val());
            
            if (!provider) {
                alert('Please select a map provider');
                return;
            }
            
            if (isNaN(zoom) || zoom < 1 || zoom > 20) {
                alert('Zoom level must be between 1 and 20');
                return;
            }
            
            if (isNaN(lat) || lat < -90 || lat > 90) {
                alert('Latitude must be between -90 and 90');
                return;
            }
            
            if (isNaN(lon) || lon < -180 || lon > 180) {
                alert('Longitude must be between -180 and 180');
                return;
            }
            
            const settings = {
                map_provider: provider,
                map_zoom: zoom,
                map_center_lat: lat,
                map_center_lon: lon
            };
            
            // Show loading state
            const btn = $('button:contains("Save Map Settings")');
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
            
            $.ajax({
                url: '/api/admin/settings.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(settings),
                dataType: 'json',
                timeout: 10000,
                success: function(response) {
                    btn.prop('disabled', false).html(originalText);
                    if (response.success || response.message) {
                        alert('Map settings saved successfully!');
                        updateMapPreview();
                    } else {
                        alert('Settings saved but no confirmation received');
                    }
                },
                error: function(xhr, status, error) {
                    btn.prop('disabled', false).html(originalText);
                    
                    let errorMsg = 'Unknown error';
                    let errorDetails = '';
                    
                    // Try to parse JSON error
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.error || errorMsg;
                        } catch (e) {
                            errorMsg = xhr.statusText || error || 'Request failed';
                            errorDetails = '\n\nRaw response: ' + xhr.responseText.substring(0, 300);
                        }
                    } else {
                        errorMsg = status === 'timeout' ? 'Request timed out' : (error || 'Network error');
                    }
                    
                    // Add HTTP status if available
                    if (xhr.status) {
                        errorDetails += '\n\nHTTP Status: ' + xhr.status;
                    }
                    
                    let fullMessage = 'Error saving settings:\n\n' + errorMsg + errorDetails;
                    
                    // Add helpful messages for common errors
                    if (errorMsg.includes('does not exist') || errorMsg.includes('Table') || errorMsg.includes('Unknown table')) {
                        fullMessage += '\n\nTo fix: Run the migration:\nmysql -u root -p dragnet < database/migrations/add_settings_table_safe.sql';
                    } else if (errorMsg.includes('Duplicate entry') || errorMsg.includes('23000')) {
                        fullMessage += '\n\nThis might be a duplicate key error. The settings may have been saved. Try refreshing the page.';
                    } else if (errorMsg.includes('Database error')) {
                        fullMessage += '\n\nCheck your database connection and ensure the settings table exists.';
                    }
                    
                    console.error('Settings save error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                    
                    alert(fullMessage);
                }
            });
        };
        
        // Utility functions
        window.escapeHtml = function(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        window.escapeRegex = function(text) {
            return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        };
        
        window.formatDate = function(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString();
        };
        
        window.formatDateTime = function(date) {
            if (!date) return '-';
            return new Date(date).toLocaleString();
        };
        
        window.getStatusBadge = function(status) {
            const badges = {
                'online': '<span class="badge bg-success">Online</span>',
                'offline': '<span class="badge bg-danger">Offline</span>',
                'moving': '<span class="badge bg-primary">Moving</span>',
                'idle': '<span class="badge bg-warning">Idle</span>',
                'parked': '<span class="badge bg-secondary">Parked</span>'
            };
            return badges[status] || '<span class="badge bg-secondary">' + escapeHtml(status) + '</span>';
        };
    });
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>
