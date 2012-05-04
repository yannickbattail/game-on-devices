<?php

session_start();

include 'db_json/db_json_lib.php';

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
	foreach ($db as $key => $user) {
		if (($user['email'] == $email) && ($user['password'] == $password) && ($user['game'] == $game) && ($user['pseudoInGame'] == $pseudoInGame)) {
			return $key;
		}
	}
	return false;
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
		$_SESSION['email'] = $params['email'];
		$_SESSION['pseudoInGame'] = $params['pseudoInGame'];
		$_SESSION['game'] = $params['game'];
		$_SESSION['authKey'] = $authKey;
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
