<?php

require_once('parser/Synonym.class.php');

Class Parrot2 {
	public function speak($userEnv, $texts) {
		$response = new Response();
		$syns = Synonym::getSynonyms(strtolower($texts['originalText']));
		if (count($syns) == 0)
			$response->message = $texts['originalText'];
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
		$response->info = 'bonjour '.$userEnv['pseudoInGame'].' ('.$userEnv['email'].') bienvenue dans le jeux '.$userEnv['game']
		.'. vous m avez dit: '.$texts['originalText'];
		return $response;
	}
}
