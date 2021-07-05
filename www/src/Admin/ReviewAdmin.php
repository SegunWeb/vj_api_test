<?php

namespace App\Admin;

use App\Constants\LocaleConstants;
use App\Entity\User;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Form\FormMapper;
use App\Constants\ActiveConstants;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\Form\Type\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ReviewAdmin extends AbstractAdmin
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
		//Устаанавливаем текущего пользователя как отвечающего на отзыв
		$this->getSubject()->setAuthorCommentReply( $this->getConfigurationPool()->getContainer()->get( 'security.token_storage' )->getToken()->getUser() );
		
		$formMapper
			->add( 'users', EntityType::class, [
				'class'    => User::class,
				'required' => false,
				'label'    => $this->trans( 'object.users' )
			] )
			->add( 'usersCity', TextType::class, array(
				'label' => $this->trans( 'object.users_city' )
			) )
			->add( 'text', TextareaType::class, [
				'label' => $this->trans( 'object.text_review' )
			] )
			->add( 'authorCommentReply', EntityType::class, [
				'class'    => User::class,
				'required' => false,
				"disabled" => false,
				'label'    => $this->trans( 'object.author_text_reply' )
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
				"disabled" => true
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
		return 'Отзывы';
	}
}