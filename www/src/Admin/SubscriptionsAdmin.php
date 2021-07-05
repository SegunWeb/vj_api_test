<?php

namespace App\Admin;

use App\Constants\ActiveConstants;
use App\Form\Type\ParagraphType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SubscriptionsAdmin extends AbstractAdmin
{

    public function createQuery( $context = 'list' )
    {
        $query = parent::createQuery( $context );
        $query->addOrderBy( $query->getRootAlias() . '.id', 'DESC' );

        return $query;
    }

    protected function configureBatchActions($actions)
    {
        $actions['not_active'] = [
            'ask_confirmation' => true,
            'label' => 'label.disabled_video'
        ];
        $actions['active'] = [
            'ask_confirmation' => true,
            'label' => 'label.enabled_video'
        ];

        return parent::configureBatchActions($actions);
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters( DatagridMapper $datagridMapper ): void
    {
        $datagridMapper
            ->add( 'user', null, [
                'label' => $this->trans( 'object.users' ),
                //'show_filter' => true,
                'expanded' => false,
            ], null, [
                'multiple' => true
            ] )
            ->add( 'createdAt', 'doctrine_orm_date_range', [
                'expanded' => false,
                'label'       => $this->trans( 'object.created' )
            ], DateRangePickerType::class, [
                    'field_options_start' => array( 'format' => 'yyyy-MM-dd' ),
                    'field_options_end'   => array( 'format' => 'yyyy-MM-dd' )
                ]
            )
            ->add( 'activated_at', 'doctrine_orm_date_range', [
                'expanded' => false,
                'label'       => $this->trans( 'object.activated' )
            ], DateRangePickerType::class, [
                    'field_options_start' => array( 'format' => 'yyyy-MM-dd' ),
                    'field_options_end'   => array( 'format' => 'yyyy-MM-dd' )
                ]
            )
            ->add( 'expired_at', 'doctrine_orm_date_range', [
                'expanded' => false,
                'label'       => $this->trans( 'object.expired' )
            ], DateRangePickerType::class, [
                    'field_options_start' => array( 'format' => 'yyyy-MM-dd' ),
                    'field_options_end'   => array( 'format' => 'yyyy-MM-dd' )
                ]
            )
            ->add( 'active', 'doctrine_orm_choice', [
                'label'       => $this->trans( 'object.activity' ),
                'expanded' => false
            ], ChoiceType::class, [
                    'choices'  => ActiveConstants::loadActivityValues(),
                    'expanded' => true,
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
            ->add( '_action', null, [
                'label'   => $this->trans( 'list.actions' ),
                'actions' => [
                    'edit'   => array(),
                    'delete' => array()
                ]
            ] )
            ->addIdentifier( 'id', null, [
                'sortable' => false,
                'label' => $this->trans( 'ID' )
            ] )
            ->add( 'user', null, [
                'label' => $this->trans( 'object.users' ),
            ] )
            ->add( 'subscriptionType', null, [
                'label' => $this->trans( 'object.subscription_type' ),
            ] )
            ->add( 'createdAt', null, [
                'label' => $this->trans( 'object.created' ),
                'sortable' => false,
            ] )
            ->add( 'activatedAtString', null, [
                'label' => $this->trans( 'object.activated' )
            ] )
            ->add( 'expiredAtString', null, [
                'label' => $this->trans( 'object.expired' )
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
        if(!empty($this->getSubject()->getPrice())){
            $resCurrentCurrency = ( $this->getSubject()->getCurrencyDefault() / $this->getSubject()->getPrice() );
        }else{
            $resCurrentCurrency = 1;
        }

        $formMapper
            ->with( $this->trans( 'tab.common_info' ), [ 'class' => 'col-md-9' ] )
            ->add( 'subscriptionType', null, [ 'label' => $this->trans( 'object.subscription_type' ) ] )
            ->add( 'user', null, [
                'label'    => $this->trans( 'object.users' ),
            ] )
            ->add( "active", ChoiceType::class, [
                'required' => true,
                'label'    => $this->trans( 'object.activity' ),
                'choices'  => ActiveConstants::loadActivityValues()
            ] )
            ->add( 'activatedAt',  DatePickerType::class, [
                'required' => false,
                'label'  => $this->trans( 'object.activated' ),
                'format' => 'dd.MM.yyyy, HH:mm'
            ] )
            ->add( 'expiredAt',  DatePickerType::class, [
                'required' => false,
                'label'  => $this->trans( 'object.expired' ),
                'format' => 'dd.MM.yyyy, HH:mm'
            ] )
            ->end()
            ->with( $this->trans( 'tab.common_info_price' ), [ 'class' => 'col-md-3' ] )
            ->add( 'price', ParagraphType::class, [
                'label'    => $this->trans( 'object.order_price' ),
                "disabled" => true,
                'required' => false
            ] )
            ->add( 'priceCurrency', ParagraphType::class, [
                'label'    => $this->trans( 'object.order_price_currency' ),
                "disabled" => true,
                'required' => false
            ] )
            ->add( 'currencyDefault', ParagraphType::class, [
                'label'    => $this->trans( 'object.order_currency_default' ),
                'help'     => $this->trans( 'object.on_course' ) . ' $' . $resCurrentCurrency,
                "disabled" => true,
                'required' => false
            ] )
            ->add( 'paymentMethod', ParagraphType::class, [
                'label'    => $this->trans( 'object.subscription_payment_method' ),
                "disabled" => true,
                'required' => false
            ] )
            ->add( 'promoCode', ParagraphType::class, [
                'label'    => $this->trans( 'object.promo_code' ),
                "disabled" => true,
                'required' => false
            ] )
            ->add( 'promoCodeDiscount', ParagraphType::class, [
                'label'    => $this->trans( 'object.discount' ),
                "disabled" => true,
                'required' => false
            ] )
            ->end();

	}

}