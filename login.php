<?php 
session_start(); //ESSENTIAL
include('includes/user.class.php'); 
$user = new User();
if($user->isLoggedIn()) { $user->redirectTo('members'); }
if(isset($_POST['username'])) {
	if($user->verify($_POST['username'], $_POST['password'])) {
		$user->setLoggedIn($_POST['username'], $_POST['password']); 
		$user->redirectTo('members'); 
	} else { $message = 'Error. Incorrect username or password.'; if(isset($_SESSION['try'])) { $_SESSION['try']++; } else { $_SESSION['try'] = 1;}}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>User.class - Login</title>
<link rel="stylesheet" type="text/css" href="css/reset.css" />
<link rel="stylesheet" type="text/css" href="css/main.css" />
<!--WEB FONTS -->
<link href='http://fonts.googleapis.com/css?family=Lato:100&v2' rel='stylesheet' type='text/css'>
<!--&&&&&&&&&-->
</head>
<body>
	<div id="container">
		<h1>Login</h1>
		<div class="Sendtext">
		<form method="post">
			<?php if(isset($message)) { echo '<p>' . $message . '</p>'; } if(!isset($_SESSION['try']) || $_SESSION['try'] < 3) {?>
			<p><b>Username:</b></p>
			<input type="text" name="username" style=" margin-bottom: 10px;"/>
			<p><b>Password:</b></p>
			<input type="password" name="password" />
			</div>
			<input type="submit" value="login" class='button'/>
			<?php } else { ?>
			<p><b>You have attempted this password too many times. Please try again later.</b></p>
			<?php } ?>
		</form>
		</div>
	</div>
</body>
</html>
