<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

if (!isDoctor()) {
    $_SESSION['error'] = "Access denied";
    header("Location: schedule.php");
    exit;
}

$day = $_GET['day'] ?? 0;
$stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$doctor_id = $stmt->get_result()->fetch_assoc()['doctor_id'];

$day_names = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$day_name = $day_names[$day] ?? 'Unknown';

$del = $conn->prepare("DELETE FROM doctor_schedule WHERE doctor_id = ? AND day_of_week = ?");
$del->bind_param("ii", $doctor_id, $day);

if ($del->execute()) {
    $_SESSION['success'] = "Schedule for " . $day_name . " has been removed successfully!";
} else {
    $_SESSION['error'] = "Failed to remove schedule";
}

header("Location: schedule.php");
exit;
?>