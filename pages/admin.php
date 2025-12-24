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

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>
