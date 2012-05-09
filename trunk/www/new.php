<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>GOD - Game On Devices - new user?</title>
</head>
<body>
  <h1>GOD - Game On Devices</h1>
  <h2>new user? Let's try!</h2>
  <?php

  function checkIfExist($db, $email, $game, $pseudoInGame) {
  	if (isset($db[$email]) && isset($db[$email]['games'][$game]) && isset($db[$email]['games'][$game][$pseudoInGame])) {
  		return true;
  	} else {
  		return false;
  	}
  }
  function createNew($db, $email, $password, $game, $pseudoInGame) {
  	if (!isset($db[$email])) {
  		$db[$email] = array();
  		$db[$email]['creationDate'] = time();
  		$db[$email]['password'] = $password;
  		$db[$email]['games'] = array();
  	}
  	if (!isset($db[$email][$game])) {
  		$db[$email]['games'][$game] = array();
  	}
  	$db[$email]['games'][$game][$pseudoInGame] = array();
  	$db[$email]['games'][$game][$pseudoInGame]['creationDate'] = time();
  	$db[$email]['games'][$game][$pseudoInGame]['data'] = array();
  	return $db;
  }

  if (isset($_POST['submit'])) {
  	include 'db_json/db_json_lib.php';
  	$db = loadDb('db_json/db.json', true);
  	if (checkIfExist($db, $_POST['email'], $_POST['game'], $_POST['pseudoInGame'])) {
  		echo 'Your already subscribe to this game with this pseudo.';
  	} else {
  		$db = createNew($db, $_POST['email'], $_POST['password'], $_POST['game'], $_POST['pseudoInGame']);
  		saveDb($db, 'db_json/db.json');
  		echo 'Your user has been created.<br />';
		echo 'Now you can use the web player <a href="new.php">Let\'s play!</a>';
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
        <th><label for="game">Game</label></th>
        <td><select name="game">
        <?php
        $iterator = new DirectoryIterator('./games/');
        foreach ($iterator as $fileinfo) {
        	if ($fileinfo->isDir() && !$fileinfo->isDot()) {
        		echo '<option>'.$fileinfo->getFilename().'</option>';
        	}
        }
        ?>
        </select></td>
      </tr>
      <tr>
        <th><label for="pseudoInGame">Pseudo in game</label></th>
        <td><input type="text" name="pseudoInGame" value="" /></td>
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
