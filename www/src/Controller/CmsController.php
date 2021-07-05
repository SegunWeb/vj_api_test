<?php

namespace App\Controller;

use App\Constants\PaymentConstants;
use App\Helper\Form;
use App\Constants\ActiveConstants;
use App\Constants\TypePageConstants;
use Doctrine\Common\Collections\Expr\Comparison;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Knp\Component\Pager\PaginatorInterface;

class CmsController extends AbstractController
{
	
	/**
	 * @Route("/{slug}", name="app_cms_content", methods={"GET", "POST"}, defaults={"_locale"="%locale%"},
	 *     requirements={"_locale" = "%locales_in_line%"})
	 */
	public function index( $slug, Request $request, Form $form, TranslatorInterface $translator, PaginatorInterface $paginator )
	{

		$object = $this->getDoctrine()->getRepository( 'App\Entity\Page' )->getOneBySlug( $slug, $request->getLocale() );
		
		if ( ! $object ) {
			//Если не найдена страница на каком-то языке - пробуем найти на дефолтному языку страницу и отдать контент с нее
			$object = $this->getDoctrine()->getRepository( 'App\Entity\Page' )->getOneBySlug( $slug, $this->getParameter( 'locale' ) );
			if ( ! $object ) {
				if($slug == 'admin'){
					return $this->redirectToRoute('admin_login', [], 301);
				}
				throw $this->createNotFoundException( 'Unable to find text page.' );
			}
		}
		
		$breadcrumbs[] = ['url' => $this->generateUrl('home'), 'title' => $translator->trans('front.home')];
		
		$setting = $this->getDoctrine()->getRepository( 'App\Entity\Setting' )->dataForTheMainPage();
		
		$settingForming = $form->getFormingFrom( $setting );
		
		$setting = $settingForming->setting[0];
		
		//Данные мета. Сначала берутся данные страницы, в случае отсутствия - по умолчанию заполненные в настройках
		$meta = (object) [
			'title'       => $object->getMetaTitle() ?: $setting->getMetaTitle(),
			'description' => $object->getMetaDescription() ?: $setting->getMetaDescription(),
			'keywords'    => $object->getMetaKeywords() ?: $setting->getMetaKeywords(),
			'image'       => $object->getMetaImage() ?: $setting->getMetaImage(),
			'canonical'   => $object->getMetaCanonical() ?: $setting->getMetaCanonical(),
		];
		
		//Общий массив для всех динамических данных страниц
		$dynamic = [];
		
		$template = 'page/static_page.html.twig';
		
		//Если getType равен нулю, значит эта страница без каких-либо подвязок
		if ( $object->getType() > 0 ) {

            $dynamic['subscription'] = false;
            $dynamic['subscription_type'] = false;
            $dynamic['price'] = false;
            $dynamic['is_subscribe_enable'] = false;

            switch ($setting->getPaymentType()) {
                case (PaymentConstants::ONLY_SUBSCRIBE):
                    $dynamic['is_subscribe_enable'] = true;
                    break;
                default:
                    $dynamic['is_subscribe_enable'] = false;
                    break;
            }

            //Массив страниц личного кабинета
            $userAccountTypes = [
                TypePageConstants::USER_ACCOUNT_VALUES,
                TypePageConstants::USER_ACCOUNT_PAID_VALUES,
                TypePageConstants::USER_ACCOUNT_NOT_PAID_VALUES,
                TypePageConstants::USER_ACCOUNT_SETTING_VALUES,
            ];

            // Если getType равен одной из страниц пользователя, получаем данные о подписке
            if( in_array( $object->getType(), $userAccountTypes ) ) {
                if($this->getUser()) {
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
                }
            }

			//Тип 5 - Страница каталога
			if ( $object->getType() == TypePageConstants::ALL_VIDEO_VALUES ) {
				
				$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $object->getSlug()]), 'title' => $translator->trans('front.catalog')];
				
				//Устанавливаем лимит на отображение количества видео на странице
				$limit = 30;
				
