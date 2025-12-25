<?php

/**
 * Settings Page
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

$title = 'Settings - Dragnet Intelematics';
$showNav = true;

ob_start();
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Settings</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h6>Notifications</h6>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="pushNotifications" onchange="togglePushNotifications()">
                        <label class="form-check-label" for="pushNotifications">
                            Enable Push Notifications
                        </label>
                    </div>
                    <small class="text-muted">Receive real-time alerts and updates</small>
                </div>
                
                <div class="mb-4">
                    <h6>Preferences</h6>
                    <div class="mb-3">
                        <label class="form-label">Default Map View</label>
                        <select class="form-select">
                            <option>Satellite</option>
                            <option selected>Street Map</option>
                            <option>Terrain</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Time Zone</label>
                        <select class="form-select">
                            <option>UTC</option>
                            <option selected>Local Time</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6>Data Export</h6>
                    <button class="btn btn-outline-primary" onclick="exportData()">
                        <i class="fas fa-download me-1"></i>Export My Data
                    </button>
                    <small class="text-muted d-block mt-2">Download all your data in JSON format</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePushNotifications() {
    const enabled = $('#pushNotifications').is(':checked');
    if (enabled) {
        if (typeof DragNet !== 'undefined' && DragNet.requestNotificationPermission) {
            DragNet.requestNotificationPermission();
        } else {
            alert('Push notifications are not available in this browser');
            $('#pushNotifications').prop('checked', false);
        }
    }
}

function exportData() {
    alert('Data export feature coming soon');
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

