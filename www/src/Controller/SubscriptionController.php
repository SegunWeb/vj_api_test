<?php

namespace App\Controller;

use App\Constants\ActiveConstants;
use App\Constants\MailEventConstants;
use App\Entity\Setting;
use App\Entity\Subscription;
use App\Entity\User;
use App\Service\MailTemplate;
use App\Service\RenderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PaymentService;
use Symfony\Contracts\Translation\TranslatorInterface;

class SubscriptionController extends AbstractController
{

    /**
     * @Route("/subscription/payment_result", name="subscription_payment", defaults={"_locale"="%locale%"}, requirements={"_locale"="%locales_in_line%", "id"="\d+"}, methods={"GET", "POST"})
     */
    public function subscriptionResultPayment( Request $request, TranslatorInterface $translator, RenderService $service, MailTemplate $template )
    {

        $setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);

        $text = $translator->trans('front.payment_not_found');

        //Проверка PayPal
        // http://127.0.0.1:8000/subscription/payment_result?paymentId=PAYID-L3XXNVQ5JU72815LN683000A&token=EC-3U33108957798874T&PayerID=2DKJJCNQ4XT2Y
        if($request->query->has('paymentId') && $request->query->has('PayerID')){

            $paymentId = $request->query->get('paymentId');
            $payerID = $request->query->get('PayerID');
            $subscription = $this->getDoctrine()->getRepository('App\Entity\Subscription')->findOneBy(['paymentIdOrder' => $paymentId]);
            if(!empty($subscription)){
                $this->checkoutPayPal( $subscription, $paymentId, $payerID, $setting, $request, $service, $template );
                if( $subscription->getRequestFromOrderId() ) {
                    $order = $this->getDoctrine()->getRepository( 'App\Entity\Order' )->findOneBy( [ 'id' => $subscription->getRequestFromOrderId() ] );
                    if($order && $order->getId()) {
                        return $this->redirectToRoute( 'full_video_render_processing', [ 'id' => $order->getId(), 'slug' => $order->getVideo()->getSlug() ] );
                    }
                }
                return $this->redirectToRoute('subscription_success_payment');
            }
        }

        //Проверка на Interkassa
        if($request->request->has('ik_pm_no')){
            $paymentId = $request->request->get('ik_pm_no');
            $exp = explode('-', $paymentId);
            $paymentId = $exp[0];
            $subscription = $this->getDoctrine()->getRepository('App\Entity\Subscription')->findOneBy(['id' => $paymentId]);
            if(!empty($subscription)){
                if( $subscription->getRequestFromOrderId() ) {
                    $order = $this->getDoctrine()->getRepository( 'App\Entity\Order' )->findOneBy( [ 'id' => $subscription->getRequestFromOrderId() ] );
                    if($order && $order->getId()) {
                        return $this->redirectToRoute( 'full_video_render_processing', [ 'id' => $order->getId(), 'slug' => $order->getVideo()->getSlug() ] );
                    }
                }

                return $this->redirectToRoute('subscription_success_payment');
            }
        }
        $meta = (object) [
            'title'       => $setting->getMetaTitle(),
            'description' => $setting->getMetaDescription(),
            'keywords'    => $setting->getMetaKeywords(),
            'image'       => $setting->getMetaImage(),
            'canonical'   => $setting->getMetaCanonical(),
        ];

        return $this->render('page/successful_pay_page.html.twig', ['text'=> $text, 'meta' => $meta, 'title' => $translator->trans('check_payment_video'), 'setting' => $setting]);
    }

    private function checkoutPayPal( $subscription, $paymentId, $payerID, Setting $setting, Request $request, RenderService $service, MailTemplate $template  )
    {

        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $setting->getApiKeyPayPalClientId(),
                $setting->getApiKeyPayPalClientSecret()
            )
        );

        $payment = \PayPal\Api\Payment::get($paymentId, $apiContext);
        $execution = new \PayPal\Api\PaymentExecution();
        $execution->setPayerId($payerID);
        try {
            // Take the payment
            $payment->execute($execution, $apiContext);
            try {
                $this->updateSubscriptionAfterPay( $subscription, 'PayPal', $request, $service );
            } catch (Exception $e) {
                // Failed to retrieve payment from PayPal
            }

        } catch (\Exception $e) {
            // Failed to take payment
        }
    }

    /**
     * @Route("/subscription/success_payment", name="subscription_success_payment", defaults={"_locale"="%locale%"}, requirements={"_locale"="%locales_in_line%", "id"="\d+"}, methods={"GET", "POST"})
     */
    public function subscriptionSuccessfulPayment( Request $request, TranslatorInterface $translator )
    {

        $setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);

        $text = $translator->trans('front.payment_success');

        $meta = (object) [
            'title'       => $setting->getMetaTitle(),
            'description' => $setting->getMetaDescription(),
            'keywords'    => $setting->getMetaKeywords(),
            'image'       => $setting->getMetaImage(),
            'canonical'   => $setting->getMetaCanonical(),
        ];

        return $this->render('page/successful_pay_page.html.twig', ['text'=> $text, 'meta' => $meta, 'title' => $translator->trans('check_payment_video'), 'setting' => $setting]);
    }

    /**
     * @Route("/subscription/{id}", name="subscription", defaults={"_locale"="%locale%"}, requirements={"_locale"="%locales_in_line%", "id"="\d+"}, methods={"POST"})
     * @param $id
     * @param  Request  $request
     *
     * @param  TranslatorInterface  $translator
     *
     * @return Response
     */
    public function createSubscription( $id, Request $request, TranslatorInterface $translator, RenderService $renderService )
    {
        $user = $this->getUser();

        $subscriptionType = $this->getDoctrine()->getRepository( 'App\Entity\SubscriptionType' )->find($id);

        if ( $user && $subscriptionType ) {

            //Если у пользователя определена страна, то определяем курс валюты привязаный к стране (если их несколько то берем лишь первую)
            if(!empty($user->getCountry())){
                if($user->getCountry()->getCurrency()->isEmpty() == false){
                    $currency = $user->getCountry()->getCurrency()->first();
                    $price = $subscriptionType->getPriceByISO($currency->getCodeISO());
                }
            }

            if( empty( $currency ) ){
                $currency = $this->getDoctrine()->getRepository( 'App\Entity\Currency' )->findOneBy( [ 'defaultCurrency' => 1 ] );
                $price    = $subscriptionType->getPriceByISO( $currency->getCodeISO() );
            }

            $code = 200;
            $message = '';
            $promocode = null;
            $promocodeDiscount = null;

            if( $request->request->get('promocode') ) {
                $promocode = $this->getDoctrine()->getRepository('App\Entity\PromoCode')->findOneBy(['promoCode' => $request->request->get('promocode'), 'active' => ActiveConstants::ACTIVE]);

                if( empty( $promocode ) ) {
                    $code = 400; $message = $translator->trans('front.promotional_code_is_not_valid');
                }

                if( $code != 400 && $promocode->getDateEndOfAction() <= new \DateTime('NOW') ) {
                    $code = 400; $message = $translator->trans('front.promotional_code_has_reached_the_limit_of_use');
                }

                if( $code != 400 && $promocode->getNumberOfUses() <= 0 ) {
                    $code = 400; $message = $translator->trans('front.promotional_code_is_not_valid');
                }

                if( $code == 400 ) {
                    return new JsonResponse( [ 'code' => $code, 'message' => $message ], 200 );
                }

                $promocode->setNumberOfUses( $promocode->getNumberOfUses() - 1 );
                $this->getDoctrine()->getManager()->flush( $promocode );

                if( !empty( $promocode->getDiscount() ) ) {
                    $promocodeDiscount = $promocode->getDiscount();
                } else {
                    $promocodeDiscount = 'free';
                }
            }

            $paid = ( $request->request->get('paid') ) ? $request->request->get('paid') : 3;
            $order_id = ( $request->request->get('order_id') ) ? $request->request->get('order_id') : false;

            $newSubscription = new Subscription();
            $newSubscription->setSubscriptionType( $subscriptionType );
            $newSubscription->setUser( $user );
            $newSubscription->setPrice( !empty($price) ? $price : $subscriptionType->getPriceUsd() );
            $newSubscription->setPriceCurrency( $currency->getName() );
            $newSubscription->setCurrencyDefault( $subscriptionType->getPriceUsd() * $currency->getCourse() );
            $newSubscription->setActive(ActiveConstants::ORDER_NOT_PAID_VALUE);

            if( $order_id ) {
                $newSubscription->setRequestFromOrderId( $order_id );
            }

            if( $promocode ) {
                if( $promocodeDiscount == 'free' ) {
                    $newSubscription->setPromoCode($promocode->getPromoCode());
                    $newSubscription->setPaymentMethod('free');
                    $newSubscription->firstActivate();
                } else {
                    $newSubscription->setPromoCode($promocode->getPromoCode());
                    $newSubscription->setPromoCodeDiscount($promocode->getDiscount());
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist( $newSubscription );
            $em->flush();

            if ( $promocodeDiscount !== 'free' ) {

                $setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);

                $paymentService = new PaymentService($em);
                if( $paid == 'paypal' ) {
                    $payPal = $paymentService->paymentPayPal($request, $setting, $newSubscription, '/subscription/payment_result', '/subscription/payment_result');
                    $result['link'] = $payPal != false ? $payPal : '';
                    return new JsonResponse( $result, 200 );
                } else {
                    $interkassa = $paymentService->paymentInterkassaSubscription( $request, $setting, $newSubscription, '/subscription/payment_result', '/subscription/payment_result', $paid );

                    $result = [];
                    if(is_array($interkassa)){
                        $response = new Response(
                            $this->render( 'form/payment_interkassa.html.twig', [ 'a_payment' => $interkassa ] )->getContent(),
                            Response::HTTP_OK,
                            array( 'content-type' => 'text/html' )
                        );
                        $result['form'] = $response->getContent();
                    }else{
                        $result['link'] = $interkassa;
                    }
                }


                return new JsonResponse( $result, 200 );
            } else {
                if( $order_id ) {
                    $order = $this->getDoctrine()->getRepository( 'App\Entity\Order' )->findOneBy( [ 'id' => $order_id ] );
                }
                if(!empty($order) && $order->getId()) {
                    $order->setActive( ActiveConstants::ORDER_SUBSCRIPTION_VALUE );
                    $order->setPaymentMethod('subscription');
                    $this->getDoctrine()->getManager()->flush( $order );

                    $renderService->renderFullVideo( $request, $order );
                    $result['link'] =  $this->generateUrl( 'full_video_render_processing', [ 'id' => $order->getId(), 'slug' => $order->getVideo()->getSlug() ] );
                } else {
                    $result['link'] = $this->generateUrl('subscription_success_payment');
                }
                return new JsonResponse( $result, 200 );
            }

        }

        return new Response("Error");
    }

    /**
     * @Route("/subscription/webhook/interkassa", name="subscription_webhook_interkassa", defaults={"_locale"="%locale%"}, requirements={"_locale"="%locales_in_line%", "id"="\d+"}, methods={"GET", "POST"})
     */
    public function subscriptionWebHookInterkassa( Request $request, RenderService $renderService )
    {

        $setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);

        if($request->request->has('ik_inv_id')){

            //Проверка на статус, если оплачено то продолжаем
            if($request->request->get('ik_inv_st') == 'success') {

                $key = $setting->getApiKeyInterkassaSecretKey();

                //Запоминаем подпись
                $signOld = $request->request->get('ik_sign');

                //Удаляем из массива
                $request->request->remove('ik_sign');

                //Забираем все POST данные
                $dataSet = $request->request->all();

                //Формируем подпись
                ksort($dataSet, SORT_STRING);
                array_push($dataSet, $key);
                $signString = implode(':', $dataSet);
                $sign = base64_encode(md5($signString, true));

                if($signOld == $sign) {
                    $paymentId = $request->request->get( 'ik_pm_no' );
                    $exp       = explode( '-', $paymentId );
                    $paymentId = $exp[0];
                    $subscription     = $this->getDoctrine()->getRepository( 'App\Entity\Subscription' )->findOneBy( [ 'id' => $paymentId ] );

                   $this->updateSubscriptionAfterPay( $subscription, 'interkassa.com', $request, $renderService );
                }
            }
        }

        return $this->render('page/payment_page.html.twig', ['text' => 'OK', 'title' => 'Оплата PayPal', 'setting' => $setting]);
    }

    private function updateSubscriptionAfterPay( $subscription, $paymentMethod, Request $request, RenderService $renderService ) {
        if ( $subscription->getActive() !== ActiveConstants::ORDER_PAID_VALUE ) {

            $subscription->setPaymentMethod($paymentMethod);
            $subscription->firstActivate();
            $this->getDoctrine()->getManager()->flush( $subscription );

            if( $subscription->getRequestFromOrderId() ) {
                $order = $this->getDoctrine()->getRepository( 'App\Entity\Order' )->findOneBy( [ 'id' => $subscription->getRequestFromOrderId() ] );

                if( $order ) {
                    $order->setActive( ActiveConstants::ORDER_SUBSCRIPTION_VALUE );
                    $order->setPaymentMethod('subscription');
                    $this->getDoctrine()->getManager()->flush( $order );

                    //Запускаем рендеринг полного видео
                    $renderService->renderFullVideo( $request, $order );
                }
            }

            //$template->sendMailMessages( MailEventConstants::SUCCESSFUL_PAYMENT, MailEventConstants::SUCCESSFUL_PAYMENT_VALUES, (object) $object, $order->getUsers()->getEmail(), $unsubscribe );

        }
    }

    public function expiredSubscriptions()
    {
        $subscriptions = $this->getDoctrine()->getRepository( 'App\Entity\Subscription' )->findForDisable();

        foreach ( $subscriptions as $subscription ) {
            $subscription->setActive(0);
            $this->getDoctrine()->getManager()->flush( $subscription );
        }
    }

}
