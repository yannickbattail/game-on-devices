<?php

session_start();



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>GOD - Game On Devices - new user?</title>
</head>
<body>
  <h1>GOD - Game On Devices</h1>
  <h2>Manage your games.</h2>
  <?php

  function auth($db, $email, $password) {
  	if (isset($db[$email]) && isset($db[$email]['password']) && ($password == $db[$email]['password'])) {
  		return true;
  	} else {
  		return false;
  	}
  }

  include '../db_json/db_json_lib.php';
  $db = loadDb('../db_json/db.json', true);
  if (isset($_POST['submit']) && auth($db, $_POST['email'], $_POST['password'])) {
  	$_SESSION['email'] = $_POST['email'];
  }
  if (isset($_SESSION['email']) && $_SESSION['email']) {
  	if (isset($_POST['newGame']) && isset($_POST['game']) && $_POST['game'] && isset($_POST['pseudoInGame']) && $_POST['pseudoInGame']) {
  		$email = $_SESSION['email'];
  		$game = $_POST['game'];
  		$pseudoInGame = $_POST['pseudoInGame'];
  		if (isset($db[$email]['games'][$game]) && isset($db[$email]['games'][$game][$pseudoInGame])) {
  			echo '<div>Pseudo in this game already exist.</div>';
  		} else {
  			$db[$email]['games'][$game][$pseudoInGame] = array();
  			$db[$email]['games'][$game][$pseudoInGame]['creationDate'] = time();
  			$db[$email]['games'][$game][$pseudoInGame]['data'] = array();
  			saveDb($db, '../db_json/db.json');
  			echo '<div>Pseudo '.$pseudoInGame.' created in game '.$game.'.</div>';
  		}
  	}
  	echo '<table class="gameList" border="1">';
	echo '<tr><th>Games</th><th>Pseudo</th></tr>';
  	foreach ($db[$_SESSION['email']]['games'] as $gameK => $valueData) {
  		echo '<tr><td>'.$gameK.'</td><td><ul>';
  		foreach ($valueData as $pseudoInGame => $v) {
  			echo '<li>'.$pseudoInGame.'</li>';
  		}
  		echo '</ul></td></tr>';
  	}
  	echo '</table>';
  	?>
  <br />
  <form action="" method="post">
    <table>
      <tr>
        <th><label for="game">Game</label></th>
        <td><select name="game">
        <?php
        $iterator = new DirectoryIterator('../games/');
        foreach ($iterator as $fileinfo) {
        	if ($fileinfo->isDir() && !$fileinfo->isDot() && ($fileinfo->getFilename() != '.svn')) {
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
        <td><input type="submit" name="newGame" value="create pseudo" /></td>
      </tr>
    </table>
  </form>
  <?php
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
