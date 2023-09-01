<?php
session_start();
require 'google-config.php';
include 'db_connect.php';


// Include Parsedown class
require 'Parsedown.php';

// Instantiate Parsedown
$parsedown = new Parsedown();


$message = "";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// Check for userInfo in session
if (isset($_SESSION['userInfo']) && is_array($_SESSION['userInfo'])) {
    $userInfo = $_SESSION['userInfo'];
    $email = $conn->real_escape_string($userInfo['email']);
    $username = $conn->real_escape_string($userInfo['givenName']);
} else {
    echo "<!-- User Not Logged in -->";
}

// Check if prompt_id is passed to fetch the prompt details
if (isset($_GET['prompt_id'])) {
    $prompt_id = intval($_GET['prompt_id']);
    $result = $conn->query("SELECT * FROM prompts WHERE prompt_id = $prompt_id");
    if ($result->num_rows > 0) {
        $prompt = $result->fetch_assoc();
        
        // Fetch user details
        $user_id = $prompt['user_id'];
        $user_result = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
        if ($user_result->num_rows > 0) {
            $user_details = $user_result->fetch_assoc();
        }


        // Fetch comments related to the current prompt from the database
        $comments_query = "SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.user_id WHERE prompt_id = '$prompt_id' ORDER BY timestamp ASC";
        $comments_result = $conn->query($comments_query);
        $comments = [];
        if ($comments_result->num_rows > 0) {
            while($row = $comments_result->fetch_assoc()) {
                $comments[] = $row;
            }
        }


    } else {
        $message = "Prompt not found!";
    }
}

// Check if the page has received a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_text']) && isset($userInfo) && isset($_GET['prompt_id'])) {
    // Sanitize user input
    $comment_text = $conn->real_escape_string($_POST['comment_text']);
    
    // Fetch the Qp-Rompt user_id using the email from Google OAuth
    $google_user_email = $conn->real_escape_string($userInfo['email']);
    $user_query = "SELECT user_id FROM users WHERE email = '$google_user_email'";
    $user_result = $conn->query($user_query);
    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $qp_rompt_user_id = $user_data['user_id'];
    } else {
        $message = "Error: User not found!";
        return;
    }

    $prompt_id = intval($_GET['prompt_id']);

    // Prepare SQL statement to insert comment
    $sql = "INSERT INTO comments (prompt_id, user_id, comment_text, timestamp) VALUES ('$prompt_id', '$qp_rompt_user_id', '$comment_text', NOW())";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        $message = "Comment added successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }

    // If it's an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        // Fetch the latest comments
        $comments_query = "SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.user_id WHERE prompt_id = '$prompt_id' ORDER BY timestamp ASC";

        $comments_result = $conn->query($comments_query);
        $comments = [];
        while ($row = $comments_result->fetch_assoc()) {
            $comments[] = $row;
        }
        echo json_encode(['comments' => $comments]);
        exit;
    }


}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prompt Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'menu.php'; ?>

<div class="container">
    <div class="result-box">
        <?php if (isset($prompt)): ?>
            <h2><?= $prompt['prompt_title'] ?> <button id="copyPromptBtn">Copy Prompt</button></h2>
            <p><?= $parsedown->text($prompt['prompt_text']) ?></p>
            <p>Source: <a href="<?= $prompt['source_url'] ?>"><?= $prompt['source_url'] ?></a></p>
            <p>Created by: <?= $user_details['username'] ?></p>
            <p>Notes on use: <?= $prompt['prompt_notes'] ?></p>
            <hr>
            <h3>Comments:</h3>
            <div class="commentsSection">
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <!-- Fetch and display comment user details here -->
                    <p><em><?= $comment['timestamp'] ?></em> -- <strong><?= $comment['username'] ?>:</strong>
                    <?= $comment['comment_text'] ?>
                    </p>
                </div>
            <?php endforeach; ?>
            </div>

            <!-- Comment form if user is logged in -->
            <?php if (isset($userInfo)): ?>
                <form action="" method="post" id="commentForm">
                    <textarea name="comment_text" placeholder="Add a comment..."></textarea>
                    <input type="submit" value="Submit">
                </form>
            <?php endif; ?>
            
        <?php else: ?>
            <p><?= $message ?></p>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>


<script>
document.getElementById('commentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    let commentTextElement = document.querySelector('[name="comment_text"]');
    let commentText = commentTextElement.value;

    fetch('', {  // Empty string for the current URL
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams(`comment_text=${commentText}`)
    })
    .then(response => response.json())
    .then(data => {
        let commentsSection = document.querySelector('.commentsSection');  // Assuming you have a wrapping div for comments
        commentsSection.innerHTML = '';  // Clear existing comments
        data.comments.forEach(comment => {
            let commentDiv = document.createElement('div');
            commentDiv.classList.add('comment');
            commentDiv.innerHTML = `
                <p><em>${comment.timestamp}</em> -- <strong>${comment.username}:</strong>
                ${comment.comment_text}</p>

            `;
            commentsSection.appendChild(commentDiv);
        });

        // Clear the textarea content after successful submission
        commentTextElement.value = '';
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

// copy prompt button
document.getElementById('copyPromptBtn').addEventListener('click', function() {
    let promptText = <?= json_encode($prompt['prompt_text']) ?>; // JSON-encode the prompt text
    let textArea = document.createElement("textarea");
    textArea.value = promptText;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
    alert('Prompt copied to clipboard!');
});


</script>


</body>
</html>
