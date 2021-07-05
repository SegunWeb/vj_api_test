<?php

namespace App\Admin;

use App\Entity\Blog;
use App\Constants\ActiveConstants;
use App\Entity\BlogCategories;
use Doctrine\ORM\EntityRepository;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Type\DatePickerType;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Validator\ErrorElement;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BlogAdmin extends AbstractAdmin
{
	/**
	 * @param DatagridMapper $datagridMapper
	 */
	protected function configureDatagridFilters( DatagridMapper $datagridMapper )
	{
		$datagridMapper
			->add( 'translations.title', null, [ 'label' => $this->trans( 'object.title' ) ] )
			->add( 'slug', null, [ 'label' => $this->trans( 'object.slug_page' ) ] );
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
			->add( 'title', TextType::class, array( 'label' => $this->trans( 'object.title' ) ) )
			->add( 'slug', null, [ 'label' => $this->trans( 'object.slug_page' ) ] )
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
		$formMapper
			->tab( $this->trans( 'tab.common_info' ) )
			->with( $this->trans( 'tab.common_info' ) )
			->add( 'title', TextType::class, [ 'label' => $this->trans( 'object.title' ) ] )
			->add( 'slug', TextType::class, [ 'label' => $this->trans( 'object.slug_page' ), 'required' => false ] )
			->add( 'description', TextareaType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.description' )
			] )
			->add( 'content', CKEditorType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.content' )
			] )
			->add( 'images', ModelListType::class, [
				'label'    => $this->trans( 'object.images_blog' ),
				'required' => false
			], array( 'link_parameters' => array( 'context' => 'blog' ) ) )
			->add( 'category', EntityType::class, [
				'class'         => BlogCategories::class,
				'placeholder'   => $this->trans( 'object.selected_blog_catigories' ),
				'required'      => true,
				'multiple'      => true,
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'v' )
					          ->orderBy( 'v.id', 'ASC' );
				},
				'label'         => $this->trans( 'object.categories_blog' )
			] )
			->add( "active", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => ActiveConstants::loadActivityValues()
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
			->add( "created_at", DatePickerType::class, [
				'required' => false,
				"label"    => $this->trans( 'object.created' ),
				'format'   => 'dd.MM.yyyy, HH:mm',
				"disabled" => true
			] )
			->add( "updated_at", DatePickerType::class, [
				'required' => false,
				"label"    => $this->trans( 'object.updated' ),
				'format'   => 'dd.MM.yyyy, HH:mm',
				"disabled" => true
			] )
			->end()
			->end();
	}
	
	public function toString( $object )
	{
		return $object instanceof Blog
			? $object->getTitle()
			: 'Блог';
	}
	
	public function configureBatchActions( $actions )
	{
		if ( $this->hasRoute( 'edit' ) && $this->hasAccess( 'edit' ) ) {
			$actions['active']    = array(
				'ask_confirmation' => true,
				'label'            => $this->trans( 'batch.on' )
			);
			$actions['in_active'] = array(
				'ask_confirmation' => true,
				'label'            => $this->trans( 'batch.off' )
			);
		}
		
		return $actions;
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
