<?php require_once '../includes/auth.php'; ?>
<?php require_once '../includes/header.php'; ?>

<div class="container">
    <h1 class="mb-4">Patient Dashboard</h1>
    <div class="alert alert-primary">
        Welcome, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>! You are logged in as <strong>Patient</strong>.
    </div>
</div>
<a href="book_appointment.php" class="btn btn-primary btn-lg px-5">
    <i class="fas fa-calendar-plus"></i> Book New Appointment
</a>
<a href="my_appointments.php" class="btn btn-info btn-lg px-5 ms-3">
    <i class="fas fa-list"></i> My Appointments
</a>

<?php require_once '../includes/footer.php'; ?>