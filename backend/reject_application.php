<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pawology";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$applicationId = $_GET['id'];

// Fetch application data
$sql = "SELECT * FROM dog_trainer_applications WHERE id = $applicationId";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

// Send rejection email
$to = $row['email'];
$subject = "Your Trainer Application Has Been Rejected";
$message = "Dear {$row['name']},\n\nWe regret to inform you that your application to become a trainer has been rejected.\n\nBest regards,\nPawology Team";
$headers = "From: pawologycare@gmail.com";

mail($to, $subject, $message, $headers);

// Delete the application from the applications table
$delete_sql = "DELETE FROM dog_trainer_applications WHERE id = $applicationId";
$conn->query($delete_sql);

echo "Application rejected and notification sent to the trainer.";

$conn->close();
?>