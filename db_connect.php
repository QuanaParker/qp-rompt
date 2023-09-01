<?php
$servername = "localhost";
$username = "";
$password = "";
$dbname = "prompt_engineer";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
