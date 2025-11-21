<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

if (!isDoctor()) die("Access denied");

$id = $_GET['id'] ?? 0;

// Verify doctor owns this appointment
$stmt = $conn->prepare("
    SELECT a.appointment_id FROM appointments a
    JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.appointment_id = ? AND d.user_id = ?
");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) die("Invalid appointment");

$update = $conn->prepare("UPDATE appointments SET status = 'completed' WHERE appointment_id = ?");
$update->bind_param("i", $id);
$update->execute();

header("Location: dashboard.php?msg=Appointment marked as completed!");
exit;
?>