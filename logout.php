<?php 

session_start();

include( 'includes/user.class.php' ); 

$user = new User();

$user->logOut();
