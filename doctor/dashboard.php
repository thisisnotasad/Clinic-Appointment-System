<?php require_once '../includes/auth.php'; ?>
<?php require_once '../includes/header.php'; ?>

<div class="container">
    <h1 class="mb-4">Doctor Dashboard</h1>
    <div class="alert alert-info">
        Welcome, Dr. <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>! You are logged in as <strong>Doctor</strong>.
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>