<?php

/**
 * Logout Page
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

$config = $GLOBALS['config'];
session_start_custom($config['session']);

// If simulator is running, show confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    session_destroy_custom();
    redirect('/login.php');
}

$title = 'Logout - Dragnet Intelematics';
$showNav = false;

ob_start();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Logging Out</h5>
                </div>
                <div class="card-body">
                    <p id="logoutMessage">Checking for active processes...</p>
                    <form method="POST" id="logoutForm" style="display: none;">
                        <input type="hidden" name="confirm_logout" value="1">
                        <button type="button" class="btn btn-warning" onclick="stopSimulatorAndLogout()">
                            <i class="fas fa-stop me-1"></i>Stop Simulator & Logout
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function stopSimulatorAndLogout() {
    localStorage.removeItem('simulatorStreaming');
    localStorage.removeItem('simulatorPacketCount');
    localStorage.removeItem('simulatorFailedCount');
    document.getElementById('logoutForm').submit();
}

// Check localStorage for simulator state
if (localStorage.getItem('simulatorStreaming')) {
    document.getElementById('logoutMessage').textContent = 'A telemetry simulator is currently running. Would you like to stop it before logging out?';
    document.getElementById('logoutForm').style.display = 'block';
} else {
    // No simulator running, proceed with logout
    window.location.href = '/login.php';
    <?php
    session_destroy_custom();
    ?>
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>
