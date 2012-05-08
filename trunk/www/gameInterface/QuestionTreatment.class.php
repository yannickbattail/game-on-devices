<?php

require_once 'parser/Synonym.class.php';

class QuestionTreatment {
	public static function treat($text) {
		$q = new Question();
		$q->originalText = $text;
		$q->cleanedText = preg_replace('/[^a-zA-y0-9-]/', '', $text);
		$q->splitedText = explode(' ', strtolower($q->cleanedText));
		return $q;
	}
}
