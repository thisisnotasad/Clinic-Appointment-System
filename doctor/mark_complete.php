<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

if (!isDoctor()) {
    $_SESSION['error'] = "Access denied";
    header("Location: dashboard.php");
    exit;
}

$id = $_GET['id'] ?? 0;

// Verify doctor owns this appointment
$stmt = $conn->prepare("
    SELECT a.appointment_id, u.full_name FROM appointments a
    JOIN doctors d ON a.doctor_id = d.doctor_id
    JOIN users u ON a.patient_id = u.user_id
    WHERE a.appointment_id = ? AND d.user_id = ?
");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Invalid appointment";
    header("Location: dashboard.php");
    exit;
}

$appointment = $result->fetch_assoc();

$update = $conn->prepare("UPDATE appointments SET status = 'completed' WHERE appointment_id = ?");
$update->bind_param("i", $id);

if ($update->execute()) {
    $_SESSION['success'] = "Appointment with " . htmlspecialchars($appointment['full_name']) . " marked as completed!";
} else {
    $_SESSION['error'] = "Failed to update appointment status";
}

header("Location: dashboard.php");
exit;
?>