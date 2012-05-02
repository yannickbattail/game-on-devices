<?php

session_start();

include 'gameInterface/Response.class.php';

function getInputParams() {
	$params = array();
	$in = $_REQUEST;
	if (isset($_REQUEST['json_data'])) {
		$in = json_decode($_REQUEST['json_data'], true);
	}
	$params['authKey'] = getParam($in, 'authKey');
	$params['question'] = getParam($in, 'question');
	if (isset($in['format']) && !empty($in['format'])) {
		$params['format'] = $in['format'];
	} else {
		$params['format'] = 'text';
	}
	return $params;
}

function getParam($in, $param) {
	if (empty($in[$param])) {
		throw new Exception('Missing parameter '.$param, 421);
	}
	return $in[$param];
}

function checkAuthKey($authKey) {
	// other check .......
	if ($authKey != $_SESSION['authKey']) {
		throw new Exception('Wrong authKey.', 401);
	}
}

function getUserEnv($authKey) {
	$userEnv['email'] = $_SESSION['email'];
	$userEnv['pseudoInGame'] = $_SESSION['pseudoInGame'];
	$userEnv['game'] = $_SESSION['game'];
	// maybe get UserEnv in DB ..... or else
	return $userEnv;
}

function gameInterface($question) {
	// synonymes
	$syn = array(
	'originalText' => $question,
	'splitedText' => explode(' ', $question),
	'synonyms' => array('word1' => array(),'word2' => array()),
	);

	return $syn;
}

/**
 *
 * Enter description here ...
 * @param array $userEnv
 * @param array $texts
 * @return Response
 */
function execGame(array $userEnv, array $texts) {
	// load game
	// include();
	// $game = new class...();
	//$response = $game->do($userEnv, $texts);
	$response = new Response();
	$response->message = 'bonjour '.$userEnv['pseudoInGame'].' ('.$userEnv['email'].') bienvenue dans le jeux '.$userEnv['game']
	.'. vous m avez dit: '.$texts['originalText'];
	$response->status = 200;
	$response->info = 'info';
	return $response;
}

try {
	$params = getInputParams();
	checkAuthKey($params['authKey']);
	$userEnv = getUserEnv($params['authKey']);
	$texts = gameInterface($params['question']);
	$response = execGame($userEnv, $texts);

	if ($params['format'] == 'json') {
		header("Content-Type: application/json");
		echo json_encode($response);
	} else if ($params['format'] == 'html') {
		header("Content-Type: text/html");
		echo '<html><head><title>GOD</title></head><body><div class="status">'.$response->status.'</div><div class="message">'.$response->message.'</div><div class="info">'.$response->info.'</div></body>';
	} else {
		header("Content-Type: text/plain");
		echo $response->message;
	}
} catch (Exception $e) {
	header("HTTP/1.1 500 Internal Server Error");
	echo $e->getMessage();
}
