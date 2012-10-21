<?php

session_start();

require_once('auth.php');

// to make sure we're on a dev server
if(file_get_contents('http://localhost/where.php') != 'kant')
	exit(1);

if(isset($_POST['user_id']))
{
	set_session_user_id($_POST['user_id']);
	
	header('Location: userctrl.php');
	
	exit(0);
}

$userId = get_session_user_id();

?>
<html>
	<head>
		<title>USERCTRL</title>
	</head>
	<body>
		<form method="post" action="userctrl.php">
			<strong>User ID:</strong>
			<input type="text" name="user_id" value="<?php  echo $userId?>" /><br />
			<input type="submit" />
		</form>
	</body>
</html>
			