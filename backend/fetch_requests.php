<?php
include 'db.php'; 
$conn = new mysqli("localhost", "root", "", "dogweb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all bookings from the `bookings` table
$sql = "SELECT * FROM bookings";
$result = $conn->query($sql);

$bookings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    echo json_encode($bookings); // Return bookings as JSON
} else {
    echo json_encode(["error" => "No bookings found"]); // Return error if no bookings
}

$conn->close();
?>