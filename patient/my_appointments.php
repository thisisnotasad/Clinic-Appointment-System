<!-- my_appointments.php -->
<?php 
require_once '../includes/auth.php'; 
require_once '../includes/header.php'; 
require_once '../includes/db_connect.php'; 

if (!isPatient()) {
    header("Location: ../dashboard.php"); 
    exit();
}

// Get filter parameter
$filter = $_GET['filter'] ?? 'all';

// Build query based on filter
$where_clause = "a.patient_id = ?";
switch($filter) {
    case 'upcoming':
        // Only pending appointments in the future
        $where_clause .= " AND a.status = 'pending' AND CONCAT(a.appointment_date, ' ', a.appointment_time) >= NOW()";
        break;
    case 'past':
        // Completed appointments or appointments whose time has passed
        $where_clause .= " AND (a.status = 'completed' OR CONCAT(a.appointment_date, ' ', a.appointment_time) < NOW())";
        break;
    case 'cancelled':
        $where_clause .= " AND a.status = 'cancelled'";
        break;
}

$stmt = $conn->prepare("
    SELECT a.*, u.full_name, s.name as spec_name, u.email as doctor_email, u.phone as doctor_phone
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.doctor_id
    JOIN users u ON d.user_id = u.user_id
    JOIN specializations s ON d.specialization_id = s.specialization_id
    WHERE $where_clause
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="content-header">
        <h1 class="content-title"><i class="fas fa-list-alt me-2"></i>My Appointments</h1>
        <p class="content-subtitle">Manage and track your healthcare appointments</p>
    </div>

    <!-- Filter Tabs (Enhanced Spacing) -->
    <div class="content-card mb-4">
        <div class="card-header-custom">
            <h3 class="card-title-custom">
                <i class="fas fa-filter me-2"></i>Filter Appointments
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-8 col-md-12 mb-3 mb-lg-0">
                    <!-- Updated: Replaced btn-group with flex container and added spacing (me-2, mb-2) to the buttons -->
                    <div class="d-flex flex-wrap">
                        <a href="?filter=all" class="btn <?= $filter == 'all' ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2">
                            <i class="fas fa-list me-2"></i>All Appointments
                        </a>
                        <a href="?filter=upcoming" class="btn <?= $filter == 'upcoming' ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2">
                            <i class="fas fa-clock me-2"></i>Upcoming
                        </a>
                        <a href="?filter=past" class="btn <?= $filter == 'past' ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2">
                            <i class="fas fa-history me-2"></i>Past
                        </a>
                        <a href="?filter=cancelled" class="btn <?= $filter == 'cancelled' ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2">
                            <i class="fas fa-times me-2"></i>Cancelled
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-start">
                    <a href="book_appointment.php" class="btn btn-success w-100 w-lg-auto mt-3 mt-lg-0">
                        <i class="fas fa-calendar-plus me-2"></i>Book New Appointment
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments List -->
    <div class="content-card">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h3 class="card-title-custom">
                <i class="fas fa-calendar-check me-2"></i>
                <?= ucfirst($filter) ?> Appointments (<?= count($appointments) ?>)
            </h3>
        </div>
        <div class="card-body p-0">
            <?php if(count($appointments) > 0): ?>
                <div class="appointments-grid">
                    <?php foreach($appointments as $apt): 
                        $isUpcoming = $apt['status'] == 'pending' && strtotime($apt['appointment_date'] . ' ' . $apt['appointment_time']) > time();
                        $isToday = date('Y-m-d') == $apt['appointment_date'];
                    ?>
                        <div class="appointment-card <?= $isToday ? 'today-appointment' : '' ?>">
                            <div class="appointment-header">
                                <div class="appointment-date">
                                    <div class="date-day"><?= date('d', strtotime($apt['appointment_date'])) ?></div>
                                    <div class="date-month"><?= date('M', strtotime($apt['appointment_date'])) ?></div>
                                </div>
                                <div class="appointment-info">
                                    <h5 class="mb-1 text-truncate">Dr. <?= $apt['full_name'] ?></h5>
                                    <p class="text-muted mb-1 text-truncate"><?= $apt['spec_name'] ?></p>
                                    <p class="mb-0">
                                        <i class="fas fa-clock me-1"></i>
                                        <?= date('h:i A', strtotime($apt['appointment_time'])) ?>
                                    </p>
                                </div>
                                <div class="appointment-status">
                                    <span class="badge status-<?= $apt['status'] ?>">
                                        <?= ucfirst($apt['status']) ?>
                                    </span>
                                    <?php if($isToday): ?>
                                        <span class="badge bg-warning today-badge">Today</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="appointment-actions">
                                <a href="print_ticket.php?id=<?= $apt['appointment_id'] ?>" 
                                   class="btn btn-outline-primary btn-sm" target="_blank">
                                    <i class="fas fa-print me-1"></i>Print Ticket
                                </a>
                                
                                <?php if ($apt['status'] == 'pending' && $isUpcoming): ?>
                                    <!-- Replaced confirm() with custom modal function for compliance -->
                                    <button class="btn btn-outline-danger btn-sm" 
                                            onclick="showCancelModal(<?= $apt['appointment_id'] ?>)">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-outline-info btn-sm" 
                                        onclick="showDoctorInfo('<?= $apt['full_name'] ?>', '<?= $apt['doctor_email'] ?>', '<?= $apt['doctor_phone'] ?>')">
                                    <i class="fas fa-info-circle me-1"></i>Doctor Info
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times feature-icon text-muted mb-3" style="font-size: 4rem;"></i>
                    <h4 class="text-muted">No appointments found</h4>
                    <p class="text-muted mb-4">You don't have any <?= $filter ?> appointments.</p>
                    <?php if($filter != 'all'): ?>
                        <a href="?filter=all" class="btn btn-primary me-2">View All Appointments</a>
                    <?php endif; ?>
                    <a href="book_appointment.php" class="btn btn-success">
                        <i class="fas fa-calendar-plus me-2"></i>Book Your First Appointment
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Doctor Info Modal -->
<div class="modal fade" id="doctorInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Doctor Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="doctorModalContent"></div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal (for Cancel) -->
<div class="modal fade" id="confirmationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmationModalTitle">Confirm Action</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmationModalContent">
                <!-- Content injected here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmationModalConfirmBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Added responsive grid definition for desktop/tablet */
.appointments-grid {
    display: grid;
    gap: 1.5rem;
    padding: 1.5rem;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); /* Responsive columns */
}

