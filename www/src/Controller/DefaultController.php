<?php

namespace App\Controller;

use App\Constants\ActiveConstants;
use App\Constants\SexConstants;
use App\Entity\FirstName;
use App\Helper\Form;
use App\Service\RenderService;
use App\Constants\TypePageConstants;
use GeoIp2\Database\Reader;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
	/**
	 * @Route("/", name="home", defaults={"_locale"="%locale%"}, requirements={"_locale" = "%locales_in_line%"})
	 */
	public function index( Form $form, Request $request, TranslatorInterface $translator)
	{
        /*domainMain = $this->getParameter('app_domain_main');
        $url = $domainMain."/api/v1/worker?type=1";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        dump($result);
        curl_close($ch);*/
        
        
        /*$domainMain = $this->getParameter('app_domain_main');
        $data_json = json_encode(array('worker'=>'8'));
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $domainMain.'/api/v1/worker');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_json)));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response  = curl_exec($ch);
        curl_close($ch);
        dump($response);*/
        
        
        $page = $this->getDoctrine()->getRepository( 'App\Entity\Page' )->findOneBy( [ 'type' => TypePageConstants::INDEX_VALUES ] );
		
        $criteria = \Doctrine\Common\Collections\Criteria::create()->orderBy(array('position'=> \Doctrine\Common\Collections\Criteria::DESC));
        
        $popularVideo = $page->getHomeVideoGreetings()->matching( $criteria );
        
		$currency = null;
		
		$breadcrumbs[] = ['url' => $this->generateUrl('home'), 'title' => $translator->trans('front.home')];
		
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
		
		$setting = $this->getDoctrine()->getRepository( 'App\Entity\Setting' )->dataForTheMainPage();
		
		$settingForming = $form->getFormingFrom( $setting );
		
		$setting = $settingForming->setting[0];
		
		$meta = (object) [
			'title'       => $page->getMetaTitle() ?: $setting->getMetaTitle(),
			'description' => $page->getMetaDescription() ?: $setting->getMetaDescription(),
			'keywords'    => $page->getMetaKeywords() ?: $setting->getMetaKeywords(),
			'image'       => $page->getMetaImage() ?: $setting->getMetaImage(),
			'canonical'   => $page->getMetaCanonical() ?: $setting->getMetaCanonical(),
		];
		
		return $this->render( 'page/index.html.twig', [
			'page'          => $page,
			'meta'          => $meta,
			'popularVideos' => $popularVideo,
			'setting'       => $setting,
			'currency'      => $currency,
			'breadcrumbs'   => $breadcrumbs,
			'menuHeader'    => $settingForming->header,
			'menuFooter'    => $settingForming->footer
		] );
	}
	
	/**
	 * @Route("/authorization", name="authorization", defaults={"_locale"="%locale%"}, requirements={"_locale" = "%locales_in_line%"})
	 */
	public function authorization( Form $form, TranslatorInterface $translator)
	{
		if(!empty($this->getUser())){
			$page = $this->getDoctrine()->getRepository( 'App\Entity\Page' )->findOneBy( [ 'type' => TypePageConstants::USER_ACCOUNT_VALUES ] );
			return $this->redirectToRoute('app_cms_content', ['slug' => $page->getSlug()]);
		}
		$breadcrumbs[] = ['url' => $this->generateUrl('home'), 'title' => $translator->trans('front.home')];
		$breadcrumbs[] = ['url' => $this->generateUrl('authorization'), 'title' => $translator->trans('front.authorization')];
		
		$setting = $this->getDoctrine()->getRepository( 'App\Entity\Setting' )->dataForTheMainPage();
		
		$settingForming = $form->getFormingFrom( $setting );
		
		$setting = $settingForming->setting[0];
		
		$meta = (object) [
			'title'       => $setting->getMetaTitle(),
			'description' => $setting->getMetaDescription(),
			'keywords'    => $setting->getMetaKeywords(),
			'image'       => $setting->getMetaImage(),
			'canonical'   => $setting->getMetaCanonical(),
		];
		
		return $this->render( 'page/authorization.html.twig', [
			'meta'       => $meta,
			'setting'    => $setting,
			'breadcrumbs'=> $breadcrumbs,
			'menuHeader' => $settingForming->header,
			'menuFooter' => $settingForming->footer
		] );
	}
	
	/**
	 * @Route("/unsubscribe/{slug}", name="unsubscribe", defaults={"_locale"="%locale%"}, requirements={"_locale" = "%locales_in_line%"})
	 */
	public function unsubscribe( $slug, TranslatorInterface $translator )
	{
		$setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);
		
		$text = $translator->trans('front.no_subscription_data_found');
		
		if(!empty($slug)){
			
			$decode = base64_decode($slug);
			
			if(!empty($decode)){
				
				$array = explode('|', $decode);
				
				if(!empty($array)){
					
					$userId = $array[0];
					$userEmail = $array[1];
					$userCreateAccount = $array[2];
					
					$user = $this->getDoctrine()->getRepository('App\Entity\User')->findOneBy(['id' => $userId, 'email' => $userEmail]);
					
					if($user->getCreatedAt()->format( 'd.m.Y' ) == $userCreateAccount) {
						
						if ( $user->getSubscribed() !== 1 ) {
							
							$user->setSubscribed( 1 );
							$this->getDoctrine()->getManager()->flush( $user );
							
							$text = $translator->trans( 'front.subscription_successfully_canceled' );
						} else {
							$text = $translator->trans( 'front.subscription_not_active' );
						}
					}
				}
			}
		}
		
		return $this->render('page/payment_page.html.twig', ['text' => $text, 'title' => $translator->trans('front.unsubscribe_from_email_newsletters'), 'setting' => $setting]);
	}
	
}
