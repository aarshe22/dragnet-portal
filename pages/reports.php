<?php

/**
 * Reports Page
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$title = 'Reports - DragNet Portal';

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-chart-bar me-2"></i>Reports</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Distance Report</h5>
                <p class="card-text text-muted">Total distance traveled by assets</p>
                <button class="btn btn-primary">Generate Report</button>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Idle Time Report</h5>
                <p class="card-text text-muted">Idle time analysis</p>
                <button class="btn btn-primary">Generate Report</button>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Violations Report</h5>
                <p class="card-text text-muted">Speed and geofence violations</p>
                <button class="btn btn-primary">Generate Report</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

