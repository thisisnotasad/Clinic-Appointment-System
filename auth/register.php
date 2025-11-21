<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    // Validation
    if (strlen($password) < 4) {
        $error = "Password must be at least 4 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Email already registered! Please login.";
        } else {
            // Insert new patient with chosen password
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, 'patient')");
            $stmt->bind_param("ssss", $full_name, $email, $phone, $password);

            if ($stmt->execute()) {
                $success = "Registration successful! You can now login with your chosen password.";
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .btn-custom {
            border-radius: 50px;
            padding: 12px 30px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0">
                <div class="card-header bg-success text-white text-center py-4">
                    <h3><i class="fas fa-user-plus me-2"></i> Patient Registration</h3>
                </div>
                <div class="card-body p-5">

                    <?php if($success): ?>
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle fa-3x mb-3"></i><br>
                            <strong><?= $success ?></strong><br><br>
                            <a href="login.php" class="btn btn-success btn-lg btn-custom">Go to Login</a>
                        </div>
                    <?php else: ?>

                        <?php if($error): ?>
                            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-lg-label"><i class="fas fa-user"></i> Full Name</label>
                                <input type="text" name="full_name" class="form-control form-control-lg" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-lg-label"><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" name="email" class="form-control form-control-lg" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-lg-label"><i class="fas fa-phone"></i> Phone</label>
                                <input type="text" name="phone" class="form-control form-control-lg" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-lg-label"><i class="fas fa-lock"></i> Password</label>
                                <input type="password" name="password" class="form-control form-control-lg" required minlength="4">
                            </div>
                            <div class="mb-4">
                                <label class="form-lg-label"><i class="fas fa-lock"></i> Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control form-control-lg" required>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg btn-custom w-100">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </form>

                        <div class="text-center mt-4 text-white">
                            <p>Already have an account? <a href="login.php" class="text-white fw-bold">Login here</a></p>
                            <p><a href="../index.php" class="text-white">&larr; Back to Home</a></p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>