<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>GOD - Game On Devices - new user?</title>
<style type="text/css">
</style>
<script type="text/javascript">

</script>
</head>
<body>
  <h1>GOD - Game On Devices</h1>
  <h2>
    Text adventure engine with gateway on chat (jabber, google Talk, facebook chat), email, sms, unix shell, smart phone (Android) <br /> and plugable game interface
  </h2>
  <div>
    You want to try? <br /> <a href="./webInterface/newUser.php">Create an account!</a> <br /> <a href="./webInterface/manageGames.php">choose a game and create a pseudo.</a> <br />
    <a href="./webInterface/webPlayer.php">Let's play here!</a> <a href="./webInterface/webConsole.php">or here for a geek version.</a>
  </div>
  <br />
  <div>
    games available for now?
    <ul>
    <?php
    $iterator = new DirectoryIterator('./games/');
    foreach ($iterator as $fileinfo) {
    	if ($fileinfo->isDir() && !$fileinfo->isDot() && ($fileinfo->getFilename() != '.svn')) {
    		$classname = ucfirst($fileinfo->getFilename());
    		if (file_exists('games/'.$fileinfo->getFilename().'/'.$classname.'.class.php')) {
    			echo '<li><b>'.htmlentities(file_get_contents('games/'.$fileinfo->getFilename().'/name.txt')).':</b></li>'
    			.htmlentities(file_get_contents('games/'.$fileinfo->getFilename().'/description.txt'));
    		}
    	}
    }
    ?>
    </ul>
  </div>
  <div>GOD - Game On Devices project at <a href="http://code.google.com/p/game-on-devices/">google code</a></div>
</body>
</html>
