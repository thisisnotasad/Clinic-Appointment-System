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

// Verify ownership and fetch details
$stmt = $conn->prepare("SELECT a.*, p.full_name AS patient_name, p.email AS patient_email,
                              docu.email AS doctor_email, docu.full_name AS doctor_name,
                              s.name AS spec_name
                       FROM appointments a
                       JOIN users p ON a.patient_id = p.user_id
                       JOIN doctors d ON a.doctor_id = d.doctor_id
                       JOIN users docu ON d.user_id = docu.user_id
                       JOIN specializations s ON d.specialization_id = s.specialization_id
                       WHERE a.appointment_id = ? AND a.patient_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Appointment not found or access denied.");
}

$apt = $result->fetch_assoc();

if ($apt['status'] !== 'pending') {
    die("This appointment cannot be cancelled (Status: {$apt['status']}).");
}

// CANCEL APPOINTMENT
$update = $conn->prepare("UPDATE appointments SET status = 'cancelled', cancelled_by = 'patient' WHERE appointment_id = ?");
$update->bind_param("i", $id);
$update->execute();

// Format date/time
$apt_date = date('d M Y', strtotime($apt['appointment_date']));
$apt_time = date('h:i A', strtotime($apt['appointment_time']));

// --- EMAIL STYLING VARIABLES ---
$bg_color = "#f4f6f9";
$container_bg = "#ffffff";
$danger_color = "#ff416c"; // Soft Red
$danger_gradient = "linear-gradient(to right, #ff416c, #ff4b2b)"; // Red/Orange Gradient for buttons/headers if supported
$text_color = "#333333";
$border_color = "#e9ecef";

// 1. PATIENT EMAIL (Beautiful Cancellation Notice)
$patient_html = "
<div style='font-family: Helvetica, Arial, sans-serif; background-color: {$bg_color}; margin: 0; padding: 40px 0;'>
    <div style='max-width: 600px; margin: 0 auto; background-color: {$container_bg}; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden;'>
        
        <div style='background-color: {$danger_color}; padding: 30px; text-align: center;'>
            <h1 style='color: #ffffff; margin: 0; font-size: 24px; font-weight: bold;'>Cancellation Confirmed</h1>
            <p style='color: rgba(255,255,255,0.9); margin: 10px 0 0 0;'>Your appointment has been removed from our schedule.</p>
        </div>

        <div style='padding: 40px 30px;'>
            <p style='font-size: 16px; color: {$text_color}; margin-bottom: 25px;'>Dear <strong>{$apt['patient_name']}</strong>,</p>
            <p style='font-size: 16px; color: #666; line-height: 1.5; margin-bottom: 30px;'>
                As requested, we have cancelled your appointment. You will not be charged (if applicable).
            </p>

            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px;'>
                <tr>
                    <td style='padding: 15px 20px; border-bottom: 1px solid #fed7d7; width: 140px;'>
                        <strong style='color: #c53030; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>Doctor</strong>
                    </td>
                    <td style='padding: 15px 20px; border-bottom: 1px solid #fed7d7; color: {$text_color}; font-weight: 600;'>
                        Dr. {$apt['doctor_name']}
                    </td>
                </tr>
                <tr>
                    <td style='padding: 15px 20px; border-bottom: 1px solid #fed7d7;'>
                        <strong style='color: #c53030; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>Date</strong>
                    </td>
                    <td style='padding: 15px 20px; border-bottom: 1px solid #fed7d7; color: {$text_color}; font-weight: 600;'>
                        {$apt_date}
                    </td>
                </tr>
                <tr>
                    <td style='padding: 15px 20px;'>
                        <strong style='color: #c53030; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>Time</strong>
                    </td>
                    <td style='padding: 15px 20px; color: {$text_color}; font-weight: 600;'>
                        {$apt_time}
                    </td>
                </tr>
            </table>

            <div style='text-align: center; margin-top: 35px;'>
                <p style='font-size: 14px; color: #999; margin-bottom: 15px;'>Did you cancel by mistake?</p>
                <a href='" . SITE_URL . "/pages/book_appointment.php' style='background-color: #333; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 6px; font-weight: bold; font-size: 14px;'>Book New Appointment</a>
            </div>
        </div>

        <div style='background-color: #eeeeee; padding: 20px; text-align: center; font-size: 12px; color: #888;'>
            &copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.
        </div>
    </div>
</div>";

