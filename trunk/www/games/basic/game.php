<?php

require_once('Synonym.class.php');
require_once('room_callbacks.php');

$rooms = loadRooms('rooms_generated.json');

runGame($rooms);

function printError()
{
	switch (json_last_error()) {
        case JSON_ERROR_NONE:
            echo ' - No errors';
        break;
        case JSON_ERROR_DEPTH:
            echo ' - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            echo ' - Syntax error, malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            echo ' - Unknown error';
        break;
    }

	echo PHP_EOL;
}

function loadRooms($jsonFile)
{
	$contents = file_get_contents($jsonFile);
	$rms = json_decode($contents, true);
	if ($rms == NULL)
		printError();
	return $rms;
}

function runGame($rooms)
{
	$stdin = fopen('php://stdin', 'r');
	$currentRoom = $rooms[0];
	while (!feof($stdin))
	{
		echo "You are in room ".$currentRoom["name"]."\r\n";
		echo " >";
		$line = fgets($stdin);
		$dafuqDidIJustRead = explode(" ", $line);
		$synVerbEntered = Synonym::getSynonymsVerb($dafuqDidIJustRead[0]);
		$synNounsEntered = array();
		for ($i = 1; $i < count($dafuqDidIJustRead); $i++)
		{
			$synNounsEntered[$i] = Synonym::getSynonymsNoun($dafuqDidIJustRead[$i]);
		}
		$callback = NULL;
		$nouns = array();
		foreach ($currentRoom["actions"] as $act)
		{
			if ($callback != NULL)
				break;
			foreach ($synVerbEntered as $verb)
			{
				if ($verb == $act["verb"])
				{
					$callback = $act["callback"];
					echo "Found action verb $verb in synonyms:";
					print_r($synVerbEntered);
					foreach ($synNounsEntered as $nounFamily)
					{
						foreach ($act["nouns"] as $actNoun)
						{
							if (in_array($actNoun, $nounFamily))
							{
								echo "Found $actNoun in synonyms:";
								print_r($nounFamily);
								$nouns[] = $actNoun;
								break;
							}
						}
					}
				}
			}
		}
		if ($callback != NULL)
			$callback($nouns);
	}
}
