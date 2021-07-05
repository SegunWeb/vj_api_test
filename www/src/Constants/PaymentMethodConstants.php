<?php

namespace App\Constants;

class PaymentMethodConstants
{
	
	const ONE = 1, TWO = 2;
	
	const PAYPAL = 'PayPal', PLATON = 'Platon.ua';
	
	
	static public function loadPaymentMethod()
	{
		return [
			self::PAYPAL => self::ONE,
			self::PLATON => self::TWO
		];
	}
}