// 2. DOCTOR EMAIL (Clean Alert)
$doctor_html = "
<div style='font-family: Helvetica, Arial, sans-serif; background-color: {$bg_color}; margin: 0; padding: 40px 0;'>
    <div style='max-width: 600px; margin: 0 auto; background-color: {$container_bg}; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; border-top: 5px solid {$danger_color};'>
        
        <div style='padding: 40px 30px;'>
            <h2 style='color: #c53030; margin-top: 0;'>Appointment Cancelled</h2>
            <p style='font-size: 16px; color: #666;'>Dear Dr. {$apt['doctor_name']},</p>
            <p style='font-size: 16px; color: #666;'>Patient <strong>{$apt['patient_name']}</strong> has cancelled their appointment.</p>

            <div style='background-color: #fff5f5; padding: 20px; border-radius: 6px; margin: 25px 0; border: 1px solid #fed7d7;'>
                <table width='100%' border='0'>
                    <tr>
                        <td style='padding-bottom: 10px; width: 80px;'><strong style='color: #c53030;'>Slot Freed:</strong></td>
                        <td style='padding-bottom: 10px; font-size: 16px;'>{$apt_date} at {$apt_time}</td>
                    </tr>
                    <tr>
                        <td><strong style='color: #c53030;'>Token:</strong></td>
                        <td style='font-size: 16px;'>#{$id}</td>
                    </tr>
                </table>
            </div>
            
            <p style='font-size: 13px; color: #999;'>This slot is now available for other patients to book.</p>
        </div>
    </div>
</div>";

// Send emails (Non-blocking attempt)
ignore_user_abort(true);
sendEmail($apt['patient_email'], "Cancellation Confirmed - Token #$id", $patient_html);
sendEmail($apt['doctor_email'], "Patient Cancelled - Token #$id", $doctor_html);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Cancelled</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body { 
            background: #f0f2f5; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .cancel-card { 
            max-width: 480px; 
            width: 100%;
            border-radius: 24px; 
            overflow: hidden; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.1); 
            background: white;
            border: none;
        }

        .card-header-gradient {
            /* Beautiful Sunset Gradient */
            background: linear-gradient(135deg, #ff512f 0%, #dd2476 100%);
            color: white; 
            text-align: center; 
            padding: 40px 30px;
            position: relative;
        }
        
        /* Subtle pattern overlay on header */
        .card-header-gradient::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9InZmZmZmZiIgZmlsbC1vcGFjaXR5PSIwLjEiLz48L3N2Zz4=');
            opacity: 0.3;
        }

        .card-body-content { 
            padding: 40px; 
            text-align: center; 
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px auto;
            backdrop-filter: blur(5px);
        }

        .icon-inner { 
            font-size: 2.5rem; 
            color: white; 
        }

        .info-box {
            background: #fff5f5;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            border: 1px dashed #feb2b2;
        }

        .btn-home {
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-custom {
            border: 2px solid #dd2476;
            color: #dd2476;
        }
        .btn-outline-custom:hover {
            background: #dd2476;
            color: white;
            transform: translateY(-2px);
        }

        .btn-gradient {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            color: white;
        }
        .btn-gradient:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            color: white;
        }

    </style>
</head>
<body>

<div class="cancel-card animate__animated animate__fadeInUp">
    <div class="card-header-gradient">
        <div class="icon-circle animate__animated animate__bounceIn delay-1s">
            <i class="fas fa-calendar-times icon-inner"></i>
        </div>
        <h2 class="fw-bold mb-0">Cancelled</h2>
        <p class="mb-0 opacity-75">Appointment successfully removed</p>
    </div>
    
    <div class="card-body-content">
        <p class="text-muted mb-4">
            We've sent a confirmation email to <br>
            <strong><?= htmlspecialchars($apt['patient_email']) ?></strong>
        </p>

        <div class="info-box text-start">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small text-uppercase fw-bold">Doctor</span>
                <span class="fw-bold text-dark">Dr. <?= htmlspecialchars($apt['doctor_name']) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small text-uppercase fw-bold">Date</span>
                <span class="text-dark"><?= $apt_date ?></span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted small text-uppercase fw-bold">Token</span>
                <span class="text-danger fw-bold">#<?= $id ?></span>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a href="my_appointments.php" class="btn btn-outline-custom btn-home">
                <i class="fas fa-arrow-left me-2"></i> Dashboard
            </a>
            <a href="book_appointment.php" class="btn btn-gradient btn-home">
                <i class="fas fa-plus me-2"></i> Book New
            </a>
        </div>

        <div class="mt-4 text-muted small">
            Redirecting in <span id="countdown" class="fw-bold text-danger">5</span>s...
        </div>
    </div>
</div>

<script>
// Auto redirect logic
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