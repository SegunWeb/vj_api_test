<?php

namespace App\Admin;

use App\Constants\FeedbackConstants;
use App\Constants\MailEventConstants;
use App\Entity\User;
use App\Entity\Feedback;
use App\Service\MailTemplate;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use App\Constants\ActiveConstants;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\Filter\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class FeedbackAdmin extends AbstractAdmin
{
	
	protected $datagridValues = [
		'_sort_order' => 'DESC',
		'_sort_by'    => 'createdAt',
	];
	
	protected function configureRoutes( RouteCollection $collection )
	{
		$collection->remove( 'create' );
	}
	
	protected function configureDatagridFilters( DatagridMapper $datagridMapper )
	{
		$datagridMapper
			->add( 'email', null, [ 'label' => $this->trans( 'object.email' ) ] )
			->add( 'phone', null, [ 'label' => $this->trans( 'object.phone' ) ] );
	}
	
	protected function configureFormFields( FormMapper $formMapper )
	{
		
		$formMapper
			->add( 'users', EntityType::class, [
				'class'    => User::class,
				'required' => true,
				'disabled' => true,
				'label'    => $this->trans( 'object.users' )
			] )
			->add( "type", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.type_feedback' ),
				'choices'  => FeedbackConstants::loadFeedbackValues()
			] )
			->add( 'email', null, [ 'required' => false, 'label' => $this->trans( 'object.email' ) ] )
			->add( 'phone', null, [ 'required' => false, 'label' => $this->trans( 'object.phone' ), 'empty_data' => '' ] )
			->add( 'text', CKEditorType::class, [ 'label' => $this->trans( 'object.text_message' ) ] )
			->add( 'reply', CKEditorType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.text_reply_message' )
			] )
			->add( "active", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => ActiveConstants::loadActivityFeedbackValues()
			] );
	}
	
	protected function configureListFields( ListMapper $listMapper )
	{
		$listMapper
			->add( 'email', null, [ 'label' => $this->trans( 'object.email' ) ] )
			->add( 'phone', null, [ 'label' => $this->trans( 'object.phone' ) ] )
			->add( 'text', null, [ 'label' => $this->trans( 'object.text_message' ) ] )
			->add( 'type', 'choice', [
				'editable' => false,
				'label'    => $this->trans( 'object.type_feedback' ),
				'choices'  => array_flip( FeedbackConstants::loadFeedbackValues() )
			] )
            ->add( 'createdAt', null, [ 'sortable' => true, 'label' => $this->trans( 'object.createAt_to_video' ) ] )
			->add( 'active', 'choice', [
				'sortable' => false,
				'editable' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => array_flip( ActiveConstants::loadActivityFeedbackValues() )
			] )
			->add( '_action', null, [
				'label'   => $this->trans( 'list.actions' ),
				'actions' => [
					'edit'   => [],
					'delete' => []
				]
			] );
	}
	
	/*
	 * При обновлении проверяем есть ли текст для отправки ответа
	 */
	public function preUpdate( $args )
	{
		//Получаем доступ к контейнеру
		$container = $this->getConfigurationPool()->getContainer();
		
		//Получаем старые данные сущности
		$oldCompanyData = $container->get( 'doctrine.orm.entity_manager' )->getUnitOfWork()->getOriginalEntityData( $args );
		
		//Проверяем точно ли перевели обращение в обработанные с другого статуса
		if ( $args->getActive() == 1 and ( $args->getActive() != $oldCompanyData['active'] ) ) {
			
			//Получаем доступ к сервису темплейтов
			$template = $container->get( MailTemplate::class );
			
			if(!empty($args->getUsers())) {
                $unsubscribe = base64_encode($args->getUsers()->getId() . '|' . $args->getUsers()->getEmail() . '|' . $args->getUsers()->getCreatedAt()->format('d.m.Y'));
            }else{
                $unsubscribe = '';
            }
			//Переделать после того как будут известны поля отправки
			//Доработать отправку
			$object = array(
				'user_name'  => $args->getFullName(),
				'user_email' => $args->getEmail(),
				'text'       => $args->getReply()
			);
			
			$template->sendMailMessages( MailEventConstants::FEEDBACK, MailEventConstants::FEEDBACK_VALUES, (object) $object, $args->getEmail(), $unsubscribe );
			
		}
	}
	
	public function toString( $object )
	{
		return $object instanceof Feedback
			? $object->getEmail()
			: 'Сообщение';
	}
	
}