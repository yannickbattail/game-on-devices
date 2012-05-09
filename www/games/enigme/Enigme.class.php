<?php

Class Enigme implements Game {
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
		$enigmeDB = loadDb('games/enigme/enigmeDB.json');
		$c = $this->changeCategorie($enigmeDB, $question);
		if ($c) {
			$uGameData['categorie'] = $c;
		}
		if (!isset($uGameData['categorie'])) {
			$uGameData['categorie'] = 'dur';
		}
		if (!isset($uGameData['categories'])) {
			$uGameData['categories'] = array();
		}
		if (!isset($uGameData['categories'][$uGameData['categorie']])) {
			$uGameData['categories'][$uGameData['categorie']] = 0;
		}
		$enigmeNb = $uGameData['categories'][$uGameData['categorie']];
		if (isset($enigmeDB[$uGameData['categorie']][$enigmeNb])) {
			$enigme = $enigmeDB[$uGameData['categorie']][$enigmeNb];
			if (strcasecmp($question->originalText, $enigme['reponse']) == 0) {
				$response->message .= 'Reponse exacte! ';
				$enigmeNb++;
				$uGameData['categories'][$uGameData['categorie']] = $enigmeNb;
			}
		}
		if (isset($enigmeDB[$uGameData['categorie']][$enigmeNb])) {
			$enigme = $enigmeDB[$uGameData['categorie']][$enigmeNb];
			$response->message .= 'Enigme n '.$enigmeNb.' ('.$uGameData['categorie'].') : '.$enigme['text'];
		} else  {
			$response->message .= ' WIN! Categorie '.$uGameData['categorie'].' finie!';
		}
		$response->status = 200;
		$response->info = 'categorie:'.$uGameData['categorie'].' enigme:'.$enigmeNb;
		$response->choices[] = '[la reponse]';
		foreach ($enigmeDB as $catKey => $categorie) {
			$response->choices[] = 'change categorie '.$catKey;
		}
		$this->saveUserGameData($db, $userEnv, $uGameData);
		return $response;
	}

	private function changeCategorie($enigmeDB, Question $question) {
		foreach ($enigmeDB as $catKey => $categorie) {
			if (strcasecmp($question->originalText, 'change categorie '.$catKey) == 0) {
				return $catKey;
			}
		}
		return false;
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
