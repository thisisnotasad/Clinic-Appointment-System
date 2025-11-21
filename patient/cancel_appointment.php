<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../includes/mailer.php';

if (!isPatient()) {
    die("Access denied.");
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    die("Invalid request.");
}

// Verify ownership
$stmt = $conn->prepare("SELECT a.*, p.full_name AS patient_name, p.email AS patient_email,
                              docu.email AS doctor_email, docu.full_name AS doctor_name
                       FROM appointments a
                       JOIN users p ON a.patient_id = p.user_id
                       JOIN doctors d ON a.doctor_id = d.doctor_id
                       JOIN users docu ON d.user_id = docu.user_id
                       WHERE a.appointment_id = ? AND a.patient_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Appointment not found or access denied.");
}

$apt = $result->fetch_assoc();

if ($apt['status'] !== 'pending') {
    die("This appointment cannot be cancelled.");
}

// CANCEL + SEND EMAILS
$update = $conn->prepare("UPDATE appointments SET status = 'cancelled', cancelled_by = 'patient' WHERE appointment_id = ?");
$update->bind_param("i", $id);
$update->execute();

// Format date/time
$apt_date = date('d M Y', strtotime($apt['appointment_date']));
$apt_time = date('h:i A', strtotime($apt['appointment_time']));

// Email to Patient
$patient_html = "
<div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;border:2px solid #dc3545;border-radius:12px;padding:25px;text-align:center;background:#fff;'>
    <h2 style='color:#dc3545;'>Appointment Cancelled</h2>
    <p>Dear <strong>{$apt['patient_name']}</strong>,</p>
    <p>Your appointment has been successfully cancelled.</p>
    <hr>
    <h4>Dr. {$apt['doctor_name']}</h4>
    <p><strong>Date:</strong> $apt_date</p>
    <p><strong>Time:</strong> $apt_time</p>
    <p><strong>Token:</strong> #$id</p>
    <hr>
    <p style='color:#666;'>We hope to see you soon!</p>
</div>";

// Email to Doctor
$doctor_html = "
<h2>Appointment Cancelled by Patient</h2>
<p>Dear Dr. {$apt['doctor_name']},</p>
<p>Patient <strong>{$apt['patient_name']}</strong> has cancelled their appointment:</p>
<hr>
<p><strong>Date:</strong> $apt_date</p>
<p><strong>Time:</strong> $apt_time</p>
<p><strong>Token:</strong> #$id</p>
";

// Send both emails in background (fast response)
ignore_user_abort(true);
sendEmail($apt['patient_email'], "Appointment Cancelled – Token #$id", $patient_html);
sendEmail($apt['doctor_email'], "Patient Cancelled Appointment – Token #$id", $doctor_html);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Cancelled</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh; display: flex; align-items: center; }
        .success-card { max-width: 500px; margin: auto; border-radius: 20px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.3); }
        .card-header { background: #dc3545; color: white; text-align: center; padding: 30px; }
        .card-body { background: white; padding: 40px; text-align: center; }
        .icon { font-size: 4rem; color: #dc3545; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="success-card">
    <div class="card-header">
        <i class="fas fa-calendar-times icon"></i>
        <h2>Appointment Cancelled</h2>
    </div>
    <div class="card-body">
        <h4>Token #<?= $id ?></h4>
        <p><strong>Dr. <?= htmlspecialchars($apt['doctor_name']) ?></strong></p>
        <p class="text-muted">
            <?= $apt_date ?> at <?= $apt_time ?>
        </p>
        <hr>
        <p class="text-success fs-5">
            <i class="fas fa-check-circle"></i> Cancellation successful!
        </p>
        <p class="text-muted">Confirmation emails have been sent to you and the doctor.</p>

        <div class="mt-4">
            <a href="my_appointments.php" class="btn btn-outline-danger btn-lg px-5">
                <i class="fas fa-list"></i> My Appointments
            </a>
            <a href="book_appointment.php" class="btn btn-success btn-lg px-5 ms-3">
                <i class="fas fa-calendar-plus"></i> Book New
            </a>
        </div>

        <div class="mt-4 text-muted small">
            <i class="fas fa-clock"></i> Auto-redirecting in <span id="countdown">5</span> seconds...
        </div>
    </div>
</div>

<script>
// Auto redirect after 5 seconds
let seconds = 5;
const countdown = document.getElementById('countdown');
const timer = setInterval(() => {
    seconds--;
    countdown.textContent = seconds;
    if (seconds <= 0) {
        clearInterval(timer);
        window.location.href = "my_appointments.php";
    }
}, 1000);
</script>

</body>
</html>