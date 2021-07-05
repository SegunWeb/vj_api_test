<?php

namespace App\Admin;

use App\Constants\ActiveConstants;
use App\Entity\PhrasesCategories;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\TranslationBundle\Filter\TranslationFieldFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PhrasesCategoriesAdmin extends AbstractAdmin
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
	
	protected function configureRoutes( RouteCollection $collection )
	{
		$collection->add( 'move', $this->getRouterIdParameter() . '/move/{position}' );
	}
	
	/**
	 * @param DatagridMapper $datagridMapper
	 */
	protected function configureDatagridFilters( DatagridMapper $datagridMapper ): void
	{
		$datagridMapper
			->add( 'translations.title', null, [ 'label' => $this->trans( 'object.title' ) ] )
			->add( 'active', 'doctrine_orm_choice', [
				'label'       => $this->trans( 'object.activity' ),
				'show_filter' => true
			], ChoiceType::class, [
					'choices'  => ActiveConstants::loadActivityValues(),
					'expanded' => false,
					'multiple' => true
				]
			);
	}
	
	/**
	 * @param ListMapper $listMapper
	 */
	protected function configureListFields( ListMapper $listMapper ): void
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
			->add( 'title', null, array( 'sortable' => false, 'label' => $this->trans( 'object.title' ) ) )
			->add( 'parent' )
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
	protected function configureFormFields( FormMapper $formMapper ): void
	{
		$formMapper
			->with( $this->trans( 'tab.common_info' ) )
			->add( 'title', TextType::class, [ 'label' => $this->trans( 'object.title' ) ] )
			->add( 'parent', EntityType::class, [
				'class'         => PhrasesCategories::class,
				'placeholder'   => 'object.selected',
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'h' )
					          ->orderBy( 'h.lft', 'ASC' );
				},
				'required'      => false,
				'choice_label'  => 'laveled_title',
				'label'         => $this->trans( 'object.parent' )
			] )
			->add( "active", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => ActiveConstants::loadActivityValues()
			] )
			->end();
	}
	
	public function toString( $object )
	{
		return $object instanceof PhrasesCategories
			? $object->getTitle()
			: 'Категория фраз';
	}
	
	public function createQuery( $context = 'list' )
	{
		$query = parent::createQuery( $context );
		
		$query->addSelect( 'tl as translations' );
		$query->innerJoin( $query->getRootAlias() . ".translations", "tl" );
		
		return $query;
	}
	
}