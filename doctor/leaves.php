<?php require_once '../includes/auth.php'; ?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/db_connect.php'; ?>
<?php if (!isDoctor()) header("Location: ../dashboard.php"); ?>

<?php
$stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$doctor_id = $stmt->get_result()->fetch_assoc()['doctor_id'];

if ($_POST['action'] ?? '' === 'add') {
    $date = $_POST['leave_date'];
    $reason = $_POST['reason'];
    $ins = $conn->prepare("INSERT INTO doctor_leaves (doctor_id, leave_date, reason) VALUES (?, ?, ?)");
    $ins->bind_param("iss", $doctor_id, $date, $reason);
    $ins->execute();
    $success_msg = "<div class='alert alert-success'>Leave date added successfully!</div>";
}
if ($_GET['del'] ?? 0) {
    $del = $conn->prepare("DELETE FROM doctor_leaves WHERE leave_id = ? AND doctor_id = ?");
    $del->bind_param("ii", $_GET['del'], $doctor_id);
    $del->execute();
    $success_msg = "<div class='alert alert-success'>Leave date removed successfully!</div>";
}
?>

<style>
.leaves-container {
    background: linear-gradient(135deg, #f5f7ff 0%, #e6ecff 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.leaves-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--box-shadow-lg);
}

.leave-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    border: 2px solid transparent;
    height: 100%;
}

.leave-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--box-shadow-lg);
}

.leave-form {
    padding: 2rem;
}

.leave-list {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
}

.leave-item {
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-light);
    transition: var(--transition);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.leave-item:hover {
    background: rgba(74, 108, 247, 0.03);
}

.leave-item:last-child {
    border-bottom: none;
}

.leave-date {
    font-weight: 700;
    color: var(--dark);
    font-size: 1.1rem;
}

.leave-reason {
    color: var(--gray);
    margin-top: 0.25rem;
}

.btn-leave {
    background: #ffb347;
    border: none;
    border-radius: 50px;
    padding: 1rem 2rem;
    font-weight: 600;
    color: white;
    transition: var(--transition);
    width: 100%;
}

.btn-leave:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 193, 7, 0.3);
    color: white;
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

.btn-remove {
    color: var(--danger);
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: var(--transition);
    font-weight: 500;
}

.btn-remove:hover {
    background: rgba(220, 53, 69, 0.1);
    color: var(--danger);
}

.form-control-custom {
    border: 2px solid var(--gray-light);
    border-radius: 12px;
    padding: 1rem;
    font-size: 1rem;
    transition: var(--transition);
    background: var(--light);
}

.form-control-custom:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(74, 108, 247, 0.1);
    background: white;
}

.leaves-info {
    background: var(--light);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    border-left: 4px solid var(--warning);
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

@media (max-width: 768px) {
    .leave-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .btn-remove {
        align-self: flex-end;
    }
}
</style>

<div class="leaves-container">
    <div class="container">
        <!-- Header -->
        <div class="leaves-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h2 fw-bold mb-2">Manage Leaves & Holidays</h1>
                    <p class="mb-0 opacity-90">Schedule your time off and manage unavailable dates</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-opacity-20 rounded-pill px-3 py-2 d-inline-block" style="background:#3d6baf;">
                        <i class="fas fa-umbrella-beach me-2"></i>
                        Time Off Management
                    </div>
                </div>
            </div>
        </div>

        <?php if(isset($success_msg)) echo $success_msg; ?>

        <!-- Info Section -->
        <div class="leaves-info">
            <div class="row align-items-center">
                <div class="col-md-10">
                    <h6 class="mb-1"><i class="fas fa-info-circle me-2 text-warning"></i>Managing Your Leaves</h6>
                    <p class="mb-0 text-muted">Mark dates when you'll be unavailable. Patients won't be able to book appointments on these days.</p>
                </div>
                <div class="col-md-2 text-end">
                    <span class="badge bg-warning">Important</span>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Add Leave Form -->
            <div class="col-lg-5 mb-4">
                <div class="leave-card">
                    <div class="leave-form">
                        <h4 class="mb-4">
                            <i class="fas fa-calendar-plus me-2 text-warning"></i>Add Leave Date
                        </h4>
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Leave Date</label>
                                <input type="date" name="leave_date" class="form-control form-control-custom" 
                                       min="<?= date('Y-m-d') ?>" required>
                                <small class="text-muted">Select the date you'll be unavailable</small>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Reason (Optional)</label>
                                <input type="text" name="reason" class="form-control form-control-custom" 
                                       placeholder="e.g., Vacation, Conference, Personal Day">
                                <small class="text-muted">Brief reason for your absence</small>
                            </div>
                            <button class="btn btn-leave">
                                <i class="fas fa-plus-circle me-2"></i>Mark as Leave
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Leaves List -->
            <div class="col-lg-7 mb-4">
                <div class="leave-list">
                    <div class="leave-header p-4 border-bottom">
                        <h4 class="mb-0">
                            <i class="fas fa-list me-2 text-primary"></i>Upcoming Leaves
                        </h4>
                    </div>
                    <div class="leave-body">
                        <?php
                        $res = $conn->query("SELECT * FROM doctor_leaves WHERE doctor_id = $doctor_id AND leave_date >= CURDATE() ORDER BY leave_date");
                        if ($res->num_rows > 0):
                            while ($l = $res->fetch_assoc()): ?>
                                <div class="leave-item">
                                    <div>
                                        <div class="leave-date">
                                            <i class="fas fa-calendar-day me-2 text-warning"></i>
                                            <?= date('l, F j, Y', strtotime($l['leave_date'])) ?>
                                        </div>
                                        <?php if ($l['reason']): ?>
                                            <div class="leave-reason">
                                                <i class="fas fa-comment me-1"></i>
                                                <?= htmlspecialchars($l['reason']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <a href="?del=<?= $l['leave_id'] ?>" class="btn-remove" 
                                       onclick="return confirm('Remove this leave date?')">
                                        <i class="fas fa-times me-1"></i>Remove
                                    </a>
                                </div>
                            <?php endwhile;
                        else: ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-check"></i>
                                <h5>No Upcoming Leaves</h5>
                                <p class="mb-0">You haven't scheduled any time off yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-center mt-4" >
            <a href="dashboard.php" class="btn btn-back me-3 btn-outline-custom">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <a href="schedule.php" class="btn btn-update btn-custom">
                <i class="fas fa-calendar-alt me-2"></i>Manage Schedule
            </a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>