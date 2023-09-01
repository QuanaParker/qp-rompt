<?php
session_start();
require 'google-config.php';
include 'db_connect.php';


// Include Parsedown class
require 'Parsedown.php';

// Instantiate Parsedown
$parsedown = new Parsedown();
?>

<?php
$message = "";

// Check for userInfo in session
if (isset($_SESSION['userInfo']) && is_array($_SESSION['userInfo'])) {
    $userInfo = $_SESSION['userInfo'];
    $email = $conn->real_escape_string($userInfo['email']);
    $username = $conn->real_escape_string($userInfo['givenName']);
} else {
    echo "<!-- User Not Logged in -->";
}

// $conn->close();


// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = intval($_POST['user_id']); // Ensure it's an integer
    $prompt_title = filter_var($_POST['prompt_title'], FILTER_SANITIZE_STRING); // Remove any tags or malicious code
    $prompt_text = filter_var($_POST['prompt_text'], FILTER_SANITIZE_STRING);
    $source_url = filter_var($_POST['source_url'], FILTER_SANITIZE_URL);

    // Use prepared statement to insert the prompt
    $stmt = $conn->prepare("INSERT INTO prompts (user_id, prompt_title, prompt_text, source_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $prompt_title, $prompt_text, $source_url);
    
    if ($stmt->execute()) {
        $message = "Prompt added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
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
            
            <!-- Current Category Display Code -->
            <h2>Categories</h2>

            <!-- Add an All Categories Link -->
            <p><a href="prompts.php?category=all">All Categories</a></p>

            <!-- Modified Category Fetch with Prompt Count -->
            <?php
                $sql = "SELECT categories.*, COUNT(prompts.prompt_id) as prompt_count FROM categories 
                        LEFT JOIN prompts ON categories.category_id = prompts.category_id 
                        GROUP BY categories.category_id";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<p><a href="prompts.php?category=' . $row["category_id"] . '">' . $row["category_name"] . '</a> (' . $row["prompt_count"] . ')</p>';
                    }
                } else {
                    echo '<p>No results found</p>';
                }
            ?>

            <button id="themeToggle">Toggle Theme</button>
        </aside>
            
            <!-- Database results go here -->

            <?php
                $category_filter = "";
                if(isset($_GET['category']) && $_GET['category'] !== 'all') {
                    $category_filter = " WHERE p.category_id=" . intval($_GET['category']);
                }

                $sql = "
                SELECT p.*, c.category_name, COUNT(com.comment_id) as comments_count
                FROM prompts p
                LEFT JOIN comments com ON p.prompt_id = com.prompt_id
                LEFT JOIN categories c ON p.category_id = c.category_id" . 
                $category_filter . 
                " GROUP BY p.prompt_id ORDER BY p.prompt_id DESC";

                $result = $conn->query($sql);
                
                $category_name = "All Categories"; // Default name

                if(isset($_GET['category']) && $_GET['category'] !== 'all') {
                    $category_sql = "SELECT category_name FROM categories WHERE category_id=" . intval($_GET['category']);
                    $category_result = $conn->query($category_sql);
                    if ($category_result->num_rows > 0) {
                        $category_data = $category_result->fetch_assoc();
                        $category_name = $category_data['category_name'];
                    }
                }
               
                ?>

                <main class="content">

                <div class="breadcrumb">
                    <a href="prompts.php?category=all">Categories</a> &gt;
                    <span><?php echo $category_name; ?></span>
                </div>


                <?php 
                    if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="result-box">';
                        echo '<h3 class="result-title"><a href="prompt_details.php?prompt_id=' . $row["prompt_id"] .'">' . $row["prompt_title"] . '</a></h3>';
                        // Check the length of the prompt_text
                        $prompt_display_text = $row["prompt_text"];
                        if (mb_strlen($prompt_display_text) > 500) {
                            $prompt_display_text = mb_substr($prompt_display_text, 0, 500) . "...";
                        }
                        // echo '<p class="result-body">' . $prompt_display_text . '</p>';
                        echo '<p class="result-body">' . $parsedown->text($prompt_display_text) . '</p>';
                        // echo '<p class="result-body">' . $parsedown->text($prompt_display_text['prompt_text']) . '</p>';

                        echo '<div class="info-bar">';
                        echo '<span class="category">' . $row["category_name"] . '</span>  ';  // Display the category
                        echo '<a href="#" class="vote-btn upvote" data-prompt-id="' . $row["prompt_id"] . '" data-vote-type="upvote">👍 ' . $row["upvote_count"] . '</a>  ';
                        echo '<a href="#" class="vote-btn downvote" data-prompt-id="' . $row["prompt_id"] . '" data-vote-type="downvote">👎 ' . $row["downvote_count"] . '</a>  ';
                        echo '<span class="comments">💬 ' . $row["comments_count"] . '</span>  ';
                        echo '<span class="details"><a href="prompt_details.php?prompt_id=' . $row["prompt_id"] .'">' . '🩻 Details</a></span>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="result-box">';
                    echo '<p class="result-body">No results found</p>';
                    echo '</div>';
                }
            ?>

            <!-- Repeat .result-box for more results -->
            <div class="pagination">
                <a href="#">&laquo;</a> <!-- Previous Page -->
                <a href="#" class="active">1</a>
                <a href="#">2</a>
                <a href="#">3</a>
                <a href="#">4</a>
                <a href="#">5</a>
                <a href="#">&raquo;</a> <!-- Next Page -->
            </div>

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
