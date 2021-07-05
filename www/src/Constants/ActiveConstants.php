<?php

namespace App\Constants;

class ActiveConstants
{
	//Обычная активность
	const NOT_PROCESSED = 2, ACTIVE = 1, INACTIVE = 0;
	const ACTIVE_TITLE = 'Активно', INACTIVE_TITLE = 'Не активно';
	
	//Активность для отзывов
	const REVIEW_NOT_PROCESSED = 0, REVIEW_ACTIVE = 1, REVIEW_INACTIVE = 2;
	const ACTIVE_REVIEW_TITLE = 'Активно', INACTIVE_REVIEW_TITLE = 'Не активно', NOT_PROCESSED_TITLE = 'Новый отзыв';
	
	//Активность для сообщений(feedback)
	const FEEDBACK_NOT_PROCESSED = 0, FEEDBACK_ACTIVE = 1, FEEDBACK_INACTIVE = 2;
	const ACTIVE_FEEDBACK_TITLE = 'Обработано', INACTIVE_FEEDBACK_TITLE = 'Не отвечено', NOT_FEEDBACK_TITLE = 'Новое обращение';
	
	//Активность для заказов
	const ORDER_SUBSCRIPTION_VALUE = 3, ORDER_PROMOCODE_VALUE = 2, ORDER_PAID_VALUE = 1, ORDER_NOT_PAID_VALUE = 0;
	const ORDER_SUBSCRIPTION = 'Подписка', ORDER_PROMOCODE = 'Промокод', ORDER_PAID = 'Оплачен', ORDER_NOT_PAID = 'Не оплачен';
	
	static public function loadActivityValues()
	{
		return [
			self::ACTIVE_TITLE   => self::ACTIVE,
			self::INACTIVE_TITLE => self::INACTIVE
		];
	}
	
	static public function loadActivityFeedbackValues()
	{
		return [
			self::NOT_FEEDBACK_TITLE      => self::FEEDBACK_NOT_PROCESSED,
			self::ACTIVE_FEEDBACK_TITLE   => self::FEEDBACK_ACTIVE,
			self::INACTIVE_FEEDBACK_TITLE => self::FEEDBACK_INACTIVE
		];
	}
	
	static public function loadActivityReviewValues()
	{
		return [
			self::NOT_PROCESSED_TITLE   => self::REVIEW_NOT_PROCESSED,
			self::ACTIVE_REVIEW_TITLE   => self::REVIEW_ACTIVE,
			self::INACTIVE_REVIEW_TITLE => self::REVIEW_INACTIVE
		];
	}
	
	static public function statusOrder()
	{
		return [
			self::ORDER_NOT_PAID => self::ORDER_NOT_PAID_VALUE,
            self::ORDER_PAID     => self::ORDER_PAID_VALUE,
            self::ORDER_PROMOCODE     => self::ORDER_PROMOCODE_VALUE,
            self::ORDER_SUBSCRIPTION    => self::ORDER_SUBSCRIPTION_VALUE
		];
	}
}