<?php require_once '../includes/auth.php'; ?>
<?php require_once '../includes/header.php'; ?>

<div class="container">
    <h1 class="mb-4">Patient Dashboard</h1>
    <div class="alert alert-primary">
        Welcome, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>! You are logged in as <strong>Patient</strong>.
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>