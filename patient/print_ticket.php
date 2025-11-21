<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
if (!isPatient()) exit;

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("
    SELECT a.*, u.full_name as patient_name, d.full_name as doctor_name, s.name as spec_name
    FROM appointments a
    JOIN users u ON a.patient_id = u.user_id
    JOIN doctors doc ON a.doctor_id = doc.doctor_id
    JOIN users d ON doc.user_id = d.user_id
    JOIN specializations s ON doc.specialization_id = s.specialization_id
    WHERE a.appointment_id = ? AND a.patient_id = ?
");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$apt = $stmt->get_result()->fetch_assoc();
if (!$apt) die("Invalid ticket");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Appointment Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>@media print { body { margin: 0; padding: 20px; } }</style>
</head>
<body class="bg-light">
<div class="container py-my-5">
    <div class="card border-primary shadow">
        <div class="card-header bg-primary text-white text-center py-4">
            <h2>Appointment Ticket</h2>
        </div>
        <div class="card-body p-5 text-center">
            <h4>Patient Name: <?= $apt['patient_name'] ?></h4>
            <h5>Doctor: <?= $apt['doctor_name'] ?> (<?= $apt['spec_name'] ?>)</h5>
            <hr>
            <h3>Date: <?= date('d M Y', strtotime($apt['appointment_date'])) ?></h3>
            <h3>Time: <?= date('h:i A', strtotime($apt['appointment_time'])) ?></h3>
            <p><strong>Status:</strong> <span class="badge bg-success fs-6"><?= ucfirst($apt['status']) ?></span></p>
            <small>Token No: <?= $apt['appointment_id'] ?></small>
        </div>
        <div class="card-footer text-center">
            <em>Thank you for choosing <?= SITE_NAME ?>!</em>
        </div>
    </div>
    <div class="text-center mt-4">
        <button onclick="window.print()" class="btn btn-success btn-lg">Print Ticket</button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg ms-3">Close</button>
    </div>
</div>
</body>
</html>