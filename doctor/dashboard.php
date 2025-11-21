<?php require_once '../includes/auth.php'; ?>
<?php require_once '../includes/header.php'; ?>

<div class="container">
    <h1 class="mb-4">Doctor Dashboard</h1>
    <div class="alert alert-info">
        Welcome, Dr. <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>! You are logged in as <strong>Doctor</strong>.
    </div>
</div>


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
?>

<h2 class="mb-4">
    Today's Appointments 
    <small class="text-muted">(<?= date('d M Y') ?>)</small>
</h2>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-success">
            <tr>
                <th>Time</th>
                <th>Patient</th>
                <th>Phone</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $today = date('Y-m-d');
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

            while ($apt = $result->fetch_assoc()): ?>
            <tr>
                <td><strong><?= date('h:i A', strtotime($apt['appointment_time'])) ?></strong></td>
                <td><?= htmlspecialchars($apt['full_name']) ?></td>
                <td><?= htmlspecialchars($apt['phone'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($apt['reason_for_visit'] ?: 'General Checkup') ?></td>
                <td>
                    <span class="badge bg-<?= $apt['status']=='completed'?'success':($apt['status']=='cancelled'?'danger':'warning') ?>">
                        <?= ucfirst($apt['status']) ?>
                    </span>
                </td>
                <td>
                    <?php if ($apt['status'] == 'pending'): ?>
                        <a href="mark_complete.php?id=<?= $apt['appointment_id'] ?>" 
                           class="btn btn-sm btn-success" 
                           onclick="return confirm('Mark as completed?')">
                            Complete
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
