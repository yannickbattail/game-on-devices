<?php

include_once 'db_json/db_json_lib.php';

function debugLog($string, $file, $line) {
	file_put_contents('log.log', date(DATE_W3C).' '.$file.':'.$line.' '.$string.PHP_EOL, FILE_APPEND);
}

function getInputParams() {
	$params = array();
	$in = $_REQUEST;
	if (isset($_REQUEST['json_data'])) {
		$in = json_decode($_REQUEST['json_data'], true);
	}
	$params['email'] = getParam($in, 'email');
	$params['password'] = getParam($in, 'password');
	$params['pseudoInGame'] = getParam($in, 'pseudoInGame');
	$params['game'] = getParam($in, 'game');
	if (isset($in['format']) && !empty($in['format'])) {
		$params['format'] = $in['format'];
	} else {
		$params['format'] = 'text';
	}
	return $params;
}

function getParam($in, $param) {
	if (empty($in[$param])) {
		throw new AuthenticationException('Missing parameter '.$param, 421);
	}
	return $in[$param];
}

function checkAuthentication($email, $password, $game, $pseudoInGame) {
	$db = loadDb();
	if (!isset($db[$email])) {
		throw new AuthenticationException('Wrong email.', 421);
	}
	if ($db[$email]['password'] != $password) {
		throw new AuthenticationException('Wrong password.', 421);
	}
	if (!isset($db[$email]['games'][$game])) {
		throw new AuthenticationException('Please subscribe to this game.', 421);
	}
	if (!isset($db[$email]['games'][$game][$pseudoInGame])) {
		throw new AuthenticationException('Wrong pseudo for the game: '.$game, 421);
	}
	return uniqid();
}

function authentication($user, $pass, $game, $pseudoInGame) {
	if (is_dir('./games/'.$game) === false) {
		throw new AuthenticationException('Game '.$game.' does not exists.', 421);
	}
	return checkAuthentication($user, $pass, $game, $pseudoInGame);
}

class AuthenticationException extends Exception {};

try {
	$params = getInputParams();
	$authKey = authentication($params['email'], $params['password'], $params['game'], $params['pseudoInGame']);

	if ($authKey) {
		$db_sessions = loadDb('db_json/db_sessions.json');
		$db_sessions[$authKey]['email'] = $params['email'];
		$db_sessions[$authKey]['pseudoInGame'] = $params['pseudoInGame'];
		$db_sessions[$authKey]['game'] = $params['game'];
		$db_sessions[$authKey]['data'] = array();
		$db_sessions[$authKey]['lastLogin'] = time();
		$db_sessions[$authKey]['lastAccess'] = time();
		saveDb($db_sessions, 'db_json/db_sessions.json');
		if ($params['format'] == 'json') {
			header("Content-Type: application/json");
			echo json_encode(array('status' => 200, 'message' => 'authentication ok', 'authKey' => $authKey));
		} else if ($params['format'] == 'html') {
			header("Content-Type: text/html");
			echo '<html><head><title>GOD</title></head><body><div class="status">200</div><div class="message">authentication ok</div><div class="authKey">'.$authKey.'</div></body>';
		} else {
			header("Content-Type: text/plain");
			echo $authKey;
		}
	} else {
		header("HTTP/1.1 401 Unauthorized");
		header("Content-Type: text/plain");
		echo 'Authentication failed.';
	}
} catch (AuthenticationException $e) {
	header("HTTP/1.1 412 Precondition Failed");
	echo 'Authentication error: '.$e->getMessage();
} catch (Exception $e) {
	header("HTTP/1.1 500 Internal Server Error");
	echo $e;
}
