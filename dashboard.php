<?php
session_start();
require 'db_connect.php';
require 'google-config.php'; // Ensure Google Client Library is loaded

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check for userInfo in session
if (isset($_SESSION['userInfo']) && is_array($_SESSION['userInfo'])) {
    $userInfo = $_SESSION['userInfo'];
    $email = $conn->real_escape_string($userInfo['email']);
    $username = $conn->real_escape_string($userInfo['givenName']);

    // Check if user exists in the database
    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($result->num_rows == 0) {
        // User not found, insert them into the database
        if (!$conn->query("INSERT INTO users (username, email) VALUES ('$username', '$email')")) {
            die("Database error: " . $conn->error);
        }
    }

    // Display user details or any other dashboard content
    echo "Hello, " . $userInfo["givenName"];
    echo "<img src='" . $userInfo["picture"] . "' alt='Profile Picture'>";
} else {
    echo "You are not logged in!";
}

$conn->close();
?>
