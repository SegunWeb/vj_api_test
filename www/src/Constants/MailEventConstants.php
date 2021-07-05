<?php

namespace App\Constants;

class MailEventConstants
{

    const RESETTING_PASSWORD = 1;
    const RESETTING_PASSWORD_VALUES = ['user_email', 'user_name', 'url'];

    const FEEDBACK = 2;
    const FEEDBACK_VALUES = ['user_name', 'user_email', 'text'];

    const REGISTRATION_USERS_WITH_DEMO = 3;
    const REGISTRATION_USERS_WITH_DEMO_VALUES = ['user_email', 'user_name', 'password'];

    const CHANGE_PASSWORD = 4;
    const CHANGE_PASSWORD_VALUES = ['user_name', 'user_email', 'password'];

    const WILLINGNESS_DEMO = 5;
    const WILLINGNESS_DEMO_VALUES = ['user_email', 'user_name', 'url', 'password'];

    const FULL_VIDEO_READINESS = 6;
    const FULL_VIDEO_READINESS_VALUES = ['user_email', 'user_name', 'url', 'url_site'];

    const SUCCESSFUL_PAYMENT = 7;
    const SUCCESSFUL_PAYMENT_VALUES = ['user_email', 'user_name', 'video_url'];

    const DISCOUNT_LETTER = 8;
    const DISCOUNT_LETTER_VALUES = ['discount', 'user_name', 'user_email', 'video_url'];

    const SMS_FULL_VIDEO_READINESS = 9;
    const SMS_FULL_VIDEO_READINESS_VALUES = ['user_email', 'user_name', 'url', 'url_site'];

    const VIDEO_RENDER_ERROR = 10;
    const VIDEO_RENDER_ERROR_VALUES = ['order_id', 'user_id', 'video_id', 'user_email'];

    const EMAIL_MARKETING_ONE_MESSAGE = 11;
    const EMAIL_MARKETING_ONE_MESSAGE_VALUES = ['discount', 'user_name', 'user_email', 'video_url'];

    const REGISTRATION_USERS = 12;
    const REGISTRATION_USERS_VALUES = ['user_email', 'user_name', 'password'];

    static public function loadValues()
    {
        return [
            self::RESETTING_PASSWORD           => 'Восстановление пароля',
            self::FEEDBACK                     => 'Ответ на сообщения',
            self::REGISTRATION_USERS_WITH_DEMO => 'Регистрация со страницы видео',
            self::CHANGE_PASSWORD              => 'Изменение пароля',
            self::WILLINGNESS_DEMO             => 'Готовность демо версии',
            self::FULL_VIDEO_READINESS         => 'Готовность полной версии',
            self::SUCCESSFUL_PAYMENT           => 'Успешная оплата',
            self::SMS_FULL_VIDEO_READINESS     => 'SMS сообщение о готовности полной версии',
            self::EMAIL_MARKETING_ONE_MESSAGE  => 'Маркетинг. Первое сообщение',
            self::DISCOUNT_LETTER              => 'Маркетинг. Второе сообщение со скидкой',
            self::REGISTRATION_USERS           => 'Регистрация',
        ];
    }
}