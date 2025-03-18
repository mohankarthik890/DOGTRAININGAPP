<?php
include 'db.php'; // Ensure correct database connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure PHPMailer is installed

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
    exit;
}

// Validate input parameters
if (!isset($_POST['trainer_id']) || !isset($_POST['status'])) {
    echo json_encode(["success" => false, "error" => "Missing parameters"]);
    exit;
}

$trainer_id = filter_var($_POST['trainer_id'], FILTER_SANITIZE_NUMBER_INT);
$status = $_POST['status'];

$valid_status = ["Pending", "Approved", "Rejected"];
if (!in_array($status, $valid_status)) {
    echo json_encode(["success" => false, "error" => "Invalid status value"]);
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "dogweb");
if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

// Fetch trainer details
$stmt = $conn->prepare("SELECT name, email FROM dog_trainer_applications WHERE trainer_id = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(["success" => false, "error" => "Database error"]);
    exit;
}

$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();

if (empty($name) || empty($email)) {
    echo json_encode(["success" => false, "error" => "Trainer not found"]);
    exit;
}

// Update status
$stmt = $conn->prepare("UPDATE dog_trainer_applications SET status = ? WHERE trainer_id = ?");
if (!$stmt) {
    error_log("Prepare failed (Update): " . $conn->error);
    echo json_encode(["success" => false, "error" => "Database error: " . $conn->error]);
    exit;
}


$stmt->bind_param("si", $status, $trainer_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    if ($status === "Approved") {
        // Check if trainer already exists in users table
        $check_stmt = $conn->prepare("SELECT trainer_id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows == 0) {
            // Generate a secure password
            $password = generateRandomPassword();
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert into users table
            $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'trainer')");
            if ($insert_stmt) {
                $insert_stmt->bind_param("sss", $name, $email, $hashedPassword);
                if ($insert_stmt->execute()) {
                    // Send approval email with credentials
                    $subject = "Congratulations! You are selected as a Trainer";
                    $message = "Dear $name,\n\nCongratulations! You have been selected as a trainer at Pawology Care.\n\nYour login credentials:\nUsername: $email\nPassword: $password\n\nPlease log in and reset your password.\n\nBest regards,\nPawology Care Team";
                    sendEmail($email, $subject, $message);
                }
                $insert_stmt->close();
            }
        }
        $check_stmt->close();
    } elseif ($status === "Rejected") {
        // Send rejection email
        $subject = "Application Update: Rejected";
        $message = "Dear $name,\n\nThank you for applying to Pawology Care. Unfortunately, we are unable to proceed with your application at this time.\n\nWe appreciate your time and effort and wish you success in your future endeavors.\n\nBest regards,\nPawology Care Team";
        sendEmail($email, $subject, $message);
    }

    echo json_encode(["success" => true, "message" => "Status updated successfully"]);
} else {
    echo json_encode(["success" => false, "error" => "No changes detected"]);
}

$stmt->close();
$conn->close();

// Function to generate a secure random password
function generateRandomPassword($length = 12) {
    return bin2hex(random_bytes($length / 2)); // Generates a secure password
}

// Function to send email using PHPMailer
// Function to send email using PHPMailer
function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'pawologycare@gmail.com'; // Use your email
        $mail->Password = 'kngs mxma dgsf czdw'; // Use App Password, NOT your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Enable debugging
        $mail->SMTPDebug = 2; // 0 = Off, 2 = Debug mode
        $mail->Debugoutput = 'error_log'; // Log output

        $mail->setFrom('pawologycare@gmail.com', 'Pawology Care Admin');
        $mail->addAddress($to);
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        error_log("Email sent successfully to $to");
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
    }
}

?>
