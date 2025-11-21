<?php
require_once '../includes/db_connect.php';
require_once '../includes/mailer.php';

$tomorrow = date('Y-m-d', strtotime('+1 day'));
$res = $conn->query("SELECT a.*, p.email, p.full_name, d.full_name as doc_name FROM appointments a JOIN users p ON a.patient_id=p.user_id JOIN doctors doc ON a.doctor_id=doc.doctor_id JOIN users du ON doc.user_id=du.user_id WHERE a.appointment_date='$tomorrow' AND a.status='pending'");

while ($apt = $res->fetch_assoc()) {
    $body = "<h2>Reminder</h2><p>Dear {$apt['full_name']},<br>Your appointment with Dr. {$apt['doc_name']} is tomorrow at " . date('h:i A', strtotime($apt['appointment_time'])) . ".</p>";
    sendEmail($apt['email'], "Appointment Reminder Tomorrow", $body);
}