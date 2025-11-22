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

// Get patients data
$patients_query = "
    SELECT DISTINCT u.user_id, u.full_name, u.email, u.phone, 
           MAX(a.appointment_date) as last_visit,
           COUNT(a.appointment_id) as total_visits
    FROM users u
    JOIN appointments a ON u.user_id = a.patient_id
    WHERE a.doctor_id = ? AND a.status = 'completed'
    GROUP BY u.user_id, u.full_name, u.email, u.phone
    ORDER BY last_visit DESC
";
$stmt = $conn->prepare($patients_query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$patients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get stats
$total_patients = count($patients);
$recent_patients = $conn->query("
    SELECT COUNT(DISTINCT patient_id) as recent 
    FROM appointments 
    WHERE doctor_id = $doctor_id 
    AND appointment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetch_assoc()['recent'];
?>

<style>
.patients-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.patients-header {
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

.patients-table {
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

.patient-avatar {
    width: 40px;
    height: 40px;
    background: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.patient-name {
    font-weight: 600;
    color: var(--dark);
}

.patient-contact {
    color: var(--gray);
    font-size: 0.85rem;
}

.visit-badge {
    background: #e7f3ff;
    color: var(--primary);
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 500;
}

.last-visit {
    color: var(--gray);
    font-size: 0.85rem;
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
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .table-responsive {
        font-size: 0.85rem;
    }
    
    .patients-header {
        padding: 1.5rem;
    }
}
</style>

<div class="patients-container">
    <div class="container">
        <!-- Header -->
        <div class="patients-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h2 fw-bold mb-2">My Patients</h1>
                    <p class="text-muted mb-0">Manage and view your patient relationships</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-light rounded-pill px-3 py-2 d-inline-block">
                        <i class="fas fa-users me-2 text-primary"></i>
                        Patient Management
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_patients ?></div>
                <div class="stat-label">Total Patients</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $recent_patients ?></div>
                <div class="stat-label">Active (30 days)</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?= $total_patients > 0 ? round(($recent_patients / $total_patients) * 100) : 0 ?>%
                </div>
                <div class="stat-label">Retention Rate</div>
            </div>
        </div>

        <!-- Patients Table -->
        <div class="patients-table">
            <div class="table-header">
                <h4 class="mb-0">
                    <i class="fas fa-list me-2 text-primary"></i>Patient List
                </h4>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Contact</th>
                            <th>Total Visits</th>
                            <th>Last Visit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($patients) > 0): ?>
                            <?php foreach($patients as $patient): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="patient-avatar">
                                                <?= strtoupper(substr($patient['full_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="patient-name"><?= htmlspecialchars($patient['full_name']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="patient-contact">
                                            <div><?= htmlspecialchars($patient['email']) ?></div>
                                            <small><?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="visit-badge">
                                            <?= $patient['total_visits'] ?> visit<?= $patient['total_visits'] > 1 ? 's' : '' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="last-visit">
                                            <?= $patient['last_visit'] ? date('M j, Y', strtotime($patient['last_visit'])) : 'Never' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Active</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-user-injured"></i>
                                        <h5>No Patients Yet</h5>
                                        <p class="mb-0">Your patient list will appear here after completed appointments.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="dashboard.php" class="btn btn-outline-custom me-3">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <a href="my_appointments.php" class="btn btn-custom">
                <i class="fas fa-calendar-check me-2"></i>View Appointments
            </a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>