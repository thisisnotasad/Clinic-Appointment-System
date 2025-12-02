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

<style>
.profile-container {
    background: linear-gradient(135deg, #f5f7ff 0%, #e6ecff 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.profile-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow-lg);
    overflow: hidden;
    margin-bottom: 2rem;
}

.profile-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    padding: 2rem;
    text-align: center;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2.5rem;
}

.profile-body {
    padding: 3rem;
}

.form-section {
    margin-bottom: 2.5rem;
}

.section-title {
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--gray-light);
    font-size: 1.3rem;
}

.form-control-custom {
    border: 2px solid var(--gray-light);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    font-size: 1rem;
    transition: var(--transition);
    background: var(--light);
}

.form-control-custom:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(74, 108, 247, 0.1);
    background: white;
}

.form-label {
    font-weight: 500;
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.btn-update {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border: none;
    border-radius: 50px;
    padding: 1rem 3rem;
    font-weight: 600;
    font-size: 1.1rem;
    color: white;
    transition: var(--transition);
    box-shadow: 0 10px 25px rgba(74, 108, 247, 0.3);
    margin-bottom: 1rem;
}

.btn-update:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(74, 108, 247, 0.4);
    color: white;
}

.btn-back {
    background: white;
    border: 2px solid var(--gray-light);
    border-radius: 50px;
    padding: 1rem 3rem;
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--dark);
    transition: var(--transition);
}

.btn-back:hover {
    border-color: var(--primary);
    background: rgba(74, 108, 247, 0.05);
    color: var(--primary);
}

.alert-custom {
    border-radius: 12px;
    border: none;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--box-shadow);
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    border-left: 4px solid var(--success);
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
    color: #721c24;
    border-left: 4px solid var(--danger);
}

.password-note {
    background: var(--light);
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
    border-left: 4px solid var(--primary);
}

@media (max-width: 768px) {
    .profile-body {
        padding: 2rem 1.5rem;
    }
    
    .btn-update, .btn-back {
        width: 100%;
        margin-bottom: 1rem;
    }
}
</style>

<div class="profile-container">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h1 class="h2 fw-bold mb-2">My Profile</h1>
                        <p class="opacity-90 mb-0">Manage your personal information and security</p>
                    </div>

                    <div class="profile-body">
                        <?php if($msg): ?>
                            <div class="alert-custom <?= strpos($msg, 'success') !== false ? 'alert-success' : 'alert-danger' ?>">
                                <?= str_replace(['<div class=\'alert alert-danger\'>', '<div class=\'alert alert-success\'>', '</div>'], '', $msg) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="form-section">
                                <h3 class="section-title">
                                    <i class="fas fa-user-circle me-2"></i>Personal Information
                                </h3>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="full_name" class="form-control form-control-custom" 
                                                value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" name="email" class="form-control form-control-custom" 
                                                value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="phone" class="form-control form-control-custom" 
                                                value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                                                placeholder="Enter your contact number">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Account Type</label>
                                        <input type="text" class="form-control form-control-custom" 
                                                value="Patient Account" disabled style="background: var(--light);">
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3 class="section-title">
                                    <i class="fas fa-lock me-2"></i>Security Settings
                                </h3>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control form-control-custom" 
                                                placeholder="Leave blank to keep current password">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" name="confirm_password" class="form-control form-control-custom" 
                                                placeholder="Retype new password">
                                    </div>
                                </div>
                                <div class="password-note">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Password must be at least 4 characters long. Leave both fields empty to keep your current password.
                                    </small>
                                </div>
                            </div>

                            <div class="text-center mt-5">
                                <button type="submit" class="btn btn-update me-3">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                                <a href="dashboard.php" class="btn btn-back">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="profile-card p-4">
                    <h3 class="section-title border-0 mb-4 pb-0"><i class="fas fa-chart-bar me-2"></i>Account Summary</h3>

                    <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                        <div class="profile-avatar" style="width: 60px; height: 60px; font-size: 1.5rem; margin: 0; margin-right: 1rem;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold"><?= htmlspecialchars($user['full_name']) ?></h5>
                            <p class="text-primary fw-bold mb-0" style="font-size: 0.85rem;">Patient Account</p>
                        </div>
                    </div>
                    
                    <div class="account-stats">
                        <div class="stat-item d-flex justify-content-between align-items-center p-3 rounded mb-3" style="background: rgba(74, 108, 247, 0.08);">
                            <span><i class="fas fa-calendar-check text-primary me-2"></i>Total Appointments</span>
                            <strong class="text-primary"><?= $conn->query("SELECT COUNT(*) as total FROM appointments WHERE patient_id = $user_id")->fetch_assoc()['total'] ?></strong>
                        </div>
                        <div class="stat-item d-flex justify-content-between align-items-center p-3 rounded mb-3" style="background: rgba(255, 193, 7, 0.1);">
                            <span><i class="fas fa-clock text-warning me-2"></i>Upcoming</span>
                            <strong class="text-warning"><?= $conn->query("SELECT COUNT(*) as upcoming FROM appointments WHERE patient_id = $user_id AND status = 'pending'")->fetch_assoc()['upcoming'] ?></strong>
                        </div>
                        <div class="stat-item d-flex justify-content-between align-items-center p-3 rounded" style="background: rgba(40, 167, 69, 0.1);">
                            <span><i class="fas fa-check-circle text-success me-2"></i>Completed</span>
                            <strong class="text-success"><?= $conn->query("SELECT COUNT(*) as completed FROM appointments WHERE patient_id = $user_id AND status = 'completed'")->fetch_assoc()['completed'] ?></strong>
                        </div>
                    </div>
                </div>

                <div class="profile-card p-4 mt-4">
                    <h3 class="section-title border-0 mb-4 pb-0"><i class="fas fa-shield-alt me-2"></i>Security Tips</h3>
                    
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

<?php require_once '../includes/footer.php'; ?>