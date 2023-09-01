<?php
session_start();
require 'google-config.php';
include 'db_connect.php';
?>

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



$message = "";


// Check for userInfo in session
if (isset($_SESSION['userInfo']) && is_array($_SESSION['userInfo'])) {
    $userInfo = $_SESSION['userInfo'];
    $email = $conn->real_escape_string($userInfo['email']);
    $username = $conn->real_escape_string($userInfo['givenName']);

    // Check if user exists in the users table
    $sql_check_user = "SELECT user_id FROM users WHERE email = '$email'";
    $result_check_user = $conn->query($sql_check_user);

    if ($result_check_user->num_rows == 0) {
        // User doesn't exist, so insert them
        $sql_insert_user = "INSERT INTO users (username, email) VALUES ('$username', '$email')";
        if (!$conn->query($sql_insert_user)) {
            die("Error: " . $conn->error);  // Add error handling
        }
    }

    // Fetch the user_id for the logged-in user
    $sql_get_user_id = "SELECT user_id FROM users WHERE email = '$email'";
    $result_get_user_id = $conn->query($sql_get_user_id);

    
    if ($result_get_user_id->num_rows > 0) {
        $row = $result_get_user_id->fetch_assoc();
        $user_id = $row['user_id'];
    } else {
        die("Error: User not found.");  // Add error handling
    }

} else {
    // User not logged in. You can redirect or show a message.
    header("Location: login.php");
    echo "<!-- User Not Logged in -->";
}



// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
//    $user_id = intval($_POST['user_id']); // Ensure it's an integer
    $prompt_title = htmlspecialchars($_POST['prompt_title']); // Remove any tags or malicious code
    $prompt_text = htmlspecialchars($_POST['prompt_text']);
    $prompt_notes = htmlspecialchars($_POST['prompt_notes']);
    $source_url = filter_var($_POST['source_url'], FILTER_SANITIZE_URL);
    $category_id = intval($_POST['category']);  // Assuming the dropdown's name is "category"

    // Use prepared statement to insert the prompt
    
    $stmt = $conn->prepare("INSERT INTO prompts (user_id, prompt_title, prompt_text, prompt_notes, source_url, category_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $user_id, $prompt_title, $prompt_text, $prompt_notes, $source_url, $category_id);
    
    if ($stmt->execute()) {
        $new_prompt_id = $conn->insert_id;
        header("Location: prompt_details.php?prompt_id=$new_prompt_id");
        exit;
    } else {
        // Handle the error
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Database Interface</title>
    <link rel="stylesheet" href="styles.css">


</head>
<body>
<?php include 'menu.php'; ?>
<!--
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="nav-logo">Logo</a>
            <ul class="nav-menu">
                <li><a href="#">Home</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>
    </nav>
                <!-- Add more menu items as needed -->

    <div class="container">
        <aside class="sidebar">
            <?php
                    if (isset($userInfo["givenName"]) && !empty($userInfo["givenName"])) {
                        echo '<h2>Add New Prompt</h2>';
                        echo '<a href="add_prompt.php">Add Prompt</a>';
                    } else {
                        echo '<h2>Login to <br>Add New Prompts</h2>';
                    }
                ?>
            
            <!-- Left sidebar content goes here -->
            <h2>Categories</h2>
            <!-- Add your menu items here -->
            <?php
                $sql = "SELECT * FROM categories";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<p>' . $row["category_name"] . '</p>';
                    }
                } else {
                    echo '<p>No results found</p>';
                }
            ?>

            <button id="themeToggle">Toggle Theme</button>
        </aside>
        <main class="content">
            <div class="breadcrumb">
                <a href="#">Categories</a> &gt;
                <a href="#">Programming</a> &gt;
                <span>Python</span>
            </div>
            
            <!-- Database results go here -->

            <div class="result-box">


            
                <h2>Add New Prompt</h2>
                <form action="add_prompt.php" method="post">
                    <div class="form-group">
                        <label for="prompt_title">Title:</label>
                        <input type="text" id="prompt_title" name="prompt_title" required>
                    </div>
                    <div class="form-group">
                        <label for="prompt_text">Prompt Text:</label>
                        <textarea id="prompt_text" name="prompt_text" rows="15" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="prompt_notes">Prompt Notes:</label>
                        <textarea id="prompt_notes" name="prompt_notes" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="source_url">Source URL (optional):</label>
                        <input type="url" id="source_url" name="source_url">
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select id="category_id" name="category">
                            <?php
                            // Fetching categories from the database
                            $sql_categories = "SELECT category_id, category_name FROM categories";
                            $result_categories = $conn->query($sql_categories);

                            if ($result_categories->num_rows > 0) {
                                while($category = $result_categories->fetch_assoc()) {
                                    echo '<option value="' . $category["category_id"] . '">' . $category["category_name"] . '</option>';
                                }
                            } else {
                                echo '<option value="">No categories available</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                    <button type="submit" class="btn btn-primary">Add Prompt</button>
                    </div>
                </form>
            </div>



            

            <!-- Repeat .result-box for more results -->

        </main>
    </div>

    <?php include 'footer.php'; ?>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.vote-btn').on('click', function(e) {
                e.preventDefault();
                
                var promptId = $(this).data('prompt-id');
                var voteType = $(this).data('vote-type');
                var clickedButton = $(this);  // Cache the clicked button

                $.ajax({
                    url: 'handle_vote.php',
                    method: 'POST',
                    data: {
                        'prompt_id': promptId,
                        'vote_type': voteType
                    },
                    success: function(response) {
                        if(response.success) {
                            // Update UI using the cached button reference
                            var currentCount = parseInt(clickedButton.text().trim().split(' ')[1]);
                            if (voteType === "upvote") {
                                clickedButton.text('👍 ' + (currentCount + 1)); 
                            } else {
                                clickedButton.text('👎 ' + (currentCount + 1)); 
                            }
                        } else {
                            // Handle errors or show messages if needed
                            alert(response.message);
                        }
                    }
                });
            });
        });
    </script>




    <script src="scripts.js"></script>
    <script>
    document.getElementById('themeToggle').addEventListener('click', function() {
        const body = document.body;
        const html = document.documentElement; // Get the <html> element

        // Toggle for body
        if (body.classList.contains('light-mode')) {
            body.classList.remove('light-mode');
        } else {
            body.classList.add('light-mode');
        }

        // Toggle for html
        if (html.classList.contains('light-mode')) {
            html.classList.remove('light-mode');
        } else {
            html.classList.add('light-mode');
        }
    });


    // this is for the pagination
    document.addEventListener('DOMContentLoaded', function() {
        const paginationLinks = document.querySelectorAll('.pagination a');

        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                // Remove active class from all links
                paginationLinks.forEach(l => l.classList.remove('active'));
                
                // Add active class to clicked link
                this.classList.add('active');
                
                // Load content for the clicked page (e.g., via AJAX)
                // ...
            });
        });
    });

</script>

</body>
</html>
