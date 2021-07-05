<?php

namespace App\Constants;

class VideoTypeConstants
{
	const All = 0, PERSONAL = 1, POSTCARD = 2, MINIMOVIE = 3;
	
	static public function loadVideoTypeValues()
	{
		return [
			self::PERSONAL  => 'Персональный ролик',
            self::POSTCARD  => 'Видео открытка',
            self::MINIMOVIE => 'Мини-фильм',
		];
	}
}