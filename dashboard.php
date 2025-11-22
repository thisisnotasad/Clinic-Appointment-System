<!-- dashboard.php -->
<?php
require_once 'includes/auth.php';     // ← Load auth FIRST (contains redirect function)

// Call redirect BEFORE any output
redirectBasedOnRole();

// If we reach here, something went wrong with redirect
require_once 'includes/header.php';   // ← Load header only if no redirect happened
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="alert alert-danger text-center">
                <h2><i class="fas fa-exclamation-triangle me-2"></i>Redirect Error</h2>
                <p class="mb-3">Unable to redirect to your dashboard. Please contact support.</p>
                <div class="user-info p-3 bg-light rounded">
                    <p><strong>User:</strong> <?= htmlspecialchars($_SESSION['full_name']) ?></p>
                    <p><strong>Role:</strong> <?= ucfirst($_SESSION['role']) ?></p>
                </div>
                <div class="mt-3">
                    <a href="auth/logout.php" class="btn btn-primary">Logout</a>
                    <a href="index.php" class="btn btn-outline-primary">Home</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>