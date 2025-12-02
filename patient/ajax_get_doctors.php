<?php
require_once '../includes/db_connect.php';

// Check if specialization_id is provided
if (isset($_GET['specialization_id'])) {
    $spec_id = intval($_GET['specialization_id']); // Sanitize input

    // Join doctors table with users table to get the name, and select consultation_fee
    $sql = "SELECT d.doctor_id, d.qualification, d.consultation_fee, u.full_name 
            FROM doctors d 
            JOIN users u ON d.user_id = u.user_id 
            WHERE d.specialization_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $spec_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }

    // Return data as JSON
    header('Content-Type: application/json');
    echo json_encode($doctors);
}
?>