<?php

namespace App\Admin;

use App\Constants\ActiveConstants;
use App\Form\Type\GenerateRandomStringType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PromoCodeAdmin extends AbstractAdmin
{
	/**
	 * @param DatagridMapper $datagridMapper
	 */
	protected function configureDatagridFilters( DatagridMapper $datagridMapper ): void
	{
		$datagridMapper
			->add( 'promoCode', null, [ 'label' => $this->trans( 'object.promo_code' ) ] )
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
			->addIdentifier( 'promoCode', null, array(
				'sortable' => false,
				'label'    => $this->trans( 'object.promo_code' )
			) )
			->add( 'numberOfUses', null, array(
				'sortable' => false,
				'label'    => $this->trans( 'object.number_of_uses' )
			) )
			->add( 'dateEndOfAction', null, array(
				'sortable' => false,
				'label'    => $this->trans( 'object.date_end_of_action' )
			) )
            ->add( 'discount', null, array(
                'sortable' => false,
                'label'    => $this->trans( 'object.discount' )
            ) )
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
			->add( 'promoCode', GenerateRandomStringType::class, [ 'label' => $this->trans( 'object.promo_code' ) ] )
			->add( 'numberOfUses', NumberType::class, array( 'label' => $this->trans( 'object.number_of_uses' ) ) )
			->add( 'dateEndOfAction', DatePickerType::class, [
				'label'  => $this->trans( 'object.date_end_of_action' ),
				'format' => 'dd.MM.yyyy, HH:mm'
			] )
            ->add( 'discount', NumberType::class, [
                'label' => $this->trans( 'object.discount' ),
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