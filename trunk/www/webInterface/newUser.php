<?php 

session_start();



?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>GOD - Game On Devices - new user?</title>
</head>
<body>
  <h1>GOD - Game On Devices</h1>
  <h2>new user? Let's try!</h2>
  <?php

  function checkIfExist($db, $email) {
  	if (isset($db[$email])) {
  		return true;
  	} else {
  		return false;
  	}
  }
  function createNew($db, $email, $password) {
  	if (!isset($db[$email])) {
  		$db[$email] = array();
  		$db[$email]['creationDate'] = time();
  		$db[$email]['password'] = $password;
  		$db[$email]['games'] = array();
  	}
  	return $db;
  }

  if (isset($_POST['submit'])) {
  	include '../db_json/db_json_lib.php';
  	$db = loadDb('../db_json/db.json', true);
  	if (checkIfExist($db, $_POST['email'])) {
  		echo 'This account already exists.';
  	} else {
  		$db = createNew($db, $_POST['email'], $_POST['password']);
  		saveDb($db, '../db_json/db.json');
  		echo 'Your user has been created.<br />';
		echo 'Now login to <a href="manageGames.php">manage your games</a>';
  	}
  } else {
  	?>
  <form action="" method="post">
    <table>
      <tr>
        <th><label for="email">Email</label></th>
        <td><input type="text" name="email" value="" /></td>
      </tr>
      <tr>
        <th><label for="password">Password</label></th>
        <td><input type="password" name="password" value="" /></td>
      </tr>
      <tr>
        <th>&nbsp;</th>
        <td><input type="submit" name="submit" value="subscribe" /></td>
      </tr>
    </table>
  </form>
  <?php } ?>
</body>
</html>
