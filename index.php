<?php
require_once 'includes/config.php';  // starts session + defines SITE_URL
// If already logged in → redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= SITE_NAME ?> - Book Your Appointment Easily</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card-shadow {
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border-radius: 15px;
            overflow: hidden;
        }
        .btn-custom {
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
        }
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="hero-section text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5">
                <h1 class="display-3 fw-bold mb-4">
                    Welcome to <span class="text-warning"><?= SITE_NAME ?></span>
                </h1>
                <p class="lead mb-5">
                    Book appointments with top doctors in just a few clicks.<br>
                    No more waiting on phone calls or long queues!
                </p>
                <a href="auth/login.php" class="btn btn-light btn-lg btn-custom shadow-lg">
                    <i class="fas fa-sign-in-alt me-2"></i> Login / Register
                </a>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-4 text-center mb-4">
                <div class="text-white">
                    <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                    <h4>Easy Booking</h4>
                    <p>Choose doctor, date & time in seconds</p>
                </div>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="text-white">
                    <div class="feature-icon"><i class="fas fa-clock"></i></div>
                    <h4>Save Time</h4>
                    <p>No waiting in line – arrive at your slot</p>
                </div>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="text-white">
                    <div class="feature-icon"><i class="fas fa-user-md"></i></div>
                    <h4>Expert Doctors</h4>
                    <p>Consult with qualified specialists</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <small>© <?= date('Y') ?> <?= SITE_NAME ?> • All rights reserved</small>
        </div>
    </div>
</div>

</body>
</html>