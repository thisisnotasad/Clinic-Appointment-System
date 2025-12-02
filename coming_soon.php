<!-- coming_soon.php -->
<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="content-card text-center">
                <div class="feature-icon mb-4" style="font-size: 5rem; color: var(--primary);">
                    <i class="fas fa-tools"></i>
                </div>
                
                <h2 class="mb-3">Feature Coming Soon</h2>
                <p class="lead text-muted mb-4">
                    This feature is currently under development and will be available in a future update.
                </p>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    We're working hard to bring you the best experience. Thank you for your patience.
                </div>

                <div class="mt-5">
                    <a href="<?= SITE_URL ?>/<?= $base_path ?>dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>