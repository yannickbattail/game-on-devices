<?php

function loadDb($db_filename = 'db_json/db.json', $createNew = FALSE) {
	$f = @file_get_contents($db_filename);
	if ($f === FALSE) {
		if ($createNew) {
			saveDb(array(), $db_filename);
		} else {
			throw new Exception('cannot open database file '.$db_filename);
		}
	}
	$db = json_decode($f, true);
	$error = json_last_error();
	if ($error != JSON_ERROR_NONE) {
		switch ($error) {
			case JSON_ERROR_NONE:
				throw new Exception( ' - No errors');
				break;
			case JSON_ERROR_DEPTH:
				throw new Exception( ' - Maximum stack depth exceeded');
				break;
			case JSON_ERROR_STATE_MISMATCH:
				throw new Exception( ' - Underflow or the modes mismatch');
				break;
			case JSON_ERROR_CTRL_CHAR:
				throw new Exception( ' - Unexpected control character found');
				break;
			case JSON_ERROR_SYNTAX:
				throw new Exception( ' - Syntax error, malformed JSON');
				break;
			case JSON_ERROR_UTF8:
				throw new Exception( ' - Malformed UTF-8 characters, possibly incorrectly encoded');
				break;
			default:
				throw new Exception( ' - Unknown error');
				break;
		}
	}
	return $db;
}

function saveDb($db, $db_filename = 'db_json/db.json') {
	$f = json_encode($db);
	$err = @file_put_contents($db_filename, $f);
	if ($err === FALSE) {
		throw new Exception('cannot write database file '.$db_filename);
	}
}
