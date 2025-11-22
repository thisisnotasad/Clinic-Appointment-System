<!-- includes/auth.php -->
<?php
require_once 'config.php';  // This starts the session

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