<?php 
session_start(); //ESSENTIAL
include('includes/user.class.php'); 
$user = new User();
if($user->isLoggedIn() && isset($_GET['id']) && $_GET['id'] != NULL) { //If the user is logged in and the get variable is set and not null
$results = $user->search('id', mysql_real_escape_string($_GET['id'])); //search the ID for the ID supplied

if($results) {
	while ($row = mysql_fetch_array($results)) {
	    printf("ID: %s  Name: %s <br />", $row['id'], $row['username']);  
	}
} else { ?>
	<i>Sorry. No results where found.</i>
<?php }} else {?>
	<i>Sorry. Please enter a search term</i>
<?php } ?>