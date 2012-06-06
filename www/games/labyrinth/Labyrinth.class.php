<?php

require_once 'games/labyrinth/Position.class.php';

Class Labyrinth implements Game {

	public $response;
	public $ud = array();
	/**
	 * current position
	 * @var Position
	 */
	public $curPos = null;
	/**
	 * new position
	 * @var Position
	 */
	public $newPos = null;
	public $lab = array();
	private $fctMaping = array(
	'!' => 'printInfo',
	'info' => 'printInfo',
	'go left' => 'getMove',
	'go right' => 'getMove',
	'go forward' => 'getMove',
	'go ahead' => 'getMove',
	'go back' => 'getMove',
	'go backward' => 'getMove'
	);

	/**
	 *
	 * Enter description here ...
	 * @param UserEnv $userEnv
	 * @param Question $question
	 * @return Response
	 */
	public function speak(UserEnv $userEnv, Question $question) {
		$this->response = new Response();
		try {
			$db = loadDb('db_json/db.json');
			$this->getUserGameData($db, $userEnv);
			if (isset($this->ud['position'])) {
				$this->curPos = new Position($this->ud['position']);
				$this->newPos = new Position($this->ud['position']);
			}
		} catch (Exception $e) {
			$this->dead(true);
			$this->response->message .= 'Hi! Wellcome to the Labyrinth game: move through the Labyrinth and find the exit and go to the next level.'.PHP_EOL;
		}
		$this->lab = $this->loadLab();
		if ($this->ud['step']) {
			$newChar = $this->getLabChar($this->newPos);
			$curChar = $this->getLabChar($this->curPos);
			$this->response->info .= "Step: ".$this->ud['step']['action']." num:".$this->ud['step']['stepNum'].PHP_EOL;
			$labActionDB = loadDb('./games/labyrinth/lab/lab'.$this->ud['currentLab'].'.json');
			if (isset($this->ud['step']['action']) && isset($labActionDB[$this->ud['step']['action']])) {
				$curAction = $labActionDB[$this->ud['step']['action']];
				if (isset($curAction['actionType'])) {
					$classname = $curAction['actionType'];
					if (file_exists('games/labyrinth/actions/'.$classname.'.class.php')) {
						require_once 'games/labyrinth/actions/'.$classname.'.class.php';
						$actionObj = new $classname($curAction);
						$actionObj->step($this, $question, $newChar, $curChar);
					} else {
						$this->ud['step'] = array();
						$this->response->info .= "the actionType ".$curAction['actionType']." do not exists.";
						$this->response->message .= 'Nothing here.'.PHP_EOL;
						//$this->runAction($curAction, $newChar, $curChar);
					}
				} else {
					$this->ud['step'] = array();
					$this->response->message .= 'Nothing here.'.PHP_EOL;
					//$this->runAction($curAction, $prevAction);
				}
			} else {
				$this->response->message .= 'Nothing here.'.PHP_EOL;
			}
		} else {
			if ($this->parseQuestion($question)) {

			} else {
				$this->response->message .= 'I don\'t understand what you said. what do you want to do'.PHP_EOL;
			}
			/*
			 if ($lab[$this->ud['curX']+$dirY][$this->ud['curY']-$dirX] != '#')
			 $this->response->message .= 'you can go left, '.PHP_EOL;
			 if ($lab[$this->ud['curX']-$dirY][$this->ud['curY']+$dirX] != '#')
			 $this->response->message .= 'you can go right, '.PHP_EOL;
			 if ($lab[$this->ud['curX']+$dirX][$this->ud['curY']+$dirY] != '#')
			 $this->response->message .= 'you can go ahead, '.PHP_EOL;
			 if ($lab[$this->ud['curX']-$dirX][$this->ud['curY']-$dirY] != '#')
			 $this->response->message .= 'you can go back.'.PHP_EOL;
			 $this->response->message .= PHP_EOL;
			 */
		}
		if (!$this->ud['step']) {
			$this->response->message .= $this->printLab();
			$this->response->choices[] = 'go right';
			$this->response->choices[] = 'go left';
			$this->response->choices[] = 'go ahead';
			$this->response->choices[] = 'go back';
			$this->response->choices[] = 'info';
		}
		$this->response->status = 200;
		$this->response->info .= 'lab'.$this->ud['currentLab'].' '.'x:'.$this->curPos->getCurX().' y:'.$this->curPos->getCurY().' prevX:'.$this->curPos->getPrevX().' prevY:'.$this->curPos->getPrevY();

		$this->ud['position'] = $this->curPos;
		if ($this->ud) { // to prevent to save an empty array or a NULL
			$this->saveUserGameData($db, $userEnv, $this->ud);
		}
		return $this->response;
	}

	private function getUserGameData($db, UserEnv $userEnv) {
		if (isset($db[$userEnv->email])
		&& isset($db[$userEnv->email]['games'][$userEnv->game])
		&& isset($db[$userEnv->email]['games'][$userEnv->game][$userEnv->pseudoInGame])
		&& isset($db[$userEnv->email]['games'][$userEnv->game][$userEnv->pseudoInGame]['data'])) {
			$this->ud = $db[$userEnv->email]['games'][$userEnv->game][$userEnv->pseudoInGame]['data'];
			if (!$this->ud) {
				throw new Exception('UserGameData empty');
			}
		} else {
			throw new Exception('cannot load user data');
		}
	}

	private function saveUserGameData($db, UserEnv $userEnv) {
		$db[$userEnv->email]['games'][$userEnv->game][$userEnv->pseudoInGame]['data'] = $this->ud;
		saveDb($db, 'db_json/db.json');
	}

	private function parseQuestion(Question $question) {
		foreach ($this->fctMaping as $txt => $fct) {
			if (strcasecmp($question->cleanedText, $txt) == 0) {
				$callback = array($this, $fct);
				return call_user_func($callback, $question);
			}
		}
		return false;
	}

	private function getDirFromText($cleanedText) {
		$dirMaping = array(
			'go left' => Position::LEFT,
			'go right' => Position::RIGHT,
			'go forward' => Position::FORWARD,
			'go ahead' => Position::FORWARD,
			'go back' => Position::BACKWARD,
			'go backward' => Position::BACKWARD
		);
		foreach ($dirMaping as $txt => $dir) {
			if (strcasecmp($cleanedText, $txt) == 0) {
				return $dir;
			}
		}
		throw new Exception('No such direction.');
	}

	private function getMove(Question $question) {
		try {
			$this->newPos = $this->curPos->move($this->getDirFromText($question->cleanedText));
			//print_r($this->curPos);
			//print_r($this->newPos);
		} catch (Exception $e) {
			$this->response->info .= $e->getMessage();
			return false;
		}
		try {
			$char = $this->getLabChar($this->newPos);
			if ($char == '#') {
				$this->response->message .= 'You are going in a wall.'.PHP_EOL;
			} else if ($char == '@') {
				$newLab = $this->loadLab('lab'.($this->ud['currentLab'] + 1));
				if (!$newLab) {
					$this->response->message .= 'WIN! No more Labyrinth'.PHP_EOL;
				} else {
					$this->response->message .= 'WIN! You manage to get out of the Labyrinth. Go on to the next one'.PHP_EOL;
					$this->ud['currentLab']++;
					$this->lab = $newLab;
					$this->initLab();
				}
			} else {
				if (ctype_alnum($char)) {
					if ($this->execAction()) {
						$this->curPos = $this->newPos;
					}
				} else {
					$this->curPos = $this->newPos;
					$this->response->message .= 'Moved!'.PHP_EOL;
				}
			}
		} catch (Exception $e) {
			$this->response->info .= $e->getMessage();
		}
		return true;
	}

	private function getLabChar(Position $p) {
		if (isset($this->lab[$p->getCurX()]) && isset($this->lab[$p->getCurX()][$p->getCurY()])) {
			return $this->lab[$p->getCurX()][$p->getCurY()];
		} else {
			throw new Exception("Position ".$p->getCurX().','.$p->getCurY().' is outside of the labyrinth.');
		}
	}

	private function printLab() {
		$ret = '';
		for ($y = 0; $y < count($this->lab[0]); $y++) {
			for ($x = 0; $x < count($this->lab); $x++) {
				if (($this->curPos->getCurX() == $x) && ($this->curPos->getCurY() == $y)) {
					$ret .= $this->printUser();
				} else {
					$ret .= $this->lab[$x][$y];
				}
			}
			$ret .= PHP_EOL;
		}
		return $ret;
	}

	private function printUser() {
		if ($this->curPos->getDirX() == 1) {
			return '>';
		}
		if ($this->curPos->getDirX() == -1) {
			return '<';
		}
		if ($this->curPos->getDirY() == 1) {
			return 'v';
		}
		if ($this->curPos->getDirY() == -1) {
			return '^';
		}
		return '*';
	}

	private function loadLab() {
		if (!file_exists('./games/labyrinth/lab/lab'.$this->ud['currentLab'].'.txt')) {
			throw new Exception("cannot load lab".$this->ud['currentLab']);
		}
		$labFile = file_get_contents('./games/labyrinth/lab/lab'.$this->ud['currentLab'].'.txt');
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

	private function initLab() {
		$start = $this->findInLab('0');
		print_r($start);
		$this->curPos->setCurX($start['x']);
		$this->curPos->setCurX($start['y']);
		$this->curPos->setPrevX($start['x']);
		$this->curPos->setPrevX($start['y'] + 1);
		$this->ud['labData'] = array();
	}

	private function findInLab($search) {
		foreach ($this->lab as $x => $line) {
			foreach ($line as $y => $char) {
				if (strcmp($char, $search) == 0) {
					return array('x' => $x, 'y' => $y);
				}
			}
		}
		return array();
	}

	private function execAction() {
		$newChar = $this->getLabChar($this->newPos);
		$curChar = $this->getLabChar($this->curPos);
		$ret = '';
		$labActionDB = loadDb('./games/labyrinth/lab/lab'.$this->ud['currentLab'].'.json');
		if (isset($labActionDB[$newChar])) {
			$curAction = $labActionDB[$newChar];
			$prevAction = array();
			if (isset($labActionDB[$curChar])) {
				$prevAction = $labActionDB[$curChar];
			}
			if (isset($curAction['actionType'])) {
				$classname = $curAction['actionType'];
				if (file_exists('games/labyrinth/actions/'.$classname.'.class.php')) {
					require_once 'games/labyrinth/actions/'.$classname.'.class.php';
					$actionObj = new $classname($curAction, $prevAction);
					return $actionObj->exec($this, $newChar, $curChar);
				} else {
					$this->response->info .= "the actionType ".$curAction['actionType']." do not exists.";
					return $this->runAction($curAction, $newChar, $curChar);
				}
			} else {
				return $this->runAction($curAction, $prevAction);
			}
		} else {
			$this->response->message .= 'Nothing here.'.PHP_EOL;;
			return true;
		}
		return true;
	}

	private function runAction(array $curAction, array $prevAction = array()){
		$this->response->message .= $curAction['text'].PHP_EOL;
		foreach ($curAction['player'] as $key => $value) {
			if ($value[0] == '+') {
				$this->ud['player'][$key] += substr($value, 1);
			} else if ($value[0] == '-') {
				$this->ud['player'][$key] -= substr($value, 1);
			} else if ($value[0] == '=') {
				$this->ud['player'][$key] = substr($value, 1);
			} else {
				$this->ud['player'][$key] = $value;
			}
		}
		if (isset($this->ud['player']['health'])) {
			if ($this->ud['player']['health'] > 100) {
				$this->ud['player']['health'] = 100;
			}
			if ($this->ud['player']['health'] <= 0) {
				return $this->dead();
			}
		}
		return true;
	}

	private function printInfo(Question $question) {
		$this->response->message .= print_r($this->ud['player'], true);
		return true;
	}

	private function dead($born = FALSE){
		if (!$born) {
			$this->response->message .= "You juste die! Tin tin tin tin! (game over musique).";
		}
		$this->ud['currentLab'] = 1;
		$this->ud['player'] = array();
		$this->ud['step'] = array();
		$this->curPos = new Position();
		$this->lab = $this->loadLab();
		$this->initLab();
	}
}
