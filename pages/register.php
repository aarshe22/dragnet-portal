<?php

/**
 * User Registration Page (via invitation)
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/invites.php';
require_once __DIR__ . '/../includes/auth.php';

$config = $GLOBALS['config'] ?? require __DIR__ . '/../config.php';
$GLOBALS['config'] = $config;

db_init($config['database']);
session_start_custom($config['session']);

$token = $_GET['token'] ?? '';
$error = '';
$success = false;
$invite = null;

if ($token) {
    $invite = invite_find_by_token($token);
    
    if (!$invite) {
        $error = 'Invalid invitation link. Please contact your administrator.';
    } elseif ($invite['accepted_at']) {
        $error = 'This invitation has already been accepted.';
    } elseif (strtotime($invite['expires_at']) < time()) {
        $error = 'This invitation has expired. Please contact your administrator for a new invitation.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token && !$error && $invite) {
    try {
        $user = invite_accept($token);
        
        // Set tenant context and log in the user
        set_tenant_context([
            'tenant_id' => $user['tenant_id'],
            'user_id' => $user['id'],
            'user_email' => $user['email'],
            'user_role' => $user['role'],
        ]);
        
        $success = true;
        
        // Redirect to dashboard after a short delay
        header('Refresh: 2; url=/dashboard.php');
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$title = 'Accept Invitation - Dragnet Intelematics';
$showNav = false;

ob_start();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center" style="background: linear-gradient(180deg, var(--dragnet-dark-gray) 0%, var(--dragnet-gray) 100%); border-bottom: 2px solid var(--dragnet-badge-gold);">
                    <h3 class="mb-0" style="font-family: var(--dragnet-typewriter); letter-spacing: 2px; text-transform: uppercase; color: var(--dragnet-white);">
                        <i class="fas fa-shield-alt me-2" style="color: var(--dragnet-badge-gold);"></i>Accept Invitation
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <h4>Account Created Successfully!</h4>
                            <p>You have been registered and logged in. Redirecting to dashboard...</p>
                        </div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="/login.php" class="btn btn-primary">Go to Login</a>
                        </div>
                    <?php elseif ($invite): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>You've been invited!</strong>
                        </div>
                        
                        <div class="mb-4">
                            <p><strong>Email:</strong> <?= htmlspecialchars($invite['email']) ?></p>
                            <?php if (isset($invite['tenant_name'])): ?>
                                <p><strong>Organization:</strong> <?= htmlspecialchars($invite['tenant_name']) ?></p>
                            <?php endif; ?>
                            <p><strong>Role:</strong> <span class="badge bg-info"><?= htmlspecialchars($invite['role']) ?></span></p>
                            <p><strong>Expires:</strong> <?= date('F j, Y \a\t g:i A', strtotime($invite['expires_at'])) ?></p>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check me-2"></i>Accept Invitation & Create Account
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>No invitation token provided.
                        </div>
                        <div class="text-center mt-3">
                            <a href="/login.php" class="btn btn-primary">Go to Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

