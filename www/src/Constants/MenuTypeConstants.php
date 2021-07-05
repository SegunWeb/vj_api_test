<?php

namespace App\Constants;

class MenuTypeConstants
{
	const STATIC_PAGE = 1, LINK = 2, CATEGORY_VIDEO = 3;
	
	static public function loadMenuValues()
	{
		return [
			self::STATIC_PAGE    => 'Страница',
			self::LINK           => 'Ссылка',
			self::CATEGORY_VIDEO => 'Категория видео',
		];
	}
}