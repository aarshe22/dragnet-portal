<?php
$title = 'Administration - DragNet Portal';
ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-cog me-2"></i>Administration</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">User Management</h5>
            </div>
            <div class="card-body">
                <p>Manage users, roles, and permissions.</p>
                <a href="/admin/users" class="btn btn-primary">
                    <i class="fas fa-users me-1"></i>Manage Users
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">System Settings</h5>
            </div>
            <div class="card-body">
                <p>Configure alert rules and system defaults.</p>
                <button class="btn btn-primary" onclick="loadSettings()">
                    <i class="fas fa-cog me-1"></i>Configure Settings
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function loadSettings() {
    $.get('/api/admin/settings', function(settings) {
        // Placeholder - would show settings modal
        alert('Settings configuration UI would go here');
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

