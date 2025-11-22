<?php require_once '../includes/auth.php'; ?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/db_connect.php'; ?>
<?php if (!isDoctor()) header("Location: ../dashboard.php"); ?>

<?php
// Get doctor's ID from users → doctors table
$stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$doctor_id = $stmt->get_result()->fetch_assoc()['doctor_id'];

// Get stats for dashboard
$today = date('Y-m-d');
$total_today = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = $doctor_id AND appointment_date = '$today'")->fetch_assoc()['total'];
$completed_today = $conn->query("SELECT COUNT(*) as completed FROM appointments WHERE doctor_id = $doctor_id AND appointment_date = '$today' AND status = 'completed'")->fetch_assoc()['completed'];
$pending_today = $conn->query("SELECT COUNT(*) as pending FROM appointments WHERE doctor_id = $doctor_id AND appointment_date = '$today' AND status = 'pending'")->fetch_assoc()['pending'];
?>

<style>
:root {
    --primary: #4a6cf7;
    --primary-light: #6a85f9;
    --primary-dark: #3a56d4;
    --secondary: #6c63ff;
    --accent: #ff6b9d;
    --accent-light: #ff8ab0;
    --light: #f8f9ff;
    --dark: #1e2a4a;
    --gray: #6c757d;
    --gray-light: #e9ecef;
    --success: #28a745;
    --danger: #dc3545;
    --warning: #ffc107;
    --border-radius: 16px;
    --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    --box-shadow-lg: 0 20px 50px rgba(0, 0, 0, 0.12);
    --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.doctor-dashboard {
    background: linear-gradient(135deg, #f5f7ff 0%, #e6ecff 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.dashboard-header {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--box-shadow);
    border-left: 5px solid var(--primary);
}

.welcome-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--box-shadow-lg);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
    border-top: 4px solid var(--primary);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--box-shadow-lg);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--gray);
    font-weight: 500;
    font-size: 0.9rem;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.action-btn {
    background: white;
    border: 2px solid var(--gray-light);
    border-radius: var(--border-radius);
    padding: 1.5rem 1rem;
    text-align: center;
    text-decoration: none;
    color: var(--dark);
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.action-btn:hover {
    border-color: var(--primary);
    background: rgba(74, 108, 247, 0.05);
    transform: translateY(-3px);
    box-shadow: var(--box-shadow);
    color: var(--primary);
}

.action-icon {
    font-size: 2rem;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.appointments-table {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--box-shadow);
}

.table-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    padding: 1.5rem;
    margin: 0;
}

.table {
    margin: 0;
}

.table th {
    background: var(--light);
    border: none;
    padding: 1rem;
    font-weight: 600;
    color: var(--dark);
}

.table td {
    padding: 1rem;
    border-color: var(--gray-light);
    vertical-align: middle;
}

.badge {
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 500;
}

.btn-success {
    background: linear-gradient(135deg, var(--success) 0%, #20c997 100%);
    border: none;
    border-radius: 50px;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    transition: var(--transition);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
}

.time-slot {
    background: var(--light);
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    color: var(--primary);
}

.patient-name {
    font-weight: 600;
    color: var(--dark);
}

.reason-text {
    color: var(--gray);
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .doctor-dashboard {
        padding: 1rem 0;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
    
    .table-responsive {
        font-size: 0.9rem;
    }
}
</style>

<div class="doctor-dashboard">
    <div class="container">
        <!-- Welcome Header -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 fw-bold mb-2">Welcome,  <?= htmlspecialchars($_SESSION['full_name']) ?>! 👨‍⚕️</h1>
                    <p class="mb-0 opacity-90">Here's your schedule and patient appointments for today</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-opacity-20 rounded-pill px-3 py-2 d-inline-block" style="background:#3d6baf;">
                        <i class="fas fa-calendar-day me-2"></i>
                        <?= date('l, F j, Y') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_today ?></div>
                <div class="stat-label">Total Appointments Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $pending_today ?></div>
                <div class="stat-label">Pending Consultations</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $completed_today ?></div>
                <div class="stat-label">Completed Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">100%</div>
                <div class="stat-label">Ready for Patients</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="schedule.php" class="action-btn">
                <div class="action-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <span>Set Weekly Schedule</span>
            </a>
            <a href="leaves.php" class="action-btn">
                <div class="action-icon">
                    <i class="fas fa-umbrella-beach"></i>
                </div>
                <span>Manage Leaves</span>
            </a>
            <a href="profile.php" class="action-btn">
                <div class="action-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                <span>Edit Profile</span>
            </a>
            <a href="../auth/logout.php" class="action-btn">
                <div class="action-icon">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <span>Logout</span>
            </a>
        </div>

        <!-- Today's Appointments -->
        <div class="appointments-table">
            <h3 class="table-header">
                <i class="fas fa-list-alt me-2"></i>
                Today's Appointments
                <small class="opacity-90">(<?= date('d M Y') ?>)</small>
            </h3>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Contact</th>
                            <th>Reason for Visit</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->prepare("
                            SELECT a.*, u.full_name, u.phone, a.reason_for_visit 
                            FROM appointments a
                            JOIN users u ON a.patient_id = u.user_id
                            WHERE a.doctor_id = ? AND a.appointment_date = ?
                            ORDER BY a.appointment_time
                        ");
                        $stmt->bind_param("is", $doctor_id, $today);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0):
                            while ($apt = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="time-slot">
                                        <?= date('h:i A', strtotime($apt['appointment_time'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="patient-name">
                                        <?= htmlspecialchars($apt['full_name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($apt['phone'] ?? 'N/A') ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="reason-text">
                                        <?= htmlspecialchars($apt['reason_for_visit'] ?: 'General Checkup') ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $apt['status']=='completed'?'success':($apt['status']=='cancelled'?'danger':'warning') ?>">
                                        <?= ucfirst($apt['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($apt['status'] == 'pending'): ?>
                                        <a href="mark_complete.php?id=<?= $apt['appointment_id'] ?>" 
                                           class="btn btn-success btn-sm" 
                                           onclick="return confirm('Mark this appointment as completed?')">
                                            <i class="fas fa-check me-1"></i>Complete
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-calendar-times fa-2x mb-3"></i>
                                        <p class="mb-0">No appointments scheduled for today</p>
                                        <small>Enjoy your day! 🎉</small>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>