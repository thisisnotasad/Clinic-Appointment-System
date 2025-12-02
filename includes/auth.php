<!-- includes/auth.php -->
<?php
require_once 'config.php';  // This starts the session
// Prevent caching of pages that include this file (protected pages)
// This helps ensure browser back/forward won't show stale authenticated content after logout
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isDoctor() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'doctor';
}

function isPatient() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'patient';
}

// Role-based redirects - call this BEFORE any HTML output
function redirectBasedOnRole() {
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
}
?>