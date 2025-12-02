<?php require_once '../includes/auth.php'; ?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/db_connect.php'; ?>
<?php if (!isDoctor()) header("Location: ../dashboard.php"); ?>

<?php
// Get doctor's ID
$stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$doctor_id = $stmt->get_result()->fetch_assoc()['doctor_id'];

// Handle form submission
$msg = '';
$just_saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $day = $_POST['day_of_week'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $duration = $_POST['slot_duration'];

    if (strtotime($start) >= strtotime($end)) {
        $msg = "<div class='alert alert-danger'>Start time must be before end time!</div>";
    } else {
        $del = $conn->prepare("DELETE FROM doctor_schedule WHERE doctor_id = ? AND day_of_week = ?");
        $del->bind_param("ii", $doctor_id, $day);
        $del->execute();

        $ins = $conn->prepare("INSERT INTO doctor_schedule (doctor_id, day_of_week, start_time, end_time, slot_duration, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $ins->bind_param("iissi", $doctor_id, $day, $start, $end, $duration);

        if ($ins->execute()) {
            $msg = "<div class='alert alert-success'>Schedule saved for <strong>" . 
                   ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][$day] . 
                   "</strong>!</div>";
            $just_saved = true;
        } else {
            $msg = "<div class='alert alert-danger'>Save failed. Try again.</div>";
        }
    }
}
?>

<style>
.schedule-container {
    background: linear-gradient(135deg, #f5f7ff 0%, #e6ecff 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.schedule-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--box-shadow-lg);
}

.day-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    border: 2px solid transparent;
    height: 100%;
    overflow: hidden;
}

.day-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--box-shadow-lg);
}

.day-card.active {
    border-color: var(--primary);
}

.day-header {
    padding: 1.5rem;
    text-align: center;
    color: white;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
}

