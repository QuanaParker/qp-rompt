<?php
session_start();
require 'google-config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>
    <link rel="stylesheet" href="styles.css">


</head>
<body>
<?php include 'menu.php'; ?>
<div class="container">
<centre>
    <h2>Login to Qp-Rompt</h2>
    <br>
    <!-- "Login with Google" button -->
    <a href="<?php echo $client->createAuthUrl(); ?>">
        <img src="https://developers.google.com/identity/images/btn_google_signin_dark_normal_web.png" alt="Login with Google">
    </a>
</centre>

</div>
<?php include 'footer.php'; ?>

</body>
</html>
