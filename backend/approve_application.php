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

// Insert into users table as a trainer
$username = strtolower(str_replace(' ', '', $row['name']));
$email = $row['email'];
$password = bin2hex(random_bytes(4)); // Generate a random password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$insert_sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', 'trainer')";

if ($conn->query($insert_sql) === TRUE) {
    // Send email to trainer
    $to = $email;
    $subject = "Your Trainer Application Has Been Approved";
    $message = "Dear {$row['name']},\n\nYour application to become a trainer has been approved. Your login credentials are:\n\nUsername: $username\nPassword: $password\n\nPlease login and change your password.\n\nBest regards,\nPawology Team";
    $headers = "From: pawologycare@gmail.com";

    mail($to, $subject, $message, $headers);

    // Delete the application from the applications table
    $delete_sql = "DELETE FROM dog_trainer_applications WHERE id = $applicationId";
    $conn->query($delete_sql);

    echo "Application approved and credentials sent to the trainer.";
} else {
    echo "Error: " . $insert_sql . "<br>" . $conn->error;
}

$conn->close();
?>