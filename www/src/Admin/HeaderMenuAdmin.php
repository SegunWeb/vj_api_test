<?php

namespace App\Admin;

use App\Entity\HeaderMenu;
use App\Entity\Page;
use App\Entity\VideoCategories;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Sonata\TranslationBundle\Filter\TranslationFieldFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityRepository;
use App\Constants\MenuTypeConstants;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Constants\ActiveConstants;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class HeaderMenuAdmin extends AbstractAdmin
{
	
	protected $datagridValues = [
		'_page'       => 1,
		'_sort_order' => 'ASC',
		'_sort_by'    => 'position',
	];
	
	public function buildDatagrid()
	{
		$this->persistFilters = true;
		parent::buildDatagrid();
	}
	
	protected function configureDatagridFilters( DatagridMapper $datagridMapper )
	{
		$datagridMapper
			->add( 'translations.title', null, [ 'label' => $this->trans( 'object.title' ) ] );;
	}
	
	protected function configureFormFields( FormMapper $formMapper )
	{
		
		$formMapper
			->add( 'title', TextType::class, [ 'label' => $this->trans( 'object.title' ) ] )
			->add( 'parent', EntityType::class, [
				'class'         => HeaderMenu::class,
				'placeholder'   => 'object.selected',
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'h' )
					          ->orderBy( 'h.lft', 'ASC' );
				},
				'required'      => false,
				'choice_label'  => 'laveled_title',
				'label'         => $this->trans( 'object.parent' )
			] )
			->add( 'typeMenuItem', ChoiceFieldMaskType::class, [
				'label'       => $this->trans( 'object.type_menu_item' ),
				'required'    => true,
				'choices'     => array_flip( MenuTypeConstants::loadMenuValues() ),
				'map'         => [
					MenuTypeConstants::LINK        => [ 'link' ],
					MenuTypeConstants::STATIC_PAGE => [ 'static_page_id' ],
					MenuTypeConstants::CATEGORY_VIDEO => [ 'staticVideoCategoryId' ]
				],
				'placeholder' => $this->trans( 'object.choice_type_menu_item' )
			] )
			->add( 'static_page_id', EntityType::class, [
				'placeholder'   => $this->trans( 'object.choice_static_page' ),
				'class'         => Page::class,
				'choice_label'  => 'laveled_title',
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'd' )
					          ->orderBy( 'd.id', 'ASC' );
				},
				'label'         => $this->trans( 'object.static_page' ),
				'required'      => false,
			] )
			->add( 'staticVideoCategoryId', EntityType::class, [
				'placeholder'   => $this->trans( 'object.choice_video_category_page' ),
				'class'         => VideoCategories::class,
				'choice_label'  => 'laveled_title',
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'c' )
					          ->orderBy( 'c.id', 'ASC' );
				},
				'label'         => $this->trans( 'object.video_category' ),
				'required'      => false,
			] )
			->add( 'link', TextType::class, [ 'label' => $this->trans( 'object.link' ), 'required' => false ] )
			->add( "active", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => ActiveConstants::loadActivityValues()
			] );
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
					'delete' => array(),
					'move'   => [
						'template' => '@PixSortableBehavior/Default/_sort.html.twig'
					],
				)
			) )
			->addIdentifier( 'title', TextType::class, array(
				'sortable' => false,
				'label'    => $this->trans( 'object.title' )
			) )
			->add( 'static_page_id', EntityType::class, [
				'placeholder'   => $this->trans( 'object.choice_static_page' ),
				'class'         => Page::class,
				'choice_label'  => 'laveled_title',
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'd' )
					          ->orderBy( 'd.id', 'ASC' );
				},
				'label'         => $this->trans( 'object.static_page' ),
				'required'      => false,
			] )
			->add( 'staticVideoCategoryId', EntityType::class, [
				'placeholder'   => $this->trans( 'object.choice_video_category_page' ),
				'class'         => VideoCategories::class,
				'choice_label'  => 'laveled_title',
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'vc' )
					          ->orderBy( 'vc.id', 'ASC' );
				},
				'label'         => $this->trans( 'object.video_category' ),
				'required'      => false,
			] )
			->add( 'link', TextType::class, [ 'label' => $this->trans( 'object.link' ), 'required' => false ] )
			->add( 'active', 'choice', [
				'sortable' => false,
				'editable' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => array_flip( ActiveConstants::loadActivityValues() )
			] );
	}
	
	protected function configureRoutes( RouteCollection $collection )
	{
		$collection->add( 'move', $this->getRouterIdParameter() . '/move/{position}' );
	}
	
	
	public function toString( $object )
	{
		return $object instanceof HeaderMenu
			? $object->getTitle()
			: 'Меню хедера';
	}
	
	public function createQuery( $context = 'list' )
	{
		$query = parent::createQuery( $context );
		
		$query->addSelect( 'tl as translations' );
		$query->innerJoin( $query->getRootAlias() . ".translations", "tl" );
		
		return $query;
	}
}