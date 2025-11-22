<!-- profile.php -->
<?php 
require_once '../includes/auth.php'; 
require_once '../includes/header.php'; 
require_once '../includes/db_connect.php'; 

if (!isPatient()) {
    header("Location: ../dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone     = trim($_POST['phone']);
    $email     = trim($_POST['email']);
    $new_pass  = $_POST['new_password'];
    $confirm   = $_POST['confirm_password'];

    if (!empty($new_pass)) {
        if ($new_pass !== $confirm) {
            $msg = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle me-2'></i>Passwords do not match!</div>";
        } elseif (strlen($new_pass) < 4) {
            $msg = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle me-2'></i>Password must be at least 4 characters.</div>";
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, password=? WHERE user_id=?");
            $stmt->bind_param("ssssi", $full_name, $email, $phone, $new_pass, $user_id);
        }
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=? WHERE user_id=?");
        $stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
    }

    if ($stmt && $stmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        $msg = "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Profile updated successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle me-2'></i>Update failed. Email might already exist.</div>";
    }
}

// Load current data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="content-header">
        <h1 class="content-title"><i class="fas fa-user-cog me-2"></i>My Profile</h1>
        <p class="content-subtitle">Manage your personal information and account settings</p>
    </div>

    <?php if($msg) echo $msg; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="content-card">
                <div class="card-header-custom">
                    <h3 class="card-title-custom">
                        <i class="fas fa-user-edit me-2"></i>Personal Information
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="fas fa-user me-2"></i>Full Name</label>
                                <input type="text" name="full_name" class="form-control form-control-lg" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                <input type="email" name="email" class="form-control form-control-lg" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="fas fa-phone me-2"></i>Phone Number</label>
                                <input type="text" name="phone" class="form-control form-control-lg" value="<?= htmlspecialchars($user['phone']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><i class="fas fa-user-tag me-2"></i>Account Type</label>
                                <input type="text" class="form-control form-control-lg" value="Patient" disabled style="background: rgba(74, 108, 247, 0.1);">
                            </div>
                        </div>

                        <hr class="my-5">

                        <h4 class="mb-4"><i class="fas fa-lock me-2"></i>Change Password</h4>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">New Password</label>
                                <input type="password" name="new_password" class="form-control form-control-lg" placeholder="Leave blank to keep current password">
                                <small class="text-muted">Minimum 4 characters</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="Retype new password">
                            </div>
                        </div>

                        <div class="text-center mt-5">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary btn-lg px-5 ms-3">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Account Summary -->
            <div class="content-card">
                <div class="card-header-custom">
                    <h3 class="card-title-custom">
                        <i class="fas fa-chart-bar me-2"></i>Account Summary
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="feature-icon me-3">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div>
                            <h5 class="mb-1"><?= htmlspecialchars($user['full_name']) ?></h5>
                            <p class="text-muted mb-0">Patient Account</p>
                        </div>
                    </div>
                    
                    <div class="account-stats">
                        <div class="stat-item d-flex justify-content-between align-items-center p-3 bg-light rounded mb-2">
                            <span><i class="fas fa-calendar-check text-primary me-2"></i>Total Appointments</span>
                            <strong><?= $conn->query("SELECT COUNT(*) as total FROM appointments WHERE patient_id = $user_id")->fetch_assoc()['total'] ?></strong>
                        </div>
                        <div class="stat-item d-flex justify-content-between align-items-center p-3 bg-light rounded mb-2">
                            <span><i class="fas fa-clock text-warning me-2"></i>Upcoming</span>
                            <strong><?= $conn->query("SELECT COUNT(*) as upcoming FROM appointments WHERE patient_id = $user_id AND status = 'pending'")->fetch_assoc()['upcoming'] ?></strong>
                        </div>
                        <div class="stat-item d-flex justify-content-between align-items-center p-3 bg-light rounded">
                            <span><i class="fas fa-check-circle text-success me-2"></i>Completed</span>
                            <strong><?= $conn->query("SELECT COUNT(*) as completed FROM appointments WHERE patient_id = $user_id AND status = 'completed'")->fetch_assoc()['completed'] ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Tips -->
            <div class="content-card mt-4">
                <div class="card-header-custom">
                    <h3 class="card-title-custom">
                        <i class="fas fa-shield-alt me-2"></i>Security Tips
                    </h3>
                </div>
                <div class="card-body">
                    <div class="security-tip d-flex align-items-start mb-3">
                        <i class="fas fa-key text-success me-2 mt-1"></i>
                        <small>Use a strong, unique password for your account</small>
                    </div>
                    <div class="security-tip d-flex align-items-start mb-3">
                        <i class="fas fa-envelope text-primary me-2 mt-1"></i>
                        <small>Keep your email address updated for notifications</small>
                    </div>
                    <div class="security-tip d-flex align-items-start">
                        <i class="fas fa-phone text-info me-2 mt-1"></i>
                        <small>Ensure your phone number is correct for emergency contact</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-control {
    border-radius: 8px;
    border: 1px solid var(--gray-light);
    transition: var(--transition);
}

.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(74, 108, 247, 0.15);
}

.security-tip {
    padding: 8px;
    border-radius: 6px;
    background: rgba(74, 108, 247, 0.05);
}
</style>

<?php require_once '../includes/footer.php'; ?>