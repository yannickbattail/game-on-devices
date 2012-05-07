<?php

Class Basic {
	/**
	 *
	 * Enter description here ...
	 * @param UserEnv $userEnv
	 * @param Question $question
	 * @return Response
	 */
	public function speak(UserEnv $userEnv, Question $question) {
		$response = new Response();
		$response->message = $question->originalText;
		$response->status = 200;
		$response->info = 'bonjour '.$userEnv->pseudoInGame.' ('.$userEnv->email.') bienvenue dans le jeux '.$userEnv->game
		.'. vous m avez dit: '.$question->originalText;
		return $response;
	}
}
