<?php
// Include DB connection
include 'db.php'; 

// Capture query parameters from the URL
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$breed_id = isset($_GET['breed_id']) ? $_GET['breed_id'] : null;
$method_id = isset($_GET['method_id']) ? $_GET['method_id'] : null;

// Build the base SQL query
$sql = "SELECT * FROM training_reviews WHERE 1=1";

// Add filters based on the provided query parameters
if ($user_id) {
    $sql .= " AND user_id = ?";
}
if ($breed_id) {
    $sql .= " AND breed_id = ?";
}
if ($method_id) {
    $sql .= " AND method_id = ?";
}

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing SQL query.");
}

// Bind dynamic parameters
$params = [];
if ($user_id) $params[] = $user_id;
if ($breed_id) $params[] = $breed_id;
if ($method_id) $params[] = $method_id;

if (!empty($params)) {
    $stmt->bind_param(str_repeat('i', count($params)), ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch and display results
if ($result->num_rows > 0) {
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
    echo json_encode($reviews);
} else {
    echo json_encode(["message" => "No records found."]);
}

$stmt->close();
$conn->close();
?>
