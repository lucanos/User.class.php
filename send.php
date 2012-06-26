<?php 

session_start();

include( 'includes/user.class.php' );

$user = new User();

if( isset( $_GET['read'] ) ){
  $message_id = mysql_real_escape_string( $_GET['read'] );
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>User.Class - Send Message</title>
<link rel="stylesheet" type="text/css" href="css/reset.css" />
<link rel="stylesheet" type="text/css" href="css/main.css" />
<!--WEB FONTS -->
<link href="http://fonts.googleapis.com/css?family=Lato:100&v2" rel="stylesheet" type="text/css" />
<!--&&&&&&&&&-->
</head>
<body>
<div id="container">
  <h1>Send a new message</h1>
<?php
if( $user->isLoggedIn() ){
  if( !isset( $_POST['to'] ) ){
    if( isset( $_GET['id'] ) ){
      $to = $user->userInfoId( $_GET['id'] );
    }
?>
		<a href="messages.php"><img src="images/mail.png" style="float:right;margin-left:5px;padding:17px" /></a>
		<div class="Sendtext">
			<form action="send.php" method="post">
        <p><b>To:</b> <input type="text" name="to" value="<?php if(isset($to)) { echo ucfirst(strtolower($to['username'])); }?>" /></p>
        <p><b>Subject:</b> <input type="text" name="subject" value="<?php if(isset($_GET['subject'])) { echo 'Re:' . $_GET['subject']; } ?>"/></p>
        <p><b>Message:</b></p>
        <textarea name="message" type="text"></textarea>
        </div>
        <input type="submit" value="send" class='button'/>
			</form>
		</div>
<?php
  }else{
    $to = $user->userInfo( $_POST['to'] ); 
    $from = $user->userInfo( $_SESSION['userName'] );
    if( $user->exists( mysql_real_escape_string( $_POST['to'] ) ) && $user->sendMessage( $to['id'] , $from['id'] , mysql_real_escape_string( $_POST['subject'] ) , $_POST['message'] ) ){
?>
    <div class="Sendtext">Sent! Click <b><a href="messages.php">here</a></b> to return</div>
<?php
    }else{
?>
    <div class="Sendtext">Error. User doesnt exist.</div>
<?php
    }
  }
}
?>
	</div>
</body>
</html>
