<?php
include 'db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$conn = new mysqli("localhost", "root", "", "dogweb");

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Function to fetch user profile
function getUserProfile() {
    global $conn;
    if (isset($_GET['user_id'])) {
        $userId = filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT);

        $stmt = $conn->prepare("SELECT user_id, username, email, breed_id, role, profile_image_path FROM users WHERE user_id = ?");
        if (!$stmt) {
            echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
            return;
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            echo json_encode($user);
        } else {
            echo json_encode(['error' => 'User not found']);
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'User ID is missing']);
    }
}

// Function to update user profile
function updateUserProfile() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);

    $userId = filter_var($data['user_id'], FILTER_SANITIZE_NUMBER_INT);
    $username = filter_var($data['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $breedId = filter_var($data['breed_id'], FILTER_SANITIZE_NUMBER_INT);
    $role = filter_var($data['role'], FILTER_SANITIZE_STRING);

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, breed_id = ?, role = ? WHERE user_id = ?");
    if (!$stmt) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        return;
    }

    $stmt->bind_param("ssisi", $username, $email, $breedId, $role, $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => 'Profile updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update profile']);
    }
    $stmt->close();
}

// Function to handle profile image upload
function uploadProfileImage() {
    global $conn;
    if (isset($_FILES['profile_image']) && isset($_POST['user_id'])) {
        $userId = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
        $targetDir = "uploads/";

        // Create the uploads directory if it doesn't exist
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetFile = $targetDir . basename($_FILES['profile_image']['name']);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($_FILES['profile_image']['tmp_name']);
        if ($check === false) {
            echo json_encode(['error' => 'File is not an image']);
            return;
        }

        // Check file size (limit to 5MB)
        if ($_FILES['profile_image']['size'] > 5000000) {
            echo json_encode(['error' => 'File is too large']);
            return;
        }

        // Allow only certain file formats
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo json_encode(['error' => 'Only JPG, JPEG, PNG, and GIF files are allowed']);
            return;
        }

        // Upload the file
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
            // Update the profile image path in the database
            $stmt = $conn->prepare("UPDATE users SET profile_image_path = ? WHERE user_id = ?");
            if (!$stmt) {
                echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
                return;
            }

            $stmt->bind_param("si", $targetFile, $userId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => 'Profile image updated successfully', 'image_path' => $targetFile]);
            } else {
                echo json_encode(['error' => 'Failed to update profile image']);
            }
            $stmt->close();
        } else {
            echo json_encode(['error' => 'Failed to upload file']);
        }
    } else {
        echo json_encode(['error' => 'Invalid request']);
    }
}

// Determine which function to call based on the request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        case 'getuserprofile':
            getUserProfile();
            break;
        default:
            echo json_encode(['error' => 'Invalid API endpoint']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    switch ($action) {
        case 'updateUserProfile':
            updateUserProfile();
            break;
        case 'uploadProfileImage':
            uploadProfileImage();
            break;
        default:
            echo json_encode(['error' => 'Invalid API endpoint']);
    }
} else {
    echo json_encode(['error' => 'Invalid API endpoint']);
}

$conn->close();
?>