.appointment-card {
    background: white;
    border: 1px solid var(--gray-light);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    transition: var(--transition);
    box-shadow: var(--box-shadow);
    overflow: hidden; /* Added to contain inner flex items and text */
}

.appointment-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--box-shadow-lg);
}

.appointment-card.today-appointment {
    border-left: 4px solid var(--warning);
    background: linear-gradient(135deg, #fff, #fff8e1);
}

.appointment-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    min-width: 0; /* Ensures flex item can shrink */
}

.appointment-date {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    min-width: 70px;
}

.date-day {
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1;
}

.date-month {
    font-size: 0.9rem;
    opacity: 0.9;
}

.appointment-info {
    flex: 1;
    min-width: 0; /* Critical for wrapping text inside flex container */
}

/* Ensure text inside info wraps */
.appointment-info h5,
.appointment-info p {
    overflow-wrap: break-word;
    word-wrap: break-word;
}

.appointment-status {
    text-align: right;
    min-width: 80px; /* ensure status badge has room */
}

.status-pending { background: var(--warning); }
.status-completed { background: var(--success); }
.status-cancelled { background: var(--danger); }

.today-badge {
    display: block;
    margin-top: 0.5rem;
}

.appointment-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 1rem; /* Added margin for separation */
}

@media (max-width: 768px) {
    /* Filter buttons now stack properly due to flex-wrap and mb-2 */
    .appointments-grid {
        gap: 1rem;
        padding: 1rem;
    }
    .appointment-card {
        padding: 1rem;
    }
    
    .appointment-header {
        flex-wrap: wrap; /* Allow status to drop below if space is tight */
        align-items: flex-start;
        gap: 0.75rem;
    }

    .appointment-info {
        flex-grow: 1;
        flex-basis: 0; /* Allow it to shrink and wrap */
    }

    .appointment-status {
        text-align: left; /* Align status badges for better reading on mobile */
        width: 100%; /* Take full width on small screens */
        margin-top: 0.5rem;
    }

    .appointment-actions {
        flex-direction: column; /* Stack buttons vertically for better touch targets */
        gap: 0.5rem;
    }
    
    .appointment-actions .btn {
        width: 100%; /* Make buttons full width */
    }
}
</style>

<script>
// Function to display Doctor Info Modal
function showDoctorInfo(name, email, phone) {
    const modalContent = document.getElementById('doctorModalContent');
    modalContent.innerHTML = `
        <div class="doctor-info">
            <h6 class="mb-3">Dr. ${name}</h6>
            <div class="info-item mb-2">
                <strong><i class="fas fa-envelope me-2"></i>Email:</strong>
                <a href="mailto:${email}">${email}</a>
            </div>
            <div class="info-item mb-2">
                <strong><i class="fas fa-phone me-2"></i>Phone:</strong>
                <a href="tel:${phone}">${phone}</a>
            </div>
            <div class="info-item">
                <strong><i class="fas fa-building me-2"></i>Clinic:</strong>
                <?= SITE_NAME ?>
            </div>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('doctorInfoModal'));
    modal.show();
}

// Function to display the custom confirmation modal for cancellation
function showCancelModal(appointmentId) {
    const modalContent = document.getElementById('confirmationModalContent');
    const modalTitle = document.getElementById('confirmationModalTitle');
    const confirmButton = document.getElementById('confirmationModalConfirmBtn');

    modalTitle.textContent = 'Confirm Cancellation';
    modalContent.innerHTML = '<p>Are you sure you want to cancel this appointment? This action cannot be undone and may incur a fee if within the cancellation window.</p>';
    
    // Set the action for the confirm button
    confirmButton.onclick = () => {
        // Navigate to the cancellation endpoint
        window.location.href = `cancel_appointment.php?id=${appointmentId}`;
    };

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    modal.show();
}
</script>

<?php require_once '../includes/footer.php'; ?>