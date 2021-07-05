<?php
namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use GeoIp2\Database\Reader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
    private $defaultLocale;

    protected $container;

    private $session;

    protected $entityManager;
	
	protected $router;

    public function __construct(ContainerInterface $container, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->defaultLocale = $container->getParameter('locale');
        $this->container = $container;
	    $this->router         = $router;
        $this->session = $container->get('session');
        $this->entityManager = $entityManager;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        
	    if($request->getMethod() == 'GET') {
		    $setCurrency = false;
	    	$locales = $this->container->getParameter('locales');
	    	if(!empty($locales) and count($locales) > 1 ) {
			    //Проверка на языковую версию и ее установка в соответствией со страной
			    if ( ! isset( $_SESSION['_locale'] ) ) {
				    //Определяем IP адрес пользователя
				    $ip = $request->getClientIp();
				    //Определяем страну пользователя
				    try {
					    $readerCountry = new Reader( 'GeoLite2-Country.mmdb' );
					    $geo           = $readerCountry->country( $ip );
				    } catch ( \Exception $e ) {
					    $geo = (object) [ 'country' => (object) [ 'isoCode' => 'US' ] ];
				    }
				
				    $currency = $this->entityManager->getRepository( 'App\Entity\Country' )->findOneBy( [ 'isoCode' => $geo->country->isoCode ] );
				    if ( ! empty( $currency ) and $currency->getDefaultCountryLocale() != 'ru' ) {
					    $request->attributes->set( '_locale', $currency->getDefaultCountryLocale() );
					    $request->setLocale( $request->getSession()->get( '_locale', $currency->getDefaultCountryLocale() ) );
					    $_SESSION['_locale']  = $currency->getDefaultCountryLocale();
					    $route                = $event->getRequest()->get( '_route' ) ?: 'home';
					    $parametrs['_locale'] = $currency->getDefaultCountryLocale();
					    if ( ! empty( $event->getRequest()->get( 'slug' ) ) ) {
						    $parametrs['slug'] = $event->getRequest()->get( 'slug' );
					    }
					
					    //Проверка на валюту и ее установка в соответствии со страной
					    $this->setCookieCurrency( $geo );
					    $setCurrency = true;
					
					    $url      = $this->router->generate( $route, $parametrs );
					    $response = new RedirectResponse( $url );
					    $event->setResponse( $response );
				    } else {
					    $this->session->set( '_locale', 'ru' );
				    }
			    }
		    }
		
		    if ( $setCurrency == false ) {
			    if ( ! isset( $_COOKIE['currency'] ) ) {
				    //Определяем IP адрес пользователя
				    $ip = $request->getClientIp();
				    try {
					    $readerCountry = new Reader( 'GeoLite2-Country.mmdb' );
					    $geo           = $readerCountry->country( $ip );
				    } catch ( \Exception $e ) {
					    $geo = (object) [ 'country' => (object) [ 'isoCode' => 'US' ] ];
				    }
				    //Проверка на валюту и ее установка в соответствии со страной
				    $this->setCookieCurrency( $geo, $request );
			    }
		    }
	    }
    }
    
    public function setCookieCurrency($geo, Request $request){
	    
	    if(!isset($_COOKIE['currency'])) {
		    if(empty($geo)) { $geo = (object)['country' => (object)['isoCode' => 'US']]; }
		    
		    $country = $this->entityManager->getRepository('App\Entity\Country')->findOneBy(['isoCode' => $geo->country->isoCode]);
		    
		    if(!empty($country) and $country->getCurrency()->isEmpty() == false) {
			    $currency = $country->getCurrency()->first();
			    setcookie( 'currency', $currency->getCodeISO(), time() + 43200, '/' );
                $request->cookies->set('currency', $currency->getCodeISO());
		    }else{
			    $currency = $this->entityManager->getRepository('App\Entity\Currency')->findOneBy(['defaultCurrency' => 1]);
			    setcookie( 'currency', $currency->getCodeISO(), time() + 43200, '/' );
                $request->cookies->set('currency', $currency->getCodeISO());
		    }
	    }
    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}