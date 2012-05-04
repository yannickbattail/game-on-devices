<?php

Class Parrot {
	public function speak($userEnv, $texts) {
		$response = new Response();
		$response->message = strtoupper($texts['originalText']);
		$response->status = 200;
		$response->info = 'bonjour '.$userEnv['pseudoInGame'].' ('.$userEnv['email'].') bienvenue dans le jeux '.$userEnv['game']
		.'. vous m avez dit: '.$texts['originalText'];
		return $response;
	}
}
