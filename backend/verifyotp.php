<?php
// Include database connection
include 'db.php'; 

// Set response header
header('Content-Type: application/json');

// Retrieve email and OTP from the request body
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'];
$otp = $data['otp'];

// Validate input
if (empty($email) || empty($otp)) {
    echo json_encode(['success' => false, 'message' => 'Email and OTP are required.']);
    exit;
}

// Check OTP and its expiration
$query = "SELECT otp, expires_at FROM otp_verification WHERE email = ? AND otp = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param('si', $email, $otp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Check if OTP has expired
    if (time() > $row['expires_at']) {
        echo json_encode(['success' => false, 'message' => 'OTP has expired.']);
    } else {
        // OTP is valid, redirect to password reset page
        echo json_encode(['success' => true, 'message' => 'OTP verification successful.', 'redirect' => 'confpass.html']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP or email.']);
}

// Close connection
$stmt->close();
$conn->close();
?>
