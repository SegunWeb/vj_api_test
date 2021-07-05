<?php

namespace App\Constants;

class TypePageConstants
{
	
	const OTHER = 'Другая страница';
	const OTHER_VALUES = 0;
	
	const INDEX = 'Главная';
	const INDEX_VALUES = 1;
	
	const REVIEW = 'Страница отзывов';
	const REVIEW_VALUES = 2;
	
	const HELP = 'Страница помощи';
	const HELP_VALUES = 4;
	
	const ALL_VIDEO = 'Страница всех роликов';
	const ALL_VIDEO_VALUES = 5;
	
	const THANK_YOU = 'Страница благодарности';
	const THANK_YOU_VALUES = 6;
	
	const USER_AGREEMENT = 'Страница пользовательского соглашения';
	const USER_AGREEMENT_VALUES = 7;
	
	const USER_ACCOUNT = 'Страница кабинета пользователя';
	const USER_ACCOUNT_VALUES = 8;
	
	const ABOUT = 'О нас';
	const ABOUT_VALUES = 9;
	
	const PAGE_NOT_FOUND = 'Страница 404';
	const PAGE_NOT_FOUND_VALUES = 10;
	
	const BLOG = 'Блог';
	const BLOG_VALUES = 11;
	
	const VIDEO = 'Видео';
	const VIDEO_VALUES = 12;
	
	const REFUND = 'Возврат средств';
	const REFUND_VALUES = 13;
	
	const CATEGORIES_VIDEO = 'Страница категорий видео';
	const CATEGORIES_VIDEO_VALUES = 14;
	
	const USER_ACCOUNT_PAID = 'Страница кабинета пользователя | Оплаченные';
	const USER_ACCOUNT_PAID_VALUES = 15;
	
	const USER_ACCOUNT_NOT_PAID = 'Страница кабинета пользователя | Неолаченные';
	const USER_ACCOUNT_NOT_PAID_VALUES = 16;
	
	const USER_ACCOUNT_SETTING = 'Страница кабинета пользователя | Настройки';
	const USER_ACCOUNT_SETTING_VALUES = 17;
	
	static public function listPages()
	{
		return [
			self::OTHER                 => self::OTHER_VALUES,
			self::INDEX                 => self::INDEX_VALUES,
			self::BLOG                  => self::BLOG_VALUES,
			self::VIDEO                 => self::VIDEO_VALUES,
			self::REVIEW                => self::REVIEW_VALUES,
			self::HELP                  => self::HELP_VALUES,
			self::ALL_VIDEO             => self::ALL_VIDEO_VALUES,
			self::THANK_YOU             => self::THANK_YOU_VALUES,
			self::USER_AGREEMENT        => self::USER_AGREEMENT_VALUES,
			self::USER_ACCOUNT          => self::USER_ACCOUNT_VALUES,
			self::USER_ACCOUNT_PAID     => self::USER_ACCOUNT_PAID_VALUES,
			self::USER_ACCOUNT_NOT_PAID => self::USER_ACCOUNT_NOT_PAID_VALUES,
			self::USER_ACCOUNT_SETTING  => self::USER_ACCOUNT_SETTING_VALUES,
			self::ABOUT                 => self::ABOUT_VALUES,
			self::REFUND                => self::REFUND_VALUES,
			self::CATEGORIES_VIDEO      => self::CATEGORIES_VIDEO_VALUES,
			self::PAGE_NOT_FOUND        => self::PAGE_NOT_FOUND_VALUES
		];
	}
}