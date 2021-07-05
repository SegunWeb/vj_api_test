<?php

namespace App\Handler;

use App\Constants\TypePageConstants;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AuthenticationHandler
 */
class AuthenticationHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
	/**
	 * @var RouterInterface
	 */
	private $router;
	/**
	 * @var Session
	 */
	private $session;
	
	/**
	 * @var TranslatorInterface
	 */
	protected $translator;
	
	protected $manager;
	
	protected $authorizationChecker;
	
	
	/**
	 * AuthenticationHandler constructor.
	 *
	 * @param RouterInterface $router
	 * @param Session $session
	 * @param TranslatorInterface $translator
	 */
	public function __construct( RouterInterface $router, Session $session, TranslatorInterface $translator, AuthorizationChecker $authorizationChecker, EntityManager $manager )
	{
		$this->router               = $router;
		$this->manager              = $manager;
		$this->session              = $session;
		$this->translator           = $translator;
		$this->authorizationChecker = $authorizationChecker;
	}
	
	/**
	 * @param Request $request
	 * @param TokenInterface $token
	 *
	 * @return JsonResponse|RedirectResponse
	 */
	public function onAuthenticationSuccess( Request $request, TokenInterface $token )
	{
		$requestCheckPost = $request->request->all();
		
		$account = $this->manager->getRepository('App\Entity\Page')->getSlugPage( TypePageConstants::USER_ACCOUNT_VALUES, $request->getLocale() );
		
		if ( $this->authorizationChecker->isGranted( 'ROLE_ADMIN' ) ) {
			return new JsonResponse( array( 'success' => true, 'url' => '/'.$account['slug'] ?: '/' , 'email' => $token->getUser()->getEmail(), 'phone' => $token->getUser()->getPhone(), 'fullName' => $token->getUser()->getFullName(), 'city' => $token->getUser()->getCity() ) );
		} elseif ( ! empty( $requestCheckPost ) ) {
			return new JsonResponse( array( 'success' => true, 'url' => '/'.$account['slug'] ?: '/', 'email' => $token->getUser()->getEmail(), 'phone' => $token->getUser()->getPhone(), 'fullName' => $token->getUser()->getFullName(), 'city' => $token->getUser()->getCity() ) );
		} else {
			return new RedirectResponse( '/'.$account['slug'] ?: '/'  );
		}
	}
	
	/**
	 * @param Request $request
	 * @param AuthenticationException $exception
	 *
	 * @return JsonResponse|RedirectResponse
	 */
	public function onAuthenticationFailure( Request $request, AuthenticationException $exception )
	{
		
		$request->setLocale( $request->request->get( '_locale', 'ru' ) );
		
		$result   = array(
			'success'  => false,
			'function' => 'onAuthenticationFailure',
			'error'    => true,
			'message'  => $this->translator->trans( $exception->getMessage(), array(), 'validation', $request->request->get( '_locale', 'ru' ) )
		);
		$response = new Response( json_encode( $result ) );
		$response->headers->set( 'Content-Type', 'application/json' );
		
		return $response;
	}
}