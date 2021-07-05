<?php

namespace App\Admin;

use App\Constants\ActiveConstants;
use App\Constants\LocaleConstants;
use App\Constants\SexConstants;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\TranslationBundle\Filter\TranslationFieldFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FirstNameAdmin extends AbstractAdmin
{
	/**
	 * @param DatagridMapper $datagridMapper
	 */
	protected function configureDatagridFilters( DatagridMapper $datagridMapper ): void
	{
		$datagridMapper
			->add( 'title', null, array( 'label' => $this->trans( 'object.first_name_person' ) ) )
			->add( 'sex', 'doctrine_orm_choice', [
				'label'       => $this->trans( 'object.sex' ),
				'show_filter' => true
			], ChoiceType::class, [
					'choices'  => SexConstants::loadSexValues(),
					'expanded' => false,
					'multiple' => true
				]
			)
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
			->add('id', null, array( 'sortable' => true) )
			->add('title', null, array( 'sortable' => true, 'label' => $this->trans( 'object.first_name_person' ) ) )
			->add('sex', 'choice', [
				'sortable' => false,
				'editable' => true,
				'label'    => $this->trans( 'object.sex' ),
				'choices'  => array_flip( SexConstants::loadSexValues() )
			] )
			->add( 'locale', 'choice', [
				'sortable' => false,
				'editable' => true,
				'label'    => $this->trans( 'object.locale_version' ),
				'choices'  => array_flip( LocaleConstants::loadLocaleValues() )
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
	protected function configureFormFields( FormMapper $formMapper ): void
	{
		$formMapper
			->with( $this->trans( 'tab.common_info' ) )
			->add( 'title', TextType::class, [ 'label' => $this->trans( 'object.first_name_person' ) ] )
			->add( "sex", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.sex' ),
				'choices'  => SexConstants::loadSexValues()
			] )
			->add( "active", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => ActiveConstants::loadActivityValues()
			] )
			->add( 'locale', ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.locale_version' ),
				'choices'  => LocaleConstants::loadLocaleValues()
			] )
			->end()
			->end();
	}
}