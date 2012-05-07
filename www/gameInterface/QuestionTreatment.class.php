<?php

require_once 'gameInterface/Question.class.php';
require_once 'parser/Synonym.class.php';

class QuestionTreatment {
	public static function treat($text) {
		$q = new Question();
		$q->originalText = $text;
		$q->splitedText = explode(' ', strtolower($text));
		foreach ($q->splitedText as $word) {
			$q->synonyms[$word] = Synonym::getSynonyms($word);
		}
		return $q;
	}
}
