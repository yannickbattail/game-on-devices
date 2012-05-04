<?php

Class Basic {
	public function speak($userEnv, $texts) {
		$response = new Response();
		$response->message = $texts['originalText'];
		$response->status = 200;
		$response->info = 'bonjour '.$userEnv['pseudoInGame'].' ('.$userEnv['email'].') bienvenue dans le jeux '.$userEnv['game']
		.'. vous m avez dit: '.$texts['originalText'];
		return $response;
	}
}
