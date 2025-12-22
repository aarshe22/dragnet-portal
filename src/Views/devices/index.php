<?php
$title = 'Devices - DragNet Portal';
ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-microchip me-2"></i>Devices</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped" id="devices-table">
            <thead>
                <tr>
                    <th>Device UID</th>
                    <th>IMEI</th>
                    <th>Status</th>
                    <th>Battery</th>
                    <th>Signal</th>
                    <th>Last Check-in</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" class="text-center">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    loadDevices();
    setInterval(loadDevices, 30000); // Refresh every 30 seconds
});

function loadDevices() {
    $.get('/api/devices', function(devices) {
        const tbody = $('#devices-table tbody');
        if (devices.length === 0) {
            tbody.html('<tr><td colspan="7" class="text-center">No devices found</td></tr>');
            return;
        }
        
        const html = devices.map(device => `
            <tr>
                <td>${device.device_uid}</td>
                <td>${device.imei || '-'}</td>
                <td>
                    <span class="badge bg-${device.status === 'online' ? 'success' : 'danger'}">
                        ${device.status}
                    </span>
                </td>
                <td>${device.battery_level !== null ? device.battery_level + '%' : '-'}</td>
                <td>${device.signal_strength !== null ? device.signal_strength : '-'}</td>
                <td>${device.last_checkin ? new Date(device.last_checkin).toLocaleString() : '-'}</td>
                <td>
                    <a href="/devices/${device.id}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
        `).join('');
        
        tbody.html(html);
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

