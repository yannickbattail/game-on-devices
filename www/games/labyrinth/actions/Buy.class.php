<?php

class Buy {
	private $text = "";
	private $price = "";
	private $itemName = "";
	
	
	public function __construct(array $actionData) {
		foreach ($actionData as $key => $value) {
			if (property_exists($this, $key)) {
				$this->{$key} = $value;
			}
		}
	}
	
	public function exec(array $UserGameData) {
		
	}
	
}
