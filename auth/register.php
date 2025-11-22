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
                $success = "Registration successful! You can now login.";
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4a6cf7;
            --primary-light: #6a85f9;
            --primary-dark: #3a56d4;
            --secondary: #6c757d;
            --accent: #e9ecef;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #dee2e6;
            --success: #28a745;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            color: var(--dark);
        }

        .auth-container {
            width: 100%;
            max-width: 450px;
        }

        .auth-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid var(--gray-light);
        }

        .auth-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .auth-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid var(--gray-light);
        }

        .auth-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .auth-body {
            padding: 2rem;
        }

        .form-control-custom {
            border: 1px solid var(--gray-light);
            border-radius: 8px;
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
            transition: var(--transition);
            background: var(--light);
        }

        .form-control-custom:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(74, 108, 247, 0.1);
            background: white;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            z-index: 2;
        }

        .input-icon input {
            padding-left: 2.5rem;
        }

        .btn-auth {
            background: var(--primary);
            border: none;
            border-radius: 8px;
            padding: 0.875rem 2rem;
            font-weight: 500;
            font-size: 1rem;
            color: white;
            transition: var(--transition);
            width: 100%;
        }

        .btn-auth:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(74, 108, 247, 0.2);
        }

        .alert-custom {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-success {
            border: 1px solid #c3e6cb;
            background: #d4edda;
            color: #155724;
            text-align: center;
        }

        .alert-danger {
            border: 1px solid #f5c6cb;
            background: #f8d7da;
            color: #721c24;
        }

        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-light);
        }

        .auth-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .auth-links a:hover {
            color: var(--primary-dark);
        }

        .brand-logo {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .success-icon {
            font-size: 3rem;
            color: var(--success);
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        @media (max-width: 480px) {
            .auth-body {
                padding: 1.5rem;
            }
            
            .auth-header {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="brand-logo"><?= SITE_NAME ?></div>
                <p class="text-muted mb-0">Create your account</p>
            </div>

            <!-- Body -->
            <div class="auth-body">
                <?php if($success): ?>
                    <div class="alert-custom alert-success">
                        <i class="fas fa-check-circle success-icon"></i>
                        <h5 class="fw-bold mb-3">Welcome!</h5>
                        <p class="mb-4"><?= $success ?></p>
                        <a href="login.php" class="btn-auth">
                            <i class="fas fa-sign-in-alt me-2"></i>Continue to Login
                        </a>
                    </div>
                <?php else: ?>

                    <?php if($error): ?>
                        <div class="alert-custom alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" name="full_name" class="form-control form-control-custom" 
                                       placeholder="Enter your full name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <div class="input-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" class="form-control form-control-custom" 
                                       placeholder="Enter your email" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <div class="input-icon">
                                <i class="fas fa-phone"></i>
                                <input type="text" name="phone" class="form-control form-control-custom" 
                                       placeholder="Enter your phone" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" class="form-control form-control-custom" 
                                       placeholder="Create a password" required minlength="4">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="confirm_password" class="form-control form-control-custom" 
                                       placeholder="Confirm your password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn-auth">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    </form>

                    <div class="auth-links">
                        <p class="mb-2">Already have an account? <a href="login.php">Sign in here</a></p>
                        <p class="mb-0"><a href="../index.php"><i class="fas fa-arrow-left me-1"></i>Back to Home</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>