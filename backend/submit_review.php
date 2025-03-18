<?php
header("Content-Type: application/json");

$servername = "localhost"; // Change if necessary
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "dogweb"; // Change to your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

// Get the POST data
$data = json_decode(file_get_contents("php://input"), true);

$method_id = $conn->real_escape_string($data['methodId']);
$question1 = $conn->real_escape_string($data['question1']);
$question2 = $conn->real_escape_string($data['question2']);
$question3 = $conn->real_escape_string($data['question3']);
$rating = intval($data['rating']);

// Insert into database
$sql = "INSERT INTO reviews (method_id, question1, question2, question3, rating) 
        VALUES ('$method_id', '$question1', '$question2', '$question3', '$rating')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
}

$conn->close();
?>
