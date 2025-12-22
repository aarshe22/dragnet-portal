<?php
$title = 'Dashboard - DragNet Portal';
ob_start();
?>

<div class="row mb-4">
    <div class="col">
        <h1><i class="fas fa-home me-2"></i>Dashboard</h1>
    </div>
</div>

<div class="row g-3 mb-4" id="dashboard-widgets">
    <!-- Widgets will be loaded here -->
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Device Status</h5>
            </div>
            <div class="card-body">
                <canvas id="deviceStatusChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Recent Alerts</h5>
            </div>
            <div class="card-body">
                <div id="recent-alerts">
                    <p class="text-muted">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load dashboard widgets
    $.get('/api/dashboard/widgets', function(data) {
        renderWidgets(data);
        renderDeviceStatusChart(data);
    });
    
    // Load recent alerts
    $.get('/api/alerts?limit=5', function(alerts) {
        renderRecentAlerts(alerts);
    });
});

function renderWidgets(data) {
    const widgets = [
        { icon: 'fa-microchip', label: 'Total Devices', value: data.total_devices, color: 'primary' },
        { icon: 'fa-signal', label: 'Online', value: data.online_devices, color: 'success' },
        { icon: 'fa-exclamation-triangle', label: 'Offline', value: data.offline_devices, color: 'danger' },
        { icon: 'fa-car', label: 'Total Assets', value: data.total_assets, color: 'info' },
        { icon: 'fa-bell', label: 'Active Alerts', value: data.active_alerts, color: 'warning' },
        { icon: 'fa-exclamation-circle', label: 'Critical', value: data.critical_alerts, color: 'danger' },
    ];
    
    const html = widgets.map(w => `
        <div class="col-md-4 col-lg-2">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas ${w.icon} fa-2x text-${w.color} mb-2"></i>
                    <h3 class="mb-0">${w.value}</h3>
                    <small class="text-muted">${w.label}</small>
                </div>
            </div>
        </div>
    `).join('');
    
    $('#dashboard-widgets').html(html);
}

function renderDeviceStatusChart(data) {
    const ctx = document.getElementById('deviceStatusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Moving', 'Idle', 'Parked'],
            datasets: [{
                data: [data.moving, data.idle, data.parked],
                backgroundColor: ['#28a745', '#ffc107', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true
        }
    });
}

function renderRecentAlerts(alerts) {
    if (alerts.length === 0) {
        $('#recent-alerts').html('<p class="text-muted">No recent alerts</p>');
        return;
    }
    
    const html = alerts.map(alert => `
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
            <div>
                <strong>${alert.type}</strong>
                <br><small class="text-muted">${alert.message || ''}</small>
            </div>
            <span class="badge bg-${alert.severity === 'critical' ? 'danger' : 'warning'}">${alert.severity}</span>
        </div>
    `).join('');
    
    $('#recent-alerts').html(html);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

