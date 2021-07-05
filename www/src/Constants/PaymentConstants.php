<?php

namespace App\Constants;

class PaymentConstants
{
    //Типы оплат
    const ONLY_SUBSCRIBE = 1, ONLY_PURCHASE = 2;
    const ONLY_SUBSCRIBE_TITLE = 'Только подписка', ONLY_PURCHASE_TITLE = 'Только покупка';

    static public function loadPaymentsTypes()
    {
        return [
            self::ONLY_SUBSCRIBE   => self::ONLY_SUBSCRIBE_TITLE,
            self::ONLY_PURCHASE => self::ONLY_PURCHASE_TITLE
        ];
    }

}