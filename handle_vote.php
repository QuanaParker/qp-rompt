<?php
header('Content-Type: application/json');

session_start();

// Check if user is logged in
if (!isset($_SESSION['userInfo'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to vote.']);
    exit;
}

// Connect to the database
include 'db_connect.php';

// Get data from POST request
$prompt_id = $_POST['prompt_id'];
$vote_type = $_POST['vote_type'];

// Determine which column to update based on the vote type
$column_to_update = ($vote_type == 'upvote') ? 'upvote_count' : 'downvote_count';

// Update the database
$sql = "UPDATE prompts SET $column_to_update = $column_to_update + 1 WHERE prompt_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $prompt_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Vote recorded']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error recording vote']);
}

$stmt->close();
?>