.day-header.off-day {
    background: linear-gradient(135deg, var(--gray) 0%, #8c94a3 100%);
}

.day-body {
    padding: 2rem 1.5rem;
    text-align: center;
}

.schedule-time {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.schedule-duration {
    color: var(--gray);
    font-weight: 500;
}

.off-day-text {
    color: var(--danger);
    font-weight: 600;
    font-size: 1.1rem;
}

.btn-schedule {
    border-radius: 50px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: var(--transition);
    margin-top: 1rem;
}

.btn-set {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border: none;
    color: white;
}

.btn-edit {
    background: linear-gradient(135deg, var(--warning) 0%, #ffb347 100%);
    background: #3d6baf;
    border: none;
    color: white;
}

.btn-schedule:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    color: white;
}

/* Button styles matched to leaves.php for consistent appearance */
.btn-back {
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

.btn-update {
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

.btn-back:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: rgba(74, 108, 247, 0.05);
}

.btn-update:hover {
    background: var(--primary-dark);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(74, 108, 247, 0.2);
}

.modal-custom .modal-content {
    border-radius: var(--border-radius);
    border: none;
    box-shadow: var(--box-shadow-lg);
}

.modal-custom .modal-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    padding: 1.5rem 2rem;
}

.modal-custom .modal-body {
    padding: 2rem;
}

.time-input {
    border: 2px solid var(--gray-light);
    border-radius: 12px;
    padding: 1rem;
    font-size: 1rem;
    transition: var(--transition);
}

.time-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(74, 108, 247, 0.1);
}

.action-buttons {
    margin-top: 3rem;
    text-align: center;
}

.week-note {
    background: var(--light);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    border-left: 4px solid var(--primary);
}

@media (max-width: 768px) {
    .schedule-header {
        padding: 1.5rem;
    }
    
    .day-body {
        padding: 1.5rem 1rem;
    }
}
</style>

<div class="schedule-container">
    <div class="container">
        <!-- Header -->
        <div class="schedule-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h2 fw-bold mb-2">My Weekly Schedule</h1>
                    <p class="mb-0 opacity-90">Set your availability for each day of the week</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-opacity-20 rounded-pill px-3 py-2 d-inline-block" style="background:#3d6baf;">
                        <i class="fas fa-calendar-week me-2"></i>
                        Weekly Availability
                    </div>
                </div>
            </div>
        </div>

        <?php if($msg): ?>
            <div class="alert-custom <?= strpos($msg, 'success') !== false ? 'alert-success' : 'alert-danger' ?>">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <?php if ($just_saved): ?>
            <script>
                setTimeout(() => {
                    window.location.href = window.location.pathname;
                }, 1200);
            </script>
        <?php endif; ?>

        <!-- Info Note -->
        <div class="week-note">
            <div class="row align-items-center">
                <div class="col-md-10">
                    <h6 class="mb-1"><i class="fas fa-info-circle me-2 text-primary"></i>Setting Your Schedule</h6>
                    <p class="mb-0 text-muted">Define your working hours for each day. Patients will only be able to book appointments during these available time slots.</p>
                </div>
                <div class="col-md-2 text-end">
                    <span class="badge bg-primary">6 Days</span>
                </div>
            </div>
        </div>

        <!-- Schedule Grid -->
        <div class="row">
            <?php 
            $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            for ($d = 1; $d <= 6; $d++): 
                $dayName = $days[$d-1];
                $stmt = $conn->prepare("SELECT * FROM doctor_schedule WHERE doctor_id = ? AND day_of_week = ?");
                $stmt->bind_param("ii", $doctor_id, $d);
                $stmt->execute();
                $result = $stmt->get_result();
                $sched = $result->num_rows > 0 ? $result->fetch_assoc() : null;
            ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="day-card <?= $sched ? 'active' : '' ?>">
                    <div class="day-header <?= $sched ? '' : 'off-day' ?>">
                        <h5 class="mb-0">
                            <i class="fas fa-<?= $sched ? 'check-circle' : 'times-circle' ?> me-2"></i>
                            <?= $dayName ?>
                        </h5>
                    </div>
                    <div class="day-body">
                        <?php if ($sched): ?>
                            <div class="schedule-time">
                                <?= date('g:i A', strtotime($sched['start_time'])) ?> – <?= date('g:i A', strtotime($sched['end_time'])) ?>
                            </div>
                            <div class="schedule-duration">
                                <i class="fas fa-clock me-1"></i>
                                <?= $sched['slot_duration'] ?> minute slots
                            </div>
                            <button class="btn btn-edit btn-schedule" 
                                    onclick="openScheduleModal(<?= $d ?>, '<?= $dayName ?>', '<?= $sched['start_time'] ?? '09:00' ?>', '<?= $sched['end_time'] ?? '17:00' ?>', <?= $sched['slot_duration'] ?? 30 ?>)">
                                <i class="fas fa-edit me-1"></i>Edit Schedule
                            </button>
                        <?php else: ?>
                            <div class="off-day-text mb-3">
                                <i class="fas fa-bed me-1"></i>Off Day
                            </div>
                            <button class="btn btn-set btn-schedule" 
                                    onclick="openScheduleModal(<?= $d ?>, '<?= $dayName ?>', '09:00', '17:00', 30)">
                                <i class="fas fa-calendar-plus me-1"></i>Set Schedule
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="dashboard.php" class="btn btn-back me-3">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <a href="leaves.php" class="btn btn-update">
                <i class="fas fa-umbrella-beach me-2"></i>Manage Leaves
            </a>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade modal-custom" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="scheduleForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Set Schedule</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="dayOfWeek" name="day_of_week">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Start Time</label>
                        <input type="time" name="start_time" class="form-control time-input" id="startTime" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">End Time</label>
                        <input type="time" name="end_time" class="form-control time-input" id="endTime" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Appointment Slot Duration</label>
                        <select name="slot_duration" class="form-select time-input" id="slotDuration" required>
                            <option value="15">15 minutes</option>
                            <option value="30" selected>30 minutes</option>
                            <option value="45">45 minutes</option>
                            <option value="60">60 minutes</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-back" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="removeBtn" class="btn btn-danger" style="display:none;" onclick="removeSchedule()">
                        <i class="fas fa-trash me-1"></i>Remove
                    </button>
                    <button type="submit" class="btn btn-update">
                        <i class="fas fa-save me-1"></i>Save Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openScheduleModal(day, dayName, start, end, duration) {
    document.getElementById('modalTitle').textContent = `Schedule for ${dayName}`;
    document.getElementById('dayOfWeek').value = day;
    document.getElementById('startTime').value = start;
    document.getElementById('endTime').value = end;
    document.getElementById('slotDuration').value = duration;
    document.getElementById('removeBtn').style.display = duration ? 'inline-block' : 'none';
    
    new bootstrap.Modal(document.getElementById('scheduleModal')).show();
}

function removeSchedule() {
    if (confirm('Remove schedule for this day?')) {
        const day = document.getElementById('dayOfWeek').value;
        window.location.href = `delete_schedule.php?day=${day}`;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>