<?php
include("config.php");

class User {
	
	function __construct(){  
    }

	function randomString($length) {
		$s = ""; //Set the string as a blank one
		$letters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"; //Possible letters to arise

		for($i=0;$i < $length;$i++) {
			$char = $letters[mt_rand(0, strlen($letters)-1)]; //Grab a random letter for $letters
			$s .= $char; //Add it to the string
		}
		return $s;
	}

	function hash($password,$salt,$created_at) {
		$lastTwo = substr($password, -2); //Adds last two letters of the password for extra security
		$date = sha1(str_replace('-', '', strrev($created_at))); //Reverses the date and removes the dashes
		return crypt($salt . $lastTwo . $password . $date . $salt , '$2a$12$' . $salt); //Yay! Bcrypt
		//return hash('sha256', $salt . $lastTwo . $password . $date . $salt); //UnComment to use SHA256 instead of bcrypt (Faster but a lot less secure)
	}
	
	//$secondSalt = substr($username, -2);
	
	function salt() {
		//$firstSalt = $this->randomString(6); //Again.. Uncomment and comment the line below for SHA256
		$firstSalt = substr(str_replace('+', '.', base64_encode(sha1(microtime(true), true))), 0, 22);
		return $firstSalt; //Return it
	}
	
	function register($userName,$userPassword) {
		if(!$this->exists($userName)) {
			$salt = $this->salt(); //Generate a salt using the username provided
			$date = date('Y-m-d');
			$password = $this->hash($userPassword, $salt, $date); //Hash the password with the new salt
		
			//The query for inserting our new user into the DB
			$q1 = "INSERT INTO users (username, password, rand, created_at) VALUES ('" . $userName . "', '" . $password . "', '" . $salt . "', '" .  $date . "')";
			mysql_query($q1) or die(mysql_error()); //Run it. If it doesn't go through stop the script and display the error.
			return true;
		} else {
			return false;
		}
	}
	
	function verify($userName, $userPassword) {
		//Grabbing all the user details with this query
		$r1 = mysql_fetch_array(mysql_query("SELECT password, rand, created_at FROM users WHERE username='" . $userName . "';")); 
		return $r1['password'] == $this->hash($userPassword, $r1['rand'], $r1['created_at']); //Return whether it is true or false
	}
	function setLoggedIn($userName, $userPassword) {
		//This function is self explanitory :)
		$_SESSION['loggedIn'] = TRUE; 
		$_SESSION['userName'] = $userName;
		$_SESSION['userPassword'] = $userPassword;
	}
	
	function isLoggedIn() { 
		if(isset($_SESSION['loggedIn'])) { //If the session variable is set (Prevents nasty warnings)
			if($_SESSION['loggedIn'] && $this->verify($_SESSION['userName'], $_SESSION['userPassword'])) { //If it's true and the details work
				return TRUE;
			}
		}
	}
	
	function redirectTo($page) { 
		header( 'Location: ' . $page . '.php' ); //Just a simple redirector to save typing.
	}
	
	function userInfo($userName) {
		//This function returns all user details to the front end. This is to save storing it all in sessions
			$r2 = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE username='" . $userName . "';")); //Fetch the array
			return $r2; //return it
	}
	function userInfoId($UID) {
		//This function returns all user details to the front end. This is to save storing it all in sessions
			$r2 = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE id='" . $UID . "';")); //Fetch the array
			return $r2; //return it
	}
	
	function logOut() {
		if(isset($_SESSION['loggedIn'])) { //If they are logged in
			unset($_SESSION['loggedIn'], $_SESSION['userName'], $_SESSION['userPassword']); //Unset the session variables
			$this->redirectTo('login'); //Redirect to the login page
		}
	}
	
	function exists($username) {
		//Checks a user exists (for the register page)
		if(mysql_num_rows(mysql_query("SELECT username FROM users WHERE username = '$username'"))){
			return TRUE;
		}
	}
	
