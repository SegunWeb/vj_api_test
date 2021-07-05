<?php

namespace App\Admin;

use App\Constants\ActiveConstants;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserAdmin extends AbstractAdmin
{
	/**
	 * @param DatagridMapper $datagridMapper
	 */
	protected function configureDatagridFilters( DatagridMapper $datagridMapper ): void
	{
		$datagridMapper
			->add( 'email', null, [ 'label' => $this->trans( 'object.email' ) ] )
			->add( 'city', null, [ 'label' => $this->trans( 'object.city' ) ] )
			->add( 'phone', null, [ 'label' => $this->trans( 'object.phone' ) ] )
			->add( 'enabled', null, [ 'label' => $this->trans( 'object.enabled' ) ] )
            ->add('with_open_comments', 'doctrine_orm_callback', array(
                'label' => $this->trans( 'object.subscriptions_label' ),
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if (!$value) {
                        return;
                    }
                    if($value['value'] == 1) {
                        $queryBuilder->leftJoin(sprintf('%s.subscriptions', $alias), 'subscription');
                        $queryBuilder->andWhere('subscription.active = :val');
                        $queryBuilder->setParameter('val', 1);
                        $queryBuilder->andWhere('subscription.expired_at >= :nowDate');
                        $queryBuilder->setParameter('nowDate', new \DateTime('NOW'));
                    } else {
                        $queryBuilder->leftJoin(sprintf('%s.subscriptions', $alias), 'subscription');
                        $queryBuilder->andWhere('subscription.active = :val');
                        $queryBuilder->setParameter('val', 0);
                    }
                    return true;
                },
            ), ChoiceType::class, [
                    'choices'  => [
                        ActiveConstants::ACTIVE_TITLE => ActiveConstants::ACTIVE
                    ],
                    'expanded' => true
                ]
            )
            ->add( 'createdAt', 'doctrine_orm_date_range', array(
                'label'       => $this->trans( 'object.created' )
            ), DateRangePickerType::class,
                array(
                    'field_options_start' => array( 'format' => 'yyyy-MM-dd' ),
                    'field_options_end'   => array( 'format' => 'yyyy-MM-dd' )
                )
            )
            ->add( 'lastLogin', 'doctrine_orm_date_range', array(
                'label'       => $this->trans( 'object.last_login' )
            ), DateRangePickerType::class,
                array(
                    'field_options_start' => array( 'format' => 'yyyy-MM-dd' ),
                    'field_options_end'   => array( 'format' => 'yyyy-MM-dd' )
                )
            )
            ->add('subscriptionExpiredAt', 'doctrine_orm_callback', array(
                'label' => $this->trans( 'front.subscription_active_to' ),
                'callback' => function($queryBuilder, $alias, $field, $value) {

                    if (!$value['value']['start'] && !$value['value']['end']) {
                        return;
                    }

                    $queryBuilder->leftJoin(sprintf('%s.subscriptions', $alias), 'subs');
                    $queryBuilder->andWhere('subs.active = :val');
                    $queryBuilder->setParameter('val', 1);
                    $queryBuilder->andWhere('subs.expired_at >= :nowDate');
                    $queryBuilder->setParameter('nowDate', new \DateTime('NOW'));

                    if( $value['value']['start'] ) {
                        $queryBuilder->andWhere('subs.expired_at >= :start');
                        $queryBuilder->setParameter('start', $value['value']['start'] );
                    }

                    if( $value['value']['end'] ) {
                        $queryBuilder->andWhere('subs.expired_at <= :end');
                        $queryBuilder->setParameter('end', $value['value']['end'] );
                    }
                    return true;
                },
            ), DateRangePickerType::class,
                array(
                    'field_options_start' => array( 'format' => 'yyyy-MM-dd' ),
                    'field_options_end'   => array( 'format' => 'yyyy-MM-dd' )
                )
            )
            ->add( 'video_callback', 'doctrine_orm_callback', [
                'label'       => $this->trans( 'object.video' ),
                'callback' => function($queryBuilder, $alias, $field, $value) {

                    if (!$value) {
                        return;
                    }

                    if($value['value'] == 1) {
                        $queryBuilder->leftJoin(sprintf('%s.orders', $alias), 'orders');
                        $queryBuilder->andWhere('orders.active IN (:val)');
                        $queryBuilder->setParameter('val', [1,2,3]);
                    } else {
                        $em = $queryBuilder->getEntityManager();
                        $sub = $em->createQueryBuilder();
                        $sub->select("1");
                        $sub->from("App\Entity\Order","orders");
                        $sub->Where( $alias . '.id = orders.users');
                        $sub->andWhere('orders.active IN (1,2,3)');
                        $queryBuilder->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->exists($sub->getDQL())));
                    }

                    return true;
                },
            ], ChoiceType::class, [
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
			->add( 'avatar', null, [
				'label'    => $this->trans( 'object.avatar' ),
				'template' => '@SonataMedia/MediaAdmin/list_image.html.twig'
			] )
			->add( 'fullName', null, [ 'label' => $this->trans( 'object.full_name' ) ] )
			->add( 'email', null, [ 'label' => $this->trans( 'object.email' ) ] )
			->add( 'city', null, [ 'label' => $this->trans( 'object.city' ) ] )
			->add( 'phone', null, [ 'label' => $this->trans( 'object.phone' ) ] )
			->add( 'enabled', null, [ 'label' => $this->trans( 'object.enabled' ) ] )
			->add( 'updatedAt', null, [ 'label' => $this->trans( 'object.update_at_profile' ) ] )
			->add( 'lastLogin', null, [ 'label' => $this->trans( 'object.last_login' ) ] )
            ->add( 'createdAt', null, [ 'label' => $this->trans( 'object.created' ) ] )
            ->add( 'getSubscriptionExpiredAt', null, [ 'label' => $this->trans( 'front.subscription_active_to' ) ] )
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
		$this->record_id = $this->request->get($this->getIdParameter());
		if (!empty($this->record_id)) {
			$passwordoptions = false;
		} else {
			$passwordoptions = true;
		}

		$formMapper
			->add( 'fullName', null, [ 'label' => $this->trans( 'object.full_name' ) ] )
			->add( 'email', null, [ 'label' => $this->trans( 'object.email' ) ] )
			->add('plainPassword', $passwordoptions == true ? TextType::class : HiddenType::class, array(
				'label' => $this->trans('object.new_password'),
				'required' => FALSE
			))
			->add( 'city', null, [ 'label' => $this->trans( 'object.city' ) ] )
			->add( 'avatar', ModelListType::class, [
				'label' => $this->trans( 'object.avatar' )
			], [
				'link_parameters' => array( 'context' => 'avatar' )
			] )
			->add( 'phone', null, [ 'label' => $this->trans( 'object.phone' ) ] )
			->add( 'enabled', null, [ 'label' => $this->trans( 'object.enabled' ) ] );

            if($this->getSubject()->getSubscriptions()) {
                $formMapper->add('subscriptions', CollectionType::class, [
                    'label' => $this->trans('object.subscriptions_label'),
                    'btn_add' => false,
                    'type_options' => [
                        'delete' => false,
                        'delete_options' => [
                            'type'         => HiddenType::class,
                            'type_options' => [
                                'mapped'   => false,
                                'required' => false,
                            ]
                        ]
                    ],

                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                ]);
            }
	}
	
}