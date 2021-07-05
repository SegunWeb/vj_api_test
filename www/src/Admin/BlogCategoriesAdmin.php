<?php

namespace App\Admin;

use App\Constants\ActiveConstants;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\TranslationBundle\Filter\TranslationFieldFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BlogCategoriesAdmin extends AbstractAdmin
{
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
					'delete' => array()
				)
			) )
			->addIdentifier( 'title', null, array( 'sortable' => false, 'label' => $this->trans( 'object.title' ) ) )
			->add( 'tl.active', 'choice', [
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
			->add( "active", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => ActiveConstants::loadActivityValues()
			] )
			->end();
	}
	
	public function createQuery( $context = 'list' )
	{
		$query = parent::createQuery( $context );
		
		$query->addSelect( 'tl as translations' );
		$query->innerJoin( $query->getRootAlias() . ".translations", "tl" );
		
		return $query;
	}
	
}