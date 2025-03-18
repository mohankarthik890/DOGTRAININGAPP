<?php
include 'db.php';
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dogweb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get breed_id and method_id from the request
$breed_id = $_GET['breed_id'];
$method_id = $_GET['method_id'];

// Fetch training materials
$sql = "SELECT * FROM training_materials WHERE group_id = (SELECT group_id FROM breeds WHERE breed_id = $breed_id) AND method_id = $method_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = array();
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(array("message" => "Training materials retrieved successfully.", "data" => $data));
} else {
    echo json_encode(array("message" => "No training materials found."));
}

$conn->close();
?>