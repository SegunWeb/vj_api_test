<?php

namespace App\Admin;

use App\Constants\ActiveConstants;
use App\Entity\Video;
use Sonata\Form\Validator\ErrorElement;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\TranslationBundle\Filter\TranslationFieldFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class VideoCategoriesAdmin extends AbstractAdmin
{
	/**
	 * @param DatagridMapper $datagridMapper
	 */
	protected function configureDatagridFilters( DatagridMapper $datagridMapper ): void
	{
		$datagridMapper
			->add( 'translations.title', null, [ 'label' => $this->trans( 'object.title' ) ] )
		;
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
			->add( 'slug', null, [ 'label' => $this->trans( 'object.slug_page' ) ] )
			->add( 'active', 'choice', [
				'sortable' => false,
				'editable' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => array_flip( ActiveConstants::loadActivityValues() )
			] )
            ->add( "sortOrder", null, [
                'label'    => $this->trans( 'object.position_category' ),
            ] );;
	}
	
	/**
	 * @param FormMapper $formMapper
	 */
	protected function configureFormFields( FormMapper $formMapper ): void
	{
		$formMapper
			->tab( $this->trans( 'tab.common_info' ) )
			->with( $this->trans( 'tab.common_info' ) )
			->add( 'title', TextType::class, [ 'label' => $this->trans( 'object.title' ) ] )
			->add( 'titleAbbreviated', TextType::class, [ 'label' => $this->trans( 'object.title_abbreviated' ) ] )
			->add( 'slug', TextType::class, [ 'label' => $this->trans( 'object.slug_page' ), 'required' => false ] )
			->add( 'images', ModelListType::class, [
				'label'    => $this->trans( 'object.images_blog' ),
				'required' => false
			], array( 'link_parameters' => array( 'context' => 'category_video' ) ) )
			->add( 'content', CKEditorType::class, [
				'required' => false,
				'empty_data' => '',
				'label'    => $this->trans( 'object.content' )
			] )
            ->add( 'subTitle', CKEditorType::class, [
				'required' => false,
				'empty_data' => '',
				'label'    => $this->trans( 'object.subtitle' )
			] )
			->add( 'videoTop', EntityType::class, [
				'class'         => Video::class,
				'placeholder'   => $this->trans( 'object.selected_top_video' ),
				'required'      => false,
				'multiple'      => true,
				'disabled'      => !empty($this->getSubject()->getId()) ? false : true,
				'query_builder' => function ( EntityRepository $er ) {
					if(!empty($this->getSubject()->getId())){
						return $er->createQueryBuilder( 'v' )
						          ->leftJoin('v.category', 'vc')
						          ->where('vc.id = '.$this->getSubject()->getId() ?: 0)
						          ->orderBy( 'v.id', 'ASC' );
					}else{
						return $er->createQueryBuilder( 'v' )
						          ->orderBy( 'v.id', 'ASC' );
					}
				},
				'label'         => $this->trans( 'object.video_top' )
			] )
			->add( "active", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => ActiveConstants::loadActivityValues()
			] )
            ->add( "sortOrder", NumberType::class, [
                'required' => true,
                'label'    => $this->trans( 'object.position_category' ),
            ] )
			->end()
			->end()
			->tab( $this->trans( 'tab.meta' ) )
			->with( $this->trans( 'tab.meta' ), [ 'class' => 'col-md-8' ] )
			->add( 'metaTitle', TextType::class, [
				'label'    => $this->trans( 'object.meta.title' ),
				'required' => false
			] )
			->add( 'metaKeywords', TextType::class, [
				'label'    => $this->trans( 'object.meta.keywords' ),
				'required' => false
			] )
			->add( 'metaDescription', TextType::class, [
				'label'    => $this->trans( 'object.meta.description' ),
				'required' => false
			] )
			->add( 'metaCanonical', TextType::class, [
				'label'    => $this->trans( 'object.meta.canonical' ),
				'required' => false
			] )
			->end()
			->with( $this->trans( 'tab.image' ), [ 'class' => 'col-md-4' ] )
			->add( 'meta_image', ModelListType::class, [
				'label'    => $this->trans( 'object.meta.image' ),
				'required' => false
			], [ 'link_parameters' => array( 'context' => 'meta' ) ] )
			->end()
			->end();
	}
	
	public function validate( ErrorElement $errorElement, $object )
	{
		$container   = $this->getConfigurationPool()->getContainer();
		$forbidSlugs = $container->getParameter( 'forbid_slug' );
		if ( $forbidSlugs ) {
			foreach ( $forbidSlugs as $forbidSlug ) {
				if ( $object->getSlug() == $forbidSlug ) {
					$error = 'Неверное значение';
					$errorElement->with( 'slug' )->addViolation( $error )->end();
					break;
				}
			}
		}
	}
	
	public function createQuery( $context = 'list' )
	{
		$query = parent::createQuery( $context );
		
		$query->addSelect( 'tl as translations' );
		$query->innerJoin( $query->getRootAlias() . ".translations", "tl" );
		
		return $query;
	}
	
}