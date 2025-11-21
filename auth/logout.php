<?php
require_once '../includes/config.php';

session_destroy();   // Clear all session data

header("Location: " . SITE_URL . "/index.php");
exit();
?>