<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
if (!isDoctor()) die("Access denied");

$day = $_GET['day'] ?? 0;
$stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$doctor_id = $stmt->get_result()->fetch_assoc()['doctor_id'];

$del = $conn->prepare("DELETE FROM doctor_schedule WHERE doctor_id = ? AND day_of_week = ?");
$del->bind_param("ii", $doctor_id, $day);
$del->execute();

header("Location: schedule.php");
exit;
?>