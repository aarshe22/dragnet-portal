<?php
$title = 'Alerts - DragNet Portal';
ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-bell me-2"></i>Alerts</h1>
    </div>
    <div class="col-auto">
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="filterAlerts('all')">All</button>
            <button class="btn btn-outline-warning" onclick="filterAlerts('unacknowledged')">Unacknowledged</button>
            <button class="btn btn-outline-danger" onclick="filterAlerts('critical')">Critical</button>
        </div>
        <button class="btn btn-primary ms-2" onclick="exportAlerts()">
            <i class="fas fa-download me-1"></i>Export
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped" id="alerts-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Severity</th>
                    <th>Message</th>
                    <th>Device</th>
                    <th>Created</th>
                    <th>Status</th>
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
let currentFilter = 'all';

$(document).ready(function() {
    loadAlerts();
});

function loadAlerts(filter = {}) {
    const params = new URLSearchParams(filter);
    $.get('/api/alerts?' + params, function(alerts) {
        const tbody = $('#alerts-table tbody');
        if (alerts.length === 0) {
            tbody.html('<tr><td colspan="7" class="text-center">No alerts found</td></tr>');
            return;
        }
        
        const html = alerts.map(alert => `
            <tr>
                <td>${alert.type}</td>
                <td><span class="badge bg-${alert.severity === 'critical' ? 'danger' : 'warning'}">${alert.severity}</span></td>
                <td>${alert.message || '-'}</td>
                <td>${alert.device_uid || alert.device_id}</td>
                <td>${new Date(alert.created_at).toLocaleString()}</td>
                <td>${alert.acknowledged ? '<span class="badge bg-success">Acknowledged</span>' : '<span class="badge bg-secondary">Pending</span>'}</td>
                <td>
                    ${!alert.acknowledged ? `
                        <button class="btn btn-sm btn-success" onclick="acknowledgeAlert(${alert.id})">
                            <i class="fas fa-check"></i>
                        </button>
                    ` : ''}
                </td>
            </tr>
        `).join('');
        
        tbody.html(html);
    });
}

function filterAlerts(type) {
    currentFilter = type;
    const filter = {};
    
    if (type === 'unacknowledged') {
        filter.acknowledged = 'false';
    } else if (type === 'critical') {
        filter.severity = 'critical';
    }
    
    loadAlerts(filter);
}

function acknowledgeAlert(id) {
    $.post(`/api/alerts/${id}/acknowledge`, function() {
        loadAlerts();
    });
}

function exportAlerts() {
    window.location.href = '/api/alerts/export?' + new URLSearchParams({ type: currentFilter });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

