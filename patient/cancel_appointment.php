<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

if (!isPatient()) {
    echo "Access denied."; exit;
}

$id = $_GET['id'] ?? 0;

// Verify this appointment belongs to the logged-in patient
$stmt = $conn->prepare("SELECT appointment_id, status FROM appointments WHERE appointment_id = ? AND patient_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid appointment or access denied.");
}

$row = $result->fetch_assoc();

if ($row['status'] !== 'pending') {
    header("Location: my_appointments.php?msg=Cannot cancel completed/cancelled appointment");
    exit;
}

// Cancel it
$update = $conn->prepare("UPDATE appointments SET status = 'cancelled', cancelled_by = 'patient' WHERE appointment_id = ?");
$update->bind_param("i", $id);

if ($update->execute()) {
    header("Location: my_appointments.php?msg=Appointment cancelled successfully!");
} else {
    header("Location: my_appointments.php?msg=Cancellation failed. Try again.");
}
exit;
?>