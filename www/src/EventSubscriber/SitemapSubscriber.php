<?php

namespace App\EventSubscriber;

use App\Constants\ActiveConstants;
use App\Constants\TypePageConstants;
use App\Entity\Page;
use Presta\SitemapBundle\Sitemap\Url as Sitemap;
use Doctrine\Common\Persistence\ObjectManager;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapSubscriber implements EventSubscriberInterface
{
	/**
	 * @var UrlGeneratorInterface
	 */
	private $urlGenerator;
	
	/**
	 * @var ObjectManager
	 */
	private $manager;
	
	protected $container;
	
	protected $locales;
	
	protected $domain;
	
	protected $request;
	
	/**
	 * @param UrlGeneratorInterface $urlGenerator
	 * @param ObjectManager $manager
	 * @param string $host
	 * @param string $scheme
	 * @param string $env
	 */
	public function __construct( UrlGeneratorInterface $urlGenerator, ObjectManager $manager, ContainerInterface $container, RequestStack $request )
	{
		$this->urlGenerator = $urlGenerator;
		$this->manager      = $manager;
		$this->container    = $container;
		$this->locales      = $this->container->getParameter( 'locales' );
		$this->request      = $request;
		$this->domain       = $this->container->getParameter('app_domain');
	}
	
	/**
	 * @inheritdoc
	 */
	public static function getSubscribedEvents()
	{
		return [
			SitemapPopulateEvent::ON_SITEMAP_POPULATE => 'registerPages',
		];
	}
	
	/**
	 * @param SitemapPopulateEvent $event
	 */
	public function registerPages( SitemapPopulateEvent $event )
	{
		$this->addPage( $event,'App\Entity\Page', 'app_cms_content', 'page' );
		$this->addPage( $event,'App\Entity\VideoCategories', 'video_category', 'page', true );
		$this->addVideo( $event,'App\Entity\Video', 'video', 'page' );
		$this->addVideo( $event,'App\Entity\Video', 'movie', 'page' );
		$this->addPage( $event,'App\Entity\Blog', 'app_cms_blog_content', 'article' );
		$this->addStaticPages( $event, [ 'home' ],'static_page' );
	}
	
	/**
	 * @param SitemapPopulateEvent $event
	 * @param string $route
	 * @param string $section
	 */
	private function addPage( SitemapPopulateEvent $event, $className, $route, $section, $images = false )
	{
		$objects = $this->manager->getRepository( $className )->findAll();
		foreach ( $objects as $object ) {
			if($object instanceof Page and $object->getType() == TypePageConstants::INDEX_VALUES) continue;
			if($object->getTranslations()->isEmpty() == false){
				foreach ($object->getTranslations()->toArray() as $translation){
					if($translation->getActive() == ActiveConstants::ACTIVE) {
						$event->getUrlContainer()->addUrl(
							new UrlConcrete(
								$this->urlGenerator->generate(
									$route,
									[ 'slug' => $object->getSlug(), '_locale' => $translation->getLocale() ],
									UrlGeneratorInterface::ABSOLUTE_URL
								), $object->getUpdatedAt(), UrlConcrete::CHANGEFREQ_DAILY
							),
							$section
						);
					}
				}
			}
			
			if($route == 'app_cms_content'){
				if($object->getType() == TypePageConstants::BLOG_VALUES){
					$categoriesBlog = $this->manager->getRepository('App\Entity\BlogCategories')->findAll();
					foreach ($categoriesBlog as $categories){
						$event->getUrlContainer()->addUrl(
							new UrlConcrete(
								$this->urlGenerator->generate(
									$route,
									[ 'slug' => $object->getSlug(), '_locale' => $translation->getLocale() ],
									UrlGeneratorInterface::ABSOLUTE_URL
								).'?category='.$categories->getId(), $object->getUpdatedAt(), UrlConcrete::CHANGEFREQ_DAILY
							),
							$section
						);
					}
				}
			}
			
			if($section == 'article'){
				if(!empty($object->getImages())) {
					/** @var $urlGenerator UrlGeneratorInterface */
					$url          = new Sitemap\UrlConcrete( $this->urlGenerator->generate( $route, [
						'slug'    => $object->getSlug(),
						'_locale' => $translation->getLocale()
					], UrlGeneratorInterface::ABSOLUTE_URL ), $object->getUpdatedAt() );
					$provider     = $this->container->get( $object->getImages()->getProviderName() );
					$urlImage     = $provider->generatePublicUrl( $object->getImages(), 'reference' );
					$decoratedUrl = new Sitemap\GoogleImageUrlDecorator( $url );
					$decoratedUrl->addImage( new Sitemap\GoogleImage( $this->domain.$urlImage, null, null, $object->getTitle() ) );
					/** @var $urls \Presta\SitemapBundle\Service\UrlContainerInterface */
					$event->getUrlContainer()->addUrl( $decoratedUrl, 'images' );
				}
			}elseif($images){
				if(!empty($object->getImages())) {
					/** @var $urlGenerator UrlGeneratorInterface */
					$url          = new Sitemap\UrlConcrete( $this->urlGenerator->generate( $route, [
						'slug'    => $object->getSlug(),
						'_locale' => $translation->getLocale()
					], UrlGeneratorInterface::ABSOLUTE_URL ), $object->getUpdatedAt());
					$provider     = $this->container->get( $object->getImages()->getProviderName() );
					$urlImage     = $provider->generatePublicUrl( $object->getImages(), 'reference' );
					$decoratedUrl = new Sitemap\GoogleImageUrlDecorator( $url );
					$decoratedUrl->addImage( new Sitemap\GoogleImage( $this->domain.$urlImage, null, null, $object->getTitle() ) );
					/** @var $urls \Presta\SitemapBundle\Service\UrlContainerInterface */
					$event->getUrlContainer()->addUrl( $decoratedUrl, 'images' );
				}
			}
		}
	}
	
	private function addStaticPages( SitemapPopulateEvent $event, $routes, $section )
	{
		if(!empty($this->locales)){
			foreach ($this->locales as $locale){
				foreach ( $routes as $route ) {
					$event->getUrlContainer()->addUrl(
						new UrlConcrete(
							$this->urlGenerator->generate(
								$route,
								['_locale' => $locale],
								UrlGeneratorInterface::ABSOLUTE_URL
							), null, UrlConcrete::CHANGEFREQ_DAILY
						),
						$section
					);
				}
			}
		}
	}
	
	private function addVideo( SitemapPopulateEvent $event, $className, $routes, $section )
	{
		$videos = $this->manager->getRepository( $className )->findBy(['active' => 1]);
		
		foreach ( $videos as $video ) {
			$event->getUrlContainer()->addUrl(
				new UrlConcrete(
					$this->urlGenerator->generate(
						$routes,
						['slug' => $video->getSlug(), '_locale' => $video->getLocale()],
						UrlGeneratorInterface::ABSOLUTE_URL
					), null, UrlConcrete::CHANGEFREQ_DAILY
				),
				$section
			);
			if(!empty($video->getImages())) {
				/** @var $urlGenerator UrlGeneratorInterface */
				$url          = new Sitemap\UrlConcrete( $this->urlGenerator->generate( $routes, [
					'slug'    => $video->getSlug(),
					'_locale' => $video->getLocale()
				], UrlGeneratorInterface::ABSOLUTE_URL ), $video->getUpdatedAt() );
				$provider     = $this->container->get( $video->getImages()->getProviderName() );
				$urlImage     = $provider->generatePublicUrl( $video->getImages(), 'reference' );
				$decoratedUrl = new Sitemap\GoogleImageUrlDecorator( $url );
				$decoratedUrl->addImage( new Sitemap\GoogleImage( $this->domain.$urlImage, null, null, $video->getTitle() ) );
				/** @var $urls \Presta\SitemapBundle\Service\UrlContainerInterface */
				$event->getUrlContainer()->addUrl( $decoratedUrl, 'images' );
			}
		}
	}
}