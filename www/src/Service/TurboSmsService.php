<?php

namespace App\Service;

use Doctrine\ORM\EntityManager;

class TurboSmsService
{
	
	protected $manager;
	
	public function __construct( EntityManager $manager )
	{
		$this->manager = $manager;
	}
	
	/**
	 * Sends text message.
	 * @return boolean whether message was successfully sent
	 * @param string $sNumber phone number (req)
	 * @param string $sMessage message (req)
	 */
	public function send($sNumber, $sMessage) {
		
		//Заменяем +, номер должен быть в международном формате без плюса спереди.
		$sNumber=str_replace('+','',$sNumber);
		
		//Проверяем на валидность номер телефона
		if ($this->isValidPhone($sNumber, false) or !mb_strlen($sMessage, 'utf-8')) {return false;}
		
		//Получаем настройки сайта где содержаться данные подключения к API turboSMS.
		$setting = $this->manager->getRepository('App\Entity\Setting')->find(1);
		
		// Подключаемся к серверу
		$client = new \SoapClient('http://turbosms.in.ua/api/wsdl.html');
		
		// Авторизируемся на сервере
		$result = $client->Auth([ 'login' => $setting->getApiTurbosmsLogin(), 'password' => $setting->getApiTurbosmsPassword() ]);
		
		if(!empty($result) and $result->AuthResult == "Вы успешно авторизировались"){
			
			$sms = [
				'sender' => $setting->getApiTurbosmsSignature(),
				'destination' => $sNumber,
				'text' => $sMessage
			];
			
			$client->SendSMS($sms);
			
		}
	}
	
	public function isValidPhone($sPhone = null, $bUseMask = true) {
		// pattern
		if ($bUseMask) {
			$sPattern = '/^\+38\s\(\s[0-9]{3}\s\)\s[0-9]{3}-[0-9]{2}-[0-9]{2}$/iUu';
		} else {
			$sPattern = '/^\+[0-9]{11,14}$/';
		}
		// result
		return preg_match($sPattern, $sPhone) ? true : false;
	}
}