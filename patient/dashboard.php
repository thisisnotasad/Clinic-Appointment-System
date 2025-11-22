<!-- dashboard.php -->
<?php 
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/db_connect.php';

// Get stats for the dashboard
$user_id = $_SESSION['user_id'];

// Total appointments
$total_appointments = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE patient_id = $user_id")->fetch_assoc()['total'];

// Upcoming appointments
$upcoming_appointments = $conn->query("SELECT COUNT(*) as upcoming FROM appointments WHERE patient_id = $user_id AND status = 'pending' AND CONCAT(appointment_date, ' ', appointment_time) >= NOW()")->fetch_assoc()['upcoming'];

// Recent appointments
$recent_appointments = $conn->query("
    SELECT a.*, u.full_name as doctor_name, s.name as spec_name 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.doctor_id 
    JOIN users u ON d.user_id = u.user_id 
    JOIN specializations s ON d.specialization_id = s.specialization_id 
    WHERE a.patient_id = $user_id 
    ORDER BY a.appointment_date DESC, a.appointment_time DESC 
    LIMIT 3
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="content-header">
        <h1 class="content-title">Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]) ?>! 👋</h1>
        <p class="content-subtitle">Here's your healthcare overview for <?= date('F j, Y') ?></p>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="content-card text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="stat-number"><?= $total_appointments ?></h3>
                <p class="stat-label">Total Appointments</p>
                <a href="my_appointments.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="content-card text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="stat-number"><?= $upcoming_appointments ?></h3>
                <p class="stat-label">Upcoming Visits</p>
                <a href="my_appointments.php?filter=upcoming" class="btn btn-sm btn-outline-primary">View</a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="content-card text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-user-md"></i>
                </div>
                <h3 class="stat-number"><?= $conn->query("SELECT COUNT(DISTINCT doctor_id) as doctors FROM appointments WHERE patient_id = $user_id")->fetch_assoc()['doctors'] ?></h3>
                <p class="stat-label">My Doctors</p>
                <a href="book_appointment.php" class="btn btn-sm btn-outline-primary">Find More</a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="content-card text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h3 class="stat-number">100%</h3>
                <p class="stat-label">Health Ready</p>
                <a href="profile.php" class="btn btn-sm btn-outline-primary">Checkup</a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="content-card">
                <div class="card-header-custom">
                    <h3 class="card-title-custom">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h3>
                </div>
                <div class="row text-center">
                    <div class="col-md-2 col-4 mb-3">
                        <a href="book_appointment.php" class="text-decoration-none">
                            <div class="quick-action-item p-3 rounded">
                                <div class="feature-icon mb-2">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <small class="d-block">Book Appointment</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <a href="my_appointments.php" class="text-decoration-none">
                            <div class="quick-action-item p-3 rounded">
                                <div class="feature-icon mb-2">
                                    <i class="fas fa-list-alt"></i>
                                </div>
                                <small class="d-block">My Appointments</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <a href="profile.php" class="text-decoration-none">
                            <div class="quick-action-item p-3 rounded">
                                <div class="feature-icon mb-2">
                                    <i class="fas fa-user-edit"></i>
                                </div>
                                <small class="d-block">Update Profile</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <a href="medical-records.php" class="text-decoration-none">
                            <div class="quick-action-item p-3 rounded">
                                <div class="feature-icon mb-2">
                                    <i class="fas fa-file-medical-alt"></i>
                                </div>
                                <small class="d-block">Medical Records</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <a href="prescriptions.php" class="text-decoration-none">
                            <div class="quick-action-item p-3 rounded">
                                <div class="feature-icon mb-2">
                                    <i class="fas fa-prescription-bottle"></i>
                                </div>
                                <small class="d-block">Prescriptions</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <a href="support.php" class="text-decoration-none">
                            <div class="quick-action-item p-3 rounded">
                                <div class="feature-icon mb-2">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <small class="d-block">Get Help</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Appointments & Health Tips -->
    <div class="row">
        <!-- Recent Appointments -->
        <div class="col-lg-8 mb-4">
            <div class="content-card">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h3 class="card-title-custom">
                        <i class="fas fa-history me-2"></i>Recent Appointments
                    </h3>
                    <a href="my_appointments.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="appointments-list">
                    <?php if(count($recent_appointments) > 0): ?>
                        <?php foreach($recent_appointments as $apt): ?>
                            <div class="appointment-item d-flex align-items-center p-3 border-bottom">
                                <div class="appointment-time <?= $apt['status'] == 'pending' ? 'bg-primary' : ($apt['status'] == 'completed' ? 'bg-success' : 'bg-danger') ?> text-white rounded p-2 text-center me-3">
                                    <div class="fw-bold"><?= date('d', strtotime($apt['appointment_date'])) ?></div>
                                    <small><?= date('M', strtotime($apt['appointment_date'])) ?></small>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Dr. <?= $apt['doctor_name'] ?></h6>
                                    <p class="text-muted mb-0"><?= $apt['spec_name'] ?> • <?= date('h:i A', strtotime($apt['appointment_time'])) ?></p>
                                </div>
                                <span class="badge bg-<?= $apt['status'] == 'pending' ? 'warning' : ($apt['status'] == 'completed' ? 'success' : 'danger') ?>">
                                    <?= ucfirst($apt['status']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times feature-icon text-muted mb-3"></i>
                            <p class="text-muted">No appointments yet</p>
                            <a href="book_appointment.php" class="btn btn-primary">Book Your First Appointment</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Health Tips & Notifications -->
        <div class="col-lg-4 mb-4">
            <div class="content-card">
                <div class="card-header-custom">
                    <h3 class="card-title-custom">
                        <i class="fas fa-bell me-2"></i>Health Tips
                    </h3>
                </div>
                <div class="health-tips">
                    <div class="health-tip p-3 bg-light rounded mb-3">
                        <h6 class="text-primary mb-2">
                            <i class="fas fa-lightbulb me-2"></i>Stay Hydrated
                        </h6>
                        <p class="mb-0 small">Drink at least 8 glasses of water daily for optimal health.</p>
                    </div>
                    <div class="health-tip p-3 bg-light rounded mb-3">
                        <h6 class="text-primary mb-2">
                            <i class="fas fa-walking me-2"></i>Daily Exercise
                        </h6>
                        <p class="mb-0 small">30 minutes of moderate activity can boost your immune system.</p>
                    </div>
                    <div class="health-tip p-3 bg-light rounded">
                        <h6 class="text-primary mb-2">
                            <i class="fas fa-apple-alt me-2"></i>Balanced Diet
                        </h6>
                        <p class="mb-0 small">Include fruits and vegetables in every meal for better nutrition.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.quick-action-item {
    transition: var(--transition);
    border: 1px solid var(--gray-light);
}

.quick-action-item:hover {
    background: rgba(74, 108, 247, 0.05);
    border-color: var(--primary);
    transform: translateY(-3px);
}

.appointment-time {
    min-width: 60px;
    transition: var(--transition);
}

.appointment-item:hover .appointment-time {
    transform: scale(1.05);
}

.health-tip {
    border-left: 4px solid var(--primary);
    transition: var(--transition);
}

.health-tip:hover {
    background: rgba(74, 108, 247, 0.05);
    transform: translateX(5px);
}
</style>

<?php require_once '../includes/footer.php'; ?>