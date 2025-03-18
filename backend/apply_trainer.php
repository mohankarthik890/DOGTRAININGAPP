<?php
include 'db.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if keys exist before accessing them
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $state = isset($_POST['state']) ? trim($_POST['state']) : '';
    $qualification = isset($_POST['qualification']) ? trim($_POST['qualification']) : '';
    $experience_rating = isset($_POST['experience_rating']) ? trim($_POST['experience_rating']) : null;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Validate experience_rating (must be numeric and between 0-10)
    if ($experience_rating !== null && (!is_numeric($experience_rating) || $experience_rating < 0 || $experience_rating > 10)) {
        die("Error: Invalid experience rating. Must be a number between 0 and 10.");
    }

    // Handle file upload
    $certification_Path = NULL;
    if (!empty($_FILES['certification_path']['name'])) {
        $targetDir = "certi/"; // Directory for uploaded files

        // Ensure the uploads directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Generate a safe file name (replace spaces)
        $fileName = str_replace(" ", "_", basename($_FILES["certification_path"]["name"]));
        $certification_Path = $targetDir . $fileName;

        // Validate file type (only allow PDF, JPG, PNG)
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($_FILES["certification_path"]["type"], $allowedTypes)) {
            die("Error: Only PDF, JPG, and PNG files are allowed.");
        }

        // Move the uploaded file to the destination folder
        if (!move_uploaded_file($_FILES["certification_path"]["tmp_name"], $certification_Path)) {
            die("Error: File upload failed.");
        }
    }

    // Use Prepared Statements to Prevent SQL Injection
    $sql = "INSERT INTO dog_trainer_applications 
            (name, email, phone, city, state, qualification, experience_rating, message, certification_path, application_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("SQL Prepare Error: " . mysqli_error($conn)); // Debugging output
    }

    // Bind parameters correctly
    mysqli_stmt_bind_param($stmt, "ssssissss", 
        $name, 
        $email, 
        $phone, 
        $city, 
        $state, 
        $qualification, 
        $experience_rating, 
        $message, 
        $certification_Path
    );

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        echo "Application submitted successfully!";
    } else {
        echo "Execution Error: " . mysqli_stmt_error($stmt);
    }

    // Close statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
