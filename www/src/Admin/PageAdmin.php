<?php

namespace App\Admin;

use App\Constants\TypePageConstants;
use App\Entity\Page;
use App\Entity\Review;
use App\Entity\ReviewVideo;
use App\Entity\Video;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use App\Constants\ActiveConstants;
use Sonata\Form\Validator\ErrorElement;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PageAdmin extends AbstractAdmin
{
	/**
	 * @param DatagridMapper $datagridMapper
	 */
	protected function configureDatagridFilters( DatagridMapper $datagridMapper )
	{
		$datagridMapper
			->add( 'slug', null, [ 'label' => $this->trans( 'object.slug_page' ) ] );
	}
	
	/**
	 * @param ListMapper $listMapper
	 */
	protected function configureListFields( ListMapper $listMapper )
	{
		$listMapper
			->add( '_action', null, array(
				'label'   => $this->trans( 'list.actions' ),
				'actions' => array(
					'edit'   => array(),
					'delete' => array()
				)
			) )
			->add( 'title', TextType::class, array( 'label' => $this->trans( 'object.title' ) ) )
			->add( 'slug', null, [ 'label' => $this->trans( 'object.slug_page' ) ] )
			->add( "type", 'choice', [
				'sortable' => false,
				'editable' => true,
				'label'    => $this->trans( 'object.type_page' ),
				'choices'  => array_flip( TypePageConstants::listPages() )
			] )
			->add( 'active', 'choice', [
				'sortable' => false,
				'editable' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => array_flip( ActiveConstants::loadActivityValues() )
			] );
	}
	
	/**
	 * @param FormMapper $formMapper
	 */
	protected function configureFormFields( FormMapper $formMapper )
	{
		$formMapper
			->tab( $this->trans( 'tab.common_info' ) )
			->with( $this->trans( 'tab.common_info' ) )
			->add( 'title', TextType::class, [ 'label' => $this->trans( 'object.title' ) ] )
			->add( 'slug', TextType::class, [ 'label' => $this->trans( 'object.slug_page' ), 'required' => false ] )
			->add( 'parent', EntityType::class, [
				'class'         => Page::class,
				'placeholder'   => $this->trans( 'object.selected_parent' ),
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'd' )
					          ->orderBy( 'd.id', 'ASC' );
				},
				'required'      => false,
				'choice_label'  => 'laveled_title',
				'label'         => $this->trans( 'object.parent' )
			] )
			->add( "type", ChoiceFieldMaskType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.type_page' ),
				'choices'  => TypePageConstants::listPages(),
				'map'      => [
					TypePageConstants::CATEGORIES_VIDEO_VALUES          => [
						'pageContentSeo'
					],
					TypePageConstants::INDEX_VALUES          => [
						'pageContentTitle',
						'pageContentSeo',
						'homeImageHeader',
						'homeVideoNovelty',
						'homeVideoHowWeDoIt',
						'homeImageHowWeDoIt',
						'homeVideoButtonPlayHeader',
						'homeVideoGreetings',
						'homeVideoReview',
						'homeReview',
						'homeContentOurAdvantages',
						'homeOurAdvantages',
						'advantageOneIcon',
						'advantageOneTitle',
						'advantageOneText',
						'advantageTwoIcon',
						'advantageTwoTitle',
						'advantageTwoText',
						'advantageThreeIcon',
						'advantageThreeTitle',
						'advantageThreeText',
						'advantageFourIcon',
						'advantageFourTitle',
						'advantageFourText'
					],
					TypePageConstants::OTHER_VALUES          => [ 'content' ],
					TypePageConstants::ALL_VIDEO_VALUES      => [
						'catalogExamplesVideo',
						'catalogExamplesImageOnVideo'
					],
					TypePageConstants::THANK_YOU_VALUES      => [ 'content' ],
					TypePageConstants::USER_AGREEMENT_VALUES => [ 'content' ],
					TypePageConstants::PAGE_NOT_FOUND_VALUES => [ 'content' ],
					TypePageConstants::REVIEW_VALUES         => [ '' ],
					TypePageConstants::HELP_VALUES           => [
						'content',
						'QaExamplesVideo',
						'QaExamplesImageOnVideo'
					],
					TypePageConstants::ABOUT_VALUES          => [
						'content',
						'aboutExamplesVideo',
						'aboutExamplesImageOnVideo',
						'homeContentOurAdvantages',
						'homeOurAdvantages',
						'advantageOneIcon',
						'advantageOneTitle',
						'advantageOneText',
						'advantageTwoIcon',
						'advantageTwoTitle',
						'advantageTwoText',
						'advantageThreeIcon',
						'advantageThreeTitle',
						'advantageThreeText',
						'advantageFourIcon',
						'advantageFourTitle',
						'advantageFourText'
					],
					TypePageConstants::USER_ACCOUNT_VALUES   => [ 'content' ],
					TypePageConstants::REFUND_VALUES         => [ 'content' ]
				],
			] )
			->add( "active", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => ActiveConstants::loadActivityValues()
			] )
			->end()
			->end()
			->tab( $this->trans( 'tab.content' ) )
			->with( '', [] )
			->add( 'pageContentTitle', TextType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.page_content_title' )
			] )
			->add( 'pageContentSeo', CKEditorType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.page_content_seo' )
			] )
			->add( 'homeVideoGreetings', EntityType::class, [
				'class'         => Video::class,
				'placeholder'   => $this->trans( 'object.selected_home_video_greetings' ),
				'required'      => false,
				'multiple'      => true,
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'v' )
					          ->orderBy( 'v.id', 'ASC' );
				},
				'label'         => $this->trans( 'object.home_video_greetings' )
			] )
			->add( 'homeVideoNovelty', EntityType::class, [
				'class'         => Video::class,
				'placeholder'   => $this->trans( 'object.selected_home_video_novelty' ),
				'required'      => false,
				'multiple'      => true,
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'v' )
					          ->orderBy( 'v.id', 'ASC' );
				},
				'label'         => $this->trans( 'object.home_video_novelty' )
			] )
			->add( 'homeVideoReview', EntityType::class, [
				'class'         => ReviewVideo::class,
				'placeholder'   => $this->trans( 'object.selected_home_video_review' ),
				'required'      => false,
				'multiple'      => true,
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'v' )
					          ->orderBy( 'v.id', 'ASC' );
				},
				'label'         => $this->trans( 'object.home_video_review' )
			] )
			->add( 'homeReview', EntityType::class, [
				'class'         => Review::class,
				'placeholder'   => $this->trans( 'object.selected_home_review' ),
				'required'      => false,
				'multiple'      => true,
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'v' )
					          ->orderBy( 'v.id', 'ASC' );
				},
				'label'         => $this->trans( 'object.home_review' )
			] )
			->add( 'homeContentOurAdvantages', TextareaType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.home_content_our_advantages' )
			] )
			->add( 'catalogExamplesVideo', ModelListType::class, [
				'label'    => $this->trans( 'object.examples_video' ),
				'required' => false
			], array( 'link_parameters' => array( 'context' => 'examples_video' ) ) )
			->add( 'catalogExamplesImageOnVideo', ModelListType::class, [
				'label'    => $this->trans( 'object.qa_examples_video_image' ),
				'required' => false
			], array( 'link_parameters' => array( 'context' => 'video_cover' ) ) )
			->add( 'QaExamplesVideo', ModelListType::class, [
				'label'    => $this->trans( 'object.qa_examples_video' ),
				'required' => false
			], array( 'link_parameters' => array( 'context' => 'examples_video' ) ) )
			->add( 'QaExamplesImageOnVideo', ModelListType::class, [
				'label'    => $this->trans( 'object.qa_examples_video_image' ),
				'required' => false
			], array( 'link_parameters' => array( 'context' => 'video_cover' ) ) )
			->add( 'aboutExamplesVideo', ModelListType::class, [
				'label'    => $this->trans( 'object.qa_examples_video' ),
				'required' => false
			], array( 'link_parameters' => array( 'context' => 'examples_video' ) ) )
			->add( 'aboutExamplesImageOnVideo', ModelListType::class, [
				'label'    => $this->trans( 'object.qa_examples_video_image' ),
				'required' => false
			], array( 'link_parameters' => array( 'context' => 'video_cover' ) ) )
			->add( 'homeVideoHowWeDoIt', ModelListType::class, [
				'label'    => $this->trans( 'object.how_we_do_it_video' ),
				'required' => false
			], array( 'link_parameters' => array( 'context' => 'how_we_do_it' ) ) )
			->add( 'homeImageHowWeDoIt', ModelListType::class, [
				'label'    => $this->trans( 'object.how_we_do_it_image' ),
				'required' => false
			], array( 'link_parameters' => array( 'context' => 'video_cover' ) ) )
			->add( 'content', CKEditorType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.content' )
			] )
			->add( 'advantageOneIcon', ModelListType::class, array(
				'label'    => $this->trans( 'object.advantage_one_icon' ),
				'required' => false
			), array( 'link_parameters' => array( 'context' => 'advantage_icon' ) ) )
			->add( 'advantageOneTitle', TextType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.advantage_one_title' )
			] )
			->add( 'advantageOneText', TextareaType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.advantage_one_text' )
			] )
			->add( 'advantageTwoIcon', ModelListType::class, array(
				'label'    => $this->trans( 'object.advantage_two_icon' ),
				'required' => false
			), array( 'link_parameters' => array( 'context' => 'advantage_icon' ) ) )
			->add( 'advantageTwoTitle', TextType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.advantage_two_title' )
			] )
			->add( 'advantageTwoText', TextareaType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.advantage_two_text' )
			] )
			->add( 'advantageThreeIcon', ModelListType::class, array(
				'label'    => $this->trans( 'object.advantage_three_icon' ),
				'required' => false
			), array( 'link_parameters' => array( 'context' => 'advantage_icon' ) ) )
			->add( 'advantageThreeTitle', TextType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.advantage_three_title' )
			] )
			->add( 'advantageThreeText', TextareaType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.advantage_three_text' )
			] )
			->add( 'advantageFourIcon', ModelListType::class, array(
				'label'    => $this->trans( 'object.advantage_four_icon' ),
				'required' => false
			), array( 'link_parameters' => array( 'context' => 'advantage_icon' ) ) )
			->add( 'advantageFourTitle', TextType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.advantage_four_title' )
			] )
			->add( 'advantageFourText', TextareaType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.advantage_four_text' )
			] )
			->add( 'homeImageHeader', \Sonata\Form\Type\CollectionType::class, [
				'by_reference' => false,
				'label'        => $this->trans( 'object.page_home_image_header' )
			], [
					'edit'   => 'inline',
					'inline' => 'natural'
				]
			)
			->end()
			->end()
			->tab( $this->trans( 'tab.meta' ) )
			->with( $this->trans( 'tab.meta' ), [ 'class' => 'col-md-8' ] )
			->add( 'metaTitle', TextType::class, [
				'label'    => $this->trans( 'object.meta.title' ),
				'required' => false
			] )
			->add( 'metaKeywords', TextType::class, [
				'label'    => $this->trans( 'object.meta.keywords' ),
				'required' => false
			] )
			->add( 'metaDescription', TextType::class, [
				'label'    => $this->trans( 'object.meta.description' ),
				'required' => false
			] )
			->add( 'metaCanonical', TextType::class, [
				'label'    => $this->trans( 'object.meta.canonical' ),
				'required' => false
			] )
			->end()
			->with( $this->trans( 'tab.image' ), [ 'class' => 'col-md-4' ] )
			->add( 'meta_image', ModelListType::class, array(
				'label'    => $this->trans( 'object.meta.image' ),
				'required' => false
			), array( 'link_parameters' => array( 'context' => 'meta' ) ) )
			->add( "created_at", DatePickerType::class, [
				'required' => false,
				"label"    => $this->trans( 'object.created' ),
				'format'   => 'dd.MM.yyyy, HH:mm',
				"disabled" => true
			] )
			->add( "updated_at", DatePickerType::class, [
				'required' => false,
				"label"    => $this->trans( 'object.updated' ),
				'format'   => 'dd.MM.yyyy, HH:mm',
				"disabled" => true
			] )
			->end()
			->end();
	}
	
	public function toString( $object )
	{
		return $object instanceof Page
			? $object->getTitle()
			: 'Статическая страница';
	}
	
	public function configureBatchActions( $actions )
	{
		if ( $this->hasRoute( 'edit' ) && $this->hasAccess( 'edit' ) ) {
			$actions['active']    = array(
				'ask_confirmation' => true,
				'label'            => $this->trans( 'batch.on' )
			);
			$actions['in_active'] = array(
				'ask_confirmation' => true,
				'label'            => $this->trans( 'batch.off' )
			);
		}
		
		return $actions;
	}
	
	public function validate( ErrorElement $errorElement, $object )
	{
		$container   = $this->getConfigurationPool()->getContainer();
		$forbidSlugs = $container->getParameter( 'forbid_slug' );
		if ( $forbidSlugs ) {
			foreach ( $forbidSlugs as $forbidSlug ) {
				if ( $object->getSlug() == $forbidSlug ) {
					$error = 'Неверное значение';
					$errorElement->with( 'slug' )->addViolation( $error )->end();
					break;
				}
			}
		}
	}
	
	public function createQuery( $context = 'list' )
	{
		$query = parent::createQuery( $context );
		
		$query->addSelect( 'tl as translations' );
		$query->innerJoin( $query->getRootAlias() . ".translations", "tl" );
		
		return $query;
	}
}
