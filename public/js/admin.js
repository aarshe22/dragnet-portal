/**
 * Admin Panel JavaScript
 */

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
        }
    });
});

// ========== TENANTS ==========

function loadTenants() {
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
}

function showTenantModal(id = null) {
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
}

function saveTenant() {
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
}

function editTenant(id) {
    showTenantModal(id);
}

function deleteTenant(id) {
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
}

// ========== USERS ==========

function loadUsers() {
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
}

function showUserModal(id = null) {
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
}

function saveUser() {
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
}

function editUser(id) {
    showUserModal(id);
}

function deleteUser(id) {
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
}

// ========== DEVICES ==========

function loadDevices() {
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
}

function showDeviceModal(id = null) {
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
}

function saveDevice() {
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
}

function editDevice(id) {
    showDeviceModal(id);
}

function deleteDevice(id) {
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
}

// ========== LOGS ==========

function loadLogFilters() {
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
}

function loadLogs() {
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
}

function filterLogs() {
    currentLogSearch = $('#logSearch').val();
    loadLogs();
}

function toggleAutoRefresh() {
    autoRefreshEnabled = !autoRefreshEnabled;
    const btn = $('#autoRefreshBtn');
    if (autoRefreshEnabled) {
        btn.html('<i class="fas fa-pause me-1"></i>Pause Auto-Refresh');
    } else {
        btn.html('<i class="fas fa-play me-1"></i>Resume Auto-Refresh');
    }
}

function clearLogs() {
    $('#logsTableBody').empty();
}

// ========== UTILITIES ==========

function loadTenantOptions() {
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
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function escapeRegex(text) {
    return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
}

function formatDateTime(date) {
    if (!date) return '-';
    return new Date(date).toLocaleString();
}

function getStatusBadge(status) {
    const badges = {
        'online': '<span class="badge bg-success">Online</span>',
        'offline': '<span class="badge bg-danger">Offline</span>',
        'moving': '<span class="badge bg-primary">Moving</span>',
        'idle': '<span class="badge bg-warning">Idle</span>',
        'parked': '<span class="badge bg-secondary">Parked</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">' + escapeHtml(status) + '</span>';
}

