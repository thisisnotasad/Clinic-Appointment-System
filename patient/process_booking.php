

<?php
// 1. Start Output Buffering immediately to catch unwanted whitespace/errors
ob_start();

require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../includes/mailer.php'; 

header('Content-Type: application/json');

try {
    if (!isPatient()) {
        throw new Exception('Unauthorized access.');
    }

    $doctor_id  = $_POST['doctor_id'] ?? 0;
    $date       = $_POST['date'] ?? '';
    $time       = $_POST['time'] ?? '';
    $patient_id = $_SESSION['user_id'];

    if (!$doctor_id || !$date || !$time) {
        throw new Exception('Missing required data.');
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
        throw new Exception('This slot was just taken! Please choose another.');
    }

    // BOOK IT
    $stmt = $conn->prepare("
        INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) 
        VALUES (?, ?, ?, ?, 'pending')
    ");
    $stmt->bind_param("iiss", $patient_id, $doctor_id, $date, $time24);

    if ($stmt->execute()) {
        $apt_id = $conn->insert_id;

        // Get full details for email (Silent operation)
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

        
    // Common styling variables
    $bg_color = "#f4f6f9";
    $container_bg = "#ffffff";
    $primary_color = "#0d6efd"; // Bootstrap Primary Blue
    $text_color = "#333333";
    $border_color = "#e9ecef";
    
    // ---------------------------------------------------------
    // 1. PATIENT EMAIL (Rich HTML)
    // ---------------------------------------------------------
    $patient_html = "
    <div style='font-family: Helvetica, Arial, sans-serif; background-color: {$bg_color}; margin: 0; padding: 40px 0;'>
        <div style='max-width: 600px; margin: 0 auto; background-color: {$container_bg}; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden;'>
            
            <div style='background-color: {$primary_color}; padding: 30px; text-align: center;'>
                <h1 style='color: #ffffff; margin: 0; font-size: 24px; font-weight: bold;'>Appointment Confirmed</h1>
                <p style='color: rgba(255,255,255,0.9); margin: 10px 0 0 0;'>We are looking forward to seeing you.</p>
            </div>

            <div style='padding: 40px 30px;'>
                <p style='font-size: 16px; color: {$text_color}; margin-bottom: 25px;'>Dear <strong>{$info['patient_name']}</strong>,</p>
                <p style='font-size: 16px; color: #666; line-height: 1.5; margin-bottom: 30px;'>
                    Your appointment has been successfully scheduled. Please arrive 10 minutes early to complete any necessary check-in procedures.
                </p>

                <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f8f9fa; border: 1px solid {$border_color}; border-radius: 6px;'>
                    <tr>
                        <td style='padding: 15px 20px; border-bottom: 1px solid {$border_color}; width: 140px;'>
                            <strong style='color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>Doctor</strong>
                        </td>
                        <td style='padding: 15px 20px; border-bottom: 1px solid {$border_color}; color: {$text_color}; font-weight: 600;'>
                            Dr. {$info['doctor_name']} <span style='color: #777; font-weight: normal;'>({$info['spec_name']})</span>
                        </td>
                    </tr>
                    <tr>
                        <td style='padding: 15px 20px; border-bottom: 1px solid {$border_color};'>
                            <strong style='color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>Date</strong>
                        </td>
                        <td style='padding: 15px 20px; border-bottom: 1px solid {$border_color}; color: {$text_color}; font-weight: 600;'>
                            {$pretty_date}
                        </td>
                    </tr>
                    <tr>
                        <td style='padding: 15px 20px; border-bottom: 1px solid {$border_color};'>
                            <strong style='color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>Time</strong>
                        </td>
                        <td style='padding: 15px 20px; border-bottom: 1px solid {$border_color}; color: {$text_color}; font-weight: 600;'>
                            {$pretty_time}
                        </td>
                    </tr>
                    <tr>
                        <td style='padding: 15px 20px;'>
                            <strong style='color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>Token ID</strong>
                        </td>
                        <td style='padding: 15px 20px; color: {$primary_color}; font-size: 18px; font-weight: bold;'>
                            #{$apt_id}
                        </td>
                    </tr>
                </table>

                <p style='margin-top: 30px; font-size: 14px; color: #999; text-align: center;'>
                    Need to reschedule? Please log in to your dashboard or contact support.
                </p>
            </div>

            <div style='background-color: #eeeeee; padding: 20px; text-align: center; font-size: 12px; color: #888;'>
                &copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.
            </div>
        </div>
    </div>";


    // ---------------------------------------------------------
    // 2. DOCTOR EMAIL (Clean Notification)
    // ---------------------------------------------------------
    $doctor_html = "
    <div style='font-family: Helvetica, Arial, sans-serif; background-color: {$bg_color}; margin: 0; padding: 40px 0;'>
        <div style='max-width: 600px; margin: 0 auto; background-color: {$container_bg}; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; border-top: 5px solid {$primary_color};'>
            
            <div style='padding: 40px 30px;'>
                <h2 style='color: {$text_color}; margin-top: 0;'>New Appointment Alert</h2>
                <p style='font-size: 16px; color: #666;'>Dear Dr. {$info['doctor_name']},</p>
                <p style='font-size: 16px; color: #666;'>A new patient has scheduled an appointment with you.</p>

                <div style='background-color: #e7f1ff; padding: 20px; border-radius: 6px; margin: 25px 0;'>
                    <table width='100%' border='0'>
                        <tr>
                            <td style='padding-bottom: 10px; width: 80px;'><strong style='color: {$primary_color};'>Patient:</strong></td>
                            <td style='padding-bottom: 10px; font-size: 16px;'>{$info['patient_name']}</td>
                        </tr>
                        <tr>
                            <td style='padding-bottom: 10px;'><strong style='color: {$primary_color};'>Date:</strong></td>
                            <td style='padding-bottom: 10px; font-size: 16px;'>{$pretty_date}</td>
                        </tr>
                        <tr>
                            <td style='padding-bottom: 10px;'><strong style='color: {$primary_color};'>Time:</strong></td>
                            <td style='padding-bottom: 10px; font-size: 16px;'>{$pretty_time}</td>
                        </tr>
                        <tr>
                            <td><strong style='color: {$primary_color};'>Token:</strong></td>
                            <td style='font-size: 16px;'>#{$apt_id}</td>
                        </tr>
                    </table>
                </div>
                
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='#' style='background-color: {$primary_color}; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 4px; font-weight: bold; font-size: 14px;'>View in Dashboard</a>
                </div>
            </div>
             <div style='background-color: #eeeeee; padding: 15px; text-align: center; font-size: 12px; color: #888;'>
                Automated Notification System
            </div>
        </div>
    </div>";

    
    // Get doctor email
    $doc_email_result = $conn->query("SELECT email FROM users WHERE user_id = {$info['doctor_user_id']}");
    $doc_email = $doc_email_result->fetch_assoc()['email'] ?? 'admin@clinic.com';
    sendEmail($doc_email, "New Appointment - Token #$apt_id", $doctor_html);

        
        sendEmail($info['patient_email'], "Appointment Confirmed", $patient_html);

        // 2. Prepare Success Response
        $response = [
            'success' => true, 
            'message' => 'Appointment booked successfully!',
            'details' => [
                'doctor' => "Dr. " . $info['doctor_name'],
                'date'   => $pretty_date,
                'time'   => $pretty_time,
                'token'  => "#" . $apt_id
            ]
        ];

    } else {
        throw new Exception('Database error: Could not save appointment.');
    }

} catch (Exception $e) {
    $response = ['success' => false, 'error' => $e->getMessage()];
}

// 3. CLEAN BUFFER & OUTPUT JSON
// This discards any text echoed by mailer.php or warnings
ob_end_clean(); 
echo json_encode($response);
exit;
?>