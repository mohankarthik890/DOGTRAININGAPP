<?php
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
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Get breed_name and method_name from the request
$breed_name = isset($_GET['breed_name']) ? trim($_GET['breed_name']) : null;
$method_name = isset($_GET['method_name']) ? trim($_GET['method_name']) : null;

if (!$breed_name || !$method_name) {
    echo json_encode(["error" => "Missing breed_name or method_name"]);
    exit;
}

// Fetch breed_id using prepared statements
$sql = "SELECT breed_id FROM breeds WHERE breed_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $breed_name);
$stmt->execute();
$result = $stmt->get_result();
$breed = $result->fetch_assoc();
$breed_id = $breed ? $breed['breed_id'] : null;
$stmt->close();

// Debugging breed_id
if (!$breed_id) {
    echo json_encode(["error" => "Breed not found", "query" => $sql, "input" => $breed_name]);
    exit;
}

// Fetch method_id using prepared statements
$sql = "SELECT method_id FROM training_methods WHERE method_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $method_name);
$stmt->execute();
$result = $stmt->get_result();
$method = $result->fetch_assoc();
$method_id = $method ? $method['method_id'] : null;
$stmt->close();

// Debugging method_id
if (!$method_id) {
    echo json_encode(["error" => "Method not found", "query" => $sql, "input" => $method_name]);
    exit;
}

// Success response
echo json_encode(["success" => true, "breed_id" => $breed_id, "method_id" => $method_id]);

$conn->close();
?>
