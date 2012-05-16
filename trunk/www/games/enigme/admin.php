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

  include '../../db_json/db_json_lib.php';
  $db = loadDb('enigmeDB.json', true);
  //print_r($_POST)
  if (isset($_POST['submit'])
  && isset($_POST['password']) && ($_POST['password'] == 'je ne suis pas un robot')
  && isset($_POST['categorie']) && !empty($_POST['categorie'])
  && isset($_POST['text']) && !empty($_POST['text'])
  && isset($_POST['explication']) && !empty($_POST['explication'])
  && isset($_POST['reponses']) && !empty($_POST['reponses'])) {
 		$categorie = trim($_POST['categorie']);
 		$text = trim($_POST['text']);
 		$explication = trim($_POST['explication']);
 		$reponses = trim($_POST['reponses']);
 		$reponses = explode(',', $reponses);
 		$db[$categorie][] = array(
            "text" => $text,
            "explication" => $explication,
            "reponse" => $reponses[0],
            "reponses" => $reponses
 		);
 		saveDb($db, 'enigmeDB.json');
 		echo '<div>Enigme ajout&eacute;e.</div>';
  }

  ?>
  <form action="" method="post">
    <table>
      <tr>
        <th><label for="categorie">categorie</label></th>
        <td><select name="categorie"><option>dur</option>
            <option>moyen</option>
            <option>facile</option>
        </select></td>
      </tr>
      <tr>
        <th><label for="text">text de l enigme</label></th>
        <td><input type="text" name="text" value="" /></td>
      </tr>
      <tr>
        <th><label for="explication">explication</label></th>
        <td><input type="text" name="explication" value="" /></td>
      </tr>
      <tr>
        <th><label for="reponses">reponses (separees par des ,)</label></th>
        <td><input type="text" name="reponses" value="" /></td>
      </tr>
      <tr>
        <th><label for="password">Password (je ne suis pas un robot)</label></th>
        <td><input type="password" name="password" value="" /></td>
      </tr>
      <tr>
        <th>&nbsp;</th>
        <td><input type="submit" name="submit" value="ajouter enigme" /></td>
      </tr>
    </table>
  </form>
  <?php
  foreach ($db as $categorieName => $categorie) {
  	echo '<div>Categorie: '.$categorieName.'</div>';
  	echo '<table class="enigmeList" border="1">';
  	foreach ($categorie as $enigme) {
  		echo '<tr><td>';
	  	echo '<table class="enigmeList" border="1">';
  		echo '<tr><th>text</th><td>'.$enigme['text'].'</td></tr>';
  		echo '<tr><th>explication</th><td>'.$enigme['explication'].'</td></tr>';
  		echo '<tr><th>reponse</th><td>'.$enigme['reponse'].'</td></tr>';
  		echo '<tr><th>reponses</th><td>'.join(',',$enigme['reponses']).'</td></tr>';
	  	echo '</table>';
  		echo '</td></tr>';
  	}
  	echo '</table>';
  }
  ?>
</body>
</html>
