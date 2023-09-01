<?php
require 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('');  // Replace with your Client ID
$client->setClientSecret('');  // Replace with your Client Secret
$client->setRedirectUri('google-callback.php'); // Replace with your full callback url ie: https://quana.org/qp-rompt/google-callback.php
$client->addScope("email");
$client->addScope("profile");

return $client;
?>