<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Setting;
use App\Entity\Currency;
use App\Entity\Subscription;
use App\Helper\LiqPay;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class PaymentService
{
	
	protected $manager;
	
	public function __construct( EntityManager $manager )
	{
		$this->manager = $manager;
	}
	
	public function paymentPayPal(Request $request, Setting $setting, $order, $successful_payment, $unsuccessful_payment){
		
		$apiContext = new \PayPal\Rest\ApiContext(
			new \PayPal\Auth\OAuthTokenCredential(
				$setting->getApiKeyPayPalClientId(),
				$setting->getApiKeyPayPalClientSecret()
			)
		);
		
		$total = $order->getPrice();
        $currency = $this->manager->getRepository('App\Entity\Currency')->findOneBy([ 'name' => $order->getPriceCurrency() ]);

		/*
		 * Проверяем на наличие спец скидки
		 */
        $discount = 0;
        if( $order instanceof Order ) {
            if($order->getSentEmail() == 2){
                //Так как отправлено уже второе сообщение и оно содержит скидку, в этом случае если не прошел день - скидка есть
                if(date_diff( $order->getUpdatedAt(), new \DateTime(date('d.m.Y 23:59:59')))->days == 0){
                    $discount = $setting->getDiscountEmailMarketing();
                }
            }
        }

        if(!empty($order->getPromoCodeDiscount()) && $discount < $order->getPromoCodeDiscount()) {
            $discount = $order->getPromoCodeDiscount();
        }

        if($discount > 0) {
            $total = $total - (($total * $discount) / 100);
        }

        $apiConfig = array(
            /*'log.LogEnabled' => true,
            'log.FileName' => 'PayPal.log',
            'log.LogLevel' => 'DEBUG',*/
        );

        if( !$setting->getIsPayPalSandbox() ) {
            $apiConfig['mode'] = 'live';
        }

		$apiContext->setConfig(
            $apiConfig
		);
		
		$payer = new \PayPal\Api\Payer();
		$payer->setPaymentMethod( 'paypal' );
		
		$amount = new \PayPal\Api\Amount();
		$amount->setTotal( !empty($total) ? $total : 1 );
		$amount->setCurrency( $currency->getCodeISO() );
		
		$transaction = new \PayPal\Api\Transaction();
		$transaction->setAmount( $amount );
		
		$redirectUrls = new \PayPal\Api\RedirectUrls();
		$redirectUrls->setReturnUrl( $request->getUriForPath( $successful_payment ) )
		             ->setCancelUrl( $request->getUriForPath( $unsuccessful_payment ) );
		
		$payment = new \PayPal\Api\Payment();
		$payment->setIntent( 'sale' )
		        ->setPayer( $payer )
		        ->setTransactions( array( $transaction ) )
		        ->setRedirectUrls( $redirectUrls );
		
		try {
			$payment->create( $apiContext );
			
			$order->setPaymentIdOrder($payment->getId());
			$this->manager->flush($order);
			
			return $payment->getApprovalLink();
			
		} catch ( \PayPal\Exception\PayPalConnectionException $ex ) {
            return false;
		}
	}
	
	public function paymentPlaton(Request $request, Setting $setting, Order $order, $successful_payment, $unsuccessful_payment){
		
		$total = $order->getVideo()->getPriceUah();
		
		/*
		 * Проверяем на наличие спец скидки
		 */
		if($order->getSentEmail() == 2){
			//Так как отправлено уже второе сообщение и оно содержит скидку, в этом случае если не прошел день - скидка есть
			if(date_diff( $order->getUpdatedAt(), new \DateTime(date('d.m.Y 23:59:59')))->days == 0){
				$total = ($total * $setting->getDiscountEmailMarketing()) / 100;
			}
		}
		
		$product_data = base64_encode(json_encode(
			array(
				'amount' => !empty($total) ? number_format($total, 2, '.', '') : 1.00,
				'currency' => 'UAH',
				'description' => 'Оплата видео №'.$order->getId().' на VideoJoy'
			)
		));
		
		$sign = md5(strtoupper(
			strrev($setting->getApiKeyPlatonMerchantId()).
			strrev('CC').
			strrev($product_data).
			strrev($request->getUriForPath($successful_payment)).
			strrev($setting->getApiKeyPlatonMerchantPassword())
		));
		
		$a_payment = array(
			'key' => $setting->getApiKeyPlatonMerchantId(),
			'payment' => 'CC',
			'order' => $order->getId(),
			'lang' => $request->getLocale(),
			'data' => $product_data,
			'url' => $request->getUriForPath($successful_payment),
			'error_url' => $request->getUriForPath($unsuccessful_payment),
			'sign' => $sign
		);
		
		return $a_payment;
	}
	
	public function paymentInterkassa(Request $request, Setting $setting, Order $order, $successful_payment, $unsuccessful_payment, $type){
		
		$key = $setting->getApiKeyInterkassaSecretKey();
		
		$ik_pm_no =  $order->getId().'-'.time();

		/*
		 * Проверяем валюту
		 * Если UAH, устанавливаем параметры платёжного шлюза для гривный
		 * Если не UAH, то по дефолту используем настройки для рубля
		 * */
        $currency = $this->manager->getRepository('App\Entity\Currency')->findOneBy([ 'name' => $order->getPriceCurrency() ]);
        if( $currency->getCodeISO() === 'UAH' ) {
            $total = $order->getVideo()->getPriceUah();
            $ik_cur = 'UAH';
            $ik_pw_via_visa = 'visa_cpaytrz_merchant_uah';
            $ik_pw_via_mastercard = 'mastercard_cpaytrz_merchant_uah';
        } else {
            $total = $order->getVideo()->getPriceRub();
            $ik_cur = 'RUB';
            $ik_pw_via_visa = 'visa_unitpay_merchant_rub';
            $ik_pw_via_mastercard = 'mastercard_unitpay_merchant_rub';
        }

		/*
		 * Проверяем на наличие спец скидки
		 */

		// Old code
        /*if($order->getSentEmail() == 2){
            //Так как отправлено уже второе сообщение и оно содержит скидку, в этом случае если не прошел день - скидка есть
            if(date_diff( $order->getUpdatedAt(), new \DateTime(date('d.m.Y 23:59:59')))->days == 0){
                $total = ($total * $setting->getDiscountEmailMarketing()) / 100;
            }
        }*/

        $discount_email = 0;
        $discount_promocode = 0;
        $discount_sale = 0;
        if($order->getSentEmail() == 2){
	        //Так как отправлено уже второе сообщение и оно содержит скидку, в этом случае если не прошел день - скидка есть
	        if(date_diff( $order->getUpdatedAt(), new \DateTime(date('d.m.Y 23:59:59')))->days == 0){
		        $discount_email = $setting->getDiscountEmailMarketing();
	        }
        }

        if(!empty($order->getPromoCodeDiscount())) {
	        $discount_promocode = $order->getPromoCodeDiscount();
        }

        if(!empty($order->getVideo()->getDiscount())) {
	        $discount_sale = $order->getVideo()->getDiscount();
        }

		$discount = (int)max($discount_email, $discount_promocode, $discount_sale);

        if($discount > 0) {
	        $total = $total - (($total * $discount) / 100);
        }

		$a_payment = array(
			'ik_co_id' => $setting->getApiKeyInterkassaMerchantId(),
			'ik_pm_no' => $ik_pm_no,
			'ik_am' => !empty($total) ? $total : 1.00,
			'ik_cur' => $ik_cur,
			'ik_suc_u' => $request->getUriForPath($successful_payment),
			'ik_fal_u' => $request->getUriForPath($unsuccessful_payment),
			'ik_ia_u' => $request->getUriForPath('/checkout/webhook/interkassa'),
			'ik_desc' => 'Оплата видео №'.$order->getId().' на VideoJoy',
            'ik_cli' => $order->getUsers()->getEmail()
		);
		
		//Если type visa или master card
		if($type == 1){
		    $a_payment['ik_pw_via'] = $ik_pw_via_visa;
            $a_payment['ik_act'] = 'process';
            $a_payment['ik_int'] = 'json';
        }elseif($type == 2){
            $a_payment['ik_pw_via'] = $ik_pw_via_mastercard;
            $a_payment['ik_act'] = 'process';
            $a_payment['ik_int'] = 'json';
        }
        
        //Платежные направления
        //qiwi_qiwi_merchantPsp_rub
        //mastercard_cpaytrz_merchant_uah
        //visa_cpaytrz_merchant_uah
        //qiwiterminal_qiwi_merchantTerminal_rub
        //mastercard_unitpay_merchant_rub
        //visa_unitpay_merchant_rub
        //mir_unitpay_merchant_rub
        //yandexmoney_unitpay_merchant_rub
        
		$order->setPaymentIdOrder($ik_pm_no);
		$this->manager->flush($order);
		
		$a_payment_old = $a_payment;
		
		ksort($a_payment, SORT_STRING);
		array_push($a_payment, $key);
		$signString = implode(':', $a_payment);
		$sign = base64_encode(md5($signString, true));
		
		$a_payment_old['ik_sign'] = $sign;
        
        if($type != 3) {
            //Получаем данные по ссылке для оплаты
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://sci.interkassa.com/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $a_payment_old);
            $output = curl_exec($ch);
            curl_close($ch);
    
            $array = json_decode($output, true);
            if ( ! empty($array)) {
                if ($array['resultMsg'] == 'Success') {
                    if (isset($array['resultData']['paymentForm']['action'])) {
                        $a_payment_old = $array['resultData']['paymentForm']['action'].'?'.http_build_query($array['resultData']['paymentForm']['parameters']);
                    }
                }
            }
        }
        
		return $a_payment_old;
	}
	
	public function paymentLiqPay(Request $request, Setting $setting, Order $order, Currency $currency, $successful_payment){
		
		$orderID = $order->getId().'-'.time();
		$price = $order->getPrice();
		$priceISO = $currency->getCodeISO();
		
		if($currency->getCodeISO() == 'RUB'){
			$currencyNew = $this->manager->getRepository('App\Entity\Currency')->findOneBy(['defaultCurrency' => 1]);
			if($currencyNew->getCodeISO() == 'USD'){
				$price = $order->getVideo()->getPriceUsd();
				$priceISO = $currencyNew->getCodeISO();
			}elseif($currencyNew->getCodeISO() == 'UAH'){
				$price = $order->getVideo()->getPriceUah();
				$priceISO = $currencyNew->getCodeISO();
			}elseif($currencyNew->getCodeISO() == 'EUR'){
				$price = $order->getVideo()->getPriceEur();
				$priceISO = $currencyNew->getCodeISO();
			}
		}
		
		/*
		 * Проверяем на наличие спец скидки
		 */
		if($order->getSentEmail() == 2){
			//Так как отправлено уже второе сообщение и оно содержит скидку, в этом случае если не прошел день - скидка есть
			if(date_diff( $order->getUpdatedAt(), new \DateTime(date('d.m.Y 23:59:59')))->days == 0){
				$price = ($price * $setting->getDiscountEmailMarketing()) / 100;
			}
		}
		
		$liqPay = new LiqPay($setting->getApiKeyLiqPayPublicKey(), $setting->getApiKeyLiqPayPrivatKey());
		
		$form = $liqPay->cnb_form(array(
			'action'         => 'pay',
			'amount'         => !empty($price) ? $price : 1.00,
			'currency'       => $priceISO,
			'description'    => "Оплата видео №".$order->getId()." на VideoJoy",
			'order_id'       => $orderID,
			'version'        => '3',
			'result_url'     => $request->getUriForPath($successful_payment.'/?order='.$order->getId()),
			'server_url'     => $request->getUriForPath('/checkout/webhook/liqpay'),
			'product_name'   => 'Видео №'.$order->getId().' на VideoJoy',
			'product_description' => "Оплата видео №".$order->getId()." на VideoJoy"
		));
		
		$order->setPaymentIdOrder($orderID);
		$this->manager->flush($order);
		
		return $form;
	}


    public function paymentInterkassaSubscription(Request $request, Setting $setting, Subscription $subscription, $successful_payment, $unsuccessful_payment, $type){

        $key = $setting->getApiKeyInterkassaSecretKey();

        $ik_pm_no =  $subscription->getId().'-'.time();

        /*
         * Проверяем валюту
         * Если UAH, устанавливаем параметры платёжного шлюза для гривный
         * Если не UAH, то по дефолту используем настройки для рубля
         * */
        $currency = $this->manager->getRepository('App\Entity\Currency')->findOneBy([ 'name' => $subscription->getPriceCurrency() ]);
        if( $currency->getCodeISO() === 'UAH' ) {
            $total = $subscription->getSubscriptionType()->getPriceUah();
            $ik_cur = 'UAH';
            $ik_pw_via_visa = 'visa_cpaytrz_merchant_uah';
            $ik_pw_via_mastercard = 'mastercard_cpaytrz_merchant_uah';
        } else {
            $total = $subscription->getSubscriptionType()->getPriceRub();
            $ik_cur = 'RUB';
            $ik_pw_via_visa = 'visa_unitpay_merchant_rub';
            $ik_pw_via_mastercard = 'mastercard_unitpay_merchant_rub';
        }

        $discount = 0;

        if( $subscription->getSubscriptionType()->getDiscount() ) {
            $discount = $subscription->getSubscriptionType()->getDiscount();
        }

        if(!empty($subscription->getPromoCodeDiscount()) && $discount < $subscription->getPromoCodeDiscount()) {
            $discount = $subscription->getPromoCodeDiscount();
        }

        if( $discount > 0 ) {
            $finalPrice = round($total - (($total * $discount) / 100), 2);
        } else {
            $finalPrice = $total;
        }

        $a_payment = array(
            'ik_co_id' => $setting->getApiKeyInterkassaMerchantId(),
            'ik_pm_no' => $ik_pm_no,
            'ik_am' => !empty($finalPrice) ? $finalPrice : 1.00,
            'ik_cur' => $ik_cur,
            'ik_suc_u' => $request->getUriForPath($successful_payment),
            'ik_fal_u' => $request->getUriForPath($unsuccessful_payment),
            'ik_ia_u' => $request->getUriForPath('/subscription/webhook/interkassa'),
            'ik_desc' => 'Оплата подписки №'.$subscription->getId().' на VideoJoy',
            'ik_cli' => $subscription->getUser()->getEmail(),
        );

        if($type == 1){
            $a_payment['ik_pw_via'] = $ik_pw_via_visa;
            $a_payment['ik_act'] = 'process';
            $a_payment['ik_int'] = 'json';
        }elseif($type == 2){
            $a_payment['ik_pw_via'] = $ik_pw_via_mastercard;
            $a_payment['ik_act'] = 'process';
            $a_payment['ik_int'] = 'json';
        }


        $subscription->setPaymentIdOrder($ik_pm_no);
        $this->manager->flush($subscription);

        $a_payment_old = $a_payment;

        ksort($a_payment, SORT_STRING);
        array_push($a_payment, $key);
        $signString = implode(':', $a_payment);
        $sign = base64_encode(md5($signString, true));

        $a_payment_old['ik_sign'] = $sign;

        if($type != 3) {
            //Получаем данные по ссылке для оплаты
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://sci.interkassa.com/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $a_payment_old);
            $output = curl_exec($ch);
            curl_close($ch);

            $array = json_decode($output, true);
            if ( ! empty($array)) {
                if ($array['resultMsg'] == 'Success') {
                    if (isset($array['resultData']['paymentForm']['action'])) {
                        $a_payment_old = $array['resultData']['paymentForm']['action'].'?'.http_build_query($array['resultData']['paymentForm']['parameters']);
                    }
                }
            }
        }

        return $a_payment_old;
    }
	
}