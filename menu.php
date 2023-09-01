<?php /*

<!--

<div class="container" style="background-color: #333; padding: 10px;">
    <a href="index.php" style="color: #fff; margin-right: 15px; text-decoration: none;">Home |</a>
    <a href="prompts.php" style="color: #fff; margin-right: 15px; text-decoration: none;">| Prompts |</a>
    <a href="add_prompt.php" style="color: #fff; margin-right: 15px; text-decoration: none;">| Add Prompt |</a>
    <a href="usages.php" style="color: #fff; margin-right: 15px; text-decoration: none;">| Usages |</a>
    <a href="ai_tools.php" style="color: #fff; margin-right: 15px; text-decoration: none;">| AI Tools |</a>
    <a href="users.php" style="color: #fff; margin-right: 15px; text-decoration: none;">| Users |</a>
    <a href="add_user.php" style="color: #fff; margin-right: 15px; text-decoration: none;">| Add User |</a>
    <a href="register.php" style="color: #fff; margin-right: 15px; text-decoration: none;">| Register |</a>
    <a href="login.php" style="color: #fff; margin-right: 15px; text-decoration: none;">| Login</a>
</div>
    -->
*/ ?>

<nav class="navbar">
        <div class="nav-container">
            <a href="#" class="nav-logo">Qp-Rompt</a>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="prompts.php">Prompts</a></li>
                <li><a href="add_prompt.php">Add Prompt</a></li>
<?php /*
                <!-- Add more menu items as needed -->
                <li><a href="usages.php">Usages</a></li>
                <li><a href="ai_tools.php">AI Tools</a></li>
*/ ?>
                <?php
                    if (isset($userInfo["givenName"]) && !empty($userInfo["givenName"])) {
                        echo '<li><a href="logout.php">Logout ' . $userInfo["givenName"] . '</a></li>';
                    } else {
                        echo '<li><a href="login.php">Login</a></li>';
                    }
                ?>

            </ul>
        </div>
    </nav>
