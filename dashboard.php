<?php
require_once 'includes/auth.php';     // ← Load auth FIRST (contains isAdmin(), etc.)
require_once 'includes/header.php';   // ← Then load header

// Now the role functions are available!
if (isAdmin()) {
    header("Location: admin/dashboard.php");
    exit();
} elseif (isDoctor()) {
    header("Location: doctor/dashboard.php");
    exit();
} elseif (isPatient()) {
    header("Location: patient/dashboard.php");
    exit();
}
?>

<div class="alert alert-info">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h2>
    <p>Redirecting to your dashboard...</p>
</div>