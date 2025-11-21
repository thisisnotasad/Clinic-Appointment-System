<?php require_once '../includes/auth.php'; ?>
<?php require_once '../includes/header.php'; ?>

<div class="container">
    <h1 class="mb-4">Patient Dashboard</h1>
    <div class="alert alert-primary">
        Welcome, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>! You are logged in as <strong>Patient</strong>.
    </div>
</div>

<div class="row mt-4 g-3">
    <div class="col-md-6">
        <a href="book_appointment.php" class="btn btn-primary btn-lg w-100 p-4">
            Book New Appointment
        </a>
    </div>
    <div class="col-md-6">
        <a href="my_appointments.php" class="btn btn-info btn-lg w-100 p-4 text-white">
            View My Appointments
        </a>
    </div>
    <div class="col-md-12 mt-3">
        <a href="profile.php" class="btn btn-outline-secondary btn-lg w-100 p-4">
            Edit Profile
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>