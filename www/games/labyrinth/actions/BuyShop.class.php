<?php

class BuyShop {
	private $text = "buy nOtHiNg.";
	private $shopName = "nOtHiNg";
	private $actionType = "BuyShop";
	private $itemsList = array(
		'shield' => '12',
		'armor'  => '30',
		'potion' => '5'
	);

	public function __construct(array $actionData, array $prevAction = null) {
		foreach ($actionData as $key => $value) {
			if (property_exists($this, $key)) {
				$this->{$key} = $value;
			}
		}
	}

	public function exec(Labyrinth $env, $newChar, $curChar) {
		if (isset($env->ud['player']['gold'])) {
			$env->response->message .= "Wellcome to the ".$this->shopName.". What do you want to buy?".PHP_EOL;
			$env->ud['step'] = array(
				'action' => $newChar,
				'stepNum' => 1
			);
			$env->response->choices[] = array_keys($itemsList);
			return true;
		} else {
			$env->response->message .= "You don't have money. You cannot by a ".$this->itemName.".".PHP_EOL;
			$env->ud['player']['gold'] = 0;
			return false;
		}
	}
	public function step(Labyrinth $env, Question $question, $newChar, $curChar) {
		$matches = array(
			'yes' => true,
			'ok' => true,
			'no' => false
		);
		$rep = $this->parseQuestion($question, $matches);
		if ($rep === true) {
			$env->response->message .= "You just bought ".$this->itemName.".".PHP_EOL;
			$env->ud['player']['gold'] -= $this->price;
			$env->ud['step'] = array();
		} else if ($rep === false) {
			$env->response->message .= "You didn't bought ".$this->itemName.".".PHP_EOL;
			$env->ud['step'] = array();
		} else {
			$env->response->message .= "I don't undestand. Do you want to buy a ".$this->itemName.", it costs ".$this->price." gold?".PHP_EOL;
			$env->response->choices[] = 'yes';
			$env->response->choices[] = 'no';
		}
	}
	private function parseQuestion(Question $question, $matches) {
		foreach ($matches as $txt => $state) {
			if (strcasecmp($question->cleanedText, $txt) == 0) {
				return $state;
			}
		}
		return null;
	}
}
