<?php

/**
 * Login Page
 */

$title = 'Login - Dragnet Intelematics';
$showNav = false;

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$ssoConfig = $config['sso'];
$entraEnabled = $ssoConfig['providers']['entra']['enabled'];
$googleEnabled = $ssoConfig['providers']['google']['enabled'];

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fas fa-shield-alt fa-3x mb-3" style="color: #d4af37;"></i>
                    <h2 class="card-title" style="font-family: 'Special Elite', monospace; letter-spacing: 2px; text-transform: uppercase;">Dragnet <span style="color: #d4af37; text-shadow: 0 0 10px rgba(212, 175, 55, 0.8);">INTEL</span>EMATICS</h2>
                    <p class="text-muted" style="font-family: 'Special Elite', monospace; letter-spacing: 1px;">Just the facts, ma'am.</p>
                </div>
                
                <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?= h($_GET['error']) ?>
                </div>
                <?php endif; ?>
                
                <div class="d-grid gap-2">
                    <?php if ($entraEnabled): ?>
                    <a href="/auth/oauth?provider=entra" class="btn btn-primary btn-lg">
                        <i class="fab fa-microsoft me-2"></i>Sign in with Microsoft
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($googleEnabled): ?>
                    <a href="/auth/oauth?provider=google" class="btn btn-danger btn-lg">
                        <i class="fab fa-google me-2"></i>Sign in with Google
                    </a>
                    <?php endif; ?>
                    
                    <?php if (!$entraEnabled && !$googleEnabled): ?>
                    <div class="alert alert-info">
                        <strong>Development Mode</strong><br>
                        SSO is not configured. Use the test login below for development/testing.
                    </div>
                    
                    <!-- Development/Testing Login Form -->
                    <form method="POST" action="/auth_callback.php" id="devLoginForm">
                        <div class="mb-3">
                            <label for="dev_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="dev_email" name="email" 
                                   value="admin@example.com" required>
                        </div>
                        <div class="mb-3">
                            <label for="dev_tenant_id" class="form-label">Tenant ID</label>
                            <input type="number" class="form-control" id="dev_tenant_id" name="tenant_id" 
                                   value="1" min="1" required>
                        </div>
                        <input type="hidden" name="provider" value="dev">
                        <button type="submit" class="btn btn-secondary btn-lg w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Test Login
                        </button>
                    </form>
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

