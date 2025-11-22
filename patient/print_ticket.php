<!-- print_ticket.php -->
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
    <title>Appointment Ticket - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            body { 
                margin: 0; 
                padding: 0;
                background: white !important;
            }
            .no-print { display: none !important; }
            .ticket-container { 
                box-shadow: none !important;
                border: 2px solid #333 !important;
                margin: 0 !important;
            }
            .action-buttons { display: none !important; }
        }
        
        body {
            background: linear-gradient(135deg, #f8f9ff 0%, #e6ecff 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .ticket-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            margin-bottom: 2rem;
        }
        
        .ticket-header {
            background: linear-gradient(135deg, #4a6cf7 0%, #6c63ff 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .ticket-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: rgba(255,255,255,0.3);
        }
        
        .clinic-logo {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .ticket-body {
            padding: 2.5rem;
            padding-top: 0.5rem;
        }
        
        .info-section {
            margin-bottom: 2rem;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #6c757d;
            flex: 1;
        }
        
        .info-value {
            font-weight: 700;
            color: #1e2a4a;
            flex: 2;
            text-align: right;
        }
        
        .token-number {
            background: linear-gradient(135deg, #4a6cf7 0%, #6c63ff 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 1.5rem 0;
        }
        
        .status-badge {
            font-size: 1rem;
            padding: 0.5rem 1.5rem;
        }
        
        .instructions {
            background: #f8f9ff;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            border-left: 4px solid #4a6cf7;
        }
        
        .footer-note {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
            width: 100%;
            max-width: 500px;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;


        }
        
        .btn-close {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-print:hover, .btn-close:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
<div class="ticket-container">
    <div class="ticket-header">
        <div class="clinic-logo">
            <i class="fas fa-heartbeat"></i>
        </div>
        <h2 class="mb-2">Appointment Ticket</h2>
        <p class="mb-0"><?= SITE_NAME ?></p>
    </div>
    
    <div class="ticket-body">
        <div class="token-number text-center">
            Token #<?= $apt['appointment_id'] ?>
        </div>
        
        <div class="info-section">
            <div class="info-item">
                <span class="info-label"><i class="fas fa-user me-2"></i>Patient Name</span>
                <span class="info-value"><?= $apt['patient_name'] ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label"><i class="fas fa-user-md me-2"></i>Doctor</span>
                <span class="info-value">Dr. <?= $apt['doctor_name'] ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label"><i class="fas fa-stethoscope me-2"></i>Specialization</span>
                <span class="info-value"><?= $apt['spec_name'] ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label"><i class="fas fa-calendar me-2"></i>Date</span>
                <span class="info-value"><?= date('F j, Y', strtotime($apt['appointment_date'])) ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label"><i class="fas fa-clock me-2"></i>Time</span>
                <span class="info-value"><?= date('h:i A', strtotime($apt['appointment_time'])) ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label"><i class="fas fa-info-circle me-2"></i>Status</span>
                <span class="info-value">
                    <span class="badge status-badge bg-<?= $apt['status'] == 'pending' ? 'warning' : ($apt['status'] == 'completed' ? 'success' : 'danger') ?>">
                        <?= ucfirst($apt['status']) ?>
                    </span>
                </span>
            </div>
        </div>
        
        <div class="instructions">
            <h6 class="mb-3"><i class="fas fa-exclamation-circle me-2"></i>Important Instructions</h6>
            <ul class="small mb-0">
                <li>Arrive 15 minutes before your appointment time</li>
                <li>Bring this ticket and your ID card</li>
                <li>Carry your previous medical reports if any</li>
                <li>Contact reception for any queries</li>
            </ul>
        </div>
        
        <div class="footer-note">
            <p class="mb-0">Thank you for choosing <?= SITE_NAME ?>!<br>We care about your health.</p>
        </div>
    </div>
</div>

<div class="action-buttons no-print">
    <button onclick="window.print()" class="btn btn-print btn-lg">
        <i class="fas fa-print me-2"></i>Print Ticket
    </button>
    <button onclick="window.close()" class="btn btn-close btn-lg">
        <i class="fas fa-times me-2"></i> Close Window
    </button>
</div>

<script>
// Auto-print when page loads
window.onload = function() {
    setTimeout(() => {
        window.print();
    }, 500);
};
</script>
</body>
</html>