<?php
// Include DB connection
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate input
    $user_id = $_POST["user_id"] ?? null;
    $breed_id = $_POST["breed_id"] ?? null;
    $method_id = $_POST["method_id"] ?? null;
    $training_day = $_POST["training_day"] ?? null;
    $question_1 = $_POST["question_1"] ?? "";
    $question_2 = $_POST["question_2"] ?? "";
    $question_3 = $_POST["question_3"] ?? "";
    $rating = $_POST["rating"] ?? null;

    // Check if all required fields are present
    if (!$user_id || !$breed_id || !$method_id || !$training_day || !$rating) {
        echo "All required fields (user_id, breed_id, method_id, training_day, rating) must be filled.";
        exit;
    }

    // Prepare and execute query
    $sql = "INSERT INTO training_reviews (user_id, breed_id, method_id, training_day, question_1, question_2, question_3, rating) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("iiiiissi", $user_id, $breed_id, $method_id, $training_day, $question_1, $question_2, $question_3, $rating);

    if ($stmt->execute()) {
        echo "Review successfully recorded.";
    } else {
        echo "Error inserting data: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
