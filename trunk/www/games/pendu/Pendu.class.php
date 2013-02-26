<?php

Class Pendu implements Game {
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
		$uGameData = $this->getUserGameData($db, $userEnv);
		if (!isset($uGameData['word']) || !$uGameData['word']) {
			$this->restart($uGameData);
		}
		if (strlen($question->cleanedText) == 1) {
			$L = $question->cleanedText; // the letter
			if (strstr($uGameData['word'], $L) === false) { // lettre not in the word
				$uGameData['step']++;
						$response->message .= "OUPS!\r\n";
				if ($uGameData['step'] >= 10) {
					$response->message .= $this->printStep($uGameData['step']);
					$response->message .= "Pendu!\r\n";
					$response->message .= 'You just lost the Game.';
					$this->restart($uGameData);
				}
			} else { // lettre is in the word
				if (strstr($uGameData['triedLetters'], $L) === false) {
					$this->setLetter($uGameData, $L);
					$uGameData['triedLetters'] .= $L;
					if (strstr($uGameData['wordGess'], '_') === false) { // no more lettre to gess
						$response->message .= "WIN!\r\n";
						$response->message .= "Vous avez gagné en ".$uGameData['step']." étapes!\r\n";
						$this->restart($uGameData);
					} else {
						$response->message .= "OK!\r\n";
					}
				} else {
					$response->message .= "Vous avez deja essayé cette lettre.\r\n";
				}
			}
		} else { // more than 1 lettre typed
			$response->message .= "Choisisez une lettre.\r\n";
		}
		$response->message .= "\r\n".$this->printStep($uGameData['step']);
		$response->message .= 'Mot: "'.$uGameData['wordGess']."\"\r\n";
		$response->status = 200;
		$response->info = 'etape:'.$uGameData['step'].'/10 triedLetters:'.$uGameData['triedLetters'];
		// for debug
		//$response->info .= ' word:'.$uGameData['word'].' cleanedText:"'.$question->cleanedText.'"';
		$response->choices[] = 'etape';
		$response->choices[] = '[une lettre]';
		$this->saveUserGameData($db, $userEnv, $uGameData);
		return $response;
	}

	private function setLetter(&$uGameData, $L) {
		for ($i = 0; $i < strlen($uGameData['word']); $i++) {
			if ($uGameData['word'][$i] == $L) {
				$uGameData['wordGess'][$i] = $L;
			}
		}
	}
	
	private function restart(&$uGameData) {
		$uGameData['word'] = $this->getRandomWord();
		$uGameData['wordGess'] = str_repeat('_', strlen($uGameData['word']));
		$uGameData['triedLetters'] = '';
		$uGameData['step'] = 0;
	}
	
	private function getRandomWord() {
		$f = file_get_contents('games/pendu/wordList.txt');
		$wordList = explode("\n", $f);
		return trim($wordList[array_rand ($wordList)]);
	}

	private function printStep($step) {
		return file_get_contents('games/pendu/steps/step'.$step.'.txt');
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
}
