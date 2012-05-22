<?php

include_once 'db_json/db_json_lib.php';
include_once 'gameInterface/UserEnv.class.php';
include_once 'gameInterface/Question.class.php';
include_once 'gameInterface/Response.class.php';
include_once 'gameInterface/QuestionTreatment.class.php';
include_once 'gameInterface/Game.interface.php';

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

function cleanOldSessions(array $db_sessions) {
	foreach ($db_sessions as $sessionK => $session) {
		if ($session['lastAccess'] < (time() - (60*60))) { // older than 1 hour
			unset($db_sessions[$sessionK]);
		}
	}
	return $db_sessions;
}

function checkAuthKey($authKey) {
	$db_sessions = loadDb('db_json/db_sessions.json');
	if (!isset($db_sessions[$authKey])) {
		throw new Exception('Wrong authKey.', 401);
	}
	if ($db_sessions[$authKey]['lastAccess'] < (time() - 3600)) {
		throw new Exception('Authentication timeout. Must re-login.', 401);
	}
	$ue = new UserEnv();
	$ue->email = $db_sessions[$authKey]['email'];
	$ue->pseudoInGame = $db_sessions[$authKey]['pseudoInGame'];
	$ue->game = $db_sessions[$authKey]['game'];
	$ue->data = $db_sessions[$authKey]['data'];
	$db_sessions[$authKey]['lastAccess'] = time();
	$db_sessions = cleanOldSessions($db_sessions);
	saveDb($db_sessions, 'db_json/db_sessions.json');
	return $ue;
}

function getUserEnv($authKey) {
	$db_sessions = loadDb('db_json/db_sessions.json');
	$ue= new UserEnv();
	$ue->email = $db_sessions[$authKey]['email'];
	$ue->pseudoInGame = $db_sessions[$authKey]['pseudoInGame'];
	$ue->game = $db_sessions[$authKey]['game'];
	return $ue;
}

function gameInterface($question) {
	return QuestionTreatment::treat($question);
}

/**
 *
 * Enter description here ...
 * @param UserEnv $userEnv
 * @param Question $question
 * @return Response
 */
function execGame(UserEnv $userEnv, Question $question) {
	// load game
	$classname = ucfirst($userEnv->game);
	include 'games/'.$userEnv->game.'/'.$classname.'.class.php';
	$game = new $classname();
	$response = $game->speak($userEnv, $question);

	return $response;
}

function outputInFormat(Response $response, $format) {
	if ($format == 'json') {
		header("Content-Type: application/json");
		return json_encode($response);
	} else if ($format == 'html') {
		header("Content-Type: text/html");
		$ret = '<html><head><title>GOD</title></head><body>';
		$ret .= '<div class="status">'.$response->status.'</div>';
		$ret .= '<div class="message">'.$response->message.'</div>';
		$ret .= '<div class="info">'.$response->info.'</div>';
		$ret .= '<div class="choices">';
		foreach ($response->choices as $choice) {
			$ret .= '<div class="choice">'.$choice.'</div>';
		}
		$ret .= '</div>';
		$ret .= '</body>';
		return $ret;
	} else {
		header("Content-Type: text/plain");
		return $response->message;
	}
}

try {
	$params = getInputParams();
	checkAuthKey($params['authKey']);
	$userEnv = getUserEnv($params['authKey']);
	$question = gameInterface($params['question']);
	ob_start();
	$response = execGame($userEnv, $question);
	$response->info .= ob_get_clean();
	print(outputInFormat($response, $params['format']));
} catch (Exception $e) {
	header("HTTP/1.1 500 Internal Server Error");
	print($e->getMessage());
}