	function search($field, $term) {
		switch ($field) {
		    case 'id':
		        $results = mysql_query("SELECT * from users WHERE id LIKE '%$term%';");
				if(mysql_num_rows($results)) {
					return $results;
				} else {
					return FALSE;
				}
		    case 'username':
		        $results = mysql_query("SELECT * from users WHERE username LIKE '%$term%';");
				if(mysql_num_rows($results)) {
					return $results;
				} else {
					return FALSE;
				}
		        break;
		}
		
	}
	
	function messageNotification($UID) {
		$unread = mysql_query("SELECT * FROM messages WHERE message_to = '$UID' AND message_read = '0'"); //Select all unread notifications
		if(mysql_num_rows($unread)) { //If they exist
			return mysql_num_rows($unread); //Return the number of them that exist
		} else {
			return FALSE;
		}
	}
	
	function displayMessages($action, $UID, $ID = NULL) {
		switch ($action) {
		case 'list':
			$messages = mysql_query("SELECT * FROM messages INNER JOIN users ON messages.message_from=users.id WHERE messages.message_to = '$UID' ORDER BY messages.message_id DESC"); //Select all messages to UID BUT also select the user it's from
			if(mysql_num_rows($messages)) { //If any messages exist
				return $messages; //Return them (Not in an array.)
			} else {
				return FALSE;
			}
			break;
		case 'read':
			$messages = mysql_query("SELECT * FROM messages INNER JOIN users ON messages.message_from=users.id WHERE messages.message_id = '$ID'");
			if(mysql_num_rows($messages)) { //Again the same as the one above but without the ORDER BY DESC clause
				return $messages; //Return them
			} else {
				return FALSE;
			}
	}}
	
	function setMessageRead($messageID) {
		mysql_query("UPDATE messages SET message_read = 1 WHERE message_id = '$messageID'"); //Just a simple 'set read' clause
	}
	
	function setMessageUnread($messageID) {
		mysql_query("UPDATE messages SET message_read = 0 WHERE message_id = '$messageID'"); //Just a simple 'set read' clause
	}
	
	function sendMessage($to, $from, $subject, $message) {
			mysql_query("INSERT INTO messages (message_to, message_from, message_subject, message, message_read) VALUES ('" . $to . "', '" . $from . "', '" . $subject . "', '" .  $message . "', '0')"); //Inserts it. Notice the message read is set to 0
			return TRUE;
	}
	
	function deleteMessage($message_id) {
		return mysql_query("DELETE FROM messages WHERE message_id = '$message_id'"); //Simple
	}
	
	function smiley($string) { //This is our smileys!
			$smileys = array(
						'0:)' => '<img src="images/angel.png" />', //It looks like this: 'SHORTCODE' => 'HTML'
						':S' => '<img src="images/awww.png" />', //So type :S and this'll replace it with <img src="images/awww.png" />
						':|' => '<img src="images/disheartened.png" />',
						'x)' => '<img src="images/ecstatic.png" />',
						':D' => '<img src="images/great.png" />',
						':P' => '<img src="images/just-like-that.png" />',
						':@' => '<img src="images/kill-u.png" />',
						':x' => '<img src="images/mouthshut.png" />',
						':)' => '<img src="images/nice.png" />',
						'D:' => '<img src="images/omg.png" />',
						':(' => '<img src="images/sad.png" />',
						';)' => '<img src="images/wink.png" />',

						);
			return(strtr($string, $smileys)); //Return the changed string
	}
	
	function string_shorten($text, $char) {
	    $text = substr($text, 0, $char); //First chop the string to the given character length
	    if(substr($text, 0, strrpos($text, ' '))!='') $text = substr($text, 0, strrpos($text, ' ')); //If there exists any space just before the end of the chopped string take upto that portion only.
	    //In this way we remove any incomplete word from the paragraph
	    $text = $text.'...'; //Add continuation ... sign
	    return $text; //Return the value
	}
	
	function checkLevel($i) {
	   $levels = array("Normal", "Moderator", "Admin");
	   return $levels[$i];
	}
	
	
}


?>