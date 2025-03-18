<?php
// Include necessary files and libraries
include 'db.php'; // Include your database connection file
require 'vendor/autoload.php'; // Include PHPMailer autoload file (if using Composer)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set response header
header('Content-Type: application/json');

// Retrieve email from POST request body
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'];

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
    exit;
}

// Generate a random 4-digit OTP
$otp = rand(1000, 9999); 
$expiresAt = time() + 90; // OTP expires in 90 seconds

// Save OTP and expiration time to database
$stmt = $conn->prepare('INSERT INTO otp_verification (email, otp, expires_at) VALUES (?, ?, ?)');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database statement preparation failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param('ssi', $email, $otp, $expiresAt);

if ($stmt->execute()) {
    // Initialize PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'pawologycare@gmail.com'; // Replace with your Gmail address
        $mail->Password = 'kngs mxma dgsf czdw'; // Replace with Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPDebug = 2; // Enable debug output
        $mail->Debugoutput = function ($str, $level) {
            error_log("SMTP Debug: $str");
        };

        // Recipients
        $mail->setFrom('pawologycare@gmail.com', 'Dog Training App');
        $mail->addAddress($email);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Verification';
        $mail->Body = 'Your OTP is: <b>' . $otp . '</b>';

        // Send Email
        $mail->send();
        echo json_encode(['success' => true, 'message' => 'OTP sent successfully.']);
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Error: ' . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save OTP to database: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
