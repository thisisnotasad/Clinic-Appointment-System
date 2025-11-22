<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, full_name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();

    if ($user && $password === $user['password']) {
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];
        header("Location: ../dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - <?= SITE_NAME ?></title>
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
            max-width: 420px;
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
            padding: 2.5rem 2rem;
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
            border: 1px solid #f5c6cb;
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
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

        @media (max-width: 480px) {
            .auth-body {
                padding: 1.5rem;
            }
            
            .auth-header {
                padding: 2rem 1.5rem;
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
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <div class="brand-logo"><?= SITE_NAME ?></div>
                <p class="text-muted mb-0">Sign in to your account</p>
            </div>

            <!-- Body -->
            <div class="auth-body">
                <?php if($error): ?>
                    <div class="alert-custom">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-control form-control-custom" 
                                   placeholder="Enter your email" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" class="form-control form-control-custom" 
                                   placeholder="Enter your password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-auth">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </form>

                <div class="auth-links">
                    <p class="mb-2">Don't have an account? <a href="register.php">Create one here</a></p>
                    <p class="mb-0"><a href="../index.php"><i class="fas fa-arrow-left me-1"></i>Back to Home</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>