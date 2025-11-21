<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
if (!isAdmin()) die("Access denied");

$user_id = $_GET['id'] ?? 0;

// Delete user (cascades to doctors table due to FK)
$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'doctor'");
$stmt->bind_param("i", $user_id);
$stmt->execute();

header("Location: doctors.php?msg=Doctor deleted successfully");
exit;
?>