<?php

require 'instagram.class.php';
require 'instagram.config.php';

// Receive OAuth code parameter
$code = $_GET['code'];

// Check whether the user has granted access
if (true === isset($code)) {
   // Receive OAuth token object
   $data = $instagram->getOAuthToken($code);
   
   // check response
   if(empty($data->user->username))
   {
        header('Location: index.php');
   }
   else
   {
        session_start();
        $_SESSION['userdetails']=$data;
        //display user data
        echo "Username: ".$data->user->username;
        echo "fullname: ".$data->user->full_name;
        echo "Bio: ".data->user->bio;
        echo "Website: ".$website=$data->user->website;
        echo "ID: ".$data->user->id;
        echo "Token: " =$data->access_token;
        echo 'Profile Pic: '.$data->user->profile_picture;
   }
}
else
{
    //Check whether an error occurred
    if (true === isset($_GET['error']))
    {
         echo 'An error occurred: '.$_GET['error_description'];
    }
}
?>