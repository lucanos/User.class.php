<?php

session_start();

include( 'includes/user.class.php' );

$user = new User();

// If the user is logged in and the get variable is set and not null
if( $user->isLoggedIn() && isset( $_GET['id'] ) && $_GET['id']!=NULL ){
  // Search the ID for the ID supplied
  $results = $user->search( 'id' , mysql_real_escape_string( $_GET['id'] ) );
  if( $results ){
    while( $row = mysql_fetch_array( $results ) ){
	    printf( 'ID: %s  Name: %s <br />' , $row['id'] , $row['username'] );
    }
  }else{
?>
	<i>Sorry. No results where found.</i>
<?php
  }
}else{
?>
	<i>Sorry. Please enter a search term</i>
<?php
}
