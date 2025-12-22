<?php
$title = 'Login - DragNet Portal';
$showNav = false;
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fas fa-satellite-dish fa-3x text-primary mb-3"></i>
                    <h2 class="card-title">DragNet Portal</h2>
                    <p class="text-muted">Sign in to continue</p>
                </div>
                
                <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_GET['error']) ?>
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
                    <div class="alert alert-warning">
                        SSO is not configured. Please contact your administrator.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>

