<?php

namespace App\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ChangeLanguage
{
	protected $em;
	protected $container;

	function __construct(EntityManagerInterface $em, ContainerInterface $container)
	{
		$this->container = $container;
		$this->em = $em;
	}

	public function change($current_language_see = false){
		//Достаем с контейнера request_stack
		$request_stack = $this->container->get('request_stack');
		//Теперь с request_stack, получаем request страницы.
		$request = $request_stack->getCurrentRequest();

		/*Получаем текущий язык*/
		$current_locale = $request->getLocale();
		/*Получаем дефолтный язык*/
		$default_locale = $this->container->getParameter('locale');

		/*Проверка на CMS страницу или статическую и формулирование URL*/
		$url = '';

		//Достаем слаг для CMS страниц
		$slug = $request->get('slug');
		if(!empty($slug)) {
			//Проверяем, есть ли страница со слагом
			$slug_repository = $this->em->getRepository( 'App\Entity\Page' )->getOneBySlug( $request->get( 'slug' ), 'ru' );
			if(!empty($slug_repository)) {
				if ( $slug_repository->getTranslations()->isEmpty() == false ) {
					$url = $slug;
					foreach ( $slug_repository->getTranslations()->toArray() as $trans ) {
						if ( $trans->getLocale() == $default_locale ) {
							//Формулируем URL для языка
							$url_lg                         = '/' . $url;
							$language_list_front[ $url_lg ] = $trans->getLocale();
						} else {
							//Формулируем URL для языка
							$url_lg                         = '/' . $trans->getLocale() . '/' . $url;
							$language_list_front[ $url_lg ] = $trans->getLocale();
						}
					}
					return $language_list_front;
				}
			}
		}else{
			if($current_locale != $default_locale){
				$url = substr($request->getRequestUri(), 3);
			}else {
				$url = $request->getRequestUri();
			}
		}

		//Получаем список активных языков
		$language_list = $this->container->getParameter('locales');
		//Массив для формирования списка передаваемый на фронт
		$language_list_front = [];

		//Если список активных языков не пуст, формулируем массив
		if(!empty($language_list)){

			foreach ($language_list as $lg){
				if($lg == $default_locale ) {
					//Формулируем URL для языка
					$url_lg = $url;
					$language_list_front[ $url_lg ] = $lg;
				}else{
					//Формулируем URL для языка
					$url_lg = '/' . $lg . $url;
					$language_list_front[ $url_lg ] = $lg;
				}
			}
		}
		return $language_list_front;
	}

}