<?php
// Database configuration
$servername = "localhost";
$username = "root"; // Correct variable name
$password = ""; // Leave empty for XAMPP default
$dbname = "dogweb"; // Correct database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
