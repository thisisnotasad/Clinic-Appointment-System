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
        .login-icon {
            font-size: 4rem;
            color: #667eea;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3><i class="fas fa-sign-in-alt me-2"></i> Welcome Back!</h3>
                </div>
                <div class="card-body p-5 text-center">

                    <div class="mb-4">
                        <i class="fas fa-clinic-medical login-icon"></i>
                    </div>

                    <?php if($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" name="email" class="form-control form-control-lg" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                            <input type="password" name="password" class="form-control form-control-lg" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg btn-custom w-100">
                            <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                        </button>
                    </form>

                    <div class="text-center mt-4 text-black">
                        <p>Don't have an account? <a href="register.php" class="text-blue fw-bold">Register here</a></p>
                        <p><a href="../index.php" class="text-blue">&larr; Back to Home</a></p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>