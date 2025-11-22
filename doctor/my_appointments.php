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

// Filter handling
$filter = $_GET['filter'] ?? 'all';
$status_filter = '';
switch($filter) {
    case 'upcoming':
        $status_filter = "AND a.status = 'pending' AND CONCAT(a.appointment_date, ' ', a.appointment_time) >= NOW()";
        break;
    case 'pending':
        $status_filter = "AND a.status = 'pending'";
        break;
    case 'completed':
        $status_filter = "AND a.status = 'completed'";
        break;
    case 'cancelled':
        $status_filter = "AND a.status = 'cancelled'";
        break;
    default:
        $status_filter = "";
}

// Get appointments with filter
$appointments_query = "
    SELECT a.*, u.full_name, u.phone, u.email, s.name as specialization
    FROM appointments a
    JOIN users u ON a.patient_id = u.user_id
    JOIN doctors d ON a.doctor_id = d.doctor_id
    JOIN specializations s ON d.specialization_id = s.specialization_id
    WHERE a.doctor_id = ? $status_filter
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
";
$stmt = $conn->prepare($appointments_query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get stats
$total_appointments = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = $doctor_id")->fetch_assoc()['total'];
$pending_appointments = $conn->query("SELECT COUNT(*) as pending FROM appointments WHERE doctor_id = $doctor_id AND status = 'pending'")->fetch_assoc()['pending'];
$today_appointments = $conn->query("SELECT COUNT(*) as today FROM appointments WHERE doctor_id = $doctor_id AND appointment_date = CURDATE()")->fetch_assoc()['today'];
$completion_rate = $total_appointments > 0 ? round(($conn->query("SELECT COUNT(*) as completed FROM appointments WHERE doctor_id = $doctor_id AND status = 'completed'")->fetch_assoc()['completed'] / $total_appointments) * 100) : 0;
?>

<style>
.appointments-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.appointments-header {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--box-shadow);
    border-left: 4px solid var(--primary);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    text-align: center;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    border-top: 3px solid var(--primary);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--gray);
    font-weight: 500;
    font-size: 0.9rem;
}

.filter-tabs {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--box-shadow);
}

.nav-pills-custom .nav-link {
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    color: var(--dark);
    border: 1px solid var(--gray-light);
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
    transition: var(--transition);
}

.nav-pills-custom .nav-link.active {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.nav-pills-custom .nav-link:hover:not(.active) {
    border-color: var(--primary);
    color: var(--primary);
}

.appointments-table {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
}

.table-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--gray-light);
}

.table {
    margin: 0;
}

.table th {
    background: #f8f9fa;
    border: none;
    padding: 1rem 1.5rem;
    font-weight: 600;
    color: var(--dark);
    font-size: 0.9rem;
}

.table td {
    padding: 1rem 1.5rem;
    border-color: var(--gray-light);
    vertical-align: middle;
}

.appointment-date {
    text-align: center;
    min-width: 80px;
}

.date-day {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    line-height: 1;
}

.date-month {
    font-size: 0.8rem;
    color: var(--gray);
    text-transform: uppercase;
    font-weight: 600;
}

.appointment-time {
    background: #e7f3ff;
    color: var(--primary);
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-block;
}

.patient-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.patient-avatar {
    width: 45px;
    height: 45px;
    background: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    flex-shrink: 0;
}

.patient-details h6 {
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 0.25rem;
}

.patient-contact {
    color: var(--gray);
    font-size: 0.85rem;
}

.reason-text {
    color: var(--dark);
    font-weight: 500;
}

