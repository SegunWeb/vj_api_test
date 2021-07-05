<?php

namespace App\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class MyTwigExtension extends \Twig_Extension
{
	
	protected $container;
	protected $manager;
	
	public function __construct( ContainerInterface $container, EntityManagerInterface $manager )
	{
		$this->container = $container;
		$this->manager   = $manager;
	}
	
	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction( 'dynamicLinkCmsPage', array( $this, 'getDynamicLinkCmsPage' ), [
				'is_safe' => [ 'html' ]
			] ),
			new \Twig_SimpleFunction( 'dynamicLinkCategoryPage', array( $this, 'getDynamicLinkCategoryPage' ), [
				'is_safe' => [ 'html' ]
			] ),
			new \Twig_SimpleFunction( 'setting', array( $this, 'getSetting' ), [
				'is_safe' => [ 'html' ]
			] ),
			new \Twig_SimpleFunction( 'phrasesList', array( $this, 'getPhrasesList' ), [
				'is_safe' => [ 'html' ]
			] ),
            new \Twig_SimpleFunction( 'is_url_exist', array( $this, 'is_url_exist' ), [
                'is_safe' => [ 'html' ]
            ] ),
            new \Twig_SimpleFunction( 'imageToBase64', array( $this, 'getImageToBase' ), [
                'is_safe' => [ 'html' ]
            ] ),
			new \Twig_SimpleFunction('change_language', array($this, 'getChangeLanguage'),[
				'is_safe' => ['html']
			]),
			new \Twig_SimpleFunction('url_tagging', array($this, 'urlTagging'),[
				'is_safe' => ['html']
			]),
			new \Twig_SimpleFunction('active_tagging', array($this, 'activeTagging'),[
				'is_safe' => ['html']
			]),
		);
	}
	
	public function urlTagging($tag, $newTag)
	{
	    if(in_array($newTag, $tag)){
            $tag = array_diff($tag, [$newTag]);
        }else {
            array_push($tag, $newTag);
        }
		return array_unique($tag);
	}
	
	public function activeTagging($tag, $tagCurrent)
	{
		if(!empty($tag)) {
			if ( is_array( $tag ) ) {
				if ( in_array($tagCurrent, $tag ) ) {
					return true;
				} else {
					return false;
				}
			} else {
				if ( $tag == $tagCurrent ) {
					return true;
				} else {
					return false;
				}
			}
		}
		return false;
	}
	
	public function getChangeLanguage($statusLanguage = false)
	{
		return $this->container->get('app.helper.changeLanguage')->change($statusLanguage);
	}
	
	public function getImageToBase( $image ){
        $provider = $this->container->get( $image->getProviderName() );
        $url      = substr($provider->generatePublicUrl( $image, 'reference' ), 1);
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->file($url);
        return 'data:'.$type.';base64,'.base64_encode(file_get_contents($url));
    }
	
	public function getDynamicLinkCmsPage( $type, $locale )
	{
		$page = $this->manager->getRepository( 'App\Entity\Page' )->getSlugPage( $type, $locale );
		
		return ! empty( $page ) ? $page['slug'] : null;
	}
	
	public function getDynamicLinkCategoryPage( $id )
	{
		$videoCategory = $this->manager->getRepository( 'App\Entity\VideoCategories' )->findOneBy( ['id' => $id ] );
		
		return ! empty( $videoCategory ) ? $videoCategory->getSlug() : null;
	}
	
	public function getSetting()
	{
		return $this->manager->getRepository( 'App\Entity\Setting' )->find( 1 );
	}
	
	public function getPhrasesList( $category, $type, $locale )
	{
		return $this->manager->getRepository( 'App\Entity\Phrases' )->findByPhrasesCategory( $category, $type, $locale );
	}
	
	public function is_url_exist($url){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if($code == 200){
			$status = true;
		}else{
			$status = false;
		}
		curl_close($ch);
		return $status;
	}
	
}
