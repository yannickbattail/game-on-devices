<?php

Class Labyrinth implements Game {
	/**
	 *
	 * Enter description here ...
	 * @param UserEnv $userEnv
	 * @param Question $question
	 * @return Response
	 */
	public function speak(UserEnv $userEnv, Question $question) {
		$response = new Response();
		$db = loadDb('db_json/db.json');
		$ud = $this->getUserGameData($db, $userEnv);
		if (!isset($ud['currentLab'])) {
			$ud['currentLab'] = 1;
			$ud['curX'] = 1;
			$ud['curY'] = 0;
			$ud['prevX'] = 1;
			$ud['prevY'] = 1;
			$response->message .= 'Hi! Wellcome to the Labyrinth game: move through the Labyrinth and find the exit and go to the next level.'.PHP_EOL;
		} else {
			$lab = loadLab($labName);
			$dirX = $ud['prevX'] - $ud['curX'];
			$dirY = $ud['prevY'] - $ud['curY'];
			$newDir = $this->getMove($question, $dirX, $dirY);
			if (!$newDir) {
				$response->message .= 'oups invalid direction'.PHP_EOL;
			} else {
				$newX = $ud['curX'] + $newDir[0];
				$newY = $ud['curY'] + $newDir[1];
				if ($lab[$newX][$newY] == '#') {
					$response->message .= 'You are going in a wall.'.PHP_EOL;
				} else if ($lab[$newX][$newY] == 'x') {
					$response->message .= 'You manage to get out of the Labyrinth. Go on to the next one'.PHP_EOL;
					$ud['currentLab']++;
					$ud['curX'] = 1;
					$ud['curY'] = 0;
					$ud['prevX'] = 1;
					$ud['prevY'] = 1;
				} else {
					$ud['prevX'] = $ud['curX'];
					$ud['prevY'] = $ud['curY'];
					$ud['curX'] = $newX;
					$ud['curY'] = $newY;
					$dirX = $ud['prevX'] - $ud['curX'];
					$dirY = $ud['prevY'] - $ud['curY'];
					if ($lab[$newX+$dirY][$newY-$dirX])
						$response->message .= 'you can go left, '.PHP_EOL;
					if ($lab[$newX-$dirY][$newY+$dirX])
						$response->message .= 'you can go right, '.PHP_EOL;
					if ($lab[$newX+$dirX][$newY-$dirY])
						$response->message .= 'you can go ahead, '.PHP_EOL;
					if ($lab[$newX-$dirX][$newY-$dirY])
						$response->message .= 'you can go back.'.PHP_EOL;
				}
			}
		}
		$response->status = 200;
		$response->info = 'x:'.$ud['curX'].' y:'.$ud['curY'];
		$response->choices[] = 'go right';
		$response->choices[] = 'go left';
		$response->choices[] = 'go ahead';
		$response->choices[] = 'go back';
		$this->saveUserGameData($db, $userEnv, $uGameData);
		return $response;
	}

	private function getUserGameData($db, UserEnv $userEnv) {
		if (isset($db[$userEnv->email])
		&& isset($db[$userEnv->email]['games'][$userEnv->game])
		&& isset($db[$userEnv->email]['games'][$userEnv->game][$userEnv->pseudoInGame])
		&& isset($db[$userEnv->email]['games'][$userEnv->game][$userEnv->pseudoInGame]['data'])) {
			return $db[$userEnv->email]['games'][$userEnv->game][$userEnv->pseudoInGame]['data'];
		} else {
			return null;
		}
	}

	private function saveUserGameData($db, UserEnv $userEnv, $uGameData) {
		$db[$userEnv->email]['games'][$userEnv->game][$userEnv->pseudoInGame]['data'] = $uGameData;
		saveDb($db, 'db_json/db.json');
	}

	private function getMove(Question $question, $dirX, $dirY) {
		if (strcasecmp($question->originalText, 'go left') == 0) {
			return array($dirY, -$dirX);
		}
		if (strcasecmp($question->originalText, 'go left') == 0) {
			return array(-$dirY, $dirX);
		}
		if (strcasecmp($question->originalText, 'go ahead') == 0) {
			return array($dirX, $dirY);
		}
		if (strcasecmp($question->originalText, 'go back') == 0) {
			return array(-$dirX, -$dirY);
		}
		return false;
	}

	private function loadLab($labName) {
		$labFile = file_get_contents('./games/labyrinth/lab/'.$labName.'.txt');
		$lab = array();
		$labFile = explode($labFile, "\n");
		$l = 0;
		foreach ($labFile as $line) {
			for ($i = 0, $i < strlen($line); $i++) {
				$lab[$i][$l] = $line[$i];
			}
			$l++;
		}
		return $lab;
	}
}