.specialization-badge {
    background: #e7f3ff;
    color: var(--primary);
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 500;
    font-size: 0.85rem;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-sm-custom {
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    text-decoration: none;
    transition: var(--transition);
    border: none;
}

.btn-success-sm {
    background: #28a745;
    color: white;
}

.btn-success-sm:hover {
    background: #218838;
    color: white;
    transform: translateY(-1px);
}

.btn-outline-sm {
    background: white;
    border: 1px solid var(--gray-light);
    color: var(--dark);
}

.btn-outline-sm:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--gray);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.action-section {
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
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .table-responsive {
        font-size: 0.85rem;
    }
    
    .appointments-header {
        padding: 1.5rem;
    }
    
    .patient-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<div class="appointments-container">
    <div class="container">
        <!-- Header -->
        <div class="appointments-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h2 fw-bold mb-2">My Appointments</h1>
                    <p class="text-muted mb-0">Manage and track all your patient appointments</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-light rounded-pill px-3 py-2 d-inline-block">
                        <i class="fas fa-calendar-check me-2 text-primary"></i>
                        Appointment Management
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_appointments ?></div>
                <div class="stat-label">Total Appointments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $pending_appointments ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $today_appointments ?></div>
                <div class="stat-label">Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $completion_rate ?>%</div>
                <div class="stat-label">Completion Rate</div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <ul class="nav nav-pills nav-pills-custom">
                <li class="nav-item">
                    <a class="nav-link <?= $filter === 'all' ? 'active' : '' ?>" href="?filter=all">
                        <i class="fas fa-list me-1"></i>All Appointments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $filter === 'upcoming' ? 'active' : '' ?>" href="?filter=upcoming">
                        <i class="fas fa-clock me-1"></i>Upcoming
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $filter === 'pending' ? 'active' : '' ?>" href="?filter=pending">
                        <i class="fas fa-hourglass-half me-1"></i>Pending
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $filter === 'completed' ? 'active' : '' ?>" href="?filter=completed">
                        <i class="fas fa-check-circle me-1"></i>Completed
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $filter === 'cancelled' ? 'active' : '' ?>" href="?filter=cancelled">
                        <i class="fas fa-times-circle me-1"></i>Cancelled
                    </a>
                </li>
            </ul>
        </div>

        <!-- Appointments Table -->
        <div class="appointments-table">
            <div class="table-header">
                <h4 class="mb-0">
                    <i class="fas fa-list me-2 text-primary"></i>
                    <?= ucfirst($filter) ?> Appointments
                    <span class="badge bg-primary ms-2"><?= count($appointments) ?></span>
                </h4>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Patient</th>
                            <th>Specialization</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($appointments) > 0): ?>
                            <?php foreach($appointments as $apt): ?>
                                <tr>
                                    <td>
                                        <div class="appointment-date">
                                            <div class="date-day"><?= date('d', strtotime($apt['appointment_date'])) ?></div>
                                            <div class="date-month"><?= date('M', strtotime($apt['appointment_date'])) ?></div>
                                        </div>
                                        <div class="appointment-time mt-2">
                                            <?= date('h:i A', strtotime($apt['appointment_time'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="patient-info">
                                            <div class="patient-avatar">
                                                <?= strtoupper(substr($apt['full_name'], 0, 1)) ?>
                                            </div>
                                            <div class="patient-details">
                                                <h6 class="mb-1"><?= htmlspecialchars($apt['full_name']) ?></h6>
                                                <div class="patient-contact">
                                                    <?= htmlspecialchars($apt['phone'] ?? 'N/A') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="specialization-badge">
                                            <?= htmlspecialchars($apt['specialization']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="reason-text">
                                            <?= htmlspecialchars($apt['reason_for_visit'] ?: 'General Consultation') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = [
                                            'pending' => 'bg-warning',
                                            'completed' => 'bg-success',
                                            'cancelled' => 'bg-danger'
                                        ][$apt['status']] ?? 'bg-secondary';
                                        ?>
                                        <span class="status-badge <?= $status_class ?>">
                                            <?= ucfirst($apt['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($apt['status'] == 'pending'): ?>
                                                <a href="mark_complete.php?id=<?= $apt['appointment_id'] ?>" 
                                                   class="btn-sm-custom btn-success-sm"
                                                   onclick="return confirm('Mark this appointment as completed?')">
                                                    <i class="fas fa-check me-1"></i>Complete
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">Completed</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-calendar-times"></i>
                                        <h5>No Appointments Found</h5>
                                        <p class="mb-0">
                                            <?php if ($filter !== 'all'): ?>
                                                No <?= $filter ?> appointments match your criteria.
                                            <?php else: ?>
                                                You don't have any appointments scheduled yet.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-section">
            <a href="dashboard.php" class="btn btn-outline-custom me-3">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <a href="my_schedule.php" class="btn btn-custom">
                <i class="fas fa-calendar-alt me-2"></i>View Schedule
            </a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>