<?php
session_start();
require 'instagram.class.php';
require 'instagram.config.php';

// Display the login button
$loginUrl = $instagram->getLoginUrl();
echo "<a href='$loginUrl'>Signin with Instagram</a>";
?>