<?php

Class Parrot implements Game {
	/**
	 *
	 * Enter description here ...
	 * @param UserEnv $userEnv
	 * @param Question $question
	 * @return Response
	 */
	public function speak(UserEnv $userEnv, Question $question) {
		$response = new Response();
		$response->message = $this->upAndDown($question->originalText);
		$response->status = 200;
		$response->info = 'bonjour '.$userEnv->pseudoInGame.' ('.$userEnv->email.') bienvenue dans le jeux '.$userEnv->game
		.'. vous m avez dit: '.$question->originalText;
		$response->choices = array('tougoudou','meow','vrouuum');
		return $response;
	}
	private function upAndDown($tring) {
		$ret = '';
		for ($i = 0; $i < strlen($tring); $i++) {
			if ($i % 2) {
				$ret .= strtoupper($tring[$i]);
			} else {
				$ret .= strtolower($tring[$i]);
			}
		}
		return $ret;
	}
}
