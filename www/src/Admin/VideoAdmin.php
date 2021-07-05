<?php

namespace App\Admin;

use App\Constants\ActiveConstants;
use App\Constants\LocaleConstants;
use App\Constants\SexConstants;
use App\Constants\VideoTypeConstants;
use App\Entity\Holidays;
use App\Entity\Review;
use App\Entity\Video;
use App\Entity\VideoCategories;
use App\Form\Type\TagsTextType;
use App\Service\RenderService;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\RedirectResponse;

class VideoAdmin extends AbstractAdmin
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
	protected function configureDatagridFilters( DatagridMapper $datagridMapper )
	{
		$datagridMapper
            ->add( 'title', null, [ 'label' => $this->trans( 'object.title' ) ] )
            ->add( 'slug', null, [ 'label' => $this->trans( 'object.slug' ) ] )
			->add( 'sex', 'doctrine_orm_choice', [ 'label'       => $this->trans( 'object.sex' ) ],
                ChoiceType::class, [
					'choices'  => SexConstants::loadSexValuesAll(),
					'expanded' => false,
					'multiple' => true
				]
			)
            ->add( 'category', null,
                [
                    'label' => $this->trans( 'object.categories' ),
                ],
                null,
                [
                    'multiple' => true
                ]
            )
            ->add( 'priceUah', 'doctrine_orm_number',
                [
                    'label' => $this->trans( 'UAH' ),
                ]
            )
            ->add( 'priceRub', 'doctrine_orm_number',
                [
                    'label' => $this->trans( 'RUB' ),
                ]
            )
            ->add( 'priceEur', 'doctrine_orm_number',
                [
                    'label' => $this->trans( 'EUR' ),
                ]
            )
            ->add( 'priceUsd', 'doctrine_orm_number',
                [
                    'label' => $this->trans( 'USD' ),
                ]
            );
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
					'delete' => array()
				)
			) )
			->addIdentifier( 'title', null, array( 'sortable' => false, 'label' => $this->trans( 'object.title' ) ) )
			->add( 'event', null, [ 'label' => $this->trans( 'object.holidays' ) ] )
			->add( 'sex', 'choice', [
				'sortable' => false,
				'editable' => true,
				'label'    => $this->trans( 'object.sex' ),
				'choices'  => array_flip( SexConstants::loadSexValuesAll() )
			] )
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
			->add( 'category', null, [ 'label' => $this->trans( 'object.categories' ) ] )
			->add( 'locale', 'choice', [
				'sortable' => false,
				'editable' => true,
				'label'    => $this->trans( 'object.locale_version' ),
				'choices'  => array_flip( LocaleConstants::loadLocaleValues() )
			] )
            ->add( 'createdAt', null, [ 'sortable' => true, 'label' => $this->trans( 'object.createAt_to_video' ) ] )
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
	protected function configureFormFields( FormMapper $formMapper )
	{

        $disabled = $this->getDisabledAttrForCurrenciesForForm();

		$formMapper
			->tab( $this->trans( 'tab.common_info' ) )
			->with( $this->trans( 'tab.common_info' ), [ 'class' => 'col-md-9' ] )
            ->add( 'title', null, [ 'label' => $this->trans( 'object.title' ) ] )
            ->add( 'slug', null, [ 'label' => $this->trans( 'object.slug' ) ] )
			->add( 'description', CKEditorType::class, [ 'label'    => $this->trans( 'object.description' ), 'required' => false ])
            ->add( 'preloader', ModelListType::class, [
                'label' => 'Preloader',
                'required' => false
            ], [
                'link_parameters' => array( 'context' => 'preloader' )
            ] )
            ->add( 'images', ModelListType::class, [
                'label' => $this->trans( 'object.images' )
            ], [
                'link_parameters' => array( 'context' => 'video_cover' )
            ] )
            ->add( 'banner', ModelListType::class, [
                'label' => $this->trans( 'object.banner' )
            ], [
                'link_parameters' => array( 'context' => 'video_cover' )
            ] )
            ->add( 'trailer', ModelListType::class, [
                'label' => $this->trans( 'object.trailer' )
            ], [
                'link_parameters' => array( 'context' => 'trailer' )
            ] )
            ->add( 'trailerImage', ModelListType::class, [
                'label' => $this->trans( 'object.trailer_image' )
            ], [
                'link_parameters' => array( 'context' => 'video_cover' )
            ] )
			->add( 'congratulationExample', ModelListType::class, [
				'label' => $this->trans( 'object.congratulation_example' )
			], [
				'link_parameters' => array( 'context' => 'congratulation_example' )
			] )
			->add( 'project', ModelListType::class, [
				'label' => $this->trans( 'object.project' )
			], [
				'link_parameters' => array( 'context' => 'project' )
			] )
			->add( 'category', EntityType::class, [
				'class'         => VideoCategories::class,
				'placeholder'   => $this->trans( 'object.selected_categories' ),
				'required'      => true,
				'multiple'      => true,
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'v' )
					          ->orderBy( 'v.id', 'ASC' );
				},
				'choice_label'  => 'laveled_title',
				'label'         => $this->trans( 'object.categories' )
			] )
			->add( 'event', EntityType::class, [
				'class'         => Holidays::class,
				'placeholder'   => $this->trans( 'object.selected_holidays' ),
				'required'      => true,
				'multiple'      => true,
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'v' )
					          ->orderBy( 'v.id', 'ASC' );
				},
				'label'         => $this->trans( 'object.holidays' )
			] )
			->add( "sex", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.sex' ),
				'choices'  => SexConstants::loadSexValuesAll()
			] )
			->add( 'numberPersons', null, [ 'label' => $this->trans( 'object.number_of_persons' ) ] )
			->add( 'ageFrom', HiddenType::class, [ 'label' => $this->trans( 'object.age_from' ) ] )
			->add( 'ageUp', HiddenType::class, [ 'label' => $this->trans( 'object.age_up' ) ] )
			->add( "variation", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.variation' ),
				'choices'  => array_flip( VideoTypeConstants::loadVideoTypeValues() )
			] )
			->add( 'tagsText', TagsTextType::class, [ 'required' => false, 'label' => $this->trans( 'object.tags' ) ] )
			->add( "active", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => ActiveConstants::loadActivityValues()
			] )
			->add( 'pageContentSeo', CKEditorType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.page_content_seo' )
			] )
            ->add( 'review', EntityType::class, [
                'class'         => Review::class,
                'placeholder'   => $this->trans( 'object.selected_review' ),
                'required'      => false,
                'multiple'      => true,
                'query_builder' => function ( EntityRepository $er ) {
                    return $er->createQueryBuilder( 'v' )
                              ->orderBy( 'v.id', 'ASC' );
                },
                'label'         => $this->trans( 'object.review' )
            ] )
            ->add( 'position', NumberType::class, [
                'label'    => $this->trans( 'object.position' ),
                'required' => false
            ] )
            ->add( 'positionCategory', NumberType::class, [
                'label'    => $this->trans( 'object.position_category' ),
                'required' => false
            ] )
            ->add( 'skipDemo', CheckboxType::class, [
                'label'    => $this->trans( 'object.skip_demo' ),
                'required' => false
            ] )
			->add( 'locale', ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.locale_version' ),
				'choices'  => LocaleConstants::loadLocaleValues()
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
			->add( 'hidePrice', CheckboxType::class, [
				'label'    => $this->trans( 'object.hide_price' ),
				'required' => false
			] )
			->end()
			->end()
			->tab( $this->trans( 'tab.placeholder' ) )
			->with( $this->trans( 'tab.placeholder' ) )
			->add( 'placeholder', \Sonata\Form\Type\CollectionType::class, [
				'by_reference' => false,
				'label'        => $this->trans( 'object.video_placeholder_all' )
			], [
					'edit'   => 'inline',
					'inline' => 'natural'
				]
			)
			->end()
			->end();
	}
	
	public function preUpdate( $args )
	{
		//Получаем доступ к контейнеру
		$container = $this->getConfigurationPool()->getContainer();
		
		//Получаем старые данные сущности
		$oldVideoData = $container->get( 'doctrine.orm.entity_manager' )->getUnitOfWork()->getOriginalEntityData( $args );
		
		if ( empty( $oldVideoData['project'] ) ) {
			if ( ! empty( $args->getProject() ) ) {
				$args = $this->unzipArhive( $args->getProject(), $args->getId(), $args, false );
			}
		} elseif ( ! empty( $args->getProject() ) and $oldVideoData['project']->getId() != $args->getProject()->getId() ) {
			$args = $this->unzipArhive( $args->getProject(), $args->getId(), $args, false );
		}
		
		//Проверка на кнопку, если нажали "начать рендериг" запускаем
		if ( $this->request->request->has( 'btn_render' ) ) {
			
			$renderService = $container->get( 'app.service.render' );
			$object       = $renderService->testRender( $this->getRequest(), $args, $this->getConfigurationPool()->getContainer()->get( 'security.token_storage' )->getToken()->getUser() );
			
			$redirection = new RedirectResponse( $this->getConfigurationPool()->getContainer()->get( 'router' )->generate( 'video_render_processing', [ 'id' => $object->order, 'slug' => $object->video ] ) );
			$redirection->send();
			
		}

        $this->calculateEmptyPriceFields( $args, $oldVideoData );
		
		return $args;
	}

	public function prePersist( $args )
    {
        $this->calculateEmptyPriceFields( $args );
        return $args;
    }
	
	public function postPersist( $args )
	{
		
		if ( ! empty( $args->getProject() ) && ! empty( $args->getProject()->getProviderReference() ) ) {
			
			$args = $this->unzipArhive( $args->getProject(), $args->getId(), $args, true );
			
		}
		
		return $args;
	}
	
	public function unzipArhive( $media, $projectId, $args, $persist, $demo = false )
	{
		
		$provider   = $this->getConfigurationPool()->getContainer()->get( $media->getProviderName() );
		$urlArchive = $provider->generatePublicUrl( $media, 'reference' );
		
		$file = $this->request->getUriForPath( $urlArchive );
		
		$post = array(
			'file'    => $file,
			'project' => $projectId,
            'version'  => $this->getConfigurationPool()->getContainer()->getParameter( 'app_version' )
		);
		
		if ( $demo ) {
			$post['demo'] = true;
		}
		
		$url = $this->getConfigurationPool()->getContainer()->getParameter( 'win_server' ) . "/api/controller.php";
		
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
		curl_exec( $ch );
		curl_close( $ch );
		
		//Получаем доступ к контейнеру
		$container = $this->getConfigurationPool()->getContainer();
		
		//После передачи win-серверу данных архива, дополнительно проходимся по массиву и получаем имена файлов
		$renderService = $container->get( RenderService::class );
		$args          = $renderService->renderFileHistory( $args, $urlArchive, $persist );
		
		return $args;
	}
	
	public function toString( $object )
	{
		return $object instanceof Video
			? $object->getTitle()
			: 'Видео';
	}
	
	public function getNewInstance()
	{
		$instance = parent::getNewInstance();
		$instance->setDescription( json_encode( array() ) );
		
		return $instance;
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

    public function getColumns()
    {
        return $this->getDatagrid()->getColumns()->getElements();
    }

}
