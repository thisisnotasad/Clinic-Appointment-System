<?php 
require_once '../includes/auth.php'; 
require_once '../includes/db_connect.php'; 
if (!isAdmin()) header("Location: ../dashboard.php");

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT d.*, u.full_name, u.email, u.phone FROM doctors d JOIN users u ON d.user_id = u.user_id WHERE d.doctor_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();
if (!$doc) die("Doctor not found");

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name      = trim($_POST['full_name']);
    $email          = trim($_POST['email']);
    $phone          = trim($_POST['phone']);
    $specialization = $_POST['specialization'];
    $qualification  = trim($_POST['qualification']);
    $experience     = (int)$_POST['experience'];
    $fee            = (float)$_POST['fee'];

    $conn->begin_transaction();
    try {
        $stmt1 = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=? WHERE user_id = ?");
        $stmt1->bind_param("sssi", $full_name, $email, $phone, $doc['user_id']);
        $stmt1->execute();

        $stmt2 = $conn->prepare("UPDATE doctors SET specialization_id=?, qualification=?, experience_years=?, consultation_fee=? WHERE doctor_id = ?");
        $stmt2->bind_param("isidi", $specialization, $qualification, $experience, $fee, $id);
        $stmt2->execute();

        $conn->commit();
        $success = "Doctor updated successfully!";
        $doc = array_merge($doc, $_POST); // refresh data
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Update failed. Email may already exist.";
    }
}
?>

<?php require_once '../includes/header.php'; ?>

<div class="container mt-4">
    <h2>Edit Doctor: Dr. <?= htmlspecialchars($doc['full_name']) ?></h2>

    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <form method="POST" class="card shadow-lg p-5">
        <div class="row g-4">
            <div class="col-md-6"><label>Full Name</label><input type="text" name="full_name" class="form-control form-control-lg" value="<?= $doc['full_name'] ?>" required></div>
            <div class="col-md-6"><label>Email</label><input type="email" name="email" class="form-control form-control-lg" value="<?= $doc['email'] ?>" required></div>
            <div class="col-md-6"><label>Phone</label><input type="text" name="phone" class="form-control form-control-lg" value="<?= $doc['phone'] ?>"></div>
            <div class="col-md-6"><label>Specialization</label>
                <select name="specialization" class="form-select form-control-lg" required>
                    <?php
                    $res = $conn->query("SELECT * FROM specializations");
                    while ($s = $res->fetch_assoc()) {
                        $sel = $s['specialization_id'] == $doc['specialization_id'] ? 'selected' : '';
                        echo "<option value='{$s['specialization_id']}' $sel>{$s['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6"><label>Qualification</label><input type="text" name="qualification" class="form-control form-control-lg" value="<?= $doc['qualification'] ?>" required></div>
            <div class="col-md-6"><label>Experience (Years)</label><input type="number" name="experience" class="form-control form-control-lg" value="<?= $doc['experience_years'] ?>" required></div>
            <div class="col-md-6"><label>Consultation Fee (₹)</label><input type="number" step="0.01" name="fee" class="form-control form-control-lg" value="<?= $doc['consultation_fee'] ?>" required></div>
        </div>
        <div class="mt-5 text-center">
            <button type="submit" class="btn btn-primary btn-lg px-5">Update Doctor</button>
            <a href="doctors.php" class="btn btn-secondary btn-lg px-5 ms-3">Back</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>