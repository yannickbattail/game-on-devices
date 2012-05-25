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
			$ud['player'] = array('name' => $userEnv->pseudoInGame);
			$ud['curX'] = 1;
			$ud['curY'] = 1;
			$ud['prevX'] = 1;
			$ud['prevY'] = 0;
			$response->message .= 'Hi! Wellcome to the Labyrinth game: move through the Labyrinth and find the exit and go to the next level.'.PHP_EOL;
		} else {
			$lab = $this->loadLab('lab'.$ud['currentLab']);
			$dirX = $ud['curX'] - $ud['prevX'];
			$dirY = $ud['curY'] - $ud['prevY'];
			$newDir = $this->getMove($question, $dirX, $dirY);
			if (($question->originalText == '!') || (strcasecmp($question->originalText, 'info') == 0)) {
				$response->message .= print_r($ud['player'], true);
			} else if (!$newDir) {
				$response->message .= 'oups invalid direction'.PHP_EOL;
			} else {
				$newX = $ud['curX'] + $newDir[0];
				$newY = $ud['curY'] + $newDir[1];
				if ($lab[$newX][$newY] == '#') {
					$response->message .= 'You are going in a wall.'.PHP_EOL;
				} else if ($lab[$newX][$newY] == '@') {
					$lab = $this->loadLab('lab'.($ud['currentLab'] + 1));
					if (!$lab) {
						$response->message .= 'WIN! No more Labyrinth'.PHP_EOL;
					} else {
						$response->message .= 'WIN! You manage to get out of the Labyrinth. Go on to the next one'.PHP_EOL;
						$ud['currentLab']++;
						$ud['curX'] = 1;
						$ud['curY'] = 1;
						$ud['prevX'] = 1;
						$ud['prevY'] = 0;
					}
				} else {
					$ud['prevX'] = $ud['curX'];
					$ud['prevY'] = $ud['curY'];
					$ud['curX'] = $newX;
					$ud['curY'] = $newY;
					if (ctype_alnum($lab[$newX][$newY])) {
						$ud = $this->execAction($ud, $lab, $response);
					} else {
						$response->message .= 'Moved!'.PHP_EOL;
					}
				}
			}
			$dirX = $ud['curX'] - $ud['prevX'];
			$dirY = $ud['curY'] - $ud['prevY'];
			if ($lab[$ud['curX']+$dirY][$ud['curY']-$dirX] != '#')
			$response->message .= 'you can go left, '.PHP_EOL;
			if ($lab[$ud['curX']-$dirY][$ud['curY']+$dirX] != '#')
			$response->message .= 'you can go right, '.PHP_EOL;
			if ($lab[$ud['curX']+$dirX][$ud['curY']+$dirY] != '#')
			$response->message .= 'you can go ahead, '.PHP_EOL;
			if ($lab[$ud['curX']-$dirX][$ud['curY']-$dirY] != '#')
			$response->message .= 'you can go back.'.PHP_EOL;
			$response->message .= PHP_EOL;
			$response->message .= $this->printLab($lab, $ud['curX'], $ud['curY'], $dirX, $dirY);
		}
		$response->status = 200;
		$response->info = 'lab'.$ud['currentLab'].' '.'x:'.$ud['curX'].' y:'.$ud['curY'];
		$response->choices[] = 'go right';
		$response->choices[] = 'go left';
		$response->choices[] = 'go ahead';
		$response->choices[] = 'go back';
		$response->choices[] = 'info';
		if ($ud) { // to prevent to save an empty array or a NULL
			$this->saveUserGameData($db, $userEnv, $ud);
		}
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
		if (strcasecmp($question->originalText, 'go right') == 0) {
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

	private function printLab($lab, $curX, $curY, $dirX, $dirY) {
		$ret = '';
		for ($y = 0; $y < count($lab[0]); $y++) {
			for ($x = 0; $x < count($lab); $x++) {
				if (($curX == $x) && ($curY == $y)) {
					$ret .= $this->printUser($dirX, $dirY);
				} else {
					$ret .= $lab[$x][$y];
				}
			}
			$ret .= PHP_EOL;
		}
		return $ret;
	}

	private function printUser($dirX, $dirY) {
		if ($dirX == 1) {
			return '>';
		}
		if ($dirX == -1) {
			return '<';
		}
		if ($dirY == 1) {
			return 'v';
		}
		if ($dirY == -1) {
			return '^';
		}
		return '@';
	}

	private function loadLab($labName) {
		if (!file_exists('./games/labyrinth/lab/'.$labName.'.txt')) {
			return false;
		}
		$labFile = file_get_contents('./games/labyrinth/lab/'.$labName.'.txt');
		$lab = array();
		$labFile = explode("\n", $labFile);
		$ln = 0;
		foreach ($labFile as $line) {
			for ($i = 0; $i < strlen($line); $i++) {
				$lab[$i][$ln] = $line[$i];
			}
			$ln++;
		}
		return $lab;
	}

	private function execAction(array $ud, array $lab, Response $response){
		$curChar = $lab[$ud['curX']][$ud['curY']];
		$prevChar = $lab[$ud['prevX']][$ud['prevY']];
		$ret = '';
		$labActionDB = loadDb('./games/labyrinth/lab/lab'.$ud['currentLab'].'.json');
		if (isset($labActionDB[$curChar])) {
			$curAction = $labActionDB[$curChar];
			$prevAction = array();
			if (isset($labActionDB[$prevChar])) {
				$prevAction = $labActionDB[$prevChar];
			}
			return $this->runAction($ud, $lab, $response, $curAction, $prevAction);
		} else {
			$response->message .= 'Nothing here.'.PHP_EOL;;
			return $userData;
		}
		return $userData;
	}

	private function runAction(array $ud, array $lab, Response $response, array $curAction, array $prevAction = array()){
		$response->message .= $curAction['text'].PHP_EOL;
		foreach ($curAction['player'] as $key => $value) {
			if ($value[0] == '+') {
				$ud['player'][$key] += substr($value, 1);
			} else if ($value[0] == '-') {
				$ud['player'][$key] -= substr($value, 1);
			} else if ($value[0] == '=') {
				$ud['player'][$key] = substr($value, 1);
			} else {
				$ud['player'][$key] = $value;
			}
		}
		if (isset($ud['player']['health'])) {
			if ($ud['player']['health'] > 100) {
				$ud['player']['health'] = 100;
			}
			if ($ud['player']['health'] <= 0) {
				return $this->dead($ud);
			}
		}
		return $ud;
	}

	private function dead(array $ud){
		$response->message .= "You juste die! Tin tin tin tin! (game over musique).";
		$ud['currentLab'] = 1;
		//$ud['player'] = array();
		$ud['curX'] = 1;
		$ud['curY'] = 1;
		$ud['prevX'] = 1;
		$ud['prevY'] = 0;
		return $ud;
	}
}
