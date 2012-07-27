<?php

include( 'config.php' );

class User {
	
	function __construct(){}

	function randomString( $len=32 ){
    // Initialise a string
		$s = '';
    // Possible characters
		$letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

		for( $i=0 ; $i<$len ; $i++ ){
      // Grab a random letter for $letters
			$char = $letters[mt_rand( 0 , strlen( $letters )-1 )];
			$s .= $char; //Add it to the string
		}
		return $s;
	}

	function hash( $password , $salt , $created_at ){
		// Adds last two letters of the password for extra security
    $lastTwo = substr( $password , -2 );
    // Reverses the date and removes the dashes
		$date = sha1( strrev( (string) $created_at ) );
    // Yay! Bcrypt
		return crypt($salt . $lastTwo . $password . $date . $salt , '$2a$12$' . $salt);
	}

	function salt(){
		$firstSalt = substr( str_replace( '+' , '.' , base64_encode( sha1( microtime( true ) , true ) ) ) , 0 , 22 );
		return $firstSalt;
	}
	
	function register( $userName , $userPassword ){
		if( $this->exists( $userName ) )
      return false;
    $salt = $this->salt(); //Generate a salt using the username provided
    $date = time();
    $password = $this->hash( $userPassword , $salt , $date ); //Hash the password with the new salt

    //The query for inserting our new user into the DB
    $q1 = sprintf( "INSERT INTO users (username, password, rand, created_at) VALUES ('%s', '%s', '%s', '%s')" ,
            mysql_real_escape_string( $userName ) ,
            mysql_real_escape_string( $password ) ,
            mysql_real_escape_string( $salt ) ,
            mysql_real_escape_string( $date )
          );
    if( mysql_query( $q1 ) )
      return true;
    die( mysql_error() ); // Run it. If it doesn't go through stop the script and display the error.
    return false;
	}
  
  function update( $userName , $oldPassword , $newPassword ){
		if( !$this->exists( $userName ) )
      return false;
    $q1 = sprintf( "SELECT password, rand, created_at FROM users WHERE username='%s'" ,
            mysql_real_escape_string( $userName )
          );
    $r1 = mysql_fetch_array( mysql_query( $q1 ) );
    $oldHashDB = $this->hash( $r1['password'] , $r1['rand'] , $r1['created_at'] );
    $oldHashIn = $this->hash( $oldPassword , $r1['rand'] , $r1['created_at'] );
    if( $oldHashDB == $oldHashIn ){
      $salt = $this->salt();
      $newHash = $this->hash( $newPassword , $salt , $r1['created_at'] );
      $q2 = sprintf( "UPDATE users SET password='%s', rand='%s' WHERE username='%s'" ,
              mysql_real_escape_string( $newHash ) ,
              mysql_real_escape_string( $salt ) ,
              mysql_real_escape_string( $userName )
            );
      if( mysql_query( $q2 ) ){
        setLoggedIn( $userName , $newPassword );
        return true;
      }
    }
  }
	
	function verify( $userName , $userPassword ){
		// Grabbing all the user details with this query
    $q1 = sprintf( "SELECT password, rand, created_at FROM users WHERE username='%s'" ,
            mysql_real_escape_string( $userName )
          );
		$r1 = mysql_fetch_array( mysql_query( $q1 ) );
    $ph = $this->hash( $userPassword , $r1['rand'] , $r1['created_at'] );
    // Return whether it is true or false
		return ( $r1['password'] == $this->hash( $userPassword , $r1['rand'] , $r1['created_at'] ) );
	}

	function setLoggedIn($userName, $userPassword) {
		//This function is self explanitory :)
		$_SESSION['loggedIn'] = true; 
		$_SESSION['userName'] = $userName;
		$_SESSION['userPassword'] = $userPassword;
	}
	
	function isLoggedIn() { 
		return ( isset( $_SESSION['loggedIn'] )
             && $_SESSION['loggedIn']
             && $this->verify( $_SESSION['userName'] , $_SESSION['userPassword'] ) );
	}
	
	function redirectTo($page) { 
		if( !headers_sent() ){
      header( 'Location: ' . $page . '.php' );
    }
    die( '<a href="'.$page.'.php">Go to '.$page.'.php</a>' );
	}
	
