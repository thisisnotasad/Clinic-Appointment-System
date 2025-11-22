<?php require_once '../includes/auth.php'; ?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/db_connect.php'; ?>
<?php if (!isDoctor()) header("Location: ../dashboard.php"); ?>

<?php
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
            $msg = "<div class='alert alert-danger'>Passwords do not match!</div>";
        } elseif (strlen($new_pass) < 4) {
            $msg = "<div class='alert alert-danger'>Password must be at least 4 characters.</div>";
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
        $msg = "<div class='alert alert-success'>Profile updated successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Update failed. Email might already exist.</div>";
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
        <div class="profile-card">
            <!-- Header -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user-md"></i>
                </div>
                <h1 class="h2 fw-bold mb-2">My Profile</h1>
                <p class="opacity-90 mb-0">Manage your personal information and security</p>
            </div>

            <!-- Body -->
            <div class="profile-body">
                <?php if($msg): ?>
                    <div class="alert-custom <?= strpos($msg, 'success') !== false ? 'alert-success' : 'alert-danger' ?>">
                        <?= $msg ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Personal Information -->
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
                                       value="Doctor Account" disabled style="background: var(--light);">
                            </div>
                        </div>
                    </div>

                    <!-- Password Change -->
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

                    <!-- Action Buttons -->
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
</div>

<?php require_once '../includes/footer.php'; ?>