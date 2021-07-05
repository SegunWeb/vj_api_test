<?php

namespace App\Controller;

use App\Constants\MailEventConstants;
use App\Constants\PaymentConstants;
use App\Entity\Order;
use App\Entity\Setting;
use App\Helper\Form;
use App\Entity\VideoRender;
use App\Helper\LiqPay;
use App\Service\MailTemplate;
use App\Service\RenderService;
use App\Service\PaymentService;
use App\Constants\ActiveConstants;
use App\Constants\TypePageConstants;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use CV\CascadeClassifier;
use CV\Scalar;
use function CV\{imread, imwrite, cvtColor, equalizeHist, rectangleByRect};
use const CV\{COLOR_BGR2GRAY};

class VideoController extends AbstractController
{
	/**
	 * @Route("/movie/{slug}", name="movie", methods={"GET"}, defaults={"_locale"="%locale%"}, requirements={"_locale" = "%locales_in_line%"})
	 */
	public function movie( $slug, Form $form, Request $request, TranslatorInterface $translator, RouterInterface $router )
	{
		$video = $this->getDoctrine()->getRepository('App\Entity\Video')->findOneBy(['slug' => $slug]);
		$category = null;
		//Ищем категорию с которой пришел к нам пользователь
		$refererPathInfo = Request::create($request->headers->get('referer'))->getPathInfo();
		$refererPathInfo = str_replace($request->getScriptName(), '', $refererPathInfo);
		$routeInfos = $router->match($refererPathInfo);
		if(!empty($routeInfos['slug'])){
			$category = $this->getDoctrine()->getRepository('App\Entity\VideoCategories')->findOneBy(['slug' => $routeInfos['slug']]);
		}
		if(empty($category)){
			$category = $video->getCategory()->first();
		}
		
		$catalog = $this->getDoctrine()->getRepository( 'App\Entity\Page' )->getSlugPage( TypePageConstants::CATEGORIES_VIDEO_VALUES, $request->getLocale() );
		
		$breadcrumbs[] = ['url' => $this->generateUrl('home'), 'title' => $translator->trans('front.home')];
		
		$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $catalog['slug']]), 'title' => $translator->trans('front.all_video_categories')];
		
		if(!empty($category)){
			$breadcrumbs[] = ['url' => $this->generateUrl('video_category', ['slug' => $category->getSlug()]), 'title' => $category->getTitle() ];
		}
		
		$breadcrumbs[] = ['url' => $this->generateUrl('movie', ['slug' => $slug]), 'title' => !empty($video) ? $video->getTitle() : ''];
		
		$setting = $this->getDoctrine()->getRepository( 'App\Entity\Setting' )->dataForTheMainPage();
		
		$settingForming = $form->getFormingFrom( $setting );
		
		$setting = $settingForming->setting[0];
		
		$currency = null;
		
		if(!empty($this->getUser())){
			if(!empty($this->getUser()->getCountry())){
				if($this->getUser()->getCountry()->getCurrency()->isEmpty() == false) {
					$currency = $this->getUser()->getCountry()->getCurrency()->first();
					if($currency->getCodeISO() == 'USD'){ $currency = null; }
				}
			}
		}else{
			if($request->cookies->get('currency') != 'USD'){
				$currency = $this->getDoctrine()->getRepository('App\Entity\Currency')->findOneBy(['codeISO' => $request->cookies->get('currency') ]);
			}
		}

		$dynamic['currency'] = $currency;
		
		//Данные мета. По умолчанию заполненные в настройках
		$meta = (object) [
			'title'       => $setting->getMetaTitle(),
			'description' => $setting->getMetaDescription(),
			'keywords'    => $setting->getMetaKeywords(),
			'image'       => $setting->getMetaImage(),
			'canonical'   => $setting->getMetaCanonical(),
		];
		$template = (!empty($video) && $video->getVariation() == 3) ? 'page/page_pers_video.html.twig' : 'page/page_video.html.twig';

		return $this->render($template, [
			'video'      => $video,
			'page'       => (object)['pageContentSeo' => !empty($video) ? $video->getPageContentSeo() : '' ],
			'meta'       => $meta,
			'setting'    => $setting,
			'dynamic'    => $dynamic,
			'currency'   => $currency,
			'breadcrumbs'=> $breadcrumbs,
			'menuHeader' => $settingForming->header,
			'menuFooter' => $settingForming->footer
		] );
		
	}
	
	/**
	 * @Route("/video/{slug}", name="video", methods={"GET", "POST"}, defaults={"_locale"="%locale%"},
	 *     requirements={"_locale" = "%locales_in_line%"})
	 */
	public function index( $slug, Form $form, Request $request, RenderService $renderService, TranslatorInterface $translator )
	{
		$breadcrumbs[] = ['url' => $this->generateUrl('home'), 'title' => $translator->trans('front.home')];
		
		$catalog = $this->getDoctrine()->getRepository( 'App\Entity\Page' )->getSlugPage( TypePageConstants::ALL_VIDEO_VALUES, $request->getLocale() );
		if(!empty($catalog)){
			$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $catalog['slug']]), 'title' => $translator->trans('front.catalog')];
		}
		
		$video = $this->getDoctrine()->getRepository( 'App\Entity\Video' )->findOneBy( ['slug' => $slug] );
		
		$breadcrumbs[] = ['url' => $this->generateUrl('movie', ['slug' => $slug]), 'title' => !empty($video) ? $video->getTitle() : ''];
		
		$breadcrumbs[] = ['url' => $this->generateUrl('video', ['slug' => $slug]), 'title' => $translator->trans('front.create_a_clip')];
		
		$page = $this->getDoctrine()->getRepository( 'App\Entity\Page' )->findOneBy( [ 'type' => TypePageConstants::VIDEO_VALUES ] );
		
		$setting = $this->getDoctrine()->getRepository( 'App\Entity\Setting' )->dataForTheMainPage();
		
		$settingForming = $form->getFormingFrom( $setting );
		
		$setting = $settingForming->setting[0];
		
		$firstName = $this->getDoctrine()->getRepository( 'App\Entity\FirstName' )->findBy(['locale' => $request->getLocale()]);
		
		//Данные мета. Сначала берутся данные страницы, в случае отсутствия - по умолчанию заполненные в настройках
		$meta = (object) [
			'title'       => ! empty( $page ) ? $page->getMetaTitle() : $setting->getMetaTitle(),
			'description' => ! empty( $page ) ? $page->getMetaDescription() : $setting->getMetaDescription(),
			'keywords'    => ! empty( $page ) ? $page->getMetaKeywords() : $setting->getMetaKeywords(),
			'image'       => ! empty( $page ) ? $page->getMetaImage() : $setting->getMetaImage(),
			'canonical'   => ! empty( $page ) ? $page->getMetaCanonical() : $setting->getMetaCanonical(),
		];
		
		$placeholders = [];
		
		if ( ! empty( $video ) ) {
			if ( $video->getPlaceholder()->isEmpty() == false ) {
				foreach ( $video->getPlaceholder()->toArray() as $videoPlaceholder ) {
					$placeholders[ $videoPlaceholder->getType() ][] = $videoPlaceholder;
				}
			}
		}
		
		$requestCheckPost = $request->request->all();
		if ( !empty($requestCheckPost) ) {

			if ( ! empty( $video ) ) {
				
				$action = $request->request->get('action');
				$renderStatus = false;
				if( $action == 'create'){
					$render = $renderService->preRender( $request, $video, $this->getUser() );
				} elseif ( $action == 'image'){
					$render = $renderService->preUploadFile('image', $request->request->get('file') );
				} elseif ( $action == 'video'){
					$render = $renderService->preUploadFile('video', $request->files->get('file') );
				} elseif ( $action == 'postcard_full'){
					$render = $renderService->preUploadFile('postcard_full', $request->request->get('file') );
				} elseif ( $action == 'postcard_image' ){
					$render = $renderService->preUploadFile('postcard_image', $request->request->get('file') );
				} elseif ( $action == 'postcard_mouth'){
					$render = $renderService->preUploadFile('postcard_mouth', $request->request->get('file') );
				}else {
                    $lastActiveSubscription = $this->getDoctrine()->getRepository( 'App\Entity\Subscription' )->getLastActiveByUser($this->getUser());
				    $demo = ( $lastActiveSubscription ) ? false : true;
				    $activeSubscription = ( $lastActiveSubscription ) ? true : false;

					$render = $renderService->render( $request, $video, $this->getUser(), $demo, $activeSubscription );
                    $renderStatus = true;
				}
				
				return new JsonResponse( array(
					'list' => $renderStatus == true ? $this->generateUrl('video_render_processing', ['id' => $render, 'slug' => $video->getSlug()]) : $render,
					'code'    => is_array($render) ? 400 : 200,
				), 200 );
				
			}
		}

        $redirect_uri = 'https://'.$request->getHttpHost().$request->getBaseUrl().'/authorize/facebook';
        $url = 'https://www.facebook.com/dialog/oauth';
        $params = array(
            'client_id'     => $setting->getApiKeyFacebookClientId(),
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'scope'         => 'email'
        );

        $link = $url . '?' . urldecode(http_build_query($params));
		
		return $this->render( 'page/build_movie.html.twig', [
			'video'        => $video,
			'placeholders' => $placeholders,
			'firstName'    => $firstName,
			'meta'         => $meta,
			'setting'      => $setting,
			'breadcrumbs'  => $breadcrumbs,
			'menuHeader'   => $settingForming->header,
			'menuFooter'   => $settingForming->footer,
            'facebookLink' => $link
		] );
	}
	
	/**
	 * @Route("/video/edit/{slug}/{id}", name="video_edit_render", methods={"GET", "POST"}, defaults={"_locale"="%locale%"},
	 *     requirements={"_locale" = "%locales_in_line%", "id"="\d+"})
	 */
	public function videoEdit( $slug, $id, Request $request, RenderService $service, Form $form )
	{
		if ( empty( $this->getUser() ) ) {
			return $this->redirectToRoute('authorization', [], 301);
		}
		$order = $this->getDoctrine()->getRepository( 'App\Entity\Order' )->findOneBy( [
			'id'    => $id,
			'users' => $this->getUser(),
			'removeFiles' => 0
		] );
		
		if ( ! empty( $order ) ) {
			$video  = $order->getVideo();
			$render = $order->getRender()->last();
			
			$placeholders_render = [];
			
			if ( ! empty( $video ) ) {
				if ( $render->getPlaceholder()->isEmpty() == false ) {
					foreach ( $render->getPlaceholder()->toArray() as $videoPlaceholder ) {
						if(!empty($videoPlaceholder->getPlaceholderParent())) {
							$placeholders_render[ $videoPlaceholder->getType() ][ $videoPlaceholder->getPlaceholderParent()->getId() ] = $videoPlaceholder;
						}
					}
				}
			}
		}
		
		$requestCheckPost = $request->request->all();
		if ( !empty($requestCheckPost) ) {
			
			if ( ! empty( $order ) ) {
				
				$action = $request->request->get('action');
                $renderStatus = false;
                
				if ( $action == 'image'){
					$renders = $service->preUploadFile('image', $request->request->get('file') );
				} elseif ( $action == 'video'){
					$renders = $service->preUploadFile('video', $request->files->get('file') );
                } elseif ( $action == 'postcard_full'){
					$renders = $service->preUploadFile('postcard_full', $request->request->get('file') );
				} elseif ( $action == 'postcard_image' ){
					$renders = $service->preUploadFile('postcard_image', $request->request->get('file') );
				} elseif ( $action == 'postcard_mouth'){
					$renders = $service->preUploadFile('postcard_mouth', $request->request->get('file') );
				} else {
                    $renderStatus = true;
					$renders = $service->renderEdit( $request, $order, $this->getUser(), isset( $placeholders_render ) ? $placeholders_render : null, $order->getActive() == 1 or $order->getActive() == 2 ? false : true );
				}
				
				return new JsonResponse( array(
                    'list' => $renderStatus == true ? $this->generateUrl('video_render_processing', ['id' => $renders, 'slug' => $video->getSlug()]) : $renders,
					'code'    => $renders ? 200 : 400,
				), 200 );
				
			}
		}
		
		$page = $this->getDoctrine()->getRepository( 'App\Entity\Page' )->findOneBy( [ 'type' => TypePageConstants::VIDEO_VALUES ] );
		
		$setting = $this->getDoctrine()->getRepository( 'App\Entity\Setting' )->dataForTheMainPage();
		
		$settingForming = $form->getFormingFrom( $setting );
		
		$setting = $settingForming->setting[0];
		
		$firstName = $this->getDoctrine()->getRepository( 'App\Entity\FirstName' )->findBy(['locale' => $request->getLocale()]);
		
		//Данные мета. Сначала берутся данные страницы, в случае отсутствия - по умолчанию заполненные в настройках
		$meta = (object) [
			'title'       => ! empty( $page ) ? $page->getMetaTitle() : $setting->getMetaTitle(),
			'description' => ! empty( $page ) ? $page->getMetaDescription() : $setting->getMetaDescription(),
			'keywords'    => ! empty( $page ) ? $page->getMetaKeywords() : $setting->getMetaKeywords(),
			'image'       => ! empty( $page ) ? $page->getMetaImage() : $setting->getMetaImage(),
			'canonical'   => ! empty( $page ) ? $page->getMetaCanonical() : $setting->getMetaCanonical(),
		];
		
		$placeholders = [];
		
		if ( ! empty( $video ) ) {
			if ( $video->getPlaceholder()->isEmpty() == false ) {
				foreach ( $video->getPlaceholder()->toArray() as $videoPlaceholder ) {
					$placeholders[ $videoPlaceholder->getType() ][] = $videoPlaceholder;
				}
			}
		}

        $redirect_uri = 'https://'.$request->getHttpHost().$request->getBaseUrl().'/authorize/facebook';
        $url = 'https://www.facebook.com/dialog/oauth';
        $params = array(
            'client_id'     => $setting->getApiKeyFacebookClientId(),
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'scope'         => 'email'
        );

        $link = $url . '?' . urldecode(http_build_query($params));
		
		return $this->render( 'page/build_movie.html.twig', [
			'video'             => ! empty( $video ) ? $video : null,
			'order'             => $order,
			'render'            => ! empty( $render ) ? $render->getPlaceholder()->toArray() : null,
			'placeholderRender' => isset( $placeholders_render ) ? $placeholders_render : null,
			'placeholders'      => $placeholders,
			'firstName'         => $firstName,
			'meta'              => $meta,
			'setting'           => $setting,
			'menuHeader'        => $settingForming->header,
			'menuFooter'        => $settingForming->footer,
            'facebookLink' => $link
		] );
	}
	
	/**
     * @Route("/processing/video/{slug}/{id}", name="video_render_processing", methods={"GET"}, defaults={"_locale"="%locale%"}, requirements={"_locale" = "%locales_in_line%", "id"="\d+"})
     * @Route("/processing/video/2/{slug}/{id}", name="full_video_render_processing", methods={"GET"}, defaults={"_locale"="%locale%"}, requirements={"_locale" = "%locales_in_line%", "id"="\d+"})
	 */
	public function processingVideoRender($slug, $id, Form $form, Request $request )
	{
		if ( empty( $this->getUser() ) ) {
			return $this->redirectToRoute('authorization', [], 301);
		}
		$orderObject = $this->getDoctrine()->getRepository( 'App\Entity\Order' )->findOneBy( [
			'id'    => $id,
			'users' => $this->getUser()
		] );
		
		if ( empty( $orderObject ) ) {
			throw new AccessDeniedException( 'Unable to access this page!' );
		}
		
		/** @var VideoRender * */
		$render = $orderObject->getRender()->last();
		
		if($render->getType() != 2 and $request->attributes->get('_route') == 'full_video_render_processing'){
		    return $this->redirectToRoute('video_render_processing', ['id' => $id, 'slug' => $slug], 301);
        }elseif($render->getType() == 2 and $request->attributes->get('_route') == 'video_render_processing'){
            return $this->redirectToRoute('full_video_render_processing', ['id' => $id, 'slug' => $slug], 301);
        }
		
		//Если перешли на страницу, но видео уже отрендерилось
		if ( $render->getStatus() == 'finished' or $render->getStatus() == 'processing' ) {
			if ( $render->getType() == 2 ) {
				return $this->redirectToRoute( 'video_full_viewing', [ 'id' => $id, 'slug' => $orderObject->getVideo()->getSlug() ] );
			} else {
				return $this->redirectToRoute( 'video_demo_viewing', [ 'id' => $id, 'slug' => $orderObject->getVideo()->getSlug()  ] );
			}
		}
		
		$setting = $this->getDoctrine()->getRepository( 'App\Entity\Setting' )->dataForTheMainPage();
		
		$settingForming = $form->getFormingFrom( $setting );
		
		$setting = $settingForming->setting[0];
		
		//Данные мета. По умолчанию заполненные в настройках
		$meta = (object) [
			'title'       => $setting->getMetaTitle(),
			'description' => $setting->getMetaDescription(),
			'keywords'    => $setting->getMetaKeywords(),
			'image'       => $setting->getMetaImage(),
			'canonical'   => $setting->getMetaCanonical(),
		];

		return $this->render( $render->getType() == 2 ? 'page/pay_page_after.html.twig' : 'page/pay_page_before.html.twig', [
			'order'      => $orderObject,
			'preloader'  => $orderObject->getVideo()->getPreloader(),
			'render'     => $render,
			'meta'       => $meta,
			'setting'    => $setting,
			'menuHeader' => $settingForming->header,
			'menuFooter' => $settingForming->footer
		] );
	}
	
	/**
	 * @Route("/video/demo/{slug}/{id}", name="video_demo_viewing", methods={"GET"}, defaults={"_locale"="%locale%"},
	 *     requirements={"_locale" = "%locales_in_line%", "id"="\d+"})
	 */
	public function videoDemoViewing( $slug, $id, Form $form )
	{
		
		if ( empty( $this->getUser() ) ) {
			return $this->redirectToRoute('authorization', [], 301);
		}
		$orderObject = $this->getDoctrine()->getRepository( 'App\Entity\Order' )->findOneBy( [
			'id'    => $id,
			'users' => $this->getUser(),
			'removeFiles' => 0
		] );
		
		if ( empty( $orderObject ) ) {
			throw new AccessDeniedException( 'Unable to access this page!' );
		}
		
		$render = $orderObject->getRender()->last();
        //$render=null;
		$discount = null;
		$promoCode = '';
		if(!empty($render)) {
            //Если рендерится уже полная версия видео к заказу
            if ($render->getType() == 2) {
                //Проверяем готов ли рендеринг, если да то сразу к просмотру, а нет к ожиданию готовности
                if ($render->getStatus() == 'finished' or $render->getStatus() == 'processing') {
                    return $this->redirectToRoute('video_full_viewing',
                        ['id' => $id, 'slug' => $orderObject->getVideo()->getSlug()]);
                } else {
                    return $this->redirectToRoute('full_video_render_processing',
                        ['id' => $id, 'slug' => $orderObject->getVideo()->getSlug()]);
                }
            }
        }
		
		/*
		 * Проверяем на наличие спец скидки
		 */
		if($orderObject->getSentEmail() == 2){
			//Так как отправлено уже второе сообщение и оно содержит скидку, в этом случае если не прошел день - скидка есть
			if(date_diff( $orderObject->getUpdatedAt(), new \DateTime(date('d.m.Y 23:59:59')))->days == 0){
				$setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);
				$discount = $setting->getDiscountEmailMarketing();
			}
		}

        if(!empty($orderObject->getPromoCodeDiscount()) && $discount < $orderObject->getPromoCodeDiscount()) {
            $discount = $orderObject->getPromoCodeDiscount();
            $promoCode = $orderObject->getPromoCode();
        }
		
		$currency = $this->getDoctrine()->getRepository('App\Entity\Currency')->findOneBy([ 'name' => $orderObject->getPriceCurrency() ]);
		
		$setting = $this->getDoctrine()->getRepository( 'App\Entity\Setting' )->dataForTheMainPage();
		
		$settingForming = $form->getFormingFrom( $setting );
		
		$setting = $settingForming->setting[0];

		//Получаем подписку пользователя, если она есть
        $lastActiveSubscription =  $this->getDoctrine()->getRepository( 'App\Entity\Subscription' )->getLastActiveByUser($this->getUser());

        if( empty( $lastActiveSubscription ) ) {
            $lastSubscriptionType = $this->getDoctrine()->getRepository( 'App\Entity\SubscriptionType' )->getLastSubscriptionType();

            if( !empty( $lastSubscriptionType ) ) {
                if(!empty($this->getUser()->getCountry())){
                    if($this->getUser()->getCountry()->getCurrency()->isEmpty() == false){
                        $currency = $this->getUser()->getCountry()->getCurrency()->first();
                    }
                }

                if(empty($currency)){
                    $currency = $this->getDoctrine()->getRepository( 'App\Entity\Currency' )->findOneBy( [ 'defaultCurrency' => 1 ] );
                }

                $fullPrice = $lastSubscriptionType->getPriceByISO($currency->getCodeISO());
                $frontPrice = $lastSubscriptionType->getFrontPriceByISO($currency->getCodeISO());
                $subscriptionDiscount = $lastSubscriptionType->getDiscount();
                if( $subscriptionDiscount ) {
                    $finalPrice = round($fullPrice - (($fullPrice * $subscriptionDiscount) / 100), 2);
                } else {
                    $finalPrice = $fullPrice;
                }

                $price = [
                    'fullPrice' => $fullPrice,
                    'price' => $finalPrice,
                    'discount' => $subscriptionDiscount,
                    'sing'  => $currency->getSing(),
                    'frontPrice' => $frontPrice
                ];
            }
        }

        $dynamic['subscription'] = ( !empty( $lastActiveSubscription ) ) ? $lastActiveSubscription : false;
        $dynamic['subscription_type'] = ( !empty( $lastSubscriptionType ) ) ? $lastSubscriptionType : false;
        $dynamic['price'] = ( !empty( $price ) ) ? $price : false;

        $dynamic['is_subscribe_enable'] = false;
        $dynamic['is_purchase_enable'] = false;

        switch ($setting->getPaymentType()) {
            case (PaymentConstants::ONLY_SUBSCRIBE):
                $dynamic['is_subscribe_enable'] = true;
                $dynamic['is_purchase_enable'] = false;
                break;
            default:
                $dynamic['is_subscribe_enable'] = false;
                $dynamic['is_purchase_enable'] = true;
                break;
        }

        if( $dynamic['is_purchase_enable'] ) {
            $dynamic['invitation_purchase'] = $setting->getInvitationPurchase();
            $dynamic['description_purchase'] = $setting->getDescriptionPurchase();
        }

        //Данные мета. По умолчанию заполненные в настройках
		$meta = (object) [
			'title'        => $setting->getMetaTitle(),
			'description'  => $setting->getMetaDescription(),
			'keywords'     => $setting->getMetaKeywords(),
			'image'        => $setting->getMetaImage(),
			'canonical'    => $setting->getMetaCanonical(),
		];

		return $this->render( 'page/pay_page_before_demo.html.twig', [
			'order'        => $orderObject,
			'render'       => $render,
			'meta'         => $meta,
			'setting'      => $setting,
			'discount'     => $discount,
			'promoCode'    => $promoCode,
			'currency'     => $currency,
            'subscription' => $lastActiveSubscription,
			'menuHeader'   => $settingForming->header,
			'menuFooter'   => $settingForming->footer,
            'dynamic'      => $dynamic
		] );
		
	}
	
	/**
	 * @Route("/video/full/{slug}/{id}", name="video_full_viewing", methods={"GET"}, defaults={"_locale"="%locale%"},
	 *     requirements={"_locale" = "%locales_in_line%", "id"="\d+"})
	 */
	public function videoFullViewing( $slug, $id, Form $form )
	{
		
//		if ( empty( $this->getUser() ) ) {
//			return $this->redirectToRoute('authorization', [], 301);
//		}
		$orderObject = $this->getDoctrine()->getRepository( 'App\Entity\Order' )->findOneBy( [
			'id'    => $id,
			//'users' => $this->getUser(),
			'removeFiles' => 0
		] );
		
		if ( empty( $orderObject ) ) {
			throw new AccessDeniedException( 'Unable to access this page!' );
		}
		
		$render = $orderObject->getRender()->last();
		
		//Проверяем на тип последнего рендеринга, если рендерится не полная версия возвращаем на страницу ожидания рендеринга
//		if ( $render->getType() != 2 ) {
//			return $this->redirectToRoute( 'video_render_processing', [ 'id' => $id, 'slug' => $orderObject->getVideo()->getSlug() ] );
//		}else{
//			//Проверяем если рендеринг видео еще не финишировал, то возвращаем пользователя на страницу ожидания.
//			if ( $render->getStatus() != 'finished' and $render->getStatus() != 'processing' ) {
//				return $this->redirectToRoute( 'full_video_render_processing', [ 'id' => $id, 'slug' => $orderObject->getVideo()->getSlug() ] );
//			}
//		}
		
		$setting = $this->getDoctrine()->getRepository( 'App\Entity\Setting' )->dataForTheMainPage();
		
		$settingForming = $form->getFormingFrom( $setting );
		
		$setting = $settingForming->setting[0];
		
		//Данные мета. По умолчанию заполненные в настройках
		$meta = (object) [
			'title'       => $setting->getMetaTitle(),
			'description' => $setting->getMetaDescription(),
			'keywords'    => $setting->getMetaKeywords(),
			'image'       => $setting->getMetaImage(),
			'canonical'   => $setting->getMetaCanonical(),
		];
		
		return $this->render( 'page/pay_page_after_full.html.twig', [
			'order'     => $orderObject,
			'meta'       => $meta,
			'setting'    => $setting,
			'menuHeader' => $settingForming->header,
			'menuFooter' => $settingForming->footer
		] );
	}
	
	/**
	 * @Route("/video/error", name="video_error_rendering", defaults={"_locale"="%locale%"}, requirements={"_locale"="%locales_in_line%", "id"="\d+"}, methods={"GET"})
	 */
	public function videoErrorRendering( TranslatorInterface $translator )
	{
		
		$setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);
		
		$page = [
			'title' => $translator->trans('front.error_rendering_video_with_order'),
			'content' => $translator->trans('front.error_rendering_video_with_order_content')
		];
		
		return $this->render('page/static_page.html.twig', ['page' => (object)$page, 'setting' => $setting]);
	}
	
	/**
	 * @Route("/checkout/{id}", name="checkout", defaults={"_locale"="%locale%"}, requirements={"_locale"="%locales_in_line%", "id"="\d+"}, methods={"GET", "POST"})
	 */
	public function checkout( $id, Request $request, PaymentService $paymentService, TranslatorInterface $translator, RenderService $renderService, MailTemplate $template )
	{
		if ( empty( $this->getUser() ) ) {
			return $this->redirectToRoute('authorization', [], 301);
		}
		$orderObject = $this->getDoctrine()->getRepository( 'App\Entity\Order' )->findOneBy( [
			'id'    => $id,
			'users' => $this->getUser()
		] );
		
		if ( empty( $orderObject ) ) {
			throw new AccessDeniedException( 'Unable to access this page!' );
		}
		
		//Если видео оплаченое - переводим на страницу ожидания рендеринга видео
		if($orderObject->getActive() > 0){
			
			return $this->redirectToRoute('full_video_render_processing', ['id' => $id, 'slug' => $orderObject->getVideo()->getSlug()], 301);
			
		}else{
			
			if($request->request->has('paid')){
				
				$paid = $request->request->get('paid');
				
				$setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);
				
				/*if($paid == 0) {
					
					$current = $this->getDoctrine()->getRepository('App\Entity\Currency')->findOneBy(['name' => $orderObject->getPriceCurrency()]);

					$payPal = $paymentService->paymentPayPal($request, $setting, $orderObject, $current, '/checkout/successful_payment', '/checkout/unsuccessful_payment');
					
					return new JsonResponse( [ 'link' => $payPal != false ? $payPal : '' ], 200 );
				
				} elseif ($paid == 1){
					*/
					$interkassa = $paymentService->paymentInterkassa( $request, $setting, $orderObject, '/checkout/successful_payment', '/checkout/unsuccessful_payment', $paid );
					
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
					
					return new JsonResponse( $result, 200 );
				/*
				} elseif ($paid == 2){
					
					$platon = $paymentService->paymentPlaton( $request, $setting, $orderObject, '/checkout/successful_payment', '/checkout/unsuccessful_payment' );
					
					$response = new Response(
						$this->render( 'form/payment_platon.html.twig', [ 'a_payment' => $platon ] )->getContent(),
						Response::HTTP_OK,
						array( 'content-type' => 'text/html' )
					);
					
					return new JsonResponse( [ 'form' => $response->getContent() ], 200 );
					
				} elseif ($paid == 3){

                    $current = $this->getDoctrine()->getRepository('App\Entity\Currency')->findOneBy(['name' => $orderObject->getPriceCurrency()]);
				
					$form = $paymentService->paymentLiqPay($request, $setting, $orderObject, $current, '/checkout/successful_payment');
					
					return new JsonResponse( [ 'form' => $form ], 200 );
					
				} else {
					return $this->render('page/payment_page.html.twig', ['text' => $translator->trans('front.payment_not_found'), 'title' => 'Оплата видео', 'setting' => $setting]);
				}*/
			}elseif($request->request->has('promocode')){
				
				$promocod = $request->request->get('promocode');
				
				$object = $this->getDoctrine()->getRepository('App\Entity\PromoCode')->findOneBy(['promoCode' => $promocod, 'active' => ActiveConstants::ACTIVE]);

				if(!empty($object)){
					
					if($object->getDateEndOfAction() > new \DateTime('NOW')){
						
						if($object->getNumberOfUses() > 0) {
							
							$object->setNumberOfUses( $object->getNumberOfUses() - 1 );
							$this->getDoctrine()->getManager()->flush( $object );

							if(empty($object->getDiscount())) {

                                //Меняем статус на оплату через промокод и записываем сам промокод
                                $orderObject->setActive( ActiveConstants::ORDER_PROMOCODE_VALUE );
                                $orderObject->setPromoCode($promocod);
                                $orderObject->setPromoCodeDiscount(null);
                                $this->getDoctrine()->getManager()->flush( $orderObject );

                                //Запускаем рендеринг полного видео
                                $renderService->renderFullVideo( $request, $orderObject );

                                if ( $orderObject->getUsers()->getSubscribed() != 1 ) {

                                    $unsubscribe = base64_encode( $orderObject->getUsers()->getId() . '|' . $orderObject->getUsers()->getEmail() . '|' . $orderObject->getUsers()->getCreatedAt()->format( 'd.m.Y' ) );

                                    $object = array(
                                        'user_name'   => $orderObject->getUsers()->getFullName(),
                                        'user_email'  => $orderObject->getUsers()->getEmail(),
                                        'video_url'   => $request->getUriForPath( $this->generateUrl( 'video_full_viewing', [ 'id' => $orderObject->getId(), 'slug' => $orderObject->getVideo()->getSlug() ] ) )
                                    );

                                    $template->sendMailMessages( MailEventConstants::SUCCESSFUL_PAYMENT, MailEventConstants::SUCCESSFUL_PAYMENT_VALUES, (object) $object, $orderObject->getUsers()->getEmail(), $unsubscribe );

                                }

                                $code    = 200;
                                $message = $this->generateUrl( 'full_video_render_processing', [ 'id' => $id, 'slug' => $orderObject->getVideo()->getSlug() ] );

                            } else {

                                $orderObject->setPromoCode($promocod);
                                $orderObject->setPromoCodeDiscount($object->getDiscount());
                                $this->getDoctrine()->getManager()->flush( $orderObject );

                                $paid = 3;

                                $setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);

                                $interkassa = $paymentService->paymentInterkassa( $request, $setting, $orderObject, '/checkout/successful_payment', '/checkout/unsuccessful_payment', $paid );

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

                                return new JsonResponse( $result, 200 );

                            }

						}else{
							$code = 400; $message = $translator->trans('front.promotional_code_has_reached_the_limit_of_use');
						}
					}else{
						$code = 400; $message = $translator->trans('front.promotional_code_is_not_valid');
					}
				}else{
					$code = 400; $message = $translator->trans('front.promo_code_not_found');
				}
				
				return new JsonResponse( [ 'code' => $code, 'message' => $message ], 200 );
			}elseif($request->request->has('free')){
			    //Если все цены пусты - значит можно бесплатно создать видео
			    if(empty($orderObject->getVideo()->getPriceUah()) and empty($orderObject->getVideo()->getPriceEur()) and empty($orderObject->getVideo()->getPriceRub()) and empty($orderObject->getVideo()->getPriceUsd())){
                
                    //Меняем статус на оплачено
                    $orderObject->setActive( ActiveConstants::ORDER_PAID_VALUE );
                    $orderObject->setPaymentMethod('free');
                    $this->getDoctrine()->getManager()->flush( $orderObject );
                
                    //Запускаем рендеринг полного видео
                    $renderService->renderFullVideo( $request, $orderObject );
                
                    if ( $orderObject->getUsers()->getSubscribed() != 1 ) {
                    
                        $unsubscribe = base64_encode( $orderObject->getUsers()->getId() . '|' . $orderObject->getUsers()->getEmail() . '|' . $orderObject->getUsers()->getCreatedAt()->format( 'd.m.Y' ) );
                    
                        $object = array(
                            'user_name'   => $orderObject->getUsers()->getFullName(),
                            'user_email'  => $orderObject->getUsers()->getEmail(),
                            'video_url'   => $request->getUriForPath( $this->generateUrl( 'video_full_viewing', [ 'id' => $orderObject->getId(), 'slug' => $orderObject->getVideo()->getSlug() ] ) )
                        );
                    
                        $template->sendMailMessages( MailEventConstants::SUCCESSFUL_PAYMENT, MailEventConstants::SUCCESSFUL_PAYMENT_VALUES, (object) $object, $orderObject->getUsers()->getEmail(), $unsubscribe );
                    
                    }
                
                    $code    = 200; $message = $this->generateUrl( 'full_video_render_processing', [ 'id' => $id, 'slug' => $orderObject->getVideo()->getSlug() ] );
                }elseif ($this->getDoctrine()->getRepository( 'App\Entity\Subscription' )->getLastActiveByUser($this->getUser())){
                    //Меняем статус на оплачено
                    $orderObject->setActive( ActiveConstants::ORDER_SUBSCRIPTION_VALUE );
                    $orderObject->setPaymentMethod('subscription');
                    $this->getDoctrine()->getManager()->flush( $orderObject );

                    //Запускаем рендеринг полного видео
                    $renderService->renderFullVideo( $request, $orderObject );

                    if ( $orderObject->getUsers()->getSubscribed() != 1 ) {

                        $unsubscribe = base64_encode( $orderObject->getUsers()->getId() . '|' . $orderObject->getUsers()->getEmail() . '|' . $orderObject->getUsers()->getCreatedAt()->format( 'd.m.Y' ) );

                        $object = array(
                            'user_name'   => $orderObject->getUsers()->getFullName(),
                            'user_email'  => $orderObject->getUsers()->getEmail(),
                            'video_url'   => $request->getUriForPath( $this->generateUrl( 'video_full_viewing', [ 'id' => $orderObject->getId(), 'slug' => $orderObject->getVideo()->getSlug() ] ) )
                        );

                        $template->sendMailMessages( MailEventConstants::SUCCESSFUL_PAYMENT, MailEventConstants::SUCCESSFUL_PAYMENT_VALUES, (object) $object, $orderObject->getUsers()->getEmail(), $unsubscribe );

                    }

                    $code    = 200; $message = $this->generateUrl( 'full_video_render_processing', [ 'id' => $id, 'slug' => $orderObject->getVideo()->getSlug() ] );
                }else{
                    $code = 400; $message = $translator->trans('front.video_not_free');
                }
                return new JsonResponse( [ 'code' => $code, 'message' => $message ], 200 );
            }else{
				return $this->redirectToRoute('video_demo_viewing', ['id' => $id, 'slug' => $orderObject->getVideo()->getSlug()], 301);
			}
		}
	}

	/**
	 * @Route("/checkout/successful_payment", name="checkout_successful_payment", defaults={"_locale"="%locale%"}, requirements={"_locale"="%locales_in_line%", "id"="\d+"}, methods={"GET", "POST"})
	 */
	public function checkoutSuccessfulPayment( Request $request, TranslatorInterface $translator, RenderService $service, MailTemplate $template )
	{
		
		$setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);
		
		$text = $translator->trans('front.payment_not_found');
		
		//Проверка PayPal
		if($request->query->has('paymentId') && $request->query->has('PayerID')){
			$paymentId = $request->query->get('paymentId');
			$payerID = $request->query->get('PayerID');

			$order = $this->getDoctrine()->getRepository('App\Entity\Order')->findOneBy(['paymentIdOrder' => $paymentId]);
			if(!empty($order)){
				if($order->getActive() > 0){
                    $this->checkoutPayPal( $order, $paymentId, $payerID, $setting, $request, $service, $template );
					$text = $translator->trans('front.payment_success_paid');
				}else{
					$text = $translator->trans('front.payment_expect');
				}
			}
		}
		
		//Проверка на Interkassa
		if($request->request->has('ik_pm_no')){
			$paymentId = $request->request->get('ik_pm_no');
			$exp = explode('-', $paymentId);
			$paymentId = $exp[0];
			$order = $this->getDoctrine()->getRepository('App\Entity\Order')->findOneBy(['id' => $paymentId]);
			if(!empty($order)){
				if($order->getActive() > 0){
					//Если уже оплачено, то переадресация на ожилание готовности полного видео
					return $this->redirectToRoute('full_video_render_processing', ['id' => $paymentId, 'slug' => $order->getVideo()->getSlug()]);
				}else{
					$text = $translator->trans('front.payment_expect');
				}
			}
		}
		
		//Проверка на Liqpay
		if(!empty($_GET['order'])){
			$order = $this->getDoctrine()->getRepository( 'App\Entity\Order' )->findOneBy( [ 'id' => (int)$_GET['order'] ] );
			if(!empty($order)) {
				if ( $order->getActive() > 0) {
					//Если уже оплачено, то переадресация на ожилание готовности полного видео
					return $this->redirectToRoute( 'full_video_render_processing', [ 'id' => (int)$_GET['order'], 'slug' => $order->getVideo()->getSlug() ] );
				} else {
					$text = $translator->trans( 'front.payment_expect' );
				}
			}
		}
		
		//Данные мета. По умолчанию заполненные в настройках
		$meta = (object) [
			'title'       => $setting->getMetaTitle(),
			'description' => $setting->getMetaDescription(),
			'keywords'    => $setting->getMetaKeywords(),
			'image'       => $setting->getMetaImage(),
			'canonical'   => $setting->getMetaCanonical(),
		];

		return $this->render('page/payment_page.html.twig', ['text' => $text, 'meta' => $meta, 'title' => $translator->trans('check_payment_video'), 'setting' => $setting]);
	}

	private function checkoutPayPal( $order, $paymentId, $payerID, Setting $setting, Request $request, RenderService $service, MailTemplate $template  )
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
                $this->updateOrderAfterPay( $order, 'PayPal', $request, $service, $template );
            } catch (Exception $e) {
                // Failed to retrieve payment from PayPal
            }

        } catch (\Exception $e) {
            // Failed to take payment
        }
    }

	/**
	 * @Route("/checkout/unsuccessful_payment", name="checkout_unsuccessful_payment", defaults={"_locale"="%locale%"}, requirements={"_locale"="%locales_in_line%", "id"="\d+"}, methods={"GET", "POST"})
	 */
	public function checkoutUnsuccessfulPayment( Request $request, TranslatorInterface $translator )
	{
		
		$setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);
		
		return $this->render('page/payment_page.html.twig', ['text' => $translator->trans('front.payment_did_not_happen'), 'title' => 'Оплата видео', 'setting' => $setting]);
	}
	
	/**
	 * @Route("/checkout/webhook/payment", name="checkout_webhook_payment", defaults={"_locale"="%locale%"}, requirements={"_locale"="%locales_in_line%", "id"="\d+"}, methods={"GET", "POST"})
	 */
	public function checkoutWebHookPayment( Request $request )
	{
		
		$setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);
		
		return $this->render('page/payment_page.html.twig', ['text' => 'OK', 'title' => 'Оплата PayPal', 'setting' => $setting]);
	}
	
	/**
	 * @Route("/checkout/webhook/platon", name="checkout_webhook_platon", defaults={"_locale"="%locale%"}, requirements={"_locale"="%locales_in_line%", "id"="\d+"}, methods={"GET", "POST"})
	 */
	public function checkoutWebHookPlaton( Request $request )
	{
		if($request->request->has('order')){
			$paymentId = $request->request->get('order');
			$order = $this->getDoctrine()->getRepository('App\Entity\Order')->findOneBy(['id' => $paymentId]);
			if(!empty($order)){
				//Изменить статус товара на оплаченый
			}
			
		}
		
		$setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);
		
		return $this->render('page/payment_page.html.twig', ['text' => 'OK', 'title' => 'Оплата PayPal', 'setting' => $setting]);
	}
	
	/**
	 * @Route("/checkout/webhook/interkassa", name="checkout_webhook_interkassa", defaults={"_locale"="%locale%"}, requirements={"_locale"="%locales_in_line%", "id"="\d+"}, methods={"GET", "POST"})
	 */
	public function checkoutWebHookInterkassa( Request $request, RenderService $service, MailTemplate $template )
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
					$order     = $this->getDoctrine()->getRepository( 'App\Entity\Order' )->findOneBy( [ 'id' => $paymentId ] );
					if( !empty( $order ) ) {
                        $this->updateOrderAfterPay( $order, 'interkassa.com', $request, $service, $template );
                    }
				}
			}
		}
		
		return $this->render('page/payment_page.html.twig', ['text' => 'OK', 'title' => 'Оплата PayPal', 'setting' => $setting]);
	}
	
	/**
	 * @Route("/checkout/webhook/liqpay", name="checkout_webhook_liqpay", defaults={"_locale"="%locale%"}, requirements={"_locale"="%locales_in_line%", "id"="\d+"}, methods={"GET", "POST"})
	 */
	public function checkoutWebHookLiqPay( Request $request, RenderService $service, MailTemplate $template )
	{
		
		$setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);
		
		$data = $request->request->get('data');
		$signature = $request->request->get('signature');
		
		$sign = base64_encode( sha1(
			$setting->getApiKeyLiqPayPrivatKey() .
			$data .
			$setting->getApiKeyLiqPayPrivatKey()
			, 1 ));
		
		if($sign == $signature){
			
			$postData = json_decode(base64_decode($data), true);
				
			if(!empty($postData['order_id'])){
				
				$order = $this->getDoctrine()->getRepository('App\Entity\Order')->findOneBy(['paymentIdOrder' => $postData['order_id']]);
				
				//Если сумма совпадает, все ок
				if( $postData['status'] == 'success' || $postData['status'] == 'sandbox' ){
						
					if ( $order->getActive() !== ActiveConstants::ORDER_PAID_VALUE ) {
						
						$order->setActive( ActiveConstants::ORDER_PAID_VALUE ); //Меняем статус на оплачено
						$order->setPaymentMethod('LiqPay');
						$this->getDoctrine()->getManager()->flush( $order );
						
						//Запускаем рендеринг полного видео
						$service->renderFullVideo( $request, $order );
						
						if ( $order->getUsers()->getSubscribed() != 1 ) {
							
							$unsubscribe = base64_encode( $order->getUsers()->getId() . '|' . $order->getUsers()->getEmail() . '|' . $order->getUsers()->getCreatedAt()->format( 'd.m.Y' ) );
							
							$object = array(
								'user_name'   => $order->getUsers()->getFullName(),
								'user_email'  => $order->getUsers()->getEmail(),
								'video_url'   => $request->getUriForPath( $this->generateUrl( 'video_full_viewing', [ 'id' => $order->getVideo()->getId(), 'slug' => $order->getVideo()->getSlug() ] ) )
							);
							
							$template->sendMailMessages( MailEventConstants::SUCCESSFUL_PAYMENT, MailEventConstants::SUCCESSFUL_PAYMENT_VALUES, (object) $object, $order->getUsers()->getEmail(), $unsubscribe );
						}
					}
				}
				
			}
		}
		return new Response('Done', 200, array('Content-Type' => 'text/html'));
		//return $this->render('page/payment_page.html.twig', ['text' => 'OK', 'title' => 'Оплата LiqPay', 'setting' => $setting]);
	}

	private function updateOrderAfterPay( $order, $paymentMethod, Request $request, RenderService $service, MailTemplate $template ) {
        if ( $order->getActive() !== ActiveConstants::ORDER_PAID_VALUE ) {

            $order->setActive( ActiveConstants::ORDER_PAID_VALUE ); //Меняем статус на оплачено
            $order->setPaymentMethod($paymentMethod);
            $this->getDoctrine()->getManager()->flush( $order );

            //Запускаем рендеринг полного видео
            $service->renderFullVideo( $request, $order );

            if ( $order->getUsers()->getSubscribed() != 1 ) {

                $unsubscribe = base64_encode( $order->getUsers()->getId() . '|' . $order->getUsers()->getEmail() . '|' . $order->getUsers()->getCreatedAt()->format( 'd.m.Y' ) );

                $object = array(
                    'user_name'   => $order->getUsers()->getFullName(),
                    'user_email'  => $order->getUsers()->getEmail(),
                    'video_url'   => $request->getUriForPath( $this->generateUrl( 'video_full_viewing', [ 'id' => $order->getVideo()->getId(), 'slug' => $order->getVideo()->getSlug() ] ) )
                );

                $template->sendMailMessages( MailEventConstants::SUCCESSFUL_PAYMENT, MailEventConstants::SUCCESSFUL_PAYMENT_VALUES, (object) $object, $order->getUsers()->getEmail(), $unsubscribe );
            }
        }
    }
	
	/**
	 * @Route("/upload/image", name="upload_image", methods={"POST"})
	 */
	public function videoUploadImage( Request $request )
	{
		
		$image    = $request->request->get('image');
		$widthPl    = $request->request->get('width', 1920);
		$heightPl   = $request->request->get('height', 1080);

        if (!file_exists('upload/uploaded_original_image/')) {
            mkdir("upload/uploaded_original_image/", 0777);
        }
        if (!file_exists('upload/tmp/')) {
            mkdir("upload/tmp/", 0777);
        }
		$path = 'upload/tmp/';
		$filename = uniqid() . rand(1111, 9999) . time();

		$imgData = '';
		$imgData = str_replace( ' ', '+', $image );
		$imgData = substr( $imgData, strpos( $imgData, "," ) + 1 );
		$imgData = base64_decode( $imgData );

        $fp = fopen($path.$filename.'.jpg', 'w');
        fwrite($fp, $imgData);
        fclose($fp);

        $fpt = fopen('upload/uploaded_original_image/'.$filename.'.jpg', 'w');
        fwrite($fpt, $imgData);
        fclose($fpt);

		$rotate = '';

        $exif = @exif_read_data($path.$filename.'.jpg');
      	$exifEnable = false;

        if(!empty($exif)){
        	if(!empty($exif['Orientation']) and $exif['Orientation'] > 0){
				 switch( $exif['Orientation'] ) {
			        case 3:
			        	$rotate = ' -rotate "180" -strip';
			        	$exifEnable = true;
			            break;
			        case 6:
			        	$rotate = ' -rotate "90" -strip';
			        	$exifEnable = true;
			            break;
			        case 8:
			        	$rotate = ' -rotate "-90" -strip';
			        	$exifEnable = true;
			            break;
			    }
			}
		}

		list($width, $height) = getimagesize($path.$filename.'.jpg');
		
		//коэффициент запаса размера (для зума)
		$k=1;
		if ( ($width > $widthPl) and ($height > $heightPl) ) $k=2; 
		
		$ratio=$width/$height;
		//горизонтальный плейсхолдер
		if ($widthPl > $heightPl) {
			$plMax=$widthPl;		
		
			//вычисляем коэффициент для чтобы картинка была по ширине области кропалки
			if(($widthPl<600) ) $plMax=600;	

		}
	
		//вертикальный плейсхолдер
		else {
			$plMax=$heightPl;		
			//вычисляем коэффициент для чтобы картинка была по ширине области кропалки		
			if(($widthPl<400) ) {
					$x_enlarge=400/$widthPl;
					$newCropitHeight=$x_enlarge*$heightPl;
					$plMax=ceil($newCropitHeight);		
			}
		} 

		if($exifEnable){
			exec('convert '.$_SERVER['DOCUMENT_ROOT'].'/'.$path.$filename.'.jpg '.$rotate.' '.$_SERVER['DOCUMENT_ROOT'].'/'.$path.$filename.'.jpg');
		}

		if( ($width > $widthPl) and ($height > $heightPl) ){
			if($height > $width){
				exec('convert '.$_SERVER['DOCUMENT_ROOT'].'/'.$path.$filename.'.jpg -resize '.$plMax*$k.'x '.$_SERVER['DOCUMENT_ROOT'].'/'.$path.$filename.'.jpg');
			}else{
				exec('convert '.$_SERVER['DOCUMENT_ROOT'].'/'.$path.$filename.'.jpg -resize x'.$plMax*$k.' '.$_SERVER['DOCUMENT_ROOT'].'/'.$path.$filename.'.jpg');
			}
		}

		$faceAndMouth = null;
		if($request->request->has('face')){
            $faceAndMouth = $this->autoFindFace($path.$filename.'.jpg');
			if($faceAndMouth !== false){
				return new JsonResponse( [ 'faceFull' => $faceAndMouth->faceFull, 'face' => $faceAndMouth->face, 'mouth' => $faceAndMouth->mouth, 'mouthX' => $faceAndMouth->mouthX, 'mouthY' => $faceAndMouth->mouthY ], 200 );
			}else{
        		$file = $request->getUriForPath('/'.$path.$filename.'.jpg');
				return new JsonResponse( [ 'face' => 'no', 'url' => $file], 200 );
			}
        }else{
        	$file = $request->getUriForPath('/'.$path.$filename.'.jpg');
			
			return new JsonResponse( [ 'url' => $file ], 200 );
        }
		
	}

	public function autoFindFace($url){

        //Расположение фото
        $photo = $url;
        $faces = $mouth = [];
		$path = 'upload/tmp/';
        $src = imread($photo);
        $gray = cvtColor($src, COLOR_BGR2GRAY);

        //Ищем лицо по алгоритму lbpcascade_frontalface
        $faceClassifier = new CascadeClassifier();
        $faceClassifier->load('opencv/lbpcascade_frontalface.xml');
        $faceClassifier->detectMultiScale($gray, $faces);

        //Ищем максимально большой блок, там и находится лицо
        if ($faces) {
            $faceOld = 0; $faceOldWidth = 0;
            foreach ($faces as $key => $face) {
                if($face->width > $faceOldWidth){
                    $faceOld = $key;
                    $faceOldWidth = $face->width;
                }
            }
        }

        if(!empty($faces)){

            $faceFull = 'data:image/png;base64,'.base64_encode(file_get_contents($url));

			$imgFaceTmp = $path.uniqid().rand(11111, 99999).'.png';

	        //Этап №1. Получаем картинку и налаживаем маску лица
	        //Обрезаем фото по координатам лица, оставляя лишь его
	        $faceLong = new \Imagick($photo);
	        $faceLong->cropImage($faces[$faceOld]->width, 0, $faces[$faceOld]->x, $faces[$faceOld]->y);

	        //Получаем маску и ресайзим лицо под размер маски
	        $maskFace = new \Imagick('opencv/mask_opencv.png');
	        $faceLong->resizeImage($maskFace->getimagewidth() + $maskFace->getimagewidth()*0.34, 0, \Imagick::FILTER_LANCZOS, 1);

	        //Создаем прозрачную картинк с альфа каналоми налаживаем маску
	        $canvas = new \Imagick();
	        $canvas->newimage($faceLong->getimagewidth(), $faceLong->getimageheight(), "transparent");
	        $canvas->compositeImage($faceLong, \Imagick::COMPOSITE_DEFAULT, -$maskFace->getimagewidth()*0.17, 0 );
	        $canvas->compositeImage($maskFace, \Imagick::COMPOSITE_DSTIN, 0, 0, \Imagick::CHANNEL_ALPHA);
	        $canvas->trimImage(0);
	        $canvas->setImageFormat('png');
			$canvas->writeImage($imgFaceTmp); 
			$canvas->writeImage($imgFaceTmp);

	        //Этап №2. Применение маски рта
	        //Получаем маску и ресайзим рот под размер маски
			$srcFace = imread($imgFaceTmp);
			$grayMouth = cvtColor($srcFace, COLOR_BGR2GRAY);

			//Ищем лицо по алгоритму lbpcascade_frontalface
			$faceClassifierMouth = new CascadeClassifier();
			$faceClassifierMouth->load('opencv/Mouth.xml');
			$faceClassifierMouth->detectMultiScale($grayMouth, $mouth, 1.01, 10);//3, 10; 7.85, 10

			//Ищем максимально большой блок, там и находится лицо
			if ($mouth) {
                $mouthOld = 0; $mouthOldCoorY = 0;
                $mouthOldBig = 0; $mouthOldBigWidth = 0;
                $scalar = new Scalar(0, 0, 255); //blue
                foreach ($mouth as $key => $mout) {
                    if($mout->y > $mouthOldCoorY){
                        $mouthOld = $key;
                        $mouthOldCoorY = $mout->y;
                    }
                    if($mout->y > 130){
                        if($mout->width > $mouthOldBigWidth){
                            $mouthOldBig = $key;
                            $mouthOldBigWidth = $mout->width;
                        }
                    }
                }
                if($mouthOldBigWidth > 0){
                    $coordinate = $mouth[$mouthOldBig];
                }else{
                    $coordinate = $mouth[$mouthOld];
                }

                if(!empty($coordinate)){

                    $mouthOffset = 12;

                    $faceCropMouth = new \Imagick($imgFaceTmp);
                    $faceCropMouth->cropImage($coordinate->width, 23, $coordinate->x, $coordinate->y + $mouthOffset);

                    $canvasMouth = new \Imagick();
                    $canvasMouth->newimage($canvas->getimagewidth(), $canvas->getimageheight()+30, "transparent");
                    $canvasMouth->compositeImage($faceCropMouth, \Imagick::COMPOSITE_DEFAULT, $coordinate->x, $coordinate->y + $mouthOffset );
                    $canvasMouth->setImageFormat('png');
                    $mouthBase64 = 'data:image/png;base64,'.base64_encode($canvasMouth->getImageBlob());

                    //Этап №3. Налаживаем рот как маску и перезаписываем лицо без рта
                    $faceCropMouth->floodFillPaintImage('rgb(0,0,0)', 100, 'rgb(255,255,255)', 0, 0, true);
                    $canvas->compositeImage($faceCropMouth, \Imagick::COMPOSITE_DEFAULT, $coordinate->x, $coordinate->y + $mouthOffset, \Imagick::CHANNEL_ALPHA);
                    $canvas->setImageFormat('png');
                    $faceBase64 = 'data:image/png;base64,'.base64_encode($canvas->getImageBlob());

                    unlink($imgFaceTmp);

                    return (object)array('faceFull' => $faceFull, 'face' => $faceBase64, 'mouth' => $mouthBase64, 'mouthX' => $coordinate->x, 'mouthY' => $coordinate->y + $mouthOffset);
                }
			}
	        return false;
		}
		return false;
    }
}
