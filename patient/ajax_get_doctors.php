<?php
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$spec_id = $_GET['specialization_id'] ?? 0;

$stmt = $conn->prepare("
    SELECT d.doctor_id, u.full_name, d.qualification 
    FROM doctors d 
    JOIN users u ON d.user_id = u.user_id 
    WHERE d.specialization_id = ?
");
$stmt->bind_param("i", $spec_id);
$stmt->execute();
$result = $stmt->get_result();

$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

echo json_encode($doctors);
?>