<?php
session_start();
require 'google-config.php';

// Handle the OAuth 2.0 server response
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $_SESSION['access_token'] = $token;

    // Fetch the user's profile information
    $oauth2 = new Google_Service_Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    // Store user info in the session
    $_SESSION['userInfo'] = (array) $oauth2->userinfo->get();

    // Redirect to some page after successful login, e.g., user dashboard

    // Here you can handle the user data, e.g., save it to your database
    // $userInfo contains details like email, name, picture, etc.

    // Redirect to some page after successful login, e.g., user dashboard
    
    header('Location: prompts.php');
} else {
    // Handle errors or user denials here
    echo "Error encountered during authentication!";
}
?>
