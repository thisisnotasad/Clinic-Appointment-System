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

// If there's no logged-in user, redirect to login before any HTML is sent
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
            border-bottom: 1px solid rgba(74, 108, 247, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.6rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: var(--transition);
        }

        .navbar-brand:hover {
            transform: translateY(-2px);
        }

        /* --- NAVBAR TOGGLER STYLES (New) --- */
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            border-radius: 8px;
            transition: var(--transition);
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.2);
            /* Soft Blue Glow */
            outline: none;
            /* Removes the ugly black/default border */
        }

        .navbar-toggler:hover {
            background: rgba(74, 108, 247, 0.05);
            /* Subtle background on hover */
        }

        /* ----------------------------------- */

        /* --- UPDATED USER WELCOME STYLES --- */
        .user-welcome {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            /* Align text to the right on desktop */
            justify-content: center;
            line-height: 1.2;
            padding: 0.5rem 1rem;
            border-right: 1px solid rgba(0, 0, 0, 0.1);
            /* Subtle divider */
            margin-right: 1rem;
        }

        .user-welcome .fw-bold {
            color: var(--dark);
            font-size: 0.95rem;
        }

        .user-welcome .user-role {
            font-size: 0.75rem;
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(74, 108, 247, 0.1);
            padding: 2px 8px;
            border-radius: 10px;
            margin-top: 3px;
            display: inline-block;
        }

        .btn-logout {
            background: white;
            border: 2px solid rgba(74, 108, 247, 0.2);
            border-radius: 12px;
            padding: 8px 20px;
            font-weight: 600;
            color: var(--primary);
            transition: var(--transition);
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .btn-logout:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 108, 247, 0.2);
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
            top: 6rem;
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

        /* Mobile Nav Link Styles (inside collapse) */
        .mobile-nav-link {
            color: var(--dark);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .mobile-nav-link:hover {
            background: rgba(74, 108, 247, 0.05);
            color: var(--primary);
            transform: translateX(5px);
        }

        .mobile-nav-link.active {
            background: rgba(74, 108, 247, 0.1);
            color: var(--primary);
            font-weight: 600;
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


        /* --- RESPONSIVE ADJUSTMENTS --- */
        @media (max-width: 991px) {
            .navbar-nav {
                margin-top: 1rem;
                flex-direction: column;
                align-items: flex-start !important;
                /* Stack items left on mobile */
                gap: 0.5rem;
                width: 100%;
            }

            .user-welcome {
                align-items: flex-start;
                /* Align text left on mobile */
                border-right: none;
                /* Remove divider on mobile */
                border-left: 3px solid var(--primary);
                /* Add accent on left */
                padding-left: 1rem;
                margin-right: 0;
                margin-bottom: 1rem;
                margin-top: 1rem;
                width: 100%;
                background: rgba(248, 249, 255, 0.5);
                border-radius: 0 10px 10px 0;
            }

            .btn-logout {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="<?= SITE_URL ?>/<?= $base_path ?>dashboard.php">
                <i class="fas fa-heartbeat me-2"></i><?= SITE_NAME ?>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <div class="navbar-nav w-100 d-lg-none mb-3">
                    <div class="border-bottom pb-2 mb-2">
                        <span class="text-uppercase text-muted fw-bold small ps-2">Menu</span>
                    </div>

                    <a class="nav-link mobile-nav-link active" href="<?= SITE_URL ?>/<?= $base_path ?>dashboard.php">
                        <i class="fas fa-tachometer-alt me-3" style="width:20px"></i>Dashboard
                    </a>

                    <?php if (isPatient()): ?>
                        <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/patient/my_appointments.php">
                            <i class="fas fa-calendar-check me-3" style="width:20px"></i>My Appointments
                        </a>
                        <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/patient/book_appointment.php">
                            <i class="fas fa-calendar-plus me-3" style="width:20px"></i>Book Appointment
                        </a>
                        <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/patient/find_doctors.php">
                            <i class="fas fa-user-md me-3" style="width:20px"></i>Find Doctors
                        </a>
                        <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/coming_soon.php?feature=medical-records">
                            <i class="fas fa-file-medical me-3" style="width:20px"></i>Medical Records
                        </a>
                        <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/coming_soon.php?feature=prescriptions">
                            <i class="fas fa-prescription me-3" style="width:20px"></i>Prescriptions
                        </a>

                    <?php elseif (isDoctor()): ?>
                        <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/doctor/my_appointments.php">
                            <i class="fas fa-calendar-check me-3" style="width:20px"></i>My Appointments
                        </a>
                        <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/doctor/my_schedule.php">
                            <i class="fas fa-calendar-check me-3" style="width:20px"></i>My Schedule
                        </a>
                        <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/doctor/my_patients.php">
                            <i class="fas fa-users me-3" style="width:20px"></i>My Patients
                        </a>
                        <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/doctor/schedule.php">
                            <i class="fas fa-clock me-3" style="width:20px"></i>Manage Schedule
                        </a>
                        <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/coming_soon.php?feature=doctor-reports">
                            <i class="fas fa-chart-bar me-3" style="width:20px"></i>Reports
                        </a>

                    <?php elseif (isAdmin()): ?>
                        <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/admin/users.php">
                            <i class="fas fa-users-cog me-3" style="width:20px"></i>Manage Users
                        </a>
                        <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/admin/appointments.php">
                            <i class="fas fa-calendar-alt me-3" style="width:20px"></i>All Appointments
                        </a>
                        <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/admin/doctors.php">
                            <i class="fas fa-user-md me-3" style="width:20px"></i>Manage Doctors
                        </a>
                        <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/coming_soon.php?feature=admin-reports">
                            <i class="fas fa-chart-pie me-3" style="width:20px"></i>Analytics
                        </a>
                    <?php endif; ?>

                    <a class="nav-link mobile-nav-link" href="<?= SITE_URL ?>/<?= $base_path ?>profile.php">
                        <i class="fas fa-user-cog me-3" style="width:20px"></i>Profile Settings
                    </a>

                    <?php if (isAdmin()): ?>
                        <a class="nav-link mobile-nav-link text-primary" href="<?= SITE_URL ?>/admin/dashboard.php">
                            <i class="fas fa-cogs me-3" style="width:20px"></i>Admin Panel
                        </a>
                    <?php endif; ?>
                </div>

                <div class="navbar-nav ms-lg-auto align-items-center">

                    <div class="user-welcome">
                        <div class="fw-bold"><?= isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : '' ?></div>
                        <div class="user-role"><?= isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : '' ?></div>
                    </div>

                    <a href="<?= SITE_URL ?>/auth/logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>

                </div>
            </div>
        </div>
    </nav>


    <div class="main-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 d-none d-lg-block">
                    <div class="sidebar">
                        <div class="sidebar-header">
                            <h3 class="sidebar-title"><i class="fas fa-th-large me-2"></i>Navigation</h3>
                        </div>
                        <ul class="nav nav-sidebar flex-column">
                            <li class="nav-item">
                                <a class="nav-link active" href="<?= SITE_URL ?>/<?= $base_path ?>dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i>Dashboard
                                </a>
                            </li>

                            <?php if (isPatient()): ?>
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
                                    <li class="nav-item">
                                        <a class="nav-link" href="<?= SITE_URL ?>/doctor/my_appointments.php">
                                            <i class="fas fa-calendar-check"></i>My Appointments
                                        </a>
                                    </li>
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

                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/<?= $base_path ?>profile.php">
                                    <i class="fas fa-user-cog"></i>Profile Settings
                                </a>
                            </li>

                            <?php if (isAdmin()): ?>
                                <li class="nav-item mt-3">
                                    <a class="nav-link" href="<?= SITE_URL ?>/admin/dashboard.php"
                                        style="color: var(--accent);">
                                        <i class="fas fa-cogs"></i>Admin Panel
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-9">