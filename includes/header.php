<?php
require_once __DIR__ . '/auth.php';  // This checks login + starts session
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= SITE_NAME ?> - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= SITE_URL ?>/dashboard.php">MediCare Clinic</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    Hello, <?= htmlspecialchars($_SESSION['full_name']) ?> (<?= ucfirst($_SESSION['role']) ?>)
                </span>
                <a href="<?= SITE_URL ?>/auth/logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">