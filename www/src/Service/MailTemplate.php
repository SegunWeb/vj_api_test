<?php

namespace App\Service;

use App\Constants\MailEventConstants;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\EntityManager;

class MailTemplate
{
	public $mailTemplate;
	protected $mailer;
	protected $manager;
	protected $twig;
	protected $container;
	protected $baseUrl;
	
	public function __construct( EntityManager $manager, \Swift_Mailer $mailer, \Twig_Environment $twig, ContainerInterface $container )
	{
		$this->mailTemplate = $manager->getRepository( \App\Entity\MailTemplate::class );
		$this->mailer       = $mailer;
		$this->twig         = $twig;
		$this->container    = $container;
	}
	
	public function sendMailMessages( $event, $values, $object, $toEmail = null, $unsubscribe = false, $hrefText = null, $href = null )
	{
		//Ищем по ивенту информацию об темплейтах письм
		$mailTemplate = $this->mailTemplate->findOneBy( [ 'event' => $event ] );
		
		if ( $mailTemplate ) {
			
			//Если есть template сообщения для администрации, отправляем
			if ( ! empty( $mailTemplate->getBodyMessageAdmin() ) ) {
				
				$body    = $this->prepareBody( $mailTemplate->getBodyMessageAdmin(), $object, $values );
				$subject = $this->prepareBody( $mailTemplate->getSubjectMessageAdmin(), $object, $values );
				
				$adminEmails = explode( ',', str_replace( ' ', '', $mailTemplate->getAdminEmail() ) );
				
				$message = ( new \Swift_Message( strip_tags($subject) ) )
					->setFrom( $mailTemplate->getFromEmail() )
					->setTo( $adminEmails )
					->setBody( $this->renderTemplate( $body, $subject, null, null, null, $unsubscribe ), 'text/html' );
				
				$this->mailer->send( $message );
			}
			
			//Если есть template сообщения для пользователя, отправляем
			if ( ! empty( $mailTemplate->getBodyMessageUsers() ) and ! empty( $toEmail ) ) {
				
				$body    = $this->prepareBody( $mailTemplate->getBodyMessageUsers(), $object, $values );
				$subject = $this->prepareBody( $mailTemplate->getSubjectMessageUsers(), $object, $values );
				
				$message = ( new \Swift_Message( strip_tags($subject) ) )
					->setFrom( $mailTemplate->getFromEmail() )
					->setTo( $toEmail )
					->setBody( $this->renderTemplate( $body, $subject, $hrefText, $href, $toEmail, $unsubscribe ), 'text/html' );
				
				$this->mailer->send( $message );
			}
			
		}
	}
	
	public function sendSmsMessages( TurboSmsService $turboSmsService, $values, $object, $number )
	{
		//Ищем по ивенту информацию об темплейтах письм
		$mailTemplate = $this->mailTemplate->findOneBy( [ 'event' => MailEventConstants::SMS_FULL_VIDEO_READINESS ] );
		
		$number = str_replace(['(', ')', '-'], ['', '', ''], $number);
		
		if ( $mailTemplate ) {
			
			if ( ! empty( $mailTemplate->getBodyMessageUsers() ) ) {
				
				$contentSms = $this->prepareBody( $mailTemplate->getBodyMessageUsers(), $object, $values );
				
				$turboSmsService->send($number, $contentSms);
			}
			
		}
	}
	
	public function prepareBody( $body, $object, $values )
	{
		$accessor = PropertyAccess::createPropertyAccessor();
		
		foreach ( $values as $value ) {
			$body = str_replace( '{' . $value . '}', $accessor->getValue( $object, $value ), $body );
		}
		
		return $body;
	}
	
	public function renderTemplate( $body, $name = null, $button_text = null, $button_href = null, $toEmail = null, $unsubscribe = null )
	{
		return $this->twig->render( 'email/letterTemplate.twig', [
			'body'        => $body,
			'name'        => $name,
			'button_text' => $button_text,
			'button_href' => $button_href,
			'email'       => $toEmail,
			'unsubscribe' => $unsubscribe
		] );
	}
}