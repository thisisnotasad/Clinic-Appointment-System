<?php 
require_once '../includes/auth.php'; 
require_once '../includes/db_connect.php'; 
if (!isAdmin()) header("Location: ../dashboard.php");

$success = $error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name       = trim($_POST['full_name']);
    $email           = trim($_POST['email']);
    $phone           = trim($_POST['phone']);
    $password        = $_POST['password'];  // admin sets password
    $specialization  = $_POST['specialization'];
    $qualification   = trim($_POST['qualification']);
    $experience      = (int)$_POST['experience'];
    $fee             = (float)$_POST['fee'];

    // Basic validation
    if (strlen($password) < 4) {
        $error = "Password must be at least 4 characters.";
    } else {
        // Start transaction for safety
        $conn->begin_transaction();
        try {
            // 1. Create user account (role = doctor)
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, 'doctor')");
            $stmt->bind_param("ssss", $full_name, $email, $phone, $password);
            $stmt->execute();
            $user_id = $conn->insert_id;

            // 2. Create doctor profile
            $stmt2 = $conn->prepare("INSERT INTO doctors (user_id, specialization_id, qualification, experience_years, consultation_fee) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param("iisid", $user_id, $specialization, $qualification, $experience, $fee);
            $stmt2->execute();

            $conn->commit();
            $success = "Doctor added successfully! Login credentials:<br><strong>Email:</strong> $email<br><strong>Password:</strong> $password";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error: Email already exists or database issue.";
        }
    }
}
?>

<?php require_once '../includes/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4 text-primary">Add New Doctor</h2>

    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-5">
            <form method="POST">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" name="full_name" class="form-control form-control-lg" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control form-control-lg" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Phone</label>
                        <input type="text" name="phone" class="form-control form-control-lg" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" required minlength="4">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Specialization</label>
                        <select name="specialization" class="form-select form-select-lg" required>
                            <option value="">-- Select --</option>
                            <?php
                            $res = $conn->query("SELECT * FROM specializations ORDER BY name");
                            while ($row = $res->fetch_assoc()) {
                                echo "<option value='{$row['specialization_id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Qualification</label>
                        <input type="text" name="qualification" class="form-control form-control-lg" placeholder="e.g. MBBS, MD Cardiology" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Experience (Years)</label>
                        <input type="number" name="experience" class="form-control form-control-lg" min="0" value="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Consultation Fee (₹)</label>
                        <input type="number" step="0.01" name="fee" class="form-control form-control-lg" value="500" required>
                    </div>
                </div>

                <div class="mt-5 text-center">
                    <button type="submit" class="btn btn-success btn-lg px-5">
                        Add Doctor
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary btn-lg px-5 ms-3">
                        Back to Admin Panel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>