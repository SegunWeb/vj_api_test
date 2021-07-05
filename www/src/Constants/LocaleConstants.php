<?php

namespace App\Constants;

class LocaleConstants
{
	const RU = 'ru', EN = 'en', PL = 'pl', PT = 'pt';
	
	const RU_TITLE = 'Русский', EN_TITLE = 'Английский', PL_TITLE = 'Польский', PT_TITLE = 'Португальский';
	
	static public function loadLocaleValues()
	{
		return [
			self::RU_TITLE => self::RU,
			self::EN_TITLE => self::EN,
			self::PL_TITLE => self::PL,
			self::PT_TITLE => self::PT
		];
	}
}