<?php
include 'db.php'; // Ensure database connection

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch all trainer applications with required fields
$sql = "SELECT trainer_id, name, email, phone, city, state, qualification, experience_rating, message, certification_path, status FROM dog_trainer_applications"; 
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["success" => false, "error" => "Database query failed: " . $conn->error]);
    exit;
}

$applications = [];

while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}

// Return response
echo json_encode(["success" => true, "applications" => $applications]);

// Close connection
$conn->close();
?>
