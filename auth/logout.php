<?php
require_once '../includes/config.php';
// Unset all session variables
$_SESSION = [];

// If there's a session cookie, remove it
if (ini_get("session.use_cookies")) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'], $params['secure'], $params['httponly']
	);
}

// Finally destroy the session
session_destroy();

// Send no-cache headers to prevent browser from caching protected pages
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

header("Location: " . SITE_URL . "/index.php");
exit();
?>