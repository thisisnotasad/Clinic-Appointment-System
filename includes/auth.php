<?php
// includes/auth.php
require_once __DIR__ . '/config.php';   // safer include

if (!isset($_SESSION['user_id'])) {
    // Correct absolute path from project root
    header("Location: " . SITE_URL . "/auth/login.php");
    exit();
}

function isPatient()  { return $_SESSION['role'] === 'patient'; }
function isDoctor()   { return $_SESSION['role'] === 'doctor'; }
function isAdmin()    { return $_SESSION['role'] === 'admin'; }
?>

