<?php

namespace App\Helper;

use App\Constants\FeedbackConstants;
use App\Constants\MailEventConstants;
use App\Entity\User;
use App\Service\MailTemplate;
use Doctrine\ORM\EntityManager;
use App\Constants\ActiveConstants;
use App\Application\Sonata\MediaBundle\Entity\Media;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Form
{
	protected $em;
	
	protected $validator;
	
	protected $encoder;
	
	protected $container;
	
	function __construct( EntityManager $em, ValidatorInterface $validator, UserPasswordEncoderInterface $encoder, ContainerInterface $container )
	{
		$this->em        = $em;
		$this->validator = $validator;
		$this->encoder   = $encoder;
		$this->container = $container;
	}
	
	/*
	 * Формирование из многомерного массива в одномерный с групирированными данными
	 */
	public function getFormingFrom( $data )
	{
		
		$result = [];
		//Возвращаются данные общим массивом, переписываем их в струтуризированный массив
		if ( ! empty( $data ) ) {
			foreach ( $data as $key => $v ) {
				$result[ key( $v ) ][] = $v[ key( $v ) ];
			}
		}
		
		return (object) $result;
	}
	
	/*
	 * Добавление отзыва
	 */
	public function addNewReview( $text, User $users, $locale )
	{
		$review = new \App\Entity\Review();
		$review->setUsers( $users );
		$review->setText( $text );
		$review->setActive( ActiveConstants::REVIEW_NOT_PROCESSED );
		$review->setLocale( $locale );
		$review->setUsersCity( $users->getCity() );
		
		$validatorErrors = $this->validator->validate( $review );
		
		if ( count( $validatorErrors ) == 0 ) {
			
			$this->em->persist( $review );
			$this->em->flush();
			
			return true;
		}
		
		return false;
	}
	
	/*
	 * Добавление вопроса
	 */
	public function addNewFeedback( Request $request, User $users = null )
	{
		$feedback = new \App\Entity\Feedback();
		$feedback->setUsers( $users );
		$feedback->setFullName( ! empty( $users ) ? $users->getFullName() : $request->request->get( 'fullName' ) );
		$feedback->setEmail( ! empty( $users ) ? $users->getEmail() : $request->request->get( 'email' ) );
		$feedback->setPhone( ! empty( $users ) ? $users->getPhone() : $request->request->get( 'phone', '' ) );
		$feedback->setText( $request->request->get( 'text', '' ) );
		$feedback->setActive( ActiveConstants::FEEDBACK_NOT_PROCESSED );
		$feedback->setType( FeedbackConstants::QUESTION );
		
		$validatorErrors = $this->validator->validate( $feedback );
		
		if ( count( $validatorErrors ) == 0 ) {
			
			$this->em->persist( $feedback );
			$this->em->flush();
			
			return true;
		}
		
		return false;
	}
	
	/*
	 * Добавление вопроса
	 */
	public function editAccount( Request $request, User $users )
	{
		
		$type  = $request->request->get( 'type', null );
		$value = $request->request->get( 'value', null );
		
		if ( $type == 'fullName' ) {
			
			if ( ! empty( $value ) ) {
				
				$users->setFullName( $value );
				
				$validatorErrors = $this->validator->validate( $users );
				
				if ( count( $validatorErrors ) == 0 ) {
					
					$this->em->flush( $users );
					
					return true;
				}
			}
		}
		
		if ( $type == 'phone' ) {
				
			$users->setPhone( $value );
			
			$validatorErrors = $this->validator->validate( $users );
			
			if ( count( $validatorErrors ) == 0 ) {
				
				$this->em->flush( $users );
				
				return true;
			}
		}
		
		if ( $type == 'email' ) {
			
			if ( ! empty( $value ) ) {
				
				$users->setEmail( $value );
				$users->setEmailCanonical( $value );
				$users->setUsername( $value );
				$users->setUsernameCanonical( $value );
				
				$validatorErrors = $this->validator->validate( $users );
				
				if ( count( $validatorErrors ) == 0 ) {
					
					$this->em->flush( $users );
					
					return true;
				}
			}
		}
		
		if ( $type == 'city' ) {
			
			if ( ! empty( $value ) ) {
				
				$users->setCity( $value );
				
				$validatorErrors = $this->validator->validate( $users );
				
				if ( count( $validatorErrors ) == 0 ) {
					
					$this->em->flush( $users );
					
					return true;
				}
			}
		}
		
		if ( $type == 'password' ) {
			
			$passwordNew = $request->request->get( 'password' );
			
			$passwordOld = $request->request->get( 'passwordOld' );
			
			if ( empty( $passwordNew ) or iconv_strlen( $passwordNew ) < 4 ) {
				return 'password_not_valid';
			}
			
			$passwordValid = $this->encoder->isPasswordValid( $users, $passwordOld );
			
			if ( $passwordValid ) {
				
				$users->setPassword( $this->encoder->encodePassword( $users, $passwordNew ) );
				
				$validatorErrors = $this->validator->validate( $users );
				
				if ( count( $validatorErrors ) == 0 ) {
					
					$this->em->flush( $users );
					
					//Получаем доступ к сервису темплейтов
					$template = $this->container->get( MailTemplate::class );
					
					$unsubscribe = base64_encode($users->getId().'|'.$users->getEmail().'|'.$users->getCreatedAt()->format('d.m.Y'));
					
					//Переделать после того как будут известны поля отправки
					$object = array(
						'user_name' => $users->getFullName(),
						'user_email' => $users->getEmail(),
						'password'  => $passwordNew
					);
					
					$template->sendMailMessages( MailEventConstants::CHANGE_PASSWORD, MailEventConstants::CHANGE_PASSWORD_VALUES, (object) $object, $users->getEmail(), $unsubscribe );
					
					return true;
				} else {
					return 'password_not_valid';
				}
			} else {
				return 'old_password_not_valid';
			}
		}
		
		if ( $request->files->has( 'file' ) ) {
			
			/** @var UploadedFile * */
			$file = $request->files->get( 'file' );
			
			//Загружаем картинку
			$media = new Media();
			$media->setName( $file->getClientOriginalName() );
			$media->setProviderName( base64_encode( mt_rand( 1, 1000 ) . $file->getClientOriginalName() ) );
			$media->setProviderReference( base64_encode( mt_rand( 1, 1000 ) . $file->getClientOriginalName() ) );
			$media->setContext( 'avatar' );
			$media->setProviderName( 'sonata.media.provider.image' );
			$media->setBinaryContent( $file );
			$media->setProviderStatus( 1 );
			
			$this->em->persist( $media );
			$this->em->flush();
			
			$users->setAvatar( $media );
			
			$validatorErrors = $this->validator->validate( $users );
			
			if ( count( $validatorErrors ) == 0 ) {
				
				$this->em->flush( $users );
				
				$provider = $this->container->get( $users->getAvatar()->getProviderName() );
				$url      = $provider->generatePublicUrl( $users->getAvatar(), 'reference' );
				
				return $url;
			}
			
		}
		
		return false;
	}
}