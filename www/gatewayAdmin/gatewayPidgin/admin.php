<?php

session_start();
if (!isset($_SESSION['email']) || !$_SESSION['email']) {
	header("HTTP/1.1 401 Unauthorized");
	echo '<html>
<head>
<title>GOD - Game On Devices - Access denied</title>
</head>
<body>
Access denied, Please authenticate first. <a href="../../webInterface.php">here</a>
</body>
</html>';
	exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>GOD - Game On Devices - Chat gateway with pidgin</title>
</head>
<body>
  <h1>GOD - Chat gateway with pidgin</h1>
  <h2>Register a chat address.</h2>
  <?php

  include '../../db_json/db_json_lib.php';
  $db = loadDb('imAddressDB.json', true);
  //print_r($_POST)
  if (isset($_POST['submit'])
  && isset($_POST['game']) && !empty($_POST['game'])
  && isset($_POST['pseudoInGame']) && !empty($_POST['pseudoInGame'])
  && isset($_POST['imAddress']) && !empty($_POST['imAddress'])) {
      $dbUser = loadDb('../../db_json/db.json');
      $email = $_SESSION['email'];
      $password = $dbUser[$email]['password'];
      $game = trim($_POST['game']);
      $pseudoInGame = trim($_POST['pseudoInGame']);
      $imAddress = trim($_POST['imAddress']);
      $db[$imAddress] = array(
          "email" => $email,
          "password" => $password,
          "game" => $game,
          "pseudoInGame" => $pseudoInGame
      );
 		saveDb($db, 'imAddressDB.json');
 		echo '<div>Your address has been registered.</div>';
  }

  ?>
  <form action="" method="post">
    <table>
      <tr>
        <th><label for="game">Game</label></th>
        <td><select name="game">
        <?php
        $iterator = new DirectoryIterator('../../games/');
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
        <th><label for="imAddress">chat address</label></th>
        <td><input type="text" name="imAddress" value="" /></td>
      </tr>
      <tr>
        <th>&nbsp;</th>
        <td><input type="submit" name="submit" value="register chat address" /></td>
      </tr>
    </table>
  </form>
  <div>Chat address registered.</div>
  <table class="enigmeList" border="1">
  <?php
  foreach ($db as $imAddress => $imAddressDB) {
  	if ($imAddressDB['email'] == $_SESSION['email']) {
  		echo '<tr><td>'.$imAddress.'</td><td>';
  		echo '<table class="enigmeList" border="1">';
  		echo '<tr><th>game</th><td>'.$imAddressDB['game'].'</td></tr>';
  		echo '<tr><th>pseudoInGame</th><td>'.$imAddressDB['pseudoInGame'].'</td></tr>';
  		echo '</table>';
  		echo '</td></tr>';
  	}
  }
  ?>
  </table>
  <div>
    <a href="../../webInterface/manageGames.php">Go back to game managment.</a>
  </div>
</body>
</html>
