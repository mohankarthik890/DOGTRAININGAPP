<?php
include 'db.php'; 
$conn = new mysqli("localhost", "root", "", "dogweb");

if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Database connection failed: " . $conn->connect_error]));
}

// Validate input
$booking_id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;
$trainer_id = $_POST['trainer_id'] ?? null;

if (!is_numeric($booking_id)) { 
    die(json_encode(["success" => false, "error" => "Invalid booking ID."])); 
}

if (!in_array($status, ['accepted', 'rejected'])) {
    die(json_encode(["success" => false, "error" => "Invalid status."]));
}
if (!is_numeric($trainer_id)) {
    die(json_encode(["success" => false, "error" => "Invalid trainer ID."]));
}

// Prepare and execute SQL query
$sql = "UPDATE bookings SET status = ?, trainer_id = ? WHERE booking_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(["success" => false, "error" => "Failed to prepare statement: " . $conn->error]));
}
$stmt->bind_param("sii", $status, $trainer_id, $booking_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Booking status updated successfully!"]);
} else {
    error_log("Error updating booking: " . $stmt->error);
    echo json_encode(["success" => false, "error" => "Error updating status: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>