<!-- [file name]: schedule.php -->
<!-- [file content begin] -->
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

<div class="container mt-4">
    <h2 class="mb-4">My Weekly Schedule</h2>
    
    <?php if($msg) echo $msg; ?>

    <?php if ($just_saved): ?>
        <script>
            setTimeout(() => {
                // Redirect to same page without POST data to prevent resubmission
                window.location.href = window.location.pathname;
            }, 1200);
        </script>
    <?php endif; ?>

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
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header <?= $sched ? 'bg-success' : 'bg-secondary' ?> text-white">
                    <h5 class="mb-0"><?= $dayName ?></h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($sched): ?>
                        <h4><?= date('g:i A', strtotime($sched['start_time'])) ?> – <?= date('g:i A', strtotime($sched['end_time'])) ?></h4>
                        <p class="text-muted">Slots: <?= $sched['slot_duration'] ?> min</p>
                    <?php else: ?>
                        <p class="text-danger fs-5">Off Day</p>
                    <?php endif; ?>

                    <button class="btn <?= $sched ? 'btn-warning' : 'btn-primary' ?> btn-lg mt-3" 
                            onclick="openScheduleModal(<?= $d ?>, '<?= $dayName ?>', '<?= $sched['start_time'] ?? '09:00' ?>', '<?= $sched['end_time'] ?? '17:00' ?>', <?= $sched['slot_duration'] ?? 30 ?>)">
                        <?= $sched ? 'Edit' : 'Set' ?> Schedule
                    </button>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-secondary btn-lg">Back to Dashboard</a>
        <a href="leaves.php" class="btn btn-warning btn-lg ms-3">Manage Leaves</a>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="scheduleForm" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Set Schedule</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="dayOfWeek" name="day_of_week">
                    <div class="mb-3">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control form-control-lg" id="startTime" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control form-control-lg" id="endTime" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slot Duration</label>
                        <select name="slot_duration" class="form-select form-control-lg" id="slotDuration" required>
                            <option value="15">15 minutes</option>
                            <option value="30" selected>30 minutes</option>
                            <option value="45">45 minutes</option>
                            <option value="60">60 minutes</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-lg px-5">Save Schedule</button>
                    <button type="button" id="removeBtn" class="btn btn-danger" style="display:none;" onclick="removeSchedule()">Remove Schedule</button>
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
    if (confirm('Remove this schedule day?')) {
        const day = document.getElementById('dayOfWeek').value;
        window.location.href = `delete_schedule.php?day=${day}`;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>