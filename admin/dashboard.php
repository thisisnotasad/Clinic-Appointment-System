<?php require_once '../includes/auth.php'; ?>
<?php require_once '../includes/header.php'; ?>

<div class="container">
    <h1 class="mb-4">Admin Dashboard</h1>
    <div class="alert alert-success">
        Welcome, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>! You are logged in as <strong>Administrator</strong>.
    </div>
</div>

<?php require_once '../includes/auth.php'; ?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/db_connect.php'; ?>
<?php if (!isAdmin()) header("Location: ../dashboard.php"); ?>

<h2>Admin Panel</h2>
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5><i class="fas fa-users"></i> Total Patients</h5>
                <?php
                $res = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='patient'");
                echo "<h3>" . $res->fetch_assoc()['c'] . "</h3>";
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5><i class="fas fa-user-md"></i> Total Doctors</h5>
                <?php
                $res = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='doctor'");
                echo "<h3>" . $res->fetch_assoc()['c'] . "</h3>";
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5><i class="fas fa-calendar-check"></i> Today's Appointments</h5>
                <?php
                $today = date('Y-m-d');
                $res = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE appointment_date='$today'");
                echo "<h3>" . $res->fetch_assoc()['c'] . "</h3>";
                ?>
            </div>
        </div>
    </div>
</div>

<h3 class="mt-5">All Appointments</h3>
<!-- You can expand this later with filters -->
<table class="table table-striped">
    <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Doctor</th><th>Status</th></tr></thead>
    <tbody>
        <?php
        $res = $conn->query("
            SELECT a.*, p.full_name as patient, d.full_name as doctor 
            FROM appointments a
            JOIN users p ON a.patient_id = p.user_id
            JOIN doctors doc ON a.doctor_id = doc.doctor_id
            JOIN users d ON doc.user_id = d.user_id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
            LIMIT 50
        ");
        while ($r = $res->fetch_assoc()): ?>
        <tr>
            <td><?= date('d M Y', strtotime($r['appointment_date'])) ?></td>
            <td><?= date('h:i A', strtotime($r['appointment_time'])) ?></td>
            <td><?= $r['patient'] ?></td>
            <td>Dr. <?= $r['doctor'] ?></td>
            <td><span class="badge bg-info"><?= ucfirst($r['status']) ?></span></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>
