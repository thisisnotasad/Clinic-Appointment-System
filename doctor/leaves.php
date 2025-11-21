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
    echo "<div class='alert alert-success'>Leave added</div>";
}
if ($_GET['del'] ?? 0) {
    $del = $conn->prepare("DELETE FROM doctor_leaves WHERE leave_id = ? AND doctor_id = ?");
    $del->bind_param("ii", $_GET['del'], $doctor_id);
    $del->execute();
}
?>

<div class="container mt-4">
    <h2>Manage Leaves / Holidays</h2>

    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label>Date</label>
                            <input type="date" name="leave_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Reason (optional)</label>
                            <input type="text" name="reason" class="form-control" placeholder="e.g. Vacation, Conference">
                        </div>
                        <button class="btn btn-warning w-100">Mark as Leave</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <h5>Upcoming Leaves</h5>
            <div class="list-group">
                <?php
                $res = $conn->query("SELECT * FROM doctor_leaves WHERE doctor_id = $doctor_id AND leave_date >= CURDATE() ORDER BY leave_date");
                while ($l = $res->fetch_assoc()): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= date('d M Y', strtotime($l['leave_date'])) ?></strong>
                        <?= $l['reason'] ? " – " . htmlspecialchars($l['reason']) : '' ?>
                    </div>
                    <a href="?del=<?= $l['leave_id'] ?>" class="text-danger" onclick="return confirm('Remove this leave?')">
                        Remove
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>