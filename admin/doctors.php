<?php require_once '../includes/auth.php'; ?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/db_connect.php'; ?>
<?php if (!isAdmin()) header("Location: ../dashboard.php"); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-user-md"></i> Manage Doctors</h2>
        <a href="add_doctor.php" class="btn btn-success btn-lg">
            <i class="fas fa-plus"></i> Add New Doctor
        </a>
    </div>

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Specialization</th>
                            <th>Experience</th>
                            <th>Fee</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "
                            SELECT u.user_id, u.full_name, u.email, u.phone, 
                                   s.name as spec_name, d.qualification, d.experience_years, 
                                   d.consultation_fee, d.doctor_id
                            FROM users u
                            JOIN doctors d ON u.user_id = d.user_id
                            JOIN specializations s ON d.specialization_id = s.specialization_id
                            WHERE u.role = 'doctor'
                            ORDER BY u.full_name
                        ";
                        $result = $conn->query($sql);
                        $i = 1;
                        while ($doc = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><strong><?= htmlspecialchars($doc['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($doc['email']) ?></td>
                            <td><span class="badge bg-info"><?= $doc['spec_name'] ?></span></td>
                            <td><?= $doc['experience_years'] ?> years</td>
                            <td>₹<?= number_format($doc['consultation_fee'], 2) ?></td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <a href="view_doctor.php?id=<?= $doc['doctor_id'] ?>" 
                                   class="btn btn-sm btn-primary" title="View Details">
                                    View
                                </a>
                                <a href="edit_doctor.php?id=<?= $doc['doctor_id'] ?>" 
                                   class="btn btn-sm btn-warning" title="Edit">
                                    Edit
                                </a>
                                <a href="delete_doctor.php?id=<?= $doc['user_id'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Delete this doctor permanently?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>