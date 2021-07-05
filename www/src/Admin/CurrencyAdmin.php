<?php

namespace App\Admin;

use App\Constants\ActiveConstants;
use App\Entity\Country;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class CurrencyAdmin extends AbstractAdmin
{
	protected function configureRoutes( RouteCollection $collection )
	{
		$collection->remove( 'create' );
	}
	
	/**
	 * @param DatagridMapper $datagridMapper
	 */
	protected function configureDatagridFilters( DatagridMapper $datagridMapper ): void
	{
		$datagridMapper
			->add( 'name', null, [ 'label' => $this->trans( 'object.currency_name' ) ] )
			->add( 'abbreviation', null, [ 'label' => $this->trans( 'object.currency_abbreviation' ) ] )
			->add( 'sing', null, [ 'label' => $this->trans( 'object.currency_sing' ) ] )
			->add( 'codeISO', null, [ 'label' => $this->trans( 'object.currency_code_iso' ) ] )
			->add( 'course', null, [ 'label' => $this->trans( 'object.currency_course' ) ] )
			->add( 'defaultCurrency', null, [ 'label' => $this->trans( 'object.currency_default' ) ] );
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
			->add( 'name', null, array( 'sortable' => false, 'label' => $this->trans( 'object.currency_name' ) ) )
			->add( 'abbreviation', null, array(
				'sortable' => false,
				'label'    => $this->trans( 'object.currency_abbreviation' )
			) )
			->add( 'codeISO', null, array(
				'sortable' => false,
				'label'    => $this->trans( 'object.currency_code_iso' )
			) )
			->add( 'sing', null, array( 'sortable' => false, 'label' => $this->trans( 'object.currency_sing' ) ) )
			->add( 'course', null, array( 'sortable' => false, 'label' => $this->trans( 'object.currency_course' ) ) )
			->add( 'country', null, array( 'label' => $this->trans( 'object.currency_country' ) ) )
			->add( 'defaultCurrency', null, array(
				'sortable' => false,
				'label'    => $this->trans( 'object.currency_default' )
			) );
	}
	
	/**
	 * @param FormMapper $formMapper
	 */
	protected function configureFormFields( FormMapper $formMapper ): void
	{
		$formMapper
			->with( $this->trans( 'tab.common_info' ) )
			->add( 'name', TextType::class, [ 'label' => $this->trans( 'object.currency_name' ) ] )
			->add( 'abbreviation', TextType::class, [ 'label' => $this->trans( 'object.currency_abbreviation' ) ] )
			->add( 'codeISO', TextType::class, [ 'label' => $this->trans( 'object.currency_code_iso') ] )
			->add( 'sing', TextType::class, [ 'label' => $this->trans( 'object.currency_sing' ) ] )
			->add( 'course', TextType::class, [ 'label' => $this->trans( 'object.currency_course' ) ] )
			->add( 'country', EntityType::class, [
				'class'        => Country::class,
				'placeholder'  => 'object.selected_currency_country',
				'multiple'     => true,
				'choice_label' => 'laveled_title',
				'label'        => $this->trans( 'object.currency_country' )
			] )
			->add( 'defaultCurrency', CheckboxType::class, [
				'label'    => $this->trans( 'object.currency_default' ),
				'required' => false
			] )
			->add( "active", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => ActiveConstants::loadActivityValues()
			] )
			->end();
	}
}