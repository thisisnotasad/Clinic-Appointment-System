<!-- header.php -->
<?php
require_once __DIR__ . '/auth.php';  // This checks login + starts session

// Determine base path based on role
$base_path = '';
if (isPatient()) {
    $base_path = 'patient/';
} elseif (isDoctor()) {
    $base_path = 'doctor/';
} elseif (isAdmin()) {
    $base_path = 'admin/';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= SITE_NAME ?> - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4a6cf7;
            --primary-light: #6a85f9;
            --primary-dark: #3a56d4;
            --secondary: #6c63ff;
            --accent: #ff6b9d;
            --accent-light: #ff8ab0;
            --light: #f8f9ff;
            --dark: #1e2a4a;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --success: #28a745;
            --danger: #dc3545;
            --border-radius: 16px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --box-shadow-lg: 0 20px 50px rgba(0, 0, 0, 0.12);
            --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f8f9ff;
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
        }

        /* Enhanced Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            transition: var(--transition);
            border-bottom: 1px solid rgba(74, 108, 247, 0.1);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: var(--transition);
        }

        .navbar-nav{
            flex-direction: row;
        }

        .navbar-brand:hover {
            transform: translateY(-2px);
        }

        .user-welcome {
            /* -webkit-background-clip: text; */
            /* -webkit-text-fill-color: transparent; */
            /* background-clip: text; */
            font-weight: 600;
            margin-right: 1.5rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            background: rgba(74, 108, 247, 0.1);
            border: 1px solid rgba(74, 108, 247, 0.2);
        }

        .user-role {
            color: var(--gray);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .btn-logout {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            color: white;
            transition: var(--transition);
            box-shadow: 0 5px 15px rgba(74, 108, 247, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-logout::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .btn-logout:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(74, 108, 247, 0.4);
            color: white;
        }

        .btn-logout:hover::before {
            left: 100%;
        }

        /* Sidebar Navigation */
        .sidebar {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem 0;
            margin: 2rem 0;
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid var(--gray-light);
            margin-bottom: 1rem;
        }

        .sidebar-title {
            font-weight: 700;
            color: var(--dark);
            font-size: 1.1rem;
            margin: 0;
        }

        .nav-sidebar .nav-link {
            color: var(--gray);
            padding: 1rem 1.5rem;
            border-left: 3px solid transparent;
            transition: var(--transition);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .nav-sidebar .nav-link:hover {
            color: var(--primary);
            background: rgba(74, 108, 247, 0.05);
            border-left-color: var(--primary-light);
            padding-left: 2rem;
        }

        .nav-sidebar .nav-link.active {
            color: var(--primary);
            background: rgba(74, 108, 247, 0.1);
            border-left-color: var(--primary);
            font-weight: 600;
        }

        .nav-sidebar .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Main Content Area */
        .content-header {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin: 2rem 0 1.5rem;
            box-shadow: var(--box-shadow);
            border-left: 4px solid var(--primary);
        }

        .content-title {
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            font-size: 1.8rem;
        }

        .content-subtitle {
            color: var(--gray);
            margin: 0.5rem 0 0;
            font-size: 1.1rem;
        }

        .content-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--box-shadow);
            border: 1px solid var(--gray-light);
            transition: var(--transition);
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow-lg);
        }

        .card-header-custom {
            background: transparent;
            border-bottom: 1px solid var(--gray-light);
            padding: 0 0 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-title-custom {
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            font-size: 1.3rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .user-welcome {
                margin-right: 1rem;
                font-size: 0.9rem;
            }
            
            .sidebar {
                margin: 1rem 0;
                position: static;
            }
        }
    </style>
</head>
<body>
    <!-- Enhanced Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="<?= SITE_URL ?>/<?= $base_path ?>dashboard.php">
                <i class="fas fa-heartbeat me-2"></i><?= SITE_NAME ?>
            </a>
            
            <div class="navbar-nav ms-auto align-items-center">
                <div class="user-welcome text-center">
                    <div class="fw-bold"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                    <div class="user-role"><?= ucfirst($_SESSION['role']) ?></div>
                </div>
                <a href="<?= SITE_URL ?>/auth/logout.php" class="btn btn-logout">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <div class="row">
                <!-- Sidebar Navigation -->
                <div class="col-lg-3">
                    <div class="sidebar">
                        <div class="sidebar-header">
                            <h3 class="sidebar-title"><i class="fas fa-th-large me-2"></i>Navigation</h3>
                        </div>
                        <ul class="nav nav-sidebar flex-column">
                            <!-- Common Navigation for All Roles -->
                            <li class="nav-item">
                                <a class="nav-link active" href="<?= SITE_URL ?>/<?= $base_path ?>dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i>Dashboard
                                </a>
                            </li>
                            
                            <?php if (isPatient()): ?>
                            <!-- Patient Specific Navigation -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/patient/my_appointments.php">
                                    <i class="fas fa-calendar-check"></i>My Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/patient/book_appointment.php">
                                    <i class="fas fa-calendar-plus"></i>Book Appointment
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/patient/find_doctors.php">
                                    <i class="fas fa-user-md"></i>Find Doctors
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/coming_soon.php?feature=medical-records">
                                    <i class="fas fa-file-medical"></i>Medical Records
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/coming_soon.php?feature=prescriptions">
                                    <i class="fas fa-prescription"></i>Prescriptions
                                </a>
                            </li>
                            
                            <?php elseif (isDoctor()): ?>
                            <!-- Doctor Specific Navigation -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/doctor/my_schedule.php">
                                    <i class="fas fa-calendar-check"></i>My Schedule
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/doctor/my_patients.php">
                                    <i class="fas fa-users"></i>My Patients
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/doctor/schedule.php">
                                    <i class="fas fa-clock"></i>Manage Schedule
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/coming_soon.php?feature=doctor-reports">
                                    <i class="fas fa-chart-bar"></i>Reports
                                </a>
                            </li>
                            
                            <?php elseif (isAdmin()): ?>
                            <!-- Admin Specific Navigation -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/admin/users.php">
                                    <i class="fas fa-users-cog"></i>Manage Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/admin/appointments.php">
                                    <i class="fas fa-calendar-alt"></i>All Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/admin/doctors.php">
                                    <i class="fas fa-user-md"></i>Manage Doctors
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/coming_soon.php?feature=admin-reports">
                                    <i class="fas fa-chart-pie"></i>Analytics
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <!-- Common Navigation -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/<?= $base_path ?>profile.php">
                                    <i class="fas fa-user-cog"></i>Profile Settings
                                </a>
                            </li>
                            
                            <?php if (isAdmin()): ?>
                            <li class="nav-item mt-3">
                                <a class="nav-link" href="<?= SITE_URL ?>/admin/dashboard.php" style="color: var(--accent);">
                                    <i class="fas fa-cogs"></i>Admin Panel
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="col-lg-9">
                    <!-- <div class="content-header">
                        <h1 class="content-title">Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]) ?>! 👋</h1>
                        <p class="content-subtitle">Here's what's happening with your healthcare today.</p>
                    </div> -->