<?php

/**
 * Reports Page
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/devices.php';
require_once __DIR__ . '/../includes/assets.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$title = 'Reports - DragNet Portal';
$showNav = true;
$tenantId = require_tenant();

// Get date range (default to last 30 days)
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-chart-bar me-2"></i>Reports</h1>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Report Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?= h($startDate) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" value="<?= h($endDate) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-route me-2 text-primary"></i>Distance Report
                </h5>
                <p class="card-text text-muted">Total distance traveled by assets during the selected period.</p>
                <button class="btn btn-primary" onclick="generateReport('distance')">
                    <i class="fas fa-download me-1"></i>Generate Report
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-clock me-2 text-warning"></i>Idle Time Report
                </h5>
                <p class="card-text text-muted">Analysis of idle time for all assets with ignition on but no movement.</p>
                <button class="btn btn-primary" onclick="generateReport('idle')">
                    <i class="fas fa-download me-1"></i>Generate Report
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>Violations Report
                </h5>
                <p class="card-text text-muted">Speed violations and geofence entry/exit events.</p>
                <button class="btn btn-primary" onclick="generateReport('violations')">
                    <i class="fas fa-download me-1"></i>Generate Report
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-gas-pump me-2 text-info"></i>Fuel Consumption
                </h5>
                <p class="card-text text-muted">Fuel usage analysis based on telemetry data.</p>
                <button class="btn btn-primary" onclick="generateReport('fuel')">
                    <i class="fas fa-download me-1"></i>Generate Report
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-calendar-alt me-2 text-success"></i>Activity Summary
                </h5>
                <p class="card-text text-muted">Daily activity summary with hours of operation.</p>
                <button class="btn btn-primary" onclick="generateReport('activity')">
                    <i class="fas fa-download me-1"></i>Generate Report
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-battery-half me-2 text-secondary"></i>Device Health
                </h5>
                <p class="card-text text-muted">Device status, battery levels, and connectivity reports.</p>
                <button class="btn btn-primary" onclick="generateReport('health')">
                    <i class="fas fa-download me-1"></i>Generate Report
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function generateReport(type) {
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    
    // In production, this would call an API endpoint to generate and download the report
    alert(`Generating ${type} report for ${startDate} to ${endDate}...\n\nReport generation feature coming soon.`);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>
