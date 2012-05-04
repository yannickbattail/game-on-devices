<?php

class Synonym {

	private $word = ''; 
	
	private $synonyms = array();
	
	public function __construct($word) {
		$this->word = $word;
		$this->synonyms = $this->getSynonyms($this->word);
	}
	
	private static function getIndex($wordIn) {
		//echo "getting index for $wordIn";
		//echo PHP_EOL;
		$handle = fopen('parser/dico/th_en_US_new.idx', 'r');
		$line = fgets($handle);
		$line = fgets($handle);
		$line = fgets($handle);
		$i = 0;
		while ($line !== false) {
			list($currentWord,$index) = explode( '|', $line, 2);
			if ($wordIn == $currentWord) {
				fclose($handle);
				//echo "index is $index";
				//echo PHP_EOL;
				return $index;
			}
			//echo "=== ".$i." ".$currentWord." -> ".$index;
			//if ($i > 10) return $index;
			$line = fgets($handle);
			$i++;
		}
		return null;
	}

	public static function getSynonyms($wordIn) {
		$index = (int) self::getIndex($wordIn);
		if ($index === null) {
			return array();
		}
		$handle = fopen('parser/dico/th_en_US_new.dat', 'r');
		//echo "fseeking to $index";
		//echo PHP_EOL;
		fseek($handle, $index);
		$line = fgets($handle);
		@list($word,$nbLines) = explode('|', $line, 2);
		$nbLines = (int) $nbLines;
		$ret = array();
		for ($i = 0; $i < $nbLines; $i++) {
			$line = fgets($handle);
			$ret[] = explode( '|', $line);
		}
		//print_r($ret);
		return $ret;
	}

	public function getSynonymsVerb($wordIn)
	{
		return array_filter($this->synonyms, function ($var) { return ($var[0] == "(verb)"); });
	}

	public function getSynonymsNoun($wordIn)
	{
		return array_filter($this->synonyms, function ($var) { return ($var[0] == "(noun)"); });
	}

	/*
	 $index = getIndex($argv[1]);
	 echo "index of: ".$argv[1]." = ".getIndex($argv[1]);
	 if ($index !== null) {
	 $synonyms = getSynonyms((int) $index);
	 print_r($synonyms);
	 } else {
	 echo "not in directory";
	 }
	 */

}