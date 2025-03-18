<?php
// Database configuration
include('db.php');  // Include the database connection file

// Initialize response array
$response = array();

// Check if the form data has been sent via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $breedName = isset($_POST['breed_name']) ? $_POST['breed_name'] : '';
    $trainingMethod = isset($_POST['training_method']) ? $_POST['training_method'] : '';

    if ($breedName && $trainingMethod) {
        // Fetch group_id based on breed_name
        $sql = "SELECT group_id FROM breeds WHERE breed_name = '$breedName'";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $groupId = $row['group_id'];

            if (!$groupId) {
                header('Location: result.html?error=Breed not found');
                exit();
            }

            // Fetch method_id based on training_method
            $sql = "SELECT method_id FROM training_methods WHERE method_name = '$trainingMethod'";
            $result = mysqli_query($conn, $sql);

            if ($result) {
                $row = mysqli_fetch_assoc($result);
                $methodId = $row['method_id'];

                if (!$methodId) {
                    header('Location: result.html?error=Training method not found');
                    exit();
                }

                // Fetch the training materials
                $sql = "SELECT command, day_number, video_url FROM training_materials WHERE group_id = $groupId AND method_id = $methodId";
                $result = mysqli_query($conn, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    $trainingMaterials = array();

                    while ($row = mysqli_fetch_assoc($result)) {
                        $trainingMaterials[] = $row;
                    }

                    // Redirect with the data
                    $trainingDetails = urlencode(json_encode($trainingMaterials)); // Convert array to URL-safe format
                    header('Location: result.html?training_details=' . $trainingDetails);
                    exit();
                } else {
                    header('Location: result.html?error=No training materials found');
                    exit();
                }
            } else {
                header('Location: result.html?error=Training method not found');
                exit();
            }
        } else {
            header('Location: result.html?error=Breed not found');
            exit();
        }
    } else {
        header('Location: result.html?error=Breed name and training method are required');
        exit();
    }
}
?>
