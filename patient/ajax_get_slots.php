<?php
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$doctor_id = $_GET['doctor_id'] ?? 0;
$date      = $_GET['date'] ?? '';

if (!$doctor_id || !$date) {
    echo json_encode(['slots' => [], 'booked' => []]);
    exit;
}

// Get doctor's schedule for that day of week
$day_of_week = date('w', strtotime($date)); // 0=Sun, 1=Mon...

$stmt = $conn->prepare("
    SELECT start_time, end_time, slot_duration 
    FROM doctor_schedule 
    WHERE doctor_id = ? AND day_of_week = ? AND is_active = 1
");
$stmt->bind_param("ii", $doctor_id, $day_of_week);
$stmt->execute();
$sched = $stmt->get_result()->fetch_assoc();

$slots = [];
$booked = [];

if ($sched) {
    $start = strtotime($sched['start_time']);
    $end   = strtotime($sched['end_time']);
    $duration = $sched['slot_duration'] * 60; // minutes to seconds

    for ($time = $start; $time < $end; $time += $duration) {
        $slot = date('h:i A', $time);
        $slots[] = $slot;
    }

    // Check which slots are already booked
    $stmt2 = $conn->prepare("
        SELECT appointment_time 
        FROM appointments 
        WHERE doctor_id = ? AND appointment_date = ? AND status != 'cancelled'
    ");
    $stmt2->bind_param("is", $doctor_id, $date);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    while ($row = $result2->fetch_assoc()) {
        $booked[] = date('h:i A', strtotime($row['appointment_time']));
    }
    $stmt2->close();
}

echo json_encode([
    'slots' => $slots,
    'booked' => $booked
]);
?>