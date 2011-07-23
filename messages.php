<?php 
session_start(); //ESSENTIAL
include('includes/user.class.php'); 
$user = new User();
if(isset($_GET['read'])){$message_id = mysql_real_escape_string($_GET['read']);}?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>User.Class - Messages</title>
<link rel="stylesheet" type="text/css" href="css/reset.css" />
<link rel="stylesheet" type="text/css" href="css/main.css" />
<!--WEB FONTS -->
<link href='http://fonts.googleapis.com/css?family=Lato:100&v2' rel='stylesheet' type='text/css' />
<!--&&&&&&&&&-->
</head>
<body>
	<div id="container">
		<?php if($user->isLoggedIn()) { $login = $user->userInfo($_SESSION['userName']); if(!isset($_GET['read'])) {?>
		<h1>Your Messages</h1>
		<?php if(isset($_GET['delete'])) { $user->deleteMessage(mysql_real_escape_string($_GET['delete']))?>
		<div id="content">
			<p class="description" style="margin-bottom: 20px">
				Message deleted. <a href="messages.php" style="font-weight: bold;">Go back.</a>
			</p>
		</div>
		<?php die(); } ?>
		<?php if(isset($_GET['unread'])) { $user->setMessageUnread(mysql_real_escape_string($_GET['unread']))?>
		<div id="content">
			<p class="description" style="margin-bottom: 20px">
				Message marked as unread. <a href="messages.php" style="font-weight: bold;">Go back.</a>
			</p>
		</div>
		<?php die(); } ?>
		<a href="send.php"><img src='images/new.png'style='float: right; margin-left: 5px; padding: 17px 17px 17px 0px'/></a>
		<a href="members.php"><img src='images/home.png'style='float: right; margin-left: 5px; padding: 17px'/></a>
		<?php if($messages = $user->displayMessages('list', $login['id'])) {
					$count = 1;
					while($row = mysql_fetch_array($messages)) {?>
						<a href="messages.php?read=<?php echo $row['message_id'];?>">
						<div class="message" <?php if($count == 1) { ?> style="border-top: 1px solid #b2b2b2;" <?php } ?>>
							<div class='text'>
								<?php $from = $user->userInfoId($row['message_from']); ?>
								<h2><?php echo ucfirst(strtolower($from['username'])) . ': ' . $row['message_subject']; if($row['message_read'] == FALSE) { echo ' <b>(Unread)</b>'; }?></h2>
								<p><?php echo $user->string_shorten($row['message'], 120); $count++ ?></p>
							</div>
						</div>	
						</a>
		<?php }} } else {?>
			<?php if($message = $user->displayMessages('read', $login['id'], $message_id)) {
				$message = mysql_fetch_array($message);
				if($message['message_to'] == $login['id']) { $user->setMessageRead($message_id);?>
					<h1><?php echo $message['message_subject'];  ?></h1>
					<a href="messages.php"><img src='images/mail.png'style='float: right; margin-left: 5px; padding: 17px'/></a>
					<div class="messageText">
						<?php $from = $user->userInfoId($message['message_from']);?>
						<p><b>From:</b> <?php echo ucfirst(strtolower($from['username'])); ?></p>
						<p><b>To:</b> You</p>
						<p><b>Message:</b></p>
						<div class="theMessage">
						<?php echo $user->smiley(nl2br($message['message'])); ?>
					</div>
					</div>
				<?php }?>
				<?php } ?>
				<a href="send.php?id=<?php echo $message['message_from']; ?>&subject=<?php echo urlencode($message['message_subject']); ?>" class="reply">Reply to this message</a><br /><br />
				<a href="messages.php?delete=<?php echo $message['message_id']; ?>" class="reply">Delete this message</a><br /><br />
				<a href="messages.php?unread=<?php echo $message['message_id']; ?>" class="reply">Mark as unread</a>
		<?php }} else { ?>
			<div id="content">
				<p class="description" style="margin-bottom: 20px">
					Error. Please log in <a href="login.php" style="font-weight: bold;">here.</a>
				</p>
			</div>
		<?php } ?>
	</div>
</body>
</html>
