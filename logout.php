<?php 
session_start(); //ESSENTIAL
include('includes/user.class.php'); 
$user = new User();

$user->logOut(); //Our logout function. Pretty simple eh?

?>