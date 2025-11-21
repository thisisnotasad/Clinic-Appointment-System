<?php require_once '../includes/auth.php'; ?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/db_connect.php'; ?>
<?php if (!isAdmin()) header("Location: ../dashboard.php"); 

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("
    SELECT u.*, d.*, s.name as spec_name 
    FROM doctors d 
    JOIN users u ON d.user_id = u.user_id 
    JOIN specializations s ON d.specialization_id = s.specialization_id 
    WHERE d.doctor_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();

if (!$doc) die("Doctor not found");
?>

<div class="container mt-4">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white">
            <h3><i class="fas fa-user-md"></i> Doctor Profile: <?= htmlspecialchars($doc['full_name']) ?></h3>
        </div>
        <div class="card-body p-5">
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <div class="bg-light rounded-circle mx-auto" style="width:150px;height:150px;">
                        <i class="fas fa-user-md fa-5x text-primary pt-4"></i>
                    </div>
                    <h4 class="mt-3"><?= $doc['full_name'] ?></h4>
                    <p class="text-muted"><?= $doc['spec_name'] ?></p>
                </div>
                <div class="col-md-8">
                    <table class="table table-borderless">
                        <tr><th width="200">Email</th><td><?= $doc['email'] ?></td></tr>
                        <tr><th>Phone</th><td><?= $doc['phone'] ?: 'Not set' ?></td></tr>
                        <tr><th>Qualification</th><td><?= $doc['qualification'] ?></td></tr>
                        <tr><th>Experience</th><td><?= $doc['experience_years'] ?> years</td></tr>
                        <tr><th>Consultation Fee</th><td>₹<?= number_format($doc['consultation_fee'], 2) ?></td></tr>
                        <tr><th>Joined On</th><td><?= date('d M Y', strtotime($doc['created_at'])) ?></td></tr>
                    </table>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="edit_doctor.php?id=<?= $doc['doctor_id'] ?>" class="btn btn-warning btn-lg">
                    Edit Doctor
                </a>
                <a href="doctors.php" class="btn btn-secondary btn-lg ms-3">
                    Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>