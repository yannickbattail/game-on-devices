<?php


Class Parrot2 implements Game {
	/**
	 * 
	 * Enter description here ...
	 * @param UserEnv $userEnv
	 * @param Question $question
	 * @return Response
	 */
	public function speak(UserEnv $userEnv, Question $question) {
		$response = new Response();
		$syns = Synonym::getSynonyms($question->originalText);
		if (count($syns) == 0)
			$response->message = $question->originalText;
		else
		{
			foreach ($syns as $synSubKey => $synSubValue)
				unset($syns[$synSubKey][0]);
			//var_dump($syns);
			$y = array_rand($syns);
			$x = array_rand($syns[$y]);
			$response->message = $syns[$y][$x];
		}
		$response->status = 200;
		$response->info = 'bonjour '.$userEnv->pseudoInGame.' ('.$userEnv->email.') bienvenue dans le jeux '.$userEnv->game
		.'. vous m avez dit: '.$question->originalText;
		return $response;
	}
}
