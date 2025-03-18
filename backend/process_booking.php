<?php
include 'db.php'; 
$conn = new mysqli("localhost", "root", "", "dogweb");

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => "Database Connection Failed: " . $conn->connect_error]));
}

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Extract data
$state = $data['state'];
$city = $data['city'];
$phone_number = $data['phone'];
$address = $data['address'];
$date = $data['date'];
$package_name = $data['package_name'];  // Expected values: p1, p2, p3
$package_price = $data['package_price'];

// Replace this with the actual user ID from your session or authentication system
$user_id = $_SESSION['user_id'] ?? 1; // Example: Get user_id from session

// Debugging: Log received data
error_log("User ID: " . $user_id);
error_log("State: " . $state);
error_log("City: " . $city);
error_log("Phone: " . $phone_number);
error_log("Address: " . $address);
error_log("Date: " . $date);
error_log("Package Name: " . $package_name);
error_log("Package Price: " . $package_price);

if (!$state || !$city || !$phone_number || !$address || !$date || !$package_name || !$package_price) {
    die(json_encode(['success' => false, 'error' => 'Missing required fields']));
}

// Prepare and execute SQL query
$sql = "INSERT INTO bookings (user_id, state, city, address, phone_number, date, package_name, package_price, status, trainer_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NULL, CURRENT_TIMESTAMP)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issssssd", $user_id, $state, $city, $address, $phone_number, $date, $package_name, $package_price);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);  // Return SQL error
}

$stmt->close();
$conn->close();
?>