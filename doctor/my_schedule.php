<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/db_connect.php';
if (!isDoctor()) header("Location: ../dashboard.php");

// Get doctor's ID
$stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$doctor_id = $stmt->get_result()->fetch_assoc()['doctor_id'];

// Get weekly schedule
$schedule_query = "SELECT * FROM doctor_schedule WHERE doctor_id = ? ORDER BY day_of_week";
$stmt = $conn->prepare($schedule_query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$weekly_schedule = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get today's appointments
$today = date('Y-m-d');
$today_appointments = $conn->query("
    SELECT a.*, u.full_name 
    FROM appointments a 
    JOIN users u ON a.patient_id = u.user_id 
    WHERE a.doctor_id = $doctor_id 
    AND a.appointment_date = '$today' 
    AND a.status = 'pending'
    ORDER BY a.appointment_time
")->fetch_all(MYSQLI_ASSOC);

$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
?>

<style>
.schedule-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.schedule-header {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--box-shadow);
    border-left: 4px solid var(--primary);
}

.schedule-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.schedule-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    border: 1px solid var(--gray-light);
}

.schedule-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.schedule-header-day {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-light);
}

.schedule-body {
    padding: 1.5rem;
}

.schedule-time {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.schedule-duration {
    color: var(--gray);
    font-size: 0.9rem;
}

.off-day {
    text-align: center;
    color: var(--gray);
    padding: 2rem 1rem;
}

.off-day i {
    font-size: 2rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.today-appointments {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    margin-bottom: 2rem;
}

.appointment-item {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-light);
    transition: var(--transition);
}

.appointment-item:hover {
    background: rgba(74, 108, 247, 0.03);
}

.appointment-item:last-child {
    border-bottom: none;
}

.appointment-time {
    background: var(--primary);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9rem;
    min-width: 80px;
    text-align: center;
}

.patient-name {
    font-weight: 600;
    color: var(--dark);
}

.appointment-reason {
    color: var(--gray);
    font-size: 0.85rem;
}

.no-appointments {
    text-align: center;
    padding: 2rem;
    color: var(--gray);
}

.no-appointments i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.day-indicator {
    width: 30px;
    height: 30px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    margin-right: 1rem;
}

.action-buttons {
    margin-top: 2rem;
    text-align: center;
}

.btn-custom {
    background: var(--primary);
    border: none;
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    color: white;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-custom:hover {
    background: var(--primary-dark);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(74, 108, 247, 0.2);
}

.btn-outline-custom {
    background: white;
    border: 1px solid var(--gray-light);
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    color: var(--dark);
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-outline-custom:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: rgba(74, 108, 247, 0.05);
}

@media (max-width: 768px) {
    .schedule-grid {
        grid-template-columns: 1fr;
    }
    
    .schedule-header {
        padding: 1.5rem;
    }
    
    .appointment-item {
        padding: 1rem;
    }
}
</style>

<div class="schedule-container">
    <div class="container">
        <!-- Header -->
        <div class="schedule-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h2 fw-bold mb-2">My Schedule Overview</h1>
                    <p class="text-muted mb-0">View your weekly availability and today's appointments</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-light rounded-pill px-3 py-2 d-inline-block">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        <?= date('F j, Y') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Weekly Schedule -->
            <div class="col-lg-8 mb-4">
                <div class="schedule-card">
                    <div class="schedule-header-day">
                        <h4 class="mb-0">
                            <i class="fas fa-calendar-week me-2 text-primary"></i>Weekly Schedule
                        </h4>
                    </div>
                    <div class="schedule-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                        <?php for ($d = 1; $d <= 6; $d++): ?>
                            <?php 
                            $day_schedule = array_filter($weekly_schedule, function($sched) use ($d) {
                                return $sched['day_of_week'] == $d;
                            });
                            $day_schedule = !empty($day_schedule) ? reset($day_schedule) : null;
                            ?>
                            <div class="schedule-card">
                                <div class="schedule-header-day">
                                    <div class="d-flex align-items-center">
                                        <div class="day-indicator"><?= $d ?></div>
                                        <h6 class="mb-0"><?= $days[$d] ?></h6>
                                    </div>
                                </div>
                                <div class="schedule-body">
                                    <?php if ($day_schedule): ?>
                                        <div class="schedule-time">
                                            <?= date('g:i A', strtotime($day_schedule['start_time'])) ?> - 
                                            <?= date('g:i A', strtotime($day_schedule['end_time'])) ?>
                                        </div>
                                        <div class="schedule-duration">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= $day_schedule['slot_duration'] ?> min slots
                                        </div>
                                    <?php else: ?>
                                        <div class="off-day">
                                            <i class="fas fa-bed"></i>
                                            <div>Off Day</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Today's Appointments -->
            <div class="col-lg-4 mb-4">
                <div class="schedule-card">
                    <div class="schedule-header-day">
                        <h4 class="mb-0">
                            <i class="fas fa-list-alt me-2 text-primary"></i>Today's Appointments
                        </h4>
                    </div>
                    <div class="schedule-body p-0">
                        <?php if(count($today_appointments) > 0): ?>
                            <?php foreach($today_appointments as $apt): ?>
                                <div class="appointment-item">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="appointment-time">
                                            <?= date('h:i A', strtotime($apt['appointment_time'])) ?>
                                        </div>
                                        <span class="badge bg-warning">Pending</span>
                                    </div>
                                    <div class="patient-name"><?= htmlspecialchars($apt['full_name']) ?></div>
                                    <?php if($apt['reason_for_visit']): ?>
                                        <div class="appointment-reason">
                                            <?= htmlspecialchars($apt['reason_for_visit']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-appointments">
                                <i class="fas fa-calendar-check"></i>
                                <h6>No Appointments Today</h6>
                                <p class="mb-0 small">Enjoy your day!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="dashboard.php" class="btn btn-outline-custom me-3">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <a href="schedule.php" class="btn btn-custom me-3">
                <i class="fas fa-edit me-2"></i>Edit Schedule
            </a>
            <a href="leaves.php" class="btn btn-custom">
                <i class="fas fa-umbrella-beach me-2"></i>Manage Leaves
            </a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>