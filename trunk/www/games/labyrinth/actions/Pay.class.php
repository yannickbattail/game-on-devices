<?php

class Pay {
	private $text = "buy nOtHiNg.";
	private $price = 100;
	private $itemName = "nOtHiNg";
	private $actionType = "Pay";
	
	public function __construct(array $actionData, array $prevAction = null) {
		foreach ($actionData as $key => $value) {
			if (property_exists($this, $key)) {
				$this->{$key} = $value;
			}
		}
	}
	
	public function exec(Labyrinth $env, $newChar, $curChar) {
		if (isset($env->ud['player']['gold'])) {
			if ($env->ud['player']['gold'] < $this->price) {
				$env->response->message .= "You don't have enough money. You cannot by a ".$this->itemName.", it costs ".$this->price." gold.".PHP_EOL;
				return false;
			} else {
				$env->response->message .= "You just bought ".$this->itemName.".".PHP_EOL;
				$env->ud['player']['gold'] -= $this->price;
				return true;
			}
		} else {
			$env->response->message .= "You don't have money. You cannot by a ".$this->itemName.".".PHP_EOL;
			$env->ud['player']['gold'] = 0;
			return false;
		}
	}
}
