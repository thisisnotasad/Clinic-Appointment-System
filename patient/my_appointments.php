<?php require_once '../includes/auth.php'; ?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/db_connect.php'; ?>
<?php if (!isPatient()) header("Location: ../dashboard.php"); ?>

<h2 class="mb-4"><i class="fas fa-list-alt"></i> My Appointments</h2>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-primary">
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Doctor</th>
                <th>Specialization</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $conn->prepare("
                SELECT a.*, u.full_name, s.name as spec_name
                FROM appointments a
                JOIN doctors d ON a.doctor_id = d.doctor_id
                JOIN users u ON d.user_id = u.user_id
                JOIN specializations s ON d.specialization_id = s.specialization_id
                WHERE a.patient_id = ?
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
            ");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= date('d M Y', strtotime($row['appointment_date'])) ?></td>
                <td><?= date('h:i A', strtotime($row['appointment_time'])) ?></td>
                <td>Dr. <?= $row['full_name'] ?></td>
                <td><?= $row['spec_name'] ?></td>
                <td><span class="badge bg-<?= $row['status']=='pending'?'warning':($row['status']=='completed'?'success':'danger') ?>">
                    <?= ucfirst($row['status']) ?>
                </span></td>
                <td>
                    <?php if ($row['status'] == 'pending'): ?>
                        <a href="cancel_appointment.php?id=<?= $row['appointment_id'] ?>" 
                           class="btn btn-sm btn-danger" onclick="return confirm('Cancel this appointment?')">
                            Cancel
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>