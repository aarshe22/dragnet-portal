<?php
$title = 'Device Detail - DragNet Portal';
ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-microchip me-2"></i>Device Details</h1>
    </div>
</div>

<div class="card">
    <div class="card-body" id="device-details">
        <p class="text-muted">Loading...</p>
    </div>
</div>

<script>
const deviceId = <?= $device_id ?? 0 ?>;

$(document).ready(function() {
    if (deviceId) {
        loadDeviceDetails();
        setInterval(loadDeviceDetails, 30000);
    }
});

function loadDeviceDetails() {
    $.get(`/api/devices/${deviceId}`, function(device) {
        const html = `
            <h4>${device.device_uid}</h4>
            <p><strong>IMEI:</strong> ${device.imei || '-'}</p>
            <p><strong>Status:</strong> <span class="badge bg-${device.status === 'online' ? 'success' : 'danger'}">${device.status}</span></p>
            <p><strong>Battery:</strong> ${device.battery_level !== null ? device.battery_level + '%' : '-'}</p>
            <p><strong>Signal:</strong> ${device.signal_strength !== null ? device.signal_strength : '-'}</p>
            <p><strong>Last Check-in:</strong> ${device.last_checkin ? new Date(device.last_checkin).toLocaleString() : '-'}</p>
            ${device.latest_telemetry ? `
                <hr>
                <h5>Latest Position</h5>
                <p><strong>Latitude:</strong> ${device.latest_telemetry.lat}</p>
                <p><strong>Longitude:</strong> ${device.latest_telemetry.lon}</p>
                <p><strong>Speed:</strong> ${device.latest_telemetry.speed || 0} mph</p>
            ` : ''}
        `;
        $('#device-details').html(html);
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

