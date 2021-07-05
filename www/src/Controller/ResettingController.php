<?php

namespace App\Controller;

use App\Constants\MailEventConstants;
use App\Service\MailTemplate;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Event\GetResponseUserEvent;
use Symfony\Component\HttpFoundation\Response;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller managing the resetting of the password.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class ResettingController extends AbstractController
{
	
	/**
	 * Request reset user password: submit form and send email.
	 *
	 * @param Request $request
	 *
	 * @return Response
	 * @Route("/resseting", name="resseting_password", methods={"POST"})
	 */
	public function sendEmailAction( Request $request, UserManagerInterface $userManager, EventDispatcherInterface $eventDispatcher, TokenGeneratorInterface $tokenGenerator, MailTemplate $template, RouterInterface $router, TranslatorInterface $translator )
	{
		
		$username = $request->request->get( 'username' );
		
		$error = '';
		
		$user = $userManager->findUserByUsernameOrEmail( $username );
		
		if ( ! empty( $user ) ) {
			$event = new GetResponseNullableUserEvent( $user, $request );
			$eventDispatcher->dispatch( FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE, $event );
			
			if ( null !== $event->getResponse() ) {
				return $event->getResponse();
			}
			
			if ( null !== $user && $user->isPasswordRequestNonExpired( 7200 ) !== true ) {
				
				$event = new GetResponseUserEvent( $user, $request );
				$eventDispatcher->dispatch( FOSUserEvents::RESETTING_RESET_REQUEST, $event );
				
				if ( null !== $event->getResponse() ) {
					return $event->getResponse();
				}
				
				if ( null === $user->getConfirmationToken() ) {
					$user->setConfirmationToken( $tokenGenerator->generateToken() );
				}
				
				$event = new GetResponseUserEvent( $user, $request );
				$eventDispatcher->dispatch( FOSUserEvents::RESETTING_SEND_EMAIL_CONFIRM, $event );
				
				if ( null !== $event->getResponse() ) {
					return $event->getResponse();
				}
				
				$url = $router->generate( 'fos_user_resetting_reset', array( 'token' => $user->getConfirmationToken() ), UrlGeneratorInterface::ABSOLUTE_URL );
				
				$unsubscribe = base64_encode($user->getId().'|'.$user->getEmail().'|'.$user->getCreatedAt()->format('d.m.Y'));
				
				$arrayDataSendEmail = array(
					'user_email' => $user->getEmail(),
					'user_name'  => $user->getFullName(),
					'url'        => $url
				);
				
				$template->sendMailMessages(
					MailEventConstants::RESETTING_PASSWORD,
					MailEventConstants::RESETTING_PASSWORD_VALUES,
					(object) $arrayDataSendEmail,
					$user->getEmail(),
					$unsubscribe,
					$translator->trans( 'email.title.resseting', [], 'messages', $request->request->get( '_locale', 'ru' ) ),
					$url
				);
				
				$user->setPasswordRequestedAt( new \DateTime() );
				$userManager->updateUser( $user );
				
				$event = new GetResponseUserEvent( $user, $request );
				$eventDispatcher->dispatch( FOSUserEvents::RESETTING_SEND_EMAIL_COMPLETED, $event );
				
				if ( null !== $event->getResponse() ) {
					return $event->getResponse();
				}
			} else {
				$error = $translator->trans( 'security.password_reset_notification', [], 'messages', $request->request->get( '_locale', 'ru' ) );
			}
		} else {
			$error = $translator->trans( 'security.user_not_found', [], 'messages', $request->request->get( '_locale', 'ru' ) );
		}
		
		return new JsonResponse( array(
			'message' => empty( $error ) ? $translator->trans( 'security.success_send_email_resseting_password', [], 'messages', $request->request->get( '_locale', 'ru' ) ) : $error,
			'code'    => empty( $error ) ? 200 : 400,
		), 200 );
	}
	
	/**
	 * @Route("/resseting/request/success", name="resseting_request_success")
	 */
	public function requestAction(TranslatorInterface $translator)
	{
		$breadcrumbs[] = ['url' => $this->generateUrl('home'), 'title' => $translator->trans('front.home')];
		$breadcrumbs[] = ['url' => $this->generateUrl('resseting_request_success'), 'title' => $translator->trans('front.resseting_success')];
		
		$setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);
		
		return $this->render('page/static_page.html.twig', ['setting' => $setting, 'page' => ['breadcrumbs' => $breadcrumbs, 'title' => $translator->trans('front.resseting'), 'content' => $translator->trans('front.resseting_success')] ]);
	}
	
}
