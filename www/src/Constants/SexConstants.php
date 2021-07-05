<?php

namespace App\Constants;

class SexConstants
{
	const MALE = 1, FEMALE = 2, All = 0;
	
	const MALE_TITLE = 'Мужской', FEMALE_TITLE = 'Женский', All_TITLE = 'Без привязки';
	
	static public function loadSexValues()
	{
		return [
			self::FEMALE_TITLE => self::FEMALE,
			self::MALE_TITLE   => self::MALE
		];
	}
	
	static public function loadSexValuesAll()
	{
		return [
			self::All_TITLE    => self::All,
			self::FEMALE_TITLE => self::FEMALE,
			self::MALE_TITLE   => self::MALE
		];
	}
}