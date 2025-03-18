<?php
// Include the database connection file
include 'db.php';

// Set the content type to JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if an 'id' is passed in the query
    if (isset($_GET['id'])) {
        $method_id = intval($_GET['id']); // Ensure it's an integer

        $sql = "SELECT method_id, method_name FROM training_methods WHERE method_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $method_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $training_method = $result->fetch_assoc();

            echo json_encode(array(
                "message" => "Training method retrieved successfully.",
                "data" => $training_method
            ));
        } else {
            echo json_encode(array(
                "message" => "No training method found for the given ID.",
                "data" => array()
            ));
        }

        $stmt->close();
    } else {
        // Fetch all methods if no specific ID is provided
        $sql = "SELECT method_id, method_name FROM training_methods";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $training_methods = array();

            while ($row = $result->fetch_assoc()) {
                $training_methods[] = $row;
            }

            echo json_encode(array(
                "message" => "Training methods retrieved successfully.",
                "data" => $training_methods
            ));
        } else {
            echo json_encode(array(
                "message" => "No training methods found.",
                "data" => array()
            ));
        }
    }
}
?>
