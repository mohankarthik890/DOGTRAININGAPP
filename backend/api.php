<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PATCH');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));

include 'db.php';

switch ($request[0]) {
    case 'register':
        if ($method == 'POST') {
            register(); // Call the register function
        }
        break;
    case 'login':
        if ($method == 'POST') {
            loginUser();
        }
        break;
    case 'profile':
        if ($method == 'GET') {
            getUserProfile();
        }
        break;
    case 'breeds':
        if ($method == 'GET') {
            if (isset($request[1])) {
                getSpecificBreed($request[1]);
            } else {
                getAllBreeds();
            }
        }
        break;
    default:
        echo json_encode(['message' => 'Invalid API endpoint']);
}

function register() {
    global $conn;

    // Get data from POST request
    $name = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate input data
    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
        return;
    }

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email already exists
    $checkQuery = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $checkQuery->bind_param("s", $email);
    $checkQuery->execute();
    $checkQuery->store_result();

    if ($checkQuery->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Email already registered.']);
        return;
    }
    $checkQuery->close();

    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Registration failed.']);
    }

    $stmt->close();
}

function loginUser() {
    global $conn;

    if (!isset($_POST['email'], $_POST['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Required fields are missing']);
        return;
    }

    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Fetch user details including role
    $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE LOWER(email) = LOWER(?)");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal Server Error']);
        return;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        if (password_verify($password, $user['password'])) {
            // Login successful
            http_response_code(200);
            echo json_encode([
                'message' => 'Login successful',
                'user_id' => $user['user_id'], // Ensure user_id is included
                'role' => $user['role']
            ]);
        } else {
            // Password verification failed
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    } else {
        // User not found
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
}

function getUserProfile() {
    global $conn;
    if (isset($_GET['user_id'])) {
        $userId = $_GET['user_id'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        echo json_encode($user);
        $stmt->close();
    } else {
        echo json_encode(['error' => 'User ID is missing']);
    }
}

function getAllBreeds() {
    global $conn;
    $result = $conn->query("SELECT * FROM breeds");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

function getSpecificBreed($breedId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM breeds WHERE breed_id = ?");
    $stmt->bind_param("i", $breedId);
    $stmt->execute();
    echo json_encode($stmt->get_result()->fetch_assoc());
}
?>