<?php
include 'db.php'; 
$conn = new mysqli("localhost", "root", "", "dogweb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$booking_id = $_POST['id'];  // Match with `booking_id`
$status = $_POST['status'];  

// Prepare and execute SQL query to prevent SQL injection
$sql = "UPDATE bookings SET status = ? WHERE booking_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $booking_id);

if ($stmt->execute()) {
    echo "Booking status updated successfully!";
} else {
    echo "Error updating status: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>