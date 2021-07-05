<?php

namespace App\Admin;

use App\Constants\ActiveConstants;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SubscriptionTypesAdmin extends AbstractAdmin
{
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
            ->add( 'title', null, [ 'label' => $this->trans( 'object.title' ) ] );
	}
	
	/**
	 * @param ListMapper $listMapper
	 */
	protected function configureListFields( ListMapper $listMapper ): void
	{
		$listMapper
            ->addIdentifier( 'title', null, array( 'sortable' => false, 'label' => $this->trans( 'object.title' ) ) )
            ->add( 'priceUah', 'integer',
                [
                    'label' => $this->trans( 'UAH' ),
                    'editable' => true,
                ]
            )
            ->add( 'priceRub', 'integer',
                [
                    'label' => $this->trans( 'RUB' ),
                    'editable' => true,
                ]
            )
            ->add( 'priceEur', 'integer',
                [
                    'label' => $this->trans( 'EUR' ),
                    'editable' => true,
                ]
            )
            ->add( 'priceUsd', 'integer',
                [
                    'label' => $this->trans( 'USD' ),
                    'editable' => true,
                ]
            )
            ->add( 'createdAt', null, [ 'label' => $this->trans( 'object.created' ) ] )
            ->add( 'active', 'choice', [
                'sortable' => false,
                'editable' => true,
                'label'    => $this->trans( 'object.activity' ),
                'choices'  => array_flip( ActiveConstants::loadActivityValues() )
            ] )
			->add( '_action', null, array(
				'label'   => $this->trans( 'list.actions' ),
				'actions' => array(
					'edit'   => array(),
					'delete' => array()
				)
			) );
	}
	
	/**
	 * @param FormMapper $formMapper
	 */
	protected function configureFormFields( FormMapper $formMapper ): void
	{

        $disabled = $this->getDisabledAttrForCurrenciesForForm();


        $formMapper
            ->tab( $this->trans( 'tab.common_info' ) )
            ->with( $this->trans( 'tab.common_info' ), [ 'class' => 'col-md-9' ] )
            ->add( 'title', null, [ 'label' => $this->trans( 'object.title' ) ] )
            ->add( 'description', CKEditorType::class, [ 'label'    => $this->trans( 'object.description' ), 'required' => false ])
            ->add( 'period', NumberType::class, [
                'label'    => $this->trans( 'object.period' ),
            ] )
            ->add( "active", ChoiceType::class, [
                'required' => true,
                'label'    => $this->trans( 'object.activity' ),
                'choices'  => ActiveConstants::loadActivityValues()
            ] )
            ->end()
            ->with( $this->trans( 'tab.common_info_price' ), [ 'class' => 'col-md-3' ] )
            ->add( 'priceUah', NumberType::class, [
                'label'    => $this->trans( 'object.price_uah' ),
                'required' => false,
                'disabled' => $disabled['UAH']
            ] )
            ->add( 'priceRub', NumberType::class, [
                'label'    => $this->trans( 'object.price_rub' ),
                'required' => false,
                'disabled' => $disabled['RUB']
            ] )
            ->add( 'priceEur', NumberType::class, [
                'label'    => $this->trans( 'object.price_eur' ),
                'required' => false,
                'disabled' => $disabled['EUR']
            ] )
            ->add( 'priceUsd', NumberType::class, [
                'label'    => $this->trans( 'object.price_usd' ),
                'required' => false,
                'disabled' => $disabled['USD']
            ] )
            ->add( 'discount', NumberType::class, [
                'label'    => $this->trans( 'object.discount' ),
                'required' => false
            ] )

            ->add( 'frontPriceUah', NumberType::class, [
                'label'    => $this->trans( 'object.price_uah' ) . ' (For Front)',
                'required' => false,
                //'disabled' => $disabled['UAH']
            ] )
            ->add( 'frontPriceRub', NumberType::class, [
                'label'    => $this->trans( 'object.price_rub' ) . ' (For Front)',
                'required' => false,
                //'disabled' => $disabled['RUB']
            ] )
            ->add( 'frontPriceEur', NumberType::class, [
                'label'    => $this->trans( 'object.price_eur' ) . ' (For Front)',
                'required' => false,
                //'disabled' => $disabled['EUR']
            ] )
            ->add( 'frontPriceUsd', NumberType::class, [
                'label'    => $this->trans( 'object.price_usd' ) . ' (For Front)',
                'required' => false,
                //'disabled' => $disabled['USD']
            ] )
            ->end()
            ->end();
	}

    public function prePersist( $args )
    {
        $this->calculateEmptyPriceFields( $args );
        return $args;
    }

    public function preUpdate( $args )
    {

        $container = $this->getConfigurationPool()->getContainer();
        $oldVideoData = $container->get( 'doctrine.orm.entity_manager' )->getUnitOfWork()->getOriginalEntityData( $args );

        $this->calculateEmptyPriceFields( $args, $oldVideoData );

        return $args;
    }

    // Заполнение пустых полей цен, если заполнена дефолтная валюта
    public function calculateEmptyPriceFields( $args, $oldVideoData = null )
    {
        $DM =  $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();
        $currencies = $DM->getRepository('App\Entity\Currency')->findBy( ['active' => 1] );
        $defaultCurrency = array_filter($currencies, function( $currency ) {
            return ( $currency->getDefaultCurrency() == true );
        });
        $defaultCurrency = array_shift($defaultCurrency);
        $defaultCurrencyGetterMethod = 'getPrice' . ucfirst(strtolower($defaultCurrency->getCodeISO()));
        $defaultCurrencyArrayKey = 'price' . ucfirst(strtolower($defaultCurrency->getCodeISO()));

        if( !empty( $args->{$defaultCurrencyGetterMethod}() ) ) {
            foreach ( $currencies as $currency ) {
                if( $currency->getCodeISO() === $defaultCurrency->getCodeISO() ) {
                    continue;
                }
                $getter = 'getPrice' . ucfirst(strtolower($currency->getCodeISO()));
                $setter = 'setPrice' . ucfirst(strtolower($currency->getCodeISO()));
                if( empty( $args->{$getter}() )
                    || (!empty($oldVideoData) && ($oldVideoData[$defaultCurrencyArrayKey] !== $args->{$defaultCurrencyGetterMethod}())) ) {
                    $price = $args->{$defaultCurrencyGetterMethod}() * $currency->getCourse();
                    $formattedPrice = number_format((float)$price, 2, '.', '');
                    $args->{$setter}( $formattedPrice );
                }
                unset( $getter, $setter, $price, $formattedPrice );
            }
        }
    }


    public function getDisabledAttrForCurrenciesForForm()
    {
        $isNew = ( empty( $this->getSubject()->getId() ) ) ? true : false;

        $DM =  $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();
        $defaultCurrency = $DM->getRepository('App\Entity\Currency')->findOneBy( ['defaultCurrency' => 1] );

        $disabled = [
            'UAH' => false,
            'USD' => false,
            'RUB' => false,
            'EUR' => false
        ];
        if ( $isNew ) {
            foreach ( $disabled as $key => &$value ) {
                if( $key !== $defaultCurrency->getCodeISO() ) {
                    $value = true;
                }
            }
        } else {
            $defaultCurrencyGetterMethod = 'getPrice' . ucfirst(strtolower($defaultCurrency->getCodeISO()));
            if( empty( $this->getSubject()->{$defaultCurrencyGetterMethod}() ) ) {
                foreach ( $disabled as $key => &$value ) {
                    $getter = 'getPrice' . ucfirst(strtolower($key));
                    if( $key !== $defaultCurrency->getCodeISO() && empty($this->getSubject()->{$getter}()) ) {
                        $value = true;
                    }
                }
            }
        }

        return $disabled;
    }

	
}