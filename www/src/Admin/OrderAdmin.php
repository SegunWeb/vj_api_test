<?php

namespace App\Admin;

use App\Constants\ActiveConstants;
use App\Form\Type\ParagraphType;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class OrderAdmin extends AbstractAdmin
{
	
	public function createQuery( $context = 'list' )
	{
		$query = parent::createQuery( $context );
		$query->addOrderBy( $query->getRootAlias() . '.id', 'DESC' );
		
		return $query;
	}
	
	protected function configureRoutes( RouteCollection $collection )
	{
		$collection
			->remove( 'create' );
	}
	
	/**
	 * @param DatagridMapper $datagridMapper
	 */
	protected function configureDatagridFilters( DatagridMapper $datagridMapper ): void
	{
		$datagridMapper
			->add( 'video', null, [ 'label' => $this->trans( 'object.video' ), 'show_filter' => true ] )
			->add( 'users', null, [ 'label' => $this->trans( 'object.users' ), 'show_filter' => true ], null, ['multiple' => true] )
			->add( 'createdAt', 'doctrine_orm_date_range', array(
				'show_filter' => true,
				'label'       => $this->trans( 'object.created_order' )
			), DateRangePickerType::class,
				array(
					'field_options_start' => array( 'format' => 'yyyy-MM-dd' ),
					'field_options_end'   => array( 'format' => 'yyyy-MM-dd' )
				)
			)
			->add( 'active', 'doctrine_orm_choice', [
				'label'       => $this->trans( 'object.activity' ),
				'show_filter' => true
			], ChoiceType::class, [
					'choices'  => ActiveConstants::statusOrder(),
					'expanded' => true,
					'multiple' => true
				]
			)
            ->add('free_video', 'doctrine_orm_callback', array(
                'label' => $this->trans( 'object.free_video' ),
                'show_filter' => true,
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if (!$value) {
                        return;
                    }

                    $queryBuilder->andWhere(sprintf('%s.price is null', $alias));
                    $queryBuilder->andWhere(sprintf('%s.active = :val', $alias));
                    if($value['value'] == 1) {
                        $queryBuilder->setParameter('val', 1);
                    } else {
                        $queryBuilder->setParameter('val', 0);
                    }
                    return true;
                },
            ), ChoiceType::class, [
                    'choices'  => [
                        ActiveConstants::ORDER_NOT_PAID => 0,
                        ActiveConstants::ORDER_PAID => 1,
                    ],
                    'expanded' => true
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
			->add( 'video', null, array( 'sortable' => true, 'label' => $this->trans( 'object.video' ) ) )
			->add( 'users', null, array( 'sortable' => true, 'label' => $this->trans( 'object.users' ) ) )
			/*->add( 'priceRub', null, array( 'sortable' => true, 'label' => "RUB" ) )
			->add( 'priceUah', null, array( 'sortable' => true, 'label' => "UAH" ) )
			->add( 'priceEur', null, array( 'sortable' => true, 'label' => "EUR" ) )
			->add( 'priceUsd', null, array( 'sortable' => true, 'label' => "USD" ) )*/
			->add( 'price', null, [ 'sortable' => true, 'label' => $this->trans( 'object.order_price_list' ) ] )
            ->add( 'priceCurrency', null, [
                'sortable' => true,
                'label'    => $this->trans( 'object.order_price_currency' )
            ] )

            ->add( 'promoCode', null, [
                'label'    => $this->trans( 'object.promo_сode' )
            ] )
			->add( 'active', 'choice', [
				'sortable' => true,
				'editable' => true,
				'label'    => $this->trans( 'object.order_activity' ),
				'choices'  => array_flip( ActiveConstants::statusOrder() )
			] )
			->add( 'createdAt', null, array(
				'sortable' => true,
				'label'    => $this->trans( 'object.order_created_at' )
			) );
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
			->with( $this->trans( 'tab.common_info' ), [ 'class' => 'col-xs-3' ] )
			->add( 'video', ParagraphType::class, [
				"disabled" => true,
				'label'    => $this->trans( 'object.video' ),
				'required' => false
			] )
			->add( 'users', ParagraphType::class, [
				"disabled" => true,
				'label'    => $this->trans( 'object.users' ),
				'required' => false
			] )
			->add( "active", ChoiceType::class, [
				'label'    => $this->trans( 'object.order_activity' ),
				'choices'  => ActiveConstants::statusOrder(),
				'required' => false
			] )
			->add( 'createdAt', ParagraphType::class, [
				'label'    => $this->trans( 'object.order_created_at' ),
				"disabled" => true,
				'required' => false
			] )
			->end()
			->with( $this->trans( 'tab.common_info_users' ), [ 'class' => 'col-xs-3' ] )
			->add( 'fullName', ParagraphType::class, [
				'label'    => $this->trans( 'object.order_full_name' ),
				"disabled" => true,
				'required' => false
			] )
			->add( 'email', ParagraphType::class, [
				'label'    => $this->trans( 'object.email' ),
				"disabled" => true,
				'required' => false
			] )
			->add( 'phone', ParagraphType::class, [
				'label'    => $this->trans( 'object.phone' ),
				"disabled" => true,
				'required' => false
			] )
			->add( 'city', ParagraphType::class, [
				'label'    => $this->trans( 'object.city' ),
				"disabled" => true,
				'required' => false
			] )
			->end()
			->with( $this->trans( 'tab.common_info_price' ), [ 'class' => 'col-xs-3' ] )
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
				'label'    => $this->trans( 'object.payment_method' ),
				"disabled" => true,
				'required' => false
			] )
			->end()
			->with( $this->trans( 'tab.common_info_other' ), [ 'class' => 'col-xs-3' ] )
			->add( 'childSex', ParagraphType::class, [
				'label'    => $this->trans( 'object.sex' ),
				"disabled" => true,
				'required' => false
			] )
			->add( 'firstName', ParagraphType::class, [
				'label'    => $this->trans( 'object.first_name' ),
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
			->end()
			->with( $this->trans( 'tab.render_list' ) )
			->add( 'render', \Sonata\Form\Type\CollectionType::class, [
				'by_reference' => false,
				'label'        => $this->trans( 'object.render_list' ),
				'btn_add'      => false,
				'type_options' => [
					'delete' => false,
				]
			], [
				'edit'   => 'inline',
				'inline' => 'table'
			] )
			->end();
	}
	
	public function preUpdate( $args )
	{
		//Получаем доступ к контейнеру
		$container = $this->getConfigurationPool()->getContainer();
		
		//Проверка на кнопку, если нажали "начать рендериг" запускаем
		if ( $this->request->request->has( 'btn_update' ) ) {
			
			$renderService = $container->get( 'app.service.render' );
			$renderService->reloadRender( $this->getRequest(), $args );
			
		}
	}

    public function getUsersCount()
    {
        $datagrid = $this->getDatagrid();

        $usersArray = [];
        foreach ($datagrid->getResults() as $result) {
            $usersArray[] = $result->getUsers()->getUsername();
        }
        $usersArrayUniqueCount = count(array_unique($usersArray));

        $result = ( $usersArrayUniqueCount > 0 )
            ? $usersArrayUniqueCount
            : false;

        return $result;
    }

    public function getTotalPrices()
    {
        $datagrid = $this->getDatagrid();

        $prices = [
            'RUB' => 0,
            'UAH' => 0,
            'EUR' => 0,
            'USD' => 0
        ];

        foreach ( $datagrid->getResults() as $result ) {

            if( !empty( $result->getPrice() ) ) {
                switch ($result->getPriceCurrency()) {
                    case ('Гривня'):
                        $prices['UAH'] += (int)$result->getPrice();
                        break;
                    case ('Доллар'):
                        $prices['USD'] += (int)$result->getPrice();
                        break;
                    case ('Рубль'):
                        $prices['RUB'] += (int)$result->getPrice();
                        break;
                    case ('Евро'):
                        $prices['EUR'] += (int)$result->getPrice();
                        break;
                }
            }
        }

        return $prices;
    }
}