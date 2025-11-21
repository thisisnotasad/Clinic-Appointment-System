<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../includes/mailer.php';   // ← This line was missing before!

header('Content-Type: application/json');

if (!isPatient()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$doctor_id  = $_POST['doctor_id'] ?? 0;
$date       = $_POST['date'] ?? '';
$time       = $_POST['time'] ?? '';
$patient_id = $_SESSION['user_id'];

if (!$doctor_id || !$date || !$time) {
    echo json_encode(['error' => 'Missing data']);
    exit;
}

// Convert to 24-hour format
$time24 = date('H:i:s', strtotime($time));

// Prevent double booking
$check = $conn->prepare("
    SELECT appointment_id FROM appointments 
    WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'
");
$check->bind_param("iss", $doctor_id, $date, $time24);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['error' => 'This slot was just taken!']);
    exit;
}

// BOOK IT
$stmt = $conn->prepare("
    INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) 
    VALUES (?, ?, ?, ?, 'pending')
");
$stmt->bind_param("iiss", $patient_id, $doctor_id, $date, $time24);

if ($stmt->execute()) {
    $apt_id = $conn->insert_id;

    // Get full details for email
    $info = $conn->query("
        SELECT 
            a.appointment_date, a.appointment_time,
            p.full_name AS patient_name, p.email AS patient_email,
            d.full_name AS doctor_name, s.name AS spec_name,
            doc.user_id AS doctor_user_id
        FROM appointments a
        JOIN users p ON a.patient_id = p.user_id
        JOIN doctors doc ON a.doctor_id = doc.doctor_id
        JOIN users d ON doc.user_id = d.user_id
        JOIN specializations s ON doc.specialization_id = s.specialization_id
        WHERE a.appointment_id = $apt_id
    ")->fetch_assoc();

    $pretty_date = date('d M Y', strtotime($info['appointment_date']));
    $pretty_time = date('h:i A', strtotime($info['appointment_time']));

    // Email to Patient
    $patient_html = "
    <h2 style='color:#007bff;'>Appointment Confirmed!</h2>
    <p>Dear <strong>{$info['patient_name']}</strong>,</p>
    <p>Your appointment has been successfully booked.</p>
    <hr>
    <p><strong>Doctor:</strong> Dr. {$info['doctor_name']} ({$info['spec_name']})</p>
       <p><strong>Date:</strong> $pretty_date</p>
    <p><strong>Time:</strong> $pretty_time</p>
    <p><strong>Token No:</strong> #$apt_id</p>
    <hr>
    <p>Thank you for choosing <strong>" . SITE_NAME . "</strong>!</p>
    ";

    // Email to Doctor
    $doctor_html = "
    <h2>New Appointment</h2>
    <p>Dear Dr. {$info['doctor_name']},</p>
    <p>A new patient has booked an appointment with you:</p>
    <hr>
    <p><strong>Patient:</strong> {$info['patient_name']}</p>
    <p><strong>Date:</strong> $pretty_date</p>
    <p><strong>Time:</strong> $pretty_time</p>
    <p><strong>Token:</strong> #$apt_id</p>
    ";

    // Send emails
    sendEmail($info['patient_email'], "Appointment Confirmed – Token #$apt_id", $patient_html);
    
    // Get doctor email
    $doc_email_result = $conn->query("SELECT email FROM users WHERE user_id = {$info['doctor_user_id']}");
    $doc_email = $doc_email_result->fetch_assoc()['email'] ?? 'admin@clinic.com';
    sendEmail($doc_email, "New Appointment – Token #$apt_id", $doctor_html);

    echo json_encode(['success' => 'Appointment booked! Confirmation email sent.']);
} else {
    echo json_encode(['error' => 'Booking failed. Please try again.']);
}
?>