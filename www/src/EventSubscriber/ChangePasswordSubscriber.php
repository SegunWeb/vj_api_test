<?php

namespace App\EventSubscriber;

use App\Service\MailTemplate;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use App\Constants\MailEventConstants;
use \Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChangePasswordSubscriber implements EventSubscriberInterface
{
	protected $container;
	
	public function __construct( ContainerInterface $container )
	{
		$this->container = $container;
	}
	
	public function onResettingResetSuccess( FormEvent $event ): void
	{
		
		$user = $event->getForm()->getData();
		
		//Получаем доступ к сервису темплейтов
		$template = $this->container->get( MailTemplate::class );
		
		$plainPassword = $event->getRequest()->request->get( 'fos_user_resetting_form' );
		
		$unsubscribe = base64_encode( $user->getId() . '|' . $user->getEmail() . '|' . $user->getCreatedAt()->format( 'd.m.Y' ) );
		
		//Переделать после того как будут известны поля отправки
		$object = array(
			'user_name'   => $user->getFullName(),
			'user_email'  => $user->getEmail(),
			'password'    => $plainPassword['plainPassword']['first']
		);
		
		$template->sendMailMessages( MailEventConstants::CHANGE_PASSWORD, MailEventConstants::CHANGE_PASSWORD_VALUES, (object) $object, $user->getEmail(), $unsubscribe );
	}
	
	public static function getSubscribedEvents(): array
	{
		return [
			FOSUserEvents::RESETTING_RESET_SUCCESS => 'onResettingResetSuccess'
		];
	}
}