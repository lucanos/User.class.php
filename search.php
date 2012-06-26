<?php 

session_start();

include( 'includes/user.class.php' );

$user = new User();

if( $user->isLoggedIn() && isset( $_GET['name'] ) && $_GET['name']!=NULL ){
  $results = $user->search( 'username' , mysql_real_escape_string( $_GET['name'] ) );
  if( $results ){
    while( $row = mysql_fetch_array( $results ) ){
	    printf( 'ID: %s  <a href="profile.php?id=%s">Name: %s</a> <br />' , $row['id'] , $row['id'] , $row['username'] );
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
