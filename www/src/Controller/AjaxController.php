<?php

namespace App\Controller;

use App\Helper\Form;
use GeoIp2\Database\Reader;
use Doctrine\Common\Collections\Expr\Comparison;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends AbstractController
{
	
	/**
	 * @Route("/ajax/video", name="ajax_search_video", methods={"POST"})
	 */
	public function ajaxVideoSearch( Request $request )
	{
		
		$requestCheckPost = $request->request->all();
		if ( !empty($requestCheckPost) ) {
			
			//Устанавливаем лимит на отображение количества видео на странице
			$limit = 20;
			
			$offset = $request->request->has( 'offset' ) ? $request->request->get( 'offset' ) : 0;
			$lang   = $request->request->has( 'lang' ) ? $request->request->get( 'lang' ) : $request->getLocale();
			$sex    = $request->request->has( 'sex' ) ? $request->request->get( 'sex' ) : null;
			$person = $request->request->has( 'number_of_persons' ) ? $request->request->get( 'number_of_persons' ) : null;
			$events = $request->request->has( 'event' ) ? $request->request->get( 'event' ) : null;
			$cat    = $request->request->has( 'category' ) ? $request->request->get( 'category' ) : null;
			$option = $request->request->has( 'option' ) ? $request->request->get( 'option' ) : null;
			
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
			
			$videoSearch = $this->getDoctrine()->getRepository( 'App\Entity\Video' )->findByVideoByFilter( $lang, $sex, $person, $events, $cat, $option, $offset, $limit );
			
			$response = new Response(
				$this->render( 'components/blocks/video_store/block_all-video.html.twig', [
					'dynamic' => (object) [
						'video'       => $videoSearch->query,
						'video_count' => $videoSearch->count,
						'limit'       => $limit,
						'offset'      => $offset,
						'currency'    => $currency
					]
				] )->getContent(),
				Response::HTTP_OK,
				array( 'content-type' => 'text/html' )
			);
			
			$data['code']  = ! empty( $videoSearch ) ? 200 : 400;
			$data['count'] = $videoSearch->count;
			$data['list']  = $response->getContent();
			$data['limit'] = $limit;
			
			return new JsonResponse( $data, 200 );
			
		}
	}
	
	/**
	 * @Route("/ajax/category/video", name="ajax_category_search_video", methods={"POST"})
	 */
	public function ajaxCategoryVideoSearch( Request $request )
	{
		
		$requestCheckPost = $request->request->all();
		if ( !empty($requestCheckPost) ) {
			
			//Устанавливаем лимит на отображение количества видео на странице
			$limit = 20;
			
			$offset = $request->request->has( 'offset' ) ? $request->request->get( 'offset' ) : 0;
			$lang   = $request->request->has( 'lang' ) ? $request->request->get( 'lang' ) : $request->getLocale();
			$cat    = $request->request->has( 'category' ) ? $request->request->get( 'category' ) : null;
			$tags    = $request->request->has( 'tag' ) ? $request->request->get( 'tag' ) : null;
			
			$videoSearch = $this->getDoctrine()->getRepository( 'App\Entity\Video' )->findByVideoByFilter( $lang, null, null, null, $cat, null, $offset, $limit, $tags );
			$response = new Response(
				$this->render( 'components/blocks/category/block_item_video.html.twig', [
					'videos'       => $videoSearch->query,
				] )->getContent(),
				Response::HTTP_OK,
				array( 'content-type' => 'text/html' )
			);
			
			$data['code']  = ! empty( $videoSearch ) ? 200 : 400;
			$data['count'] = $videoSearch->count;
			$data['list']  = $response->getContent();
			$data['limit'] = $limit;
			
			return new JsonResponse( $data, 200 );
			
		}
	}
	/**
	 * @Route("/ajax/review", name="ajax_search_review", methods={"POST"})
	 */
	public function ajaxReviewSearch( Request $request )
	{
		
		$requestCheckPost = $request->request->all();
		if ( !empty($requestCheckPost) ) {
			//Устанавливаем лимит на отображение количества видео на странице
			$limit = 20;
			
			//Получаем количество уже отображаемых на странице отзывов
			$offset = $request->request->has( 'offset' ) ? $request->request->get( 'offset' ) : 0;
			
			//Если переданный type = 2 - значит видео отзывы, иначе обычные
			if ( $request->request->get( 'type' ) == 2 ) {
				
				$reviewSearch = $this->getDoctrine()->getRepository( 'App\Entity\ReviewVideo' )->findByReview( $offset, $limit, $request->getLocale() );
				
				$response = new Response(
					$this->render( 'components/blocks/testimonials/block_review-videos-list-wrap.html.twig', [
						'dynamic' => (object) [
							'video_review'       => $reviewSearch->query,
							'review_video_count' => $reviewSearch->count,
							'limit'              => $limit,
							'offset'             => $offset
						]
					] )->getContent(),
					Response::HTTP_OK,
					array( 'content-type' => 'text/html' )
				);
				
				$data['code']  = ! empty( $reviewSearch ) ? 200 : 400;
				$data['count'] = $reviewSearch->count;
				$data['list']  = $response->getContent();
				$data['limit'] = $limit;
				
				return new JsonResponse( $data, 200 );
				
			} else {
				
				$reviewSearch = $this->getDoctrine()->getRepository( 'App\Entity\Review' )->findByReview( $offset, $limit, $request->getLocale() );
				
				$response = new Response(
					$this->render( 'components/blocks/testimonials/block_review-list-wrap.html.twig', [
						'dynamic' => (object) [
							'review'       => $reviewSearch->query,
							'review_count' => $reviewSearch->count,
							'limit'        => $limit,
							'offset'       => $offset
						]
					] )->getContent(),
					Response::HTTP_OK,
					array( 'content-type' => 'text/html' )
				);
				
				$data['code']  = ! empty( $reviewSearch ) ? 200 : 400;
				$data['count'] = $reviewSearch->count;
				$data['list']  = $response->getContent();
				$data['limit'] = $limit;
				
				return new JsonResponse( $data, 200 );
			}
			
		}
	}
	
	/**
	 * @Route("/ajax/blog", name="ajax_blog_articles", methods={"POST"})
	 */
	public function ajaxBlogArticles( Request $request )
	{
		
		$requestCheckPost = $request->request->all();
		if ( !empty($requestCheckPost) ) {
			$category = null;
			$locale   = $request->getLocale();
			$limit    = 10;
			$offset   = 0;
			
			$request->setLocale( $request->request->get( '_locale' ) );
			
			//Если идет сортировка по категории, ищем ее и передаем в запрос
			if ( $request->request->has( 'category' ) ) {
				$category = $this->getDoctrine()->getRepository( 'App\Entity\BlogCategories' )->find( $request->request->get( 'category', null ) );
			}
			
			if ( $request->request->has( 'offset' ) ) {
				$offset = $request->request->get( 'offset' );
			}
			
			if ( ! empty( $category ) ) {
				if ( $category->getBlog()->isEmpty() == false ) {
					
					$expr = new Comparison( "active", "=", 1 );
					
					$criteria = \Doctrine\Common\Collections\Criteria::create()
					                                                 ->where( $expr )
					                                                 ->setMaxResults( $limit )
					                                                 ->setFirstResult( $offset )
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
				$blog = $this->getDoctrine()->getRepository( 'App\Entity\Blog' )->getBlogList( $locale, $limit, $offset );
			}
			
			$response = new Response(
				$this->render( 'components/blocks/blog/block_articles-box.html.twig', [
					'dynamic' => (object) [
						'blog'       => $blog->query,
						'blog_count' => $blog->count,
						'limit'      => $limit,
						'offset'     => $offset
					]
				] )->getContent(),
				Response::HTTP_OK,
				array( 'content-type' => 'text/html' )
			);
			
			$data['code']  = ! empty( $blog->query ) ? 200 : 400;
			$data['count'] = $blog->count;
			$data['list']  = $response->getContent();
			$data['limit'] = $limit;
			
			return new JsonResponse( $data, 200 );
			
		}
	}
	
	/**
	 * @Route("/ajax/add/review", name="ajax_add_review", methods={"POST"})
	 */
	public function ajaxAddNewReview( Request $request, Form $form )
	{
		
		$requestCheckPost = $request->request->all();
		if ( !empty($requestCheckPost) ) {
			
			$createReview = $form->addNewReview( $request->request->get( 'text', '' ), $this->getUser(), $request->getLocale() );
			
			return new JsonResponse(
				array(
					'code' => $createReview ? 200 : 400
				), 200 );
			
		}
	}
	
	/**
	 * @Route("/ajax/add/feedback", name="ajax_add_feedback", methods={"POST"})
	 */
	public function ajaxAddNewFeedback( Request $request, Form $form )
	{
		
		$requestCheckPost = $request->request->all();
		if ( !empty($requestCheckPost) ) {
			
			$createFeedback = $form->addNewFeedback( $request, $this->getUser() );
			
			return new JsonResponse(
				array(
					'code' => $createFeedback ? 200 : 400
				), 200 );
			
		}
	}
	
	/**
	 * @Route("/ajax/account", name="ajax_account", methods={"POST"})
	 */
	public function ajaxAccount( Request $request, Form $form )
	{
		
		$requestCheckPost = $request->request->all();
		if ( !empty($requestCheckPost) ) {
			
			$editAccount = $form->editAccount( $request, $this->getUser() );
			return new JsonResponse( array(
				'code'    => $editAccount === true ? 200 : 400,
				'message' => $editAccount
			), 200 );
			
		}
	}
	
	/**
	 * @Route("/ajax/account/avatar", name="ajax_account_avatar", methods={"POST"})
	 */
	public function ajaxAccountAvatar( Request $request, Form $form )
	{
		
		$requestCheckPost = $request->request->all();
		$requestCheckFiles = $request->files->all();

		if ( !empty($requestCheckPost) or !empty($requestCheckFiles) ) {
			
			$editAccount = $form->editAccount( $request, $this->getUser() );
			
			return new JsonResponse( array(
				'code'    => $editAccount === true ? 200 : 400,
				'message' => $editAccount
			), 200 );
			
		}
	}
}
