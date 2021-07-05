<?php

namespace App\Controller;

use App\Constants\TypePageConstants;
use App\Helper\Form;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class VideoCategoryController extends AbstractController
{
    /**
     * @Route("/category/{slug}", name="video_category", defaults={"_locale"="%locale%"}, requirements={"_locale" = "%locales_in_line%"})
     */
    public function index( $slug, Request $request, Form $form, TranslatorInterface $translator)
    {
    	$category = $this->getDoctrine()->getRepository('App\Entity\VideoCategories')->getOneBySlug( $slug, $this->getParameter( 'locale' ) );

	    $object = $this->getDoctrine()->getRepository( 'App\Entity\Page' )->getSlugPage( TypePageConstants::CATEGORIES_VIDEO_VALUES, $this->getParameter( 'locale' ) );
	
	    $breadcrumbs[] = ['url' => $this->generateUrl('home'), 'title' => $translator->trans('front.home')];
	    
	    $breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $object['slug']]), 'title' => $translator->trans('front.all_video_categories')];
	    
	    if ( ! $category ) {
		    //Если не найдена категория на каком-то языке - переадресовываем на страницу каталога
		    $object = $this->getDoctrine()->getRepository( 'App\Entity\Page' )->getSlugPage( TypePageConstants::ALL_VIDEO_VALUES, $this->getParameter( 'locale' ) );
		    if ( ! $object ) {
			    throw $this->createNotFoundException( 'Unable to find text page.' );
		    }else{
		    	if($request->getLocale() == $this->getParameter('locale')){
				    $url = '/' . $object['slug'];
			    }else {
				    $url = '/' . $request->getLocale() . '/' . $object['slug'];
			    }
		    	return $this->redirect($url, 301);
		    }
	    }
	    $breadcrumbs[] = ['url' => $this->generateUrl('video_category', ['slug' => $slug]), 'title' => $category->getTitle()];
	
	    $setting = $this->getDoctrine()->getRepository( 'App\Entity\Setting' )->dataForTheMainPage();
	
	    $settingForming = $form->getFormingFrom( $setting );
	
	    $setting = $settingForming->setting[0];
	
	    //Данные мета. Сначала берутся данные страницы, в случае отсутствия - по умолчанию заполненные в настройках
	    $meta = (object) [
		    'title'       => $category->getMetaTitle() ?: $setting->getMetaTitle(),
		    'description' => $category->getMetaDescription() ?: $setting->getMetaDescription(),
		    'keywords'    => $category->getMetaKeywords() ?: $setting->getMetaKeywords(),
		    'image'       => $category->getMetaImage() ?: $setting->getMetaImage(),
		    'canonical'   => $category->getMetaCanonical() ?: $setting->getMetaCanonical(),
	    ];
	
	    //Общий массив для всех динамических данных страниц
	    $dynamic = [];
	
	    //Устанавливаем лимит на отображение количества видео на странице
	    $limit = 20;
	
	    //Достаем все видео
	    $categoryAll = $this->getDoctrine()->getRepository( 'App\Entity\VideoCategories' )->findAll();
	
	    //Мероприятия
	    $event = $this->getDoctrine()->getRepository( 'App\Entity\Holidays' )->findAll();
	
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
	
	    //Для динамических данных со всех страниц используем один массив - dynamic
	    $dynamic['event']    = $event;
	    $dynamic['category'] = $categoryAll;
	    
	    //Достаем видео сперва по параметрам:
	    //Если определена страница, высчитываем offset. -1 от текущей страницы для коррекции работы страниц.
	    $offset = $request->query->has( 'page' ) ? ( ( $request->query->get( 'page' ) - 1 ) * $limit ) : '';
	    //Язык - по умолчанию: русский
	    $lang = $request->query->has( 'lang' ) ? $request->query->get( 'lang' ) : $request->getLocale();
	    //Пол - по умолчанию: всем
	    $sex = $request->query->has( 'sex' ) ? $request->query->get( 'sex' ) : '';
	    //Количество человек - по умолчанию: Видео для 1 человека
	    $person = $request->query->has( 'number_of_persons' ) ? $request->query->get( 'number_of_persons' ) : '';
	    //Мероприятие - по умолчанию: День Рождение
	    $events = $request->query->has( 'event' ) ? $request->query->get( 'event' ) : '';
	    //Кому - по умолчанию: ребенок
	    $cat = $category->getId();
	    //Метки
	    $tags = $request->query->get('tag');
	    //Варианты ролика
	    $option = $request->query->has( 'option' ) ? $request->query->get( 'option' ) : '';
	    //Получаем видео согласно фильтру или данным по умолчанию
	    $videos = $this->getDoctrine()->getRepository( 'App\Entity\Video' )->findByVideoByFilter( $lang, $sex, $person, $events, $cat, $option, $offset, $limit, $tags );

	    $tagCloudSlug = [];
	    $tagCloud = [];
	    $videoCategory = $this->getDoctrine()->getRepository( 'App\Entity\Video' )->findByVideoByCategory( $lang, $cat );
	    if(!empty($videoCategory)){
	    	foreach ($videoCategory as $video){
			    if($video->getTags()->isEmpty() == false){
			    	foreach ($video->getTags()->toArray() as $tag){
			    		if(in_array($tag->getSlug(), $tagCloudSlug) == false){
						    array_push($tagCloudSlug, $tag->getSlug());
						    array_push($tagCloud, $tag);
					    }
				    }
			    }
		    }
	    }
	    
	    //Добавляем видео в динамический массив страниц
	    $dynamic['video']       = $videos->query;
	    $dynamic['video_count'] = $videos->count;
	    $dynamic['tag_cloud']   = $tagCloud;
	    $dynamic['limit']       = $limit;
	    $dynamic['offset']      = $offset;
	    $dynamic['currency']    = $currency;
	
	    return $this->render( 'page/category.html.twig', [
		    'dynamic'    => (object) $dynamic,
		    'page'       => $category,
		    'meta'       => $meta,
		    'setting'    => $setting,
		    'breadcrumbs'=> $breadcrumbs,
		    'menuHeader' => $settingForming->header,
		    'menuFooter' => $settingForming->footer
	    ] );
    }
}
