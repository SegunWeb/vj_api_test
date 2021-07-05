<?php

namespace App\Controller;

use App\Constants\MailEventConstants;
use App\Service\MailTemplate;
use App\Service\RenderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\LoginForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\User;
use App\Form\Type\LoginType;
use App\Form\Type\UpdateProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class SecurityController extends AbstractController
{
	/**
	 * @var AuthenticationUtils
	 */
	private $authenticationUtils;

    protected $template;
	
	public function __construct( AuthenticationUtils $authenticationUtils, MailTemplate $template )
	{
		$this->authenticationUtils = $authenticationUtils;

		$this->template = $template;
	}
	
	/**
	 * @Route("/login", name="admin_login")
	 */
	public function loginAction(): Response
	{
		$form = $this->createForm( LoginForm::class, [
			'email' => $this->authenticationUtils->getLastUsername()
		] );
		
		return $this->render( 'security/login.html.twig', [
			'last_username' => $this->authenticationUtils->getLastUsername(),
			'form'          => $form->createView(),
			'error'         => $this->authenticationUtils->getLastAuthenticationError(),
		] );
	}
    
    /**
     * @Route("/registration", name="registration")
     */
    public function registrationAction(Request $request, RenderService $service, TranslatorInterface $translator): Response
    {
        $success = false; $message = '';
        
        $userEmail = $request->request->get('user_email');
        $userName = $request->request->get('user_name');

        if(!empty($userEmail) and !empty($userName)) {
            $create = $service->createUsers($request);
    
            if (isset($create->user)) {
                $success = true;

                $unsubscribe = base64_encode($create->user->getId().'|'.$create->user->getEmail().'|'.$create->user->getCreatedAt()->format('d.m.Y'));

                $arrayDataSendEmail = array(
                    'user_email' => $create->user->getEmail(),
                    'user_name'  => $create->user->getFullName(),
                    'password'   => $create->password,
                );

                $this->template->sendMailMessages(
                    MailEventConstants::REGISTRATION_USERS,
                    MailEventConstants::REGISTRATION_USERS_VALUES,
                    (object) $arrayDataSendEmail,
                    $create->user->getEmail(),
                    $unsubscribe
                );
            } else {
                if (is_array($create)) {
                    foreach ($create as $item) {
                        $message .= $item['message'];
                    }
                }
            }
        }else{
            $message = $translator->trans('front.field_empty');
        }
        
        return new JsonResponse( array('success' => $success, 'message' => $message ), 200 );
    }
	
	/**
	 * @Route("/logout", name="admin_logout")
	 */
	public function logoutAction(): void
	{
	}
	
	/**
	 * @Route("/authorize/facebook", name="authorize_facebook", methods={"GET"})
	 */
	public function authorizeFacebook( Request $request, TranslatorInterface $translator )
	{
		$setting = $this->getDoctrine()->getRepository('App\Entity\Setting')->find(1);
		$userInfo = null; $result = false; $url = '';
		if ($request->query->has('code')) {
			
			$params = array(
				'client_id'     => $setting->getApiKeyFacebookClientId(),
				'redirect_uri'  => 'https://'.$request->getHttpHost().$request->getBaseUrl().'/authorize/facebook',
				'client_secret' => $setting->getApiKeyFacebookClientSecret(),
				'code'          => $request->query->get('code')
			);
			
			$url = 'https://graph.facebook.com/oauth/access_token';
			$access_token = file_get_contents($url . '?' . http_build_query($params));
			if(!empty($access_token)){
				$tokenInfo = json_decode($access_token, true);
				if (!empty($tokenInfo['access_token'])) {
					$params = array('access_token' => $tokenInfo['access_token']);
					
					$userInfo = json_decode(file_get_contents('https://graph.facebook.com/me' . '?' . urldecode(http_build_query($params))), true);
					
					if (isset($userInfo['id'])) {
                        $url = $this->base64_encoded_image('http://graph.facebook.com/' . $userInfo['id'] . '/picture?width=1200');
						$result = true;
					}
				}
			}
		}
		if($result){
			$content = $translator->trans('front.authorize_facebook_success');
		}else{
			$content = $translator->trans('front.authorize_facebook_error');
		}
		
		return $this->render('page/authorization_social_networks_page.html.twig', [ 'title' => $translator->trans('front.authorize_facebook'), 'content' => $content, 'url' => $url, 'setting' => $setting]);
	}
	
    public function base64_encoded_image($img){
        $imageSize = getimagesize($img);
        $imageData = base64_encode(file_get_contents($img));
        $imageSrc = "data:{$imageSize['mime']};base64,{$imageData}";
        return $imageSrc;
    }
}