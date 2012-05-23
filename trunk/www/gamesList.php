<?php


function outputInFormat(array $games, $format) {
	if ($format == 'json') {
		header("Content-Type: application/json");
		return json_encode($games);
	} else if ($format == 'html') {
		header("Content-Type: text/html");
		$ret = '';
		foreach ($games as $game) {
			$ret .= '<option value="'.htmlentities($game['dirName']).'">'.htmlentities($game['name']).'</div>';
		}
		return $ret;
	} else {
		header("Content-Type: text/plain");
		$ret = '';
		foreach ($games as $game) {
			$ret .= $game['name'].PHP_EOL;
		}
		return $ret;
	}
}

try {
	$games = array();
	$iterator = new DirectoryIterator('./games/');
	foreach ($iterator as $fileinfo) {
		if ($fileinfo->isDir() && !$fileinfo->isDot() && ($fileinfo->getFilename() != '.svn')) {
			$classname = ucfirst($fileinfo->getFilename());
			if (file_exists('games/'.$fileinfo->getFilename().'/'.$classname.'.class.php')) {
				$games[ucfirst($fileinfo->getFilename())] = array(
					'dirName' => $fileinfo->getFilename(),
					'name' => file_get_contents('games/'.$fileinfo->getFilename().'/name.txt'),
					'description' => file_get_contents('games/'.$fileinfo->getFilename().'/description.txt')
				);
			}
		}
	}
	$format = isset($_REQUEST['format'])?$_REQUEST['format']:'';
	print(outputInFormat($games, $format));
} catch (Exception $e) {
	header("HTTP/1.1 500 Internal Server Error");
	print($e->getMessage());
}