	function userInfo( $userName ){
		// This function returns all user details to the front end. This is to save storing it all in sessions
    $q1 = sprintf( "SELECT * FROM users WHERE username='%s'" ,
            mysql_real_escape_string( $userName )
          );
    // Fetch and Return the array
    return mysql_fetch_array( mysql_query( $q1 ) );
	}

	function userInfoId( $UID ){
		// This function returns all user details to the front end. This is to save storing it all in sessions
    $q1 = sprintf( "SELECT * FROM users WHERE id=%s" ,
            (int) $UID
          );
    // Fetch and Return the array
    return mysql_fetch_array( mysql_query( $q1 ) );
	}
	
	function logOut(){
    // If they are logged in
		if( isset( $_SESSION['loggedIn'] ) ){
      // Unset the session variables
			unset( $_SESSION['loggedIn'] , $_SESSION['userName'] , $_SESSION['userPassword'] );
			// Redirect to the login page
      $this->redirectTo( 'login' );
		}
	}
	
	function exists( $userName ){
		// Checks a user exists (for the register page)
    $q1 = sprintf( "SELECT username FROM users WHERE username = '%s'" ,
            mysql_real_escape_string( $userName )
          );
		return (bool) mysql_num_rows( mysql_query( $q1 ) );
	}
	
	function search( $field , $term ){
    $sql_field = false;

		switch( $field ){
      case 'id' :
        $sql_field = 'id';
        break;
      case 'username' :
        $sql_field = 'username';
        break;
    }
    if( !$sql_field )
      return false;
    $q1 = sprintf( "SELECT * from users WHERE %s LIKE '%%%s%%'" ,
            mysql_real_escape_string( $term )
          );
    $r1 = mysql_query( $q1 );
    if( !mysql_num_rows( $r1 ) )
      return false;
    return $r1;
	}
	
	function messageNotification( $UID ){
    // Select all unread notifications
    $q1 = sprintf( "SELECT * FROM messages WHERE message_to = '%s' AND message_read = '0'" ,
            (int) $UID
          );
    $r1 = mysql_query( $q1 );
    // Return the number
    return mysql_num_rows( $r1 );
	}
	
	function displayMessages( $action , $UID , $ID=NULL ){
    $where = false;

		switch( $action ){
      case 'list' :
        $where = sprintf( "messages.message_to = %s ORDER BY messages.message_id DESC" ,
                   (int) $UID
                 );
        break;
      case 'read' :
        $where = sprintf( "messages.message_id = %s" ,
                   (int) $ID
                 );
    }
    if( !$where )
      return null;
    $q = sprintf( "SELECT * FROM messages INNER JOIN users ON messages.message_from=users.id WHERE %s" ,
           $where
         );
    $r = mysql_query( $q );
    if( !mysql_num_rows( $r ) )
      return false;
    return $r;
  }
	
	function setMessageStatus( $messageID , $status ){
    $q = sprintf( "UPDATE messages SET message_read = %s WHERE message_id = %s" ,
            (int) $status ,
            (int) $messageID
         );
		mysql_query( $q );
	}

	function setMessageUnread( $messageID ){
		setMessageStatus( $messageID , 0 );
	}
	function setMessageRead( $messageID ){
		setMessageStatus( $messageID , 1 );
	}

	function sendMessage( $to , $from , $subject , $message ){
    $q = sprintf( "INSERT INTO messages (message_to, message_from, message_subject, message, message_read) VALUES ('%s', '%s', '%s', '%s', 0)" ,
           mysql_real_escape_string( $to ) ,
           mysql_real_escape_string( $from ) ,
           mysql_real_escape_string( $subject ) ,
           mysql_real_escape_string( $message )
           
         );
    return mysql_query( $q ); //Inserts it. Notice the message read is set to 0
	}
	
	function deleteMessage( $messageID ){
    $q = sprintf( "DELETE FROM messages WHERE message_id = '%s'" ,
           (int) $messageID
         );
		return mysql_query( $q );
	}
	
	function string_shorten( $text , $len ){
    // Strip any linebreaks or multiple-spaces
    $text = preg_replace( array( "/\n|\r/" , '\s\s+' ) , ' ' , $text );
    // Split the text using the wordwrap() function
    $lines = explode( "\n" , wordwrap( $text , $len ) );
    // Get the First Line and add continuation ... sign
    return $lines[0].'...'; //Return the value
	}
	
	function checkLevel( $i ){
    $levels = array( 'Normal' , 'Moderator' , 'Admin' );
    return $levels[$i];
	}

}
