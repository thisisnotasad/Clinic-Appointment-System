<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isPatient()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$doctor_id = $_POST['doctor_id'] ?? 0;
$date      = $_POST['date'] ?? '';
$time      = $_POST['time'] ?? '';
$patient_id = $_SESSION['user_id'];

if (!$doctor_id || !$date || !$time) {
    echo json_encode(['error' => 'Missing data']);
    exit;
}

// Convert time to 24-hour format for DB
$time24 = date('H:i:s', strtotime($time));

// Double-check slot is still free (prevent race condition)
$check = $conn->prepare("
    SELECT appointment_id FROM appointments 
    WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'
");
$check->bind_param("iss", $doctor_id, $date, $time24);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['error' => 'Sorry, this slot was just taken!']);
    exit;
}

// Book it!
$stmt = $conn->prepare("
    INSERT INTO appointments 
    (patient_id, doctor_id, appointment_date, appointment_time, status) 
    VALUES (?, ?, ?, ?, 'pending')
");
$stmt->bind_param("iiss", $patient_id, $doctor_id, $date, $time24);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Appointment booked successfully!']);
} else {
    echo json_encode(['error' => 'Booking failed. Try again.']);
}
?>