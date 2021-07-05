<?php

namespace App\Constants;

class PhrasesTypeConstants
{
	const PHRASES = 1, PHRASES_AND_NAME = 2, PHRASES_AND_SEX = 3;
	
	static public function loadPhrasesValues()
	{
		return [
			self::PHRASES          => 'Фраза',
            self::PHRASES_AND_NAME => 'Фраза с именем',
            self::PHRASES_AND_SEX  => 'Фраза с полом',
		];
	}
}