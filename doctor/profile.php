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

<div class="container mt-4">
    <h2 class="mb-4">My Profile</h2>
    <?php if($msg) echo $msg; ?>

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-5">
            <form method="POST">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control form-control-lg" value="<?= $user['full_name'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control form-control-lg" value="<?= $user['email'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control form-control-lg" value="<?= $user['phone'] ?>">
                    </div>
                </div>

                <hr class="my-5">

                <h5>Change Password (optional)</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control form-control-lg" placeholder="Leave blank to keep current">
                    </div>
                    <div class="col-md-6">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="Retype new password">
                    </div>
                </div>

                <div class="text-center mt-5">
                    <button type="submit" class="btn btn-success btn-lg px-5">Update Profile</button>
                    <a href="dashboard.php" class="btn btn-secondary btn-lg px-5 ms-3">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>