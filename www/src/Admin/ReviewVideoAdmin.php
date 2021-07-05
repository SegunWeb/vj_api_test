<?php

namespace App\Admin;

use App\Constants\LocaleConstants;
use App\Entity\User;
use App\Constants\ActiveConstants;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\Form\Type\DatePickerType;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReviewVideoAdmin extends AbstractAdmin
{
	
	public function createQuery( $context = 'list' )
	{
		$query = parent::createQuery( $context );
		$query->addOrderBy( $query->getRootAlias() . '.active', 'ASC' );
		$query->addOrderBy( $query->getRootAlias() . '.id', 'DESC' );
		
		return $query;
	}
	
	protected function configureDatagridFilters( DatagridMapper $datagridMapper )
	{
		$datagridMapper
			->add( 'users', null, array(
				'label' => $this->trans( 'object.users' )
			) );
	}
	
	protected function configureListFields( ListMapper $listMapper )
	{
		$listMapper
			->add( 'video', null, [
				'label'    => $this->trans( 'object.preview_video' ),
				'template' => '@SonataMedia/MediaAdmin/list_image.html.twig'
			] )
			->add( 'title', null, array(
				'label' => $this->trans( 'object.review_video_title' )
			) )
			->add( 'users', EntityType::class, [
				'class'    => User::class,
				'required' => false,
				"disabled" => true,
				'label'    => $this->trans( 'object.users' )
			] )
			->add( 'usersCity', null, array(
				'label' => $this->trans( 'object.users_city' )
			) )
			->add( 'active', 'choice', [
				'sortable' => true,
				'editable' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => array_flip( ActiveConstants::loadActivityReviewValues() )
			] )
			->add( 'locale', 'choice', [
				'sortable' => true,
				'editable' => true,
				'label'    => $this->trans( 'object.locale_version' ),
				'choices'  => array_flip(LocaleConstants::loadLocaleValues())
			] )
			->add( '_action', null, [
				'label'   => 'Действия',
				'actions' => [
					'edit'   => [],
					'delete' => []
				]
			] );
	}
	
	protected function configureFormFields( FormMapper $formMapper )
	{
		$formMapper
			->add( 'title', TextType::class, array(
				'label' => $this->trans( 'object.review_video_title' )
			) )
			->add( 'description', TextareaType::class, array(
				'label' => $this->trans( 'object.review_video_description' )
			) )
			->add( 'users', EntityType::class, [
				'class'    => User::class,
				'required' => true,
				'label'    => $this->trans( 'object.users' )
			] )
			->add( 'usersCity', TextType::class, array(
				'label' => $this->trans( 'object.users_city' )
			) )
			->add( 'video', ModelListType::class, [
				'label' => $this->trans( 'object.video_review' )
			], [
				'link_parameters' => array( 'context' => 'video_review' )
			] )
			->add( 'images', ModelListType::class, [
				'label' => $this->trans( 'object.images' ),
				'help'  => 'Если не задать фоновое изображение, будет использоваться превью из видео'
			], [
				'link_parameters' => array( 'context' => 'video_cover' )
			] )
			->add( 'commentReply', TextareaType::class, [
				'label'    => $this->trans( 'object.text_reply' ),
				'required' => false
			] )
			->add( 'active', ChoiceType::class, [
				'label'   => $this->trans( 'object.activity' ),
				'choices' => ActiveConstants::loadActivityReviewValues()
			] )
			->add( 'locale', ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.locale_version' ),
				'choices'  => LocaleConstants::loadLocaleValues()
			] )
			->add( "createdAt", DatePickerType::class, [
				'required' => false,
				"label"    => $this->trans( 'object.created' ),
				'format'   => 'dd.MM.yyyy, HH:mm',
				"disabled" => false
			] )
			->add( "publishAt", DatePickerType::class, [
				'required' => false,
				"label"    => $this->trans( 'object.published' ),
				'format'   => 'dd.MM.yyyy, HH:mm',
				"disabled" => false
			] );
	}
	
	public function toString( $object )
	{
		return 'Видео отзывы';
	}
}