				//Достаем детские видео, а это первая активная категория
				$category = $this->getDoctrine()->getRepository( 'App\Entity\VideoCategories' )->findAll();
				
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
				$dynamic['category'] = $category;
				
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
				$cat = $request->query->has( 'category' ) ? $request->query->get( 'category' ) : 1;
				//Варианты ролика
				$option = $request->query->has( 'option' ) ? $request->query->get( 'option' ) : '';
				//Получаем видео согласно фильтру или данным по умолчанию
				$video = $this->getDoctrine()->getRepository( 'App\Entity\Video' )->findByVideoByFilter( $lang, $sex, $person, $events, $cat, $option, $offset, $limit );
				//Добавляем видео в динамический массив страниц
				$dynamic['video']       = $video->query;
				$dynamic['video_count'] = $video->count;
				$dynamic['limit']       = $limit;
				$dynamic['offset']      = $offset;
				$dynamic['currency']    = $currency;
				//Темплейт отображения страницы
				$template = 'page/video_store.html.twig';
				
			} elseif ( $object->getType() == TypePageConstants::REVIEW_VALUES ) {
				
				$limit = 2;
				
				$offset = 0;
				
				//Достаем обычные отзывы
				$review = $this->getDoctrine()->getRepository( 'App\Entity\Review' )->findByReview( 0, $limit, $request->getLocale() );
				
				//Достаем видео отзывы
				$reviewVideo = $this->getDoctrine()->getRepository( 'App\Entity\ReviewVideo' )->findByReview( 0, $limit, $request->getLocale() );
				
				$dynamic['review']             = ( isset( $review->query ) ) ? $review->query : null;
				$dynamic['video_review']       = ( isset( $reviewVideo->query ) ) ? $reviewVideo->query : null;
				$dynamic['review_count']       = ( isset( $review->count ) ) ? $review->count : null;
				$dynamic['video_review_count'] = ( isset( $reviewVideo->count ) ) ? $reviewVideo->count : null;
				$dynamic['limit']              = $limit;
				$dynamic['offset']             = $offset;
				
				//Темплейт отображения страницы
				$template = 'page/testimonials.html.twig';
				
			} elseif ( $object->getType() == TypePageConstants::HELP_VALUES ) {
				
				$help = $this->getDoctrine()->getRepository( 'App\Entity\Help' )->findBy( [ 'active' => ActiveConstants::ACTIVE ] );
				
				$dynamic['help'] = $help;
				
				//Темплейт отображения страницы
				$template = 'page/support.html.twig';
				
			} elseif ( $object->getType() == TypePageConstants::BLOG_VALUES ) {
				
				$limit      = 10;
				$category   = null;
				$locale     = $request->getLocale();
				$categories = $this->getDoctrine()->getRepository( 'App\Entity\BlogCategories' )->findAll();
				
				$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $object->getSlug()]), 'title' => $translator->trans('front.blog')];
				
				//Если идет сортировка по категории, ищем ее и передаем в запрос
				if ( $request->query->has( 'category' ) ) {
					$queryCategory = $request->query->get( 'category' );
					if ( ! empty( $categories ) ) {
						foreach ( $categories as $cat ) {
							if ( $cat->getId() == $queryCategory ) {
								$category = $cat;
								break;
							}
						}
					}
				}
				if ( ! empty( $category ) ) {
					
					$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $object->getSlug()]).'?category='.$category->getId(), 'title' => $category->getTitle()];
					
					if ( $category->getBlog()->isEmpty() == false ) {
						
						$expr = new Comparison( "active", "=", 1 );
						
						$criteria = \Doctrine\Common\Collections\Criteria::create()
						                                                 ->where( $expr )
						                                                 ->setMaxResults( $limit )
						                                                 ->setFirstResult( 0 )
						                                                 ->orderBy( array( 'id' => \Doctrine\Common\Collections\Criteria::DESC ) );
						
						$query = $category->getBlog()->matching( $criteria );
						
						$criteriaCount = \Doctrine\Common\Collections\Criteria::create()
						                                                      ->where( $expr )
						                                                      ->setFirstResult( 0 );
						
						$queryCount = $category->getBlog()->matching( $criteriaCount );
						
						$blog = (object) array( 'count' => $queryCount->count(), 'query' => $query->toArray() );
					} else {
						$blog = null;
					}
				} else {
					$blog = $this->getDoctrine()->getRepository( 'App\Entity\Blog' )->getBlogList( $locale, $limit, 0 );
				}
				
				$dynamic['blog']       = ( isset( $blog->query ) ) ? $blog->query : null;
				$dynamic['blog_count'] = ( isset( $blog->count ) ) ? $blog->count : null;
				$dynamic['limit']      = $limit;
				$dynamic['category']   = $categories;
				
				//Темплейт отображения страницы
				$template = 'page/blog.html.twig';
				
			} elseif ( $object->getType() == TypePageConstants::USER_ACCOUNT_VALUES ) {
				
				$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $object->getSlug()]), 'title' => $translator->trans('front.account')];
				
				if ( empty( $this->getUser() ) ) {
					return $this->redirectToRoute('authorization', [], 301);
				}
				$limit = 10;
				
				$orderList  = $this->getDoctrine()->getRepository( 'App\Entity\Order' )->findBy(['users' => $this->getUser()], ['createdAt' => 'DESC'] );
				
				$paginate = $paginator->paginate( $orderList, $request->query->getInt('page', 1), $limit );
				
				$dynamic['orders'] = $paginate;

				$template = 'page/account.html.twig';

			} elseif ( $object->getType() == TypePageConstants::USER_ACCOUNT_PAID_VALUES) {
				$pageAccount = $this->getDoctrine()->getRepository('App\Entity\Page')->getSlugPage(TypePageConstants::USER_ACCOUNT_PAID_VALUES, $request->getLocale());
				$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $pageAccount['slug'] ]), 'title' => $translator->trans('front.account')];
				$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $object->getSlug()]), 'title' => $object->getTitle() ];
				
				if ( empty( $this->getUser() ) ) {
					return $this->redirectToRoute('authorization', [], 301);
				}
				$limit = 10;
				
				$orderList  = $this->getDoctrine()->getRepository( 'App\Entity\Order' )->findBy(['users' => $this->getUser(), 'active' => ActiveConstants::ORDER_PAID_VALUE], ['createdAt' => 'DESC']  );
				
				$paginate = $paginator->paginate( $orderList, $request->query->getInt('page', 1), $limit );
				
				$dynamic['orders'] = $paginate;
				
				$template = 'page/account_paid.html.twig';
				
			} elseif ( $object->getType() == TypePageConstants::USER_ACCOUNT_NOT_PAID_VALUES) {
				$pageAccount = $this->getDoctrine()->getRepository('App\Entity\Page')->getSlugPage(TypePageConstants::USER_ACCOUNT_NOT_PAID_VALUES, $request->getLocale());
				$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $pageAccount['slug'] ]), 'title' => $translator->trans('front.account')];
				$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $object->getSlug()]), 'title' => $object->getTitle() ];
				
				if ( empty( $this->getUser() ) ) {
					return $this->redirectToRoute('authorization', [], 301);
				}
				$limit = 10;
				
				$orderList  = $this->getDoctrine()->getRepository( 'App\Entity\Order' )->findBy(['users' => $this->getUser(), 'active' => ActiveConstants::ORDER_NOT_PAID_VALUE], ['createdAt' => 'DESC']  );
				
				$paginate = $paginator->paginate( $orderList, $request->query->getInt('page', 1), $limit );
				
				$dynamic['orders'] = $paginate;
				
				$template = 'page/account_order.html.twig';
				
			} elseif ( $object->getType() == TypePageConstants::USER_ACCOUNT_SETTING_VALUES) {
				$pageAccount = $this->getDoctrine()->getRepository('App\Entity\Page')->getSlugPage(TypePageConstants::USER_ACCOUNT_VALUES, $request->getLocale());
				$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $pageAccount['slug'] ]), 'title' => $translator->trans('front.account')];
				$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $object->getSlug()]), 'title' => $object->getTitle() ];
				
				if ( empty( $this->getUser() ) ) {
					return $this->redirectToRoute('authorization', [], 301);
				}
				
				$template = 'page/account_setting.html.twig';
				
			} elseif ( $object->getType() == TypePageConstants::USER_AGREEMENT_VALUES ) {
				
				$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $object->getSlug()]), 'title' => $translator->trans('front.terms_of_use')];
				
				$template = 'page/static_page.html.twig';
				
			} elseif ( $object->getType() == TypePageConstants::REFUND_VALUES ) {
				
				$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $object->getSlug()]), 'title' => $translator->trans('front.refund')];
				
				$template = 'page/static_page.html.twig';
				
			} elseif ( $object->getType() == TypePageConstants::ABOUT_VALUES ) {
				
				$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $object->getSlug()]), 'title' => $translator->trans('front.how_we_do_it')];
				
				$template = 'page/about_us.html.twig';
				
			} elseif ( $object->getType() == TypePageConstants::INDEX_VALUES ) {
				
				return $this->redirectToRoute('home', [], 301);
				
			} elseif ( $object->getType() == TypePageConstants::PAGE_NOT_FOUND_VALUES ) {
				
				throw $this->createNotFoundException('The page not found');
				
			} elseif ( $object->getType() == TypePageConstants::CATEGORIES_VIDEO_VALUES ) {
				
				$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $object->getSlug()]), 'title' => $translator->trans('front.all_video_categories')];
				
				$category = $this->getDoctrine()->getRepository('App\Entity\VideoCategories')->findByCategory( $request->getLocale() );
				
				$dynamic['categories'] = $category;
				
				$template = 'page/categories.html.twig';
				
			}
		}
		
		return $this->render( $template, [
			'dynamic'    => (object) $dynamic,
			'page'       => $object,
			'meta'       => $meta,
			'setting'    => $setting,
			'breadcrumbs'=> $breadcrumbs,
			'menuHeader' => $settingForming->header,
			'menuFooter' => $settingForming->footer
		] );
	}
	
	/**
	 * @Route("/blog/{slug}", name="app_cms_blog_content", methods={"GET", "POST"}, defaults={"_locale"="%locale%"},
	 *     requirements={"_locale" = "%locales_in_line%"})
	 */
	public function blogArticle( $slug, Form $form, Request $request, TranslatorInterface $translator )
	{
		
		$object = $this->getDoctrine()->getRepository( 'App\Entity\Page' )->findOneBy( [ 'type' => TypePageConstants::BLOG_VALUES ] );
		
		$breadcrumbs[] = ['url' => $this->generateUrl('home'), 'title' => $translator->trans('front.home')];
		
		if ( ! empty( $object ) ) {
			
			$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_content', ['slug' => $object->getSlug()]), 'title' => $translator->trans('front.blog')];
			
			$dynamic['category'] = $this->getDoctrine()->getRepository( 'App\Entity\BlogCategories' )->getCategories( $request->getLocale() );;
			
			$blog = $this->getDoctrine()->getRepository( 'App\Entity\Blog' )->getPost( $slug, $request->getLocale() );
			
			if($blog->getCategory()->isEmpty() == false) {
				$breadcrumbs[] = [ 'url'   => $this->generateUrl( 'app_cms_content', [ 'slug' => $object->getSlug() ] ) . '?category=' . $blog->getCategory()->first()->getId(), 'title' => $blog->getCategory()->first()->getTitle() ];
			}
			$breadcrumbs[] = ['url' => $this->generateUrl('app_cms_blog_content', ['slug' => $slug]), 'title' => $blog->getTitle()];
			
			//Дополнительные запросы что бы достать следующий и предыдущие записи блога
			$next = $this->getDoctrine()->getRepository( 'App\Entity\Blog' )->getNextArticle( $slug, $request->getLocale() );
			$prev = $this->getDoctrine()->getRepository( 'App\Entity\Blog' )->getPrevArticle( $slug, $request->getLocale() );
			
			$setting = $this->getDoctrine()->getRepository( 'App\Entity\Setting' )->dataForTheMainPage();
			
			$settingForming = $form->getFormingFrom( $setting );
			
			$setting = $settingForming->setting[0];
			
			//Данные мета. Сначала берутся данные страницы, в случае отсутствия - по умолчанию заполненные в настройках
			$meta = (object) [
				'title'       => $object->getMetaTitle() ?: $setting->getMetaTitle(),
				'description' => $object->getMetaDescription() ?: $setting->getMetaDescription(),
				'keywords'    => $object->getMetaKeywords() ?: $setting->getMetaKeywords(),
				'image'       => $object->getMetaImage() ?: $setting->getMetaImage(),
				'canonical'   => $object->getMetaCanonical() ?: $setting->getMetaCanonical(),
			];
			
			return $this->render( 'page/blog_article.html.twig', [
				'blog'       => $blog,
				'next'       => $next,
				'prev'       => $prev,
				'dynamic'    => (object) $dynamic,
				'page'       => $object,
				'meta'       => $meta,
				'setting'    => $setting,
				'breadcrumbs'=> $breadcrumbs,
				'menuHeader' => $settingForming->header,
				'menuFooter' => $settingForming->footer
			] );
			
		} else {
			throw $this->createNotFoundException( 'Unable to find text page.' );
		}
	}
}
