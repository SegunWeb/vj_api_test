<?php

namespace App\Service;

use App\Constants\ActiveConstants;
use App\Entity\RelOrderFirstName;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\Video;
use GeoIp2\Database\Reader;
use App\Entity\VideoRender;
use Psr\Log\LoggerInterface;
use App\Constants\SexConstants;
use App\Entity\VideoRenderFile;
use Doctrine\ORM\EntityManager;
use App\Constants\VideoConstants;
use App\Constants\MailEventConstants;
use App\Entity\VideoRenderPlaceholder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouterInterface;
use \Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Model\UserManagerInterface;
use App\Entity\VideoRenderImageManyPlaceholder;
use Doctrine\Common\Collections\Expr\Comparison;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use App\Application\Sonata\MediaBundle\Entity\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Contracts\Translation\TranslatorInterface;

class RenderService
{
	protected $manager;
	
	protected $container;
	
	protected $router;
	
	protected $userManager;
	
	protected $tokenGenerator;
	
	protected $validator;
	
	protected $template;

	protected $logger;
	
	protected $translator;
	
	public function __construct( EntityManager $manager, ContainerInterface $container, RouterInterface $router, TokenGeneratorInterface $tokenGenerator, UserManagerInterface $user_manager, ValidatorInterface $validator, MailTemplate $template, LoggerInterface $logger, TranslatorInterface $translator )
	{
		$this->router         = $router;
		$this->manager        = $manager;
		$this->template       = $template;
		$this->container      = $container;
		$this->validator      = $validator;
		$this->userManager    = $user_manager;
		$this->tokenGenerator = $tokenGenerator;
		$this->logger         = $logger;
		$this->translator     = $translator;
	}
	
	/*
	 * Первый этап при процессе создания видео.
	 * В этом процессе создается пользователь и заказ, к которому после будет идти прикрепление всех данных
	 */
	public function preRender( Request $request, Video $video, $user ){
		
		$userNew = false;
		
		//Если пользователь гость, то создаем нового пользователя
		if ( empty( $user ) ) {
            
            //------------------------------------------------ ЧАСТЬ ДЛЯ УДАЛЕНИЯ ---------------------------------------------------------------------------------
            $userFind = $this->manager->getRepository('App\Entity\User')->findOneBy(['email' => $request->request->get( 'user_email', '' )]);
            if(!empty($userFind)){
                
                if($userFind->hasRole('ROLE_SUPER_ADMIN')) {
                    $errors = [];
                    $errors[] = [
                        'field'   => 'email',
                        'message' => $this->translator->trans('front.sorry_mail_is_busy')
                    ];
                    return $errors;
                }else {
                    //Handle getting or creating the user entity likely with a posted form
                    // The third parameter "main" can change according to the name of your firewall in security.yml
                    $token = new UsernamePasswordToken($userFind, null, 'main', $userFind->getRoles());
                    $this->container->get('security.token_storage')->setToken($token);
    
                    // If the firewall name is not main, then the set value would be instead:
                    // $this->get('session')->set('_security_XXXFIREWALLNAMEXXX', serialize($token));
                    $this->container->get('session')->set('_security_main', serialize($token));
    
                    // Fire the login event manually
                    $event = new InteractiveLoginEvent($request, $token);
                    $this->container->get("event_dispatcher")->dispatch("security.interactive_login", $event);
    
                    $user = $userFind;
                }
            }else {
            //------------------------------------------------ ЧАСТЬ ДЛЯ УДАЛЕНИЯ ---------------------------------------------------------------------------------
                $userData = $this->createUsers($request);
    
                if (isset($userData->user)) {
                    $userNew = true;
                    $user    = $userData->user;
                } else {
                    return $userData;
                }
            //------------------------------------------------ ЧАСТЬ ДЛЯ УДАЛЕНИЯ ---------------------------------------------------------------------------------
            }
            //------------------------------------------------ ЧАСТЬ ДЛЯ УДАЛЕНИЯ ---------------------------------------------------------------------------------
		}
		
		$currency = null;
		
		//Если у пользователя определена страна, то определяем курс валюты привязаный к стране (если их несколько то берем лишь первую)
		if(!empty($user->getCountry())){
			if($user->getCountry()->getCurrency()->isEmpty() == false){
				$currency = $user->getCountry()->getCurrency()->first();
				$price = $this->getPriceVideo($currency->getCodeISO(), $video);
			}
		}
		
		if(empty($currency)){
				$currency = $this->manager->getRepository( 'App\Entity\Currency' )->findOneBy( [ 'defaultCurrency' => 1 ] );
				$price    = $this->getPriceVideo( $currency->getCodeISO(), $video );
		}
		
		$newOrder = new Order();
		$newOrder->setUsers( $user );
		$newOrder->setVideo( $video );
		$newOrder->setVideoTitle( $video->getTitle() );
		$newOrder->setActive( 0 );
		$newOrder->setPrice( !empty($price) ? $price : $video->getPriceUsd() );
		$newOrder->setPriceCurrency( $currency->getName() );
		$newOrder->setCurrencyDefault( $video->getPriceUsd() * $currency->getCourse() );
		$newOrder->setFullName( $request->request->get( 'user_name' ) );
		$newOrder->setPhone( $request->request->get( 'user_phone' ) );
		$newOrder->setEmail( $request->request->get( 'user_email' ) );
		$newOrder->setCity( $request->request->get( 'user_city' ) );
		
		$this->manager->persist( $newOrder );
		$this->manager->flush();
		
		//Если пользователь создавался, отправляем сообщение о регистрации и с ссылкой на просмотр демки
		if ( $userNew == true and $user->getSubscribed() != 1 ) {
			
			$unsubscribe = base64_encode($user->getId().'|'.$user->getEmail().'|'.$user->getCreatedAt()->format('d.m.Y'));
			
			$arrayDataSendEmail = array(
				'user_email' => $user->getEmail(),
				'user_name'  => $user->getFullName(),
				'password'   => $userData->password,
				'url'        => $this->router->generate( 'video_demo_viewing', [ 'id' => $newOrder->getId(), 'slug' => $newOrder->getVideo()->getSlug() ] )
			);
			
			$this->template->sendMailMessages(
				MailEventConstants::REGISTRATION_USERS_WITH_DEMO,
				MailEventConstants::REGISTRATION_USERS_WITH_DEMO_VALUES,
				(object) $arrayDataSendEmail,
				$user->getEmail(),
				$unsubscribe
			);
		}
		
		return $newOrder->getId();
	
	}
	
	/*
	 * Второй этап при процессе создания видео.
	 * В этом процесс добавляются картинки и видео которые могут быть тяжеловестные
	 */
	public function preUploadFile( $type, $file ){
	
		if( $type == 'video' ){
			
			$media = $this->saveFileMediaBundle( $file, 'placeholder_video', 'file' );
			
			return $media->getId();
			
		}elseif( $type == 'image' or $type == 'postcard_image' or $type == 'postcard_full' or $type == 'postcard_mouth' ){
			if($type == 'image'){
				$exception = 'jpg';
			}else{
				$exception = 'png';
			}
			//Конвертируем URL Data формат в картинку
			$imgData = '';
			$imgData = str_replace( ' ', '+', $file );
			$imgData = substr( $imgData, strpos( $imgData, "," ) + 1 );
			$imgData = base64_decode( $imgData );
			
			//Создаем под картинку tmp файл и записываем содержание картинки
			$tempFile = tmpfile();
			fwrite( $tempFile, $imgData );
			
			//Получаем данные tmp файла, а точнее расположение файла
			$stream = stream_get_meta_data( $tempFile );
			
			//Приводим тип файла к загрузочному для Sonata
			$fileUploader = new UploadedFile( $stream['uri'], $type.'.'.$exception );
			
			//Сохраняем картинку
			$media = $this->saveFileMediaBundle( $fileUploader, 'placeholder' );
			
			if($type == 'postcard_mouth') {
				$provider = $this->container->get( $media->getProviderName() );
				$url      = $provider->generatePublicUrl( $media, 'reference' );
				exec( 'convert ' . $_SERVER['DOCUMENT_ROOT'] . $url . ' -trim +repage ' . $_SERVER['DOCUMENT_ROOT'] . $url );
				/*$dst_image1 = imagecreatetruecolor( 200, 280 );
				$black      = imagecolorallocate( $dst_image1, 0, 0, 0 );
				imagecolortransparent( $dst_image1, $black );
				$src_image1 = imagecreatefrompng( $_SERVER['DOCUMENT_ROOT'] . $url );
				imagecopyresampled( $dst_image1, $src_image1, 0, 0, 0, 0, 200, 280, 200, 280 );
				imagepng( $dst_image1, $_SERVER['DOCUMENT_ROOT'] . $url );*/
			}elseif($type == 'postcard_image'){
				$provider = $this->container->get( $media->getProviderName() );
				$url      = $provider->generatePublicUrl( $media, 'reference' );
				exec( 'convert ' . $_SERVER['DOCUMENT_ROOT'] . $url . ' -trim +repage ' . $_SERVER['DOCUMENT_ROOT'] . $url );
				/*$dst_image1 = imagecreatetruecolor( 200, 280 );
				$black      = imagecolorallocate( $dst_image1, 0, 0, 0 );
				imagecolortransparent( $dst_image1, $black );
				$src_image1 = imagecreatefrompng( $_SERVER['DOCUMENT_ROOT'] . $url );
				imagecopyresampled( $dst_image1, $src_image1, 55, 210, 0, 0, 200, 280, 200, 280 );
				imagepng( $dst_image1, $_SERVER['DOCUMENT_ROOT'] . $url );*/
			}
			
			return $media->getId();
			
		}
		
		return false;
		
	}
	
	public function render( Request $request, Video $video, $user, $demo = false, $activeSubscription = false )
	{
		//Если видео демо, добавляем к папке проект _demo.
		if( $demo ) { $demo = '_demo'; } else { $demo = ''; }
		
		//Получаем версию, она контролирует обмен данными с windows-сервером
		$version = $this->container->getParameter('app_version');
		
		//Если версия не пустая (а она такая для основной ветки), то добавляем слэш что бы структоризировать данные по версиям на стороне windows сервера
        $version = !empty($version) ? $version.'/' : '';
		
		//Массивы для формирования файлов для рендеринга
		$placeholderAssetsJson = [];
		
		//Статус существования в прейсхолдерах массива изображений.
		//Важен для определения и внесения дополнительных данных в скрипты
		$imageMany     = $manyPlaceholder = [];
		$imageManyPush = false;
		
		//Если заполнены плейсхолдеры, "проходимся" по ним и записываем новые значения для занесению данных в рендеринга
		if ( $video->getPlaceholder()->isEmpty() == false ) {
			
			//Добавляем в assets.json путь
			$placeholderAssetsJson['template'] = [
				'src'         => 'file:///C:/Apache24/htdocs/project/' . $version . $video->getId() . '/project'.$demo.'.aepx',
				'composition' => 'Render',
				'outputExt'   => 'mp4'
			];
			
			//Начинаем создание очереди заказов видео рендеринга
			$newVideoRender = new VideoRender();
			$newVideoRender->setUsers( $user );
			$newVideoRender->setVideo( $video );
			//Если ролик с пропуском демо версии - ставимс статус finished
            if($video->getSkipDemo() === true) {
                $newVideoRender->setStatus( 'finished' );
            }else{
                $newVideoRender->setStatus( 'queued' );
            }
			$newVideoRender->setType( !empty($demo) ? VideoConstants::ONE : VideoConstants::TWO);
			
			//Получаем список плейсхолдеров
			$placeholders = $video->getPlaceholder()->toArray();
			
			if ( ! empty( $placeholders ) ) {
				
				$array_involved_assets = [];
				$sexList = $postCardJsx = '';
				$childNameList = $tempPrerender = [];
				$countPlaceholderSex = 0;
				foreach ( $placeholders as $placeholder ) {
					
					//Проверка на существование новых данных для плейсхолдера
					if ( ! empty( $request->request->has( $placeholder->getId() ) ) or ! empty( $request->files->has( $placeholder->getId() ) ) ) {
						
						//Записываем значение текстовых плейсхолдеров
						$pls = ! empty( $request->request->get( $placeholder->getId() ) ) ? $request->request->get( $placeholder->getId() ) : '';
						
						//Если текстовые не были найдены, то ищем файлы
						if ( empty( $pls ) ) {
							$pls = ! empty( $request->files->get( $placeholder->getId() ) ) ? $request->files->get( $placeholder->getId() ) : '';
						}
						
						//Создаем плейсхолдеры к видео
						$newPlaceholder = new VideoRenderPlaceholder();
						$newPlaceholder->setType( $placeholder->getType() );
						$newPlaceholder->setRender( $newVideoRender );
						$newPlaceholder->setLayerName( $placeholder->getLayerName() );
						$newPlaceholder->setLayerIndex( $placeholder->getLayerIndex() );
						$newPlaceholder->setComposition( $placeholder->getComposition() );
						$newPlaceholder->setPlaceholderParent( $placeholder );
						
						//Проверка на тип плейсхолдера и соответствующая запись по методам
						if ( $placeholder->getType() == VideoConstants::TEXT ) {
							
							$newPlaceholder->setText( $pls );
							
							$temp             = [];
							$temp["type"]     = "data";
							$temp["property"] = "Source Text";
							$temp["value"]    = $pls;
							if ( ! empty( $placeholder->getLayerName() ) ) {
								$temp["layerName"] = $placeholder->getLayerName();
							} elseif ( ! empty( $placeholder->getLayerIndex() ) ) {
								$temp["layerIndex"] = $placeholder->getLayerIndex();
							}
							if ( ! empty( $placeholder->getComposition() ) ) {
								$temp["composition"] = $placeholder->getComposition();
							}
							
						} elseif ( $placeholder->getType() == VideoConstants::IMAGE_MANY ) {
							
							if ( ! empty( $pls ) ) {
								for ( $i = 0; $i < count( $pls['images'] ); $i ++ ) {
									if($placeholder->getMaxFiles() >= $i+1) {
										if ( $pls['images'][ $i ] !== "undefined" ) {
											
											$media = $this->manager->getRepository('ApplicationSonataMediaBundle:Media')->find( $pls['images'][ $i ] );
											
											$provider = $this->container->get( $media->getProviderName() );
											$url      = $provider->generatePublicUrl( $media, 'reference' );
											
											if ( isset( $pls['phrase'][ $i ] ) ) {
												$phraseSearch = $this->manager->getRepository( 'App\Entity\Phrases' )->find( $pls['phrase'][ $i ] );
											} else {
												$phraseSearch = null;
											}
											
											//Генерируем дополнительное имя для того, что бы не было совпадающих имен
											$genName = $this->generateRandomString( 10 );
											
											$genNameAudio = $this->generateRandomString( 10 );
											
											//Если версия присутствует, то добавляем ее в урлы картинки и фразы, убираем последний символ так как он не верный слеш (/) для windows
											if(!empty($version)){
                                                $urlNewImage = "C:\\\\Apache24\\\\htdocs\\\\temp\\\\".mb_substr($version, 0, -1)."\\\\ID_PROJECT\\\\" . $genName . ".jpg";
                                                $urlNewPhrases = "C:\\\\Apache24\\\\htdocs\\\\temp\\\\".mb_substr($version, 0, -1)."\\\\ID_PROJECT\\\\" . $genNameAudio . ".wav";
                                            }else{
											    $urlNewImage = "C:\\\\Apache24\\\\htdocs\\\\temp\\\\ID_PROJECT\\\\" . $genName . ".jpg";
                                                $urlNewPhrases = "C:\\\\Apache24\\\\htdocs\\\\temp\\\\ID_PROJECT\\\\" . $genNameAudio . ".wav";
                                            }
                                            
											$imageMany[] = [
												'compositionChangeImage' => $placeholder->getLayerName(),
												'urlNewImage'            => $urlNewImage,
												'newCompositionImage'    => $genName,
												'myCompName'             => $placeholder->getComposition(),
												'image'                  => $media,
												'imageOrientation'       => ! empty( $pls['position'][ $i ] ) ? $pls['position'][ $i ] : 'h',
												'phrases'                => $phraseSearch,
												'phrasesName'            => $genNameAudio,
												'compositionChangeAudio' => $placeholder->getlayerNameAudio(),
												'urlNewPhrases'          => $urlNewPhrases,
											];
											
											$manyPlaceholder = [
												'type'              => $placeholder->getType(),
												'layerIndex'        => $placeholder->getLayerIndex(),
												'layerName'         => $placeholder->getLayerName(),
												'composition'       => $placeholder->getComposition(),
												'parentPlaceholder' => $placeholder
											];
											
											$temp              = [];
											$temp["type"]      = "image";
											$temp["src"]       = $request->getUriForPath( $url );
											$temp["layerName"] = $genName . '.jpg';
											array_push($array_involved_assets, $genName. '.jpg');
											
											$placeholderAssetsJson['assets'][] = $temp;
											$imageManyPush                     = true;
											
											if ( ! empty( $phraseSearch ) ) {
												
												$provider = $this->container->get( $phraseSearch->getAudio()->first()->getProviderName() );
												$url      = $provider->generatePublicUrl( $phraseSearch->getAudio()->first(), 'reference' );
												
												$temp              = [];
												$temp["type"]      = "audio";
												$temp["src"]       = $request->getUriForPath( $url );
												$temp["layerName"] = $genNameAudio . '.wav';
												array_push($array_involved_assets, $genNameAudio. '.wav');
												
												$placeholderAssetsJson['assets'][] = $temp;
											}
										}
									}
								}
							}
							
						} elseif ( $placeholder->getType() == VideoConstants::IMAGE ) {
							
							if ( ! empty( $pls ) ) {
								
								for ( $i = 0; $i < count( $pls['images'] ); $i ++ ) {
									
									if ( $pls['images'][ $i ] !== "undefined" ) {
										
										$media = $this->manager->getRepository('ApplicationSonataMediaBundle:Media')->find( $pls['images'][ $i ] );
										if(!empty($media)) {
                                            $provider = $this->container->get($media->getProviderName());
                                            $url      = $provider->generatePublicUrl($media, 'reference');
                                            
                                            $newPlaceholder->setImage($media);
                                            $newPlaceholder->setImageOrientation(! empty($pls['position'][$i]) ? $pls['position'][$i] : 'h');
                                            
                                            $temp         = [];
                                            $temp["type"] = "image";
                                            $temp["src"]  = $request->getUriForPath($url);
                                            if ( ! empty($placeholder->getLayerName())) {
                                                $temp["layerName"] = $placeholder->getLayerName() . '.jpg';
                                                array_push($array_involved_assets,
                                                    $placeholder->getLayerName() . '.jpg');
                                            } elseif ( ! empty($placeholder->getLayerIndex())) {
                                                $temp["layerIndex"] = $placeholder->getLayerIndex();
                                                array_push($array_involved_assets, $placeholder->getLayerIndex());
                                            }
                                            if ( ! empty($placeholder->getComposition())) {
                                                $temp["composition"] = $placeholder->getComposition();
                                            }
                                        }
									}
								}
							}
							
						} elseif ( $placeholder->getType() == VideoConstants::VIDEO ) {
							
							if ( $pls !== "undefined" ) {
								
								$media = $this->manager->getRepository('ApplicationSonataMediaBundle:Media')->find( $pls );
								
								$provider = $this->container->get( $media->getProviderName() );
								$url      = $provider->generatePublicUrl( $media, 'reference' );
								$statusChangeFileName = false;
                                //Узнаем ширину и высоту видео через ffmpeg
                                $outputExec = '';
                                exec("ffmpeg -i ".$request->getUriForPath( $url )." 2>&1 | grep -oP 'Stream .*, \K[0-9]+x[0-9]+'", $outputExec);
                                if(!empty($outputExec)){
                                    //Полученный размер переводим в массив
                                    $widthAndHeight = explode(':', $outputExec);

                                    //Если размер видео не равен тому что задан в плейсхолдере - задаем правила в пререндер
                                    if($widthAndHeight[0] !== $placeholder->getImageWidth() and $widthAndHeight[1] !== $placeholder->getImageHeight()){
                                        //Если высота меньше ширины, то ресайзим по ширине
                                        if($widthAndHeight[0] > $widthAndHeight[1]){
                                            $tempPrerender[] = [
                                                'layerName' =>  $placeholder->getLayerName() . '1.mp4',
                                                'layerNameNew' =>  $placeholder->getLayerName() . '.mp4',
                                                'params' => ['-vf' => 'scale='.$placeholder->getImageWidth().':-2 ' ]
                                            ];
                                        }else{
                                            $tempPrerender[] = [
                                                'layerName' =>  $placeholder->getLayerName() . '1.mp4',
                                                'layerNameNew' =>  $placeholder->getLayerName() . '.mp4',
                                                'params' => ['-vf' => 'scale=-2:'.$placeholder->getImageHeight() ] //', pad='.$placeholder->getImageWidth().':'.$placeholder->getImageHeight().':(ow-iw)/2:0'
                                            ];
                                        }
                                        //Ставим отметку что нужно изменить имя
                                        $statusChangeFileName = true;
                                    }
                                }

								$newPlaceholder->setVideo( $media );
								
								$temp         = [];
								$temp["type"] = "video";
								$temp["src"]  = $request->getUriForPath( $url );
								if ( ! empty( $placeholder->getLayerName() ) ) {
								    $layerNameAdded = $statusChangeFileName == true ? '1' : '';
									$temp["layerName"] = $placeholder->getLayerName() . $layerNameAdded . '.mp4';
									array_push($array_involved_assets, $placeholder->getLayerName(). $layerNameAdded .  '.mp4');
								}
								if ( ! empty( $placeholder->getComposition() ) ) {
									$temp["composition"] = $placeholder->getComposition();
								}
							}
							
						} elseif ( $placeholder->getType() == VideoConstants::URL_AUDIO ) {
							
							$phrase = null;
							
							//Получаем фразу
							$phraseAudio = $placeholder->getAudioPhrase();
							
							$childNameId = trim($pls['childName']);
                            $sexList .= ' '.$pls['sex'];
                            array_push($childNameList, $pls['childName']);
                            //Собираем критерию поиска по ArrayCollection аудио фразу относительно выбранного имени
                            $expr = new Comparison( "name", "=", $childNameId.'.wav' );
                            $criteria = \Doctrine\Common\Collections\Criteria::create()
                                                                             ->where( $expr )
                                                                             ->setMaxResults( 1 );
                            //Применяем критерию поиска
                            $phr = $phraseAudio->getAudio()->matching($criteria);
                            if(!empty($phr)){
                                $phrase = $phr->first();
                            }
                            $newPlaceholder->setAudioPhrases( $phraseAudio );

                            if(!empty($phrase)) {
                                $provider = $this->container->get( $phrase->getProviderName() );
                                $url      = $provider->generatePublicUrl( $phrase, 'reference' );

                                $temp = [];
                                $temp["type"] = "audio";
                                $temp["src"]  = $request->getUriForPath( $url );
                                if ( ! empty( $placeholder->getLayerName() ) ) {
                                    $temp["layerName"] = $placeholder->getLayerName() . '.wav';
                                    array_push($array_involved_assets, $placeholder->getLayerName() . '.wav');
                                }elseif( ! empty( $placeholder->getLayerIndex() ) ){
                                    $temp["layerIndex"] = $placeholder->getLayerIndex();
                                    array_push($array_involved_assets, $placeholder->getLayerIndex());
                                }
                                if(!empty($placeholder->getComposition())){
                                    $temp["composition"] = $placeholder->getComposition();
                                }
                            }
                            //Ищем плейсхолдер с данными о фразе с полом
                            $findPlaceholderSex = $this->manager->getRepository('App\Entity\VideoPlaceholder')->findBy(['video' => $video, 'type' => 8]);
                            if(!empty($findPlaceholderSex)){
                                $plcSex = isset($findPlaceholderSex[$countPlaceholderSex]) ? $findPlaceholderSex[$countPlaceholderSex] : $findPlaceholderSex[0];
                                $phrase = null;
                                if(!empty($plcSex)) {
                                    //Получаем фразу
                                    $phraseAudio = $plcSex->getAudioPhrase();
                                    if ($pls['sex'] == '1') {
                                        $sexType = 'm';
                                    } else {
                                        $sexType = 'f';
                                    }
                                    //Собираем критерию поиска по ArrayCollection аудио фразу относительно выбранного имени
                                    $expr     = new Comparison("name", "=", $sexType . '.wav');
                                    $criteria = \Doctrine\Common\Collections\Criteria::create()
                                                                                     ->where($expr)
                                                                                     ->setMaxResults(1);
                                    //Применяем критерию поиска
                                    $phr = $phraseAudio->getAudio()->matching($criteria);
                                    if ( ! empty($phr)) {
                                        $phrase = $phr->first();
                                    }
    
                                    if ( ! empty($phrase)) {
                                        $provider = $this->container->get($phrase->getProviderName());
                                        $url      = $provider->generatePublicUrl($phrase, 'reference');
        
                                        $temp_two         = [];
                                        $temp_two["type"] = "audio";
                                        $temp_two["src"]  = $request->getUriForPath($url);
                                        if ( ! empty($plcSex->getLayerName())) {
                                            $temp_two["layerName"] = $plcSex->getLayerName() . '.wav';
                                            array_push($array_involved_assets, $plcSex->getLayerName() . '.wav');
                                        } elseif ( ! empty($plcSex->getLayerIndex())) {
                                            $temp_two["layerIndex"] = $plcSex->getLayerIndex();
                                            array_push($array_involved_assets, $plcSex->getLayerIndex());
                                        }
                                        if ( ! empty($plcSex->getComposition())) {
                                            $temp_two["composition"] = $plcSex->getComposition();
                                        }
                                    }
                                }
                                
                            }
                            
                            //Увеличиваем счетчик фраз с именем в которых предположительно есть связь с фразой с полом.
                            $countPlaceholderSex++;
                            
						} elseif ( $placeholder->getType() == VideoConstants::AUDIO_PHRASE ) {
							
							$phrase = $this->manager->getRepository( 'App\Entity\Phrases' )->find( $pls );
							
							$newPlaceholder->setAudioPhrases( $phrase );
							
							$provider = $this->container->get( $phrase->getAudio()->first()->getProviderName() );
							$url      = $provider->generatePublicUrl( $phrase->getAudio()->first(), 'reference' );
							
							$temp = [];
							$temp["type"] = "audio";
							$temp["src"]  = $request->getUriForPath( $url );
							if ( ! empty( $placeholder->getLayerName() ) ) {
								$temp["layerName"] = $placeholder->getLayerName(). '.wav';
								array_push($array_involved_assets, $placeholder->getLayerName(). '.wav');
							}elseif( ! empty( $placeholder->getLayerIndex() ) ){
								$temp["layerIndex"] = $placeholder->getLayerIndex();
								array_push($array_involved_assets, $placeholder->getLayerIndex());
							}
							if(!empty($placeholder->getComposition())){
								$temp["composition"] = $placeholder->getComposition();
							}
							
						} elseif ( $placeholder->getType() == VideoConstants::POSTCARD ) {
							
							if ( ! empty( $pls ) ) {
								
								$media = $this->manager->getRepository('ApplicationSonataMediaBundle:Media')->find( $pls['full'] );
								$newPlaceholder->setImage( $media );
								
								if ( $pls['image'] !== "undefined" ) {
									
									$media = $this->manager->getRepository('ApplicationSonataMediaBundle:Media')->find( $pls['image'] );
									
									$provider = $this->container->get( $media->getProviderName() );
									$url      = $provider->generatePublicUrl( $media, 'reference' );
									
									$newPlaceholder->setImageFace( $media );
									
									$temp         = [];
									$temp["type"] = "image";
									$temp["src"]  = $request->getUriForPath( $url );
									if ( ! empty( $placeholder->getLayerName() ) ) {
										$temp["layerName"] = $placeholder->getLayerName() . '.png';
										array_push($array_involved_assets, $placeholder->getLayerName(). '.png');
									} elseif ( ! empty( $placeholder->getLayerIndex() ) ) {
										$temp["layerIndex"] = $placeholder->getLayerIndex();
										array_push($array_involved_assets, $placeholder->getLayerIndex());
									}
								}
								
								if ( $pls['mouth'] !== "undefined" ) {
									
									$media = $this->manager->getRepository('ApplicationSonataMediaBundle:Media')->find( $pls['mouth'] );
									
									$provider = $this->container->get( $media->getProviderName() );
									$url      = $provider->generatePublicUrl( $media, 'reference' );
									
									$newPlaceholder->setImageMouth( $media );
									
									if ( ! empty( $placeholder->getLayerNameMouth() ) ) {
										$temp_two         = [];
										$temp_two["type"] = "image";
										$temp_two["src"]  = $request->getUriForPath( $url );
										$temp_two["layerName"] = $placeholder->getLayerNameMouth() . '.png';
										array_push($array_involved_assets, $placeholder->getLayerNameMouth(). '.png');
										
                                        if(strpos($placeholder->getComposition(), ',') === false){
	                                        $postCardJsx .= '(function() {
		                                                    nexrender.selectLayersByName("'.$placeholder->getComposition().'", "'.$placeholder->getLayerNameMouth().'.png", function(layer) {
		                                                        layer.property("Position").setValue(['.$pls['mouth-offset-x'].', '.$pls['mouth-offset-y'].', 0]);
		                                                    });
		                                                })();';
                                        }else{
                                        	$compositionList = explode(',', $placeholder->getComposition());
                                        	if(!empty($compositionList)) {
                                        		foreach ($compositionList as $comp) {
			                                        $postCardJsx .= '(function() {
		                                                    nexrender.selectLayersByName("' . trim($comp) . '", "' . $placeholder->getLayerNameMouth() . '.png", function(layer) {
		                                                        layer.property("Position").setValue([' . $pls['mouth-offset-x'] . ', ' . $pls['mouth-offset-y'] . ', 0]);
		                                                    });
		                                                })();';
		                                        }
	                                        }
                                        }
									}
								}
							}
						}
						
						if ( ! empty( $temp ) and $imageManyPush === false ) {
							$placeholderAssetsJson['assets'][] = $temp;
							if(!empty($temp_two)){
								$placeholderAssetsJson['assets'][] = $temp_two;
							}
							$newVideoRender->addPlaceholder( $newPlaceholder );
						}
					}
					$imageManyPush = false;
				}
				
				$temp = $this->renderFileTempOutput($video, $placeholderAssetsJson, $array_involved_assets );
				if(!empty($temp)) {
					$placeholderAssetsJson = $temp;
				}
				
				//Заносим в базу заказ видео рендеринга и новые плейсхолдеры
				if ( ! empty( $newPlaceholder ) ) {
					$this->manager->persist( $newPlaceholder );
				}
				
				$this->manager->persist( $newVideoRender );
				$this->manager->flush();
				
				
				//После занесения данных рендеринга в базу, переходим к наполнению папки заказа (создается public/project/ID)
				if ( ! empty( $placeholderAssetsJson ) ) {

				    $sexString = trim($sexList);
					
					//Последним этапом - ищем заказ для добавления в него рендеринга
					$newOrder = $this->manager->getRepository('App\Entity\Order')->find($request->request->get('order'));
					$newOrder->addRender( $newVideoRender );
                    $newOrder->setChildSex( $sexString );
					$this->manager->persist( $newOrder );

                    if(!empty($childNameList)) {
                        foreach ($childNameList as $item) {
                            $child = $this->manager->getRepository('App\Entity\FirstName')->find($item);
                            $newFirstName = new RelOrderFirstName();
                            $newFirstName->setOrder( $newOrder );
                            $newFirstName->setFirstName($child);
                            $this->manager->persist( $newFirstName );
                            $newOrder->addFirstName($newFirstName);
                        }
                    }

					$this->manager->flush();
					
					$fileSystem = new Filesystem();
					
					//Генерируем уникальное имя для рендеринга
					//projectID - это имя по которому будет рендерится и доступное после видео
					$projectID = $newVideoRender->getId() . $this->generateRandomString( 7 );
					if(!empty($version)){
					    $projectID = substr($version, 0, -1).$projectID;
                    }
					//Записываем в Assets идентификатор проекта
					$placeholderAssetsJson["uid"] = $projectID;
					$placeholderAssetsJson['output'] = 'file:///D:/temp/'.$projectID.'.mp4';
					
					$temp   = [];
					/*$temp[] = [
						'module' => '@nexrender/action-encode',
						'preset' => 'mp4',
						'output' => 'encoded.mp4',
                        'input' => 'D:/temp/'.$projectID.'.avi'
					];*/
					
					$temp[] = [
						'module' => '@nexrender/action-copy',
						'input'  => 'D:/temp/'.$projectID.'.mp4',
						'output' => 'C:/Apache24/htdocs/results/' . $version . $projectID . '.mp4'
					];
					
					//И записываем actions, в котором настраиваем конвертацию видео из тяжеловестного mov в легковестный mp4
					$placeholderAssetsJson['actions'] = [ 'postrender' => $temp ];

					//Если массив пререндеров не пуст, добавляем его
					if(!empty($tempPrerender)){
					    $newFormattingPrerender = [];
					    foreach ($tempPrerender as $prerender){
                            $newFormattingPrerender[] = [
                                'module' => '@nexrender/action-encode',
                                'preset' => 'mp4',
                                'output' => 'C:/Apache24/htdocs/temp/' . $version . $projectID .'/'. $prerender['layerNameNew'],
                                'input'  => 'C:/Apache24/htdocs/temp/' . $version . $projectID .'/'. $prerender['layerName'],
                                'params' => $prerender['params']
                            ];
                        }
                        $placeholderAssetsJson['actions']['prerender'] = $newFormattingPrerender;
                    }
					//Копируем настроеный - render.js. Он содержит все основные настройки для рендеринга
					$fileSystem->copy( 'project/render.js', 'project/' . $newOrder->getId() . '/render.js', true );

                    //Меняем воркер для рендера
                    if(!empty($version)){
                        //Шлем запрос на основной сервер который управляет очередью
                        $worker = $this->getWorker(1);
                        if(!empty($worker)){
                            $file    = file_get_contents('project/'.$newOrder->getId().'/render.js');
                            $content = str_replace('ip_change', $worker['ip'], $file);
                            $content = str_replace('port_change', $worker['port'], $content);
                            $content = str_replace('domains', $this->container->getParameter('app_domain'), $content);
                            file_put_contents('project/'.$newOrder->getId().'/render.js', $content);
                            //Заносим для истории данные
                            $newVideoRender->setWorker( json_encode($worker) );
                        }
                    }else {
                        $worker = $this->manager->getRepository('App\Entity\WorkerLoad')->findByWorkerDemo();
                        if ( ! empty($worker)) {
                            $file    = file_get_contents('project/'.$newOrder->getId().'/render.js');
                            $content = str_replace('ip_change', $worker->getIp(), $file);
                            $content = str_replace('port_change', $worker->getPort(), $content);
                            $content = str_replace('domains', $this->container->getParameter('app_domain'), $content);
                            file_put_contents('project/'.$newOrder->getId().'/render.js', $content);
                            $worker->setNumberOfTasks($worker->getNumberOfTasks() + 1);
                            $this->manager->flush($worker);
                            $newVideoRender->setWorker($worker->getId());
                        }
                    }

                    if(!empty($postCardJsx)){
                        //Копируем настоеный - script.jsx. Он содержит настройки к AE рендерингу.
                        $fileSystem->copy( 'project/script.jsx', 'project/' . $newOrder->getId() . '/script.jsx', true );

                        $placeholderAssetsJson['assets'][] = [
                            'src'  => $request->getUriForPath( '/project/' . $newOrder->getId() . '/script.jsx' ),
                            'type' => 'script'
                        ];
                        //Получаем текущие настройки и файл Script.jsx
                        $file = file_get_contents( 'project/' . $newOrder->getId() . '/script.jsx' );

                        //Меняем в контенте файла уже известный нам ID рендеринга
                        $content = $file . $postCardJsx;
                        $content = str_replace( 'ID_PROJECT', $projectID, $content );

                        //Добавляем в него функции клонирования и замены картинок
                        file_put_contents( 'project/' . $newOrder->getId() . '/script.jsx', $content );
                    }

					//Если есть в плейсхолдерах массив изображений, добавляем свой JSX скрипт в котором содержится
					//1. Клонирование композиции 2. Замена изображений в композиции на новые
					if ( is_array( $imageMany ) and ! empty( $imageMany ) ) {

                        if(empty($postCardJsx)) {
                            //Копируем настоеный - script.jsx. Он содержит настройки к AE рендерингу.
                            $fileSystem->copy('project/script.jsx', 'project/' . $newOrder->getId() . '/script.jsx', true);

                            $placeholderAssetsJson['assets'][] = [
                                'src' => $request->getUriForPath('/project/' . $newOrder->getId() . '/script.jsx'),
                                'type' => 'script'
                            ];
                        }
						$functionJavaScript = '';
						
						//*Создаем плейсхолдер
						$newPlaceholder = new VideoRenderPlaceholder();
						$newPlaceholder->setType( $manyPlaceholder['type'] );
						$newPlaceholder->setRender( $newVideoRender );
						$newPlaceholder->setLayerName( $manyPlaceholder['layerName'] );
						$newPlaceholder->setLayerIndex( $manyPlaceholder['layerIndex'] );
						$newPlaceholder->setComposition( $manyPlaceholder['composition'] );
						$newPlaceholder->setPlaceholderParent( $manyPlaceholder['parentPlaceholder'] );
						
						$searchNewChangeImage = '';
						$searchNewPhrasesName = '';
						$genOldName = '';
						$keyImageManyLast = 0;
						
						foreach ( $imageMany as $key => $image ) {
							//Добавляем надстройки к замене изображений
							if ( $key == 0 ) {
								$searchNewChangeImage = $image['newCompositionImage'] . '.jpg';
								$searchNewPhrasesName = $image['phrasesName'].'.wav';
								$genOldName = $image['myCompName'];
								$functionJavaScript   .= '
							(function() {
							        for (var i = 1; i <= app.project.numItems; i ++){
							            if(app.project.item(i).mainSource instanceof FileSource){
							                app.project.item(i).mainSource.reload();
							           }
							        }
							        
							        var myCompName' . $key . ' = "' . $image['myCompName'] . '";
							        var newCompositionImage' . $key . ' = "' . $image['newCompositionImage'] . '";//Задаем свое новое имя
							        var newPhrasesName' . $key . ' = "' . $image['phrasesName'] . '";//Задаем свое новое имя
							        var urlNewImage' . $key . ' = "' . $image['urlNewImage'] . '";
							        var urlNewPhrases' . $key . ' = "' . $image['urlNewPhrases'] . '";
							        var compositionChangeImage' . $key . ' = "' . $image['compositionChangeImage'] . '.jpg";
							        var compositionChangePhrases' . $key . ' = "' . $image['compositionChangeAudio'].'.wav";
							    
							        //Импортируем новую картинку
							        var io' . $key . ' = new ImportOptions(File(urlNewImage' . $key . '));
							        var newImport' . $key . ' = app.project.importFile(io' . $key . ');
							        
							        //Импортируем новую фразу
							        var ioAudio' . $key . ' = new ImportOptions(File(urlNewPhrases' . $key . '));
							        var newImportAudio' . $key . ' = app.project.importFile(ioAudio' . $key . ');
					
							        nexrender.selectCompositionsByName(myCompName' . $key . ', function(comp) {
						
							            //Первый проход для поиска основной композиции, берем из нее данные продолжительности
							            for (var j = 1; j <= comp.numLayers; j++) {
							                var layer = comp.layer(j);
							                if(layer.name === compositionChangeImage' . $key . '){
							                    layer.replaceSource(newImport' . $key . ', false);
							                }
							                if(layer.name === compositionChangePhrases' . $key . '){
							                    layer.replaceSource(newImportAudio' . $key . ', false);
							                }
							            }
							    
							        })
							    
							        return true;
							    })();';
								
							} else {
								$functionJavaScript .= '
							(function() {
							        var myCompName' . $key . ' = "' . $image['myCompName'] . '";
							        var newCompositionImage' . $key . ' = "' . $image['newCompositionImage'] . '";//Задаем свое новое имя
							        var newPhrasesName' . $key . ' = "' . $image['phrasesName'] . '";//Задаем свое новое имя
							        var urlNewImage' . $key . ' = "' . $image['urlNewImage'] . '";
							        var compositionChangeImage' . $key . ' = "' . $searchNewChangeImage . '";
							        var urlNewPhrases' . $key . ' = "' . $image['urlNewPhrases'] . '";
							        var compositionChangePhrases' . $key . ' = "' . $searchNewPhrasesName . '";
							        var prevComp = "'.$genOldName.'";
							    
							        var mainComp' . $key . ' = null;
							    
							        for (var i = 1; i <= app.project.numItems; i ++){
							            if ((app.project.item(i) instanceof CompItem) && (app.project.item(i).name == myCompName' . $key . ')){
							                var mainComp' . $key . ' = app.project.item(i);
							                break;
							            }
							        }
							
							        var newComp' . $key . ' = nexrender.duplicateStructure(mainComp' . $key . ');
							        newComp' . $key . '.name = newCompositionImage' . $key . ';
							
							        //Импортируем новую картинку
							        var io' . $key . ' = new ImportOptions(File(urlNewImage' . $key . '));
							        var newImport' . $key . ' = app.project.importFile(io' . $key . ');
							        
							        //Импортируем новую фразу
							        var ioAudio' . $key . ' = new ImportOptions(File(urlNewPhrases' . $key . '));
							        var newImportAudio' . $key . ' = app.project.importFile(ioAudio' . $key . ');
							    
							        var LayerIn = null;
							        var LayerOut = null;
							
							        nexrender.selectCompositionsByName("Render", function(comp) {
							
							            //Добавляем новую композицию
							            var mylayer = comp.layers.add(newComp' . $key . ');
							
							            //Первый проход для поиска основной композиции, берем из нее данные продолжительности
							            for (var j = 1; j <= comp.numLayers; j++) {
							                var layer = comp.layer(j);
							                if(layer.name === myCompName' . $key . '){
							                    var LayerIn = layer.inPoint;
							                    var LayerOut = layer.outPoint;
										
							                	var res = LayerOut - LayerIn;
                                                mylayer.startTime = LayerIn + (res * ' . $key . ');
							                    mylayer.inPoint = LayerIn + (res * ' . $key . ');
							                    mylayer.outPoint = LayerOut + (res * ' . $key . ');
							
							                    nexrender.selectLayersByName(mylayer.name, compositionChangeImage' . $key . ', function(layers) {
							                        layers.replaceSource(newImport' . $key . ', false);
							                    });
							
							                    nexrender.selectLayersByName(mylayer.name, compositionChangePhrases' . $key . ', function(layers) {
							                        layers.replaceSource(newImportAudio' . $key . ', false);
							                    });

							                    nexrender.selectLayersByName("Render", prevComp, function(layers) {
													mylayer.moveAfter(layers); //Устанавливаем новую композицию сразу после скопированной
							                    });
							
							                    break;
							                }
							            }

							        	var resDuration = LayerOut - LayerIn;
										comp.duration = resDuration + comp.duration;
							        })
							    
							        return true;
							    })();';
								$genOldName = $image['newCompositionImage'];
							}
							
							//Прикрепляем изображения к заказу и рендерингу
							$newManyImage = new VideoRenderImageManyPlaceholder();
							$newManyImage->setImage( $image['image'] );
							$newManyImage->setImageOrientation( $image['imageOrientation'] );
							$newManyImage->setAudioPhrases( $image['phrases'] );
							$this->manager->persist( $newManyImage );
							$this->manager->flush();
							
							//Добавляем изображения в плейсхолдер
							$newPlaceholder->addImageMany( $newManyImage );
							
							//Записываем последний элемент массива, а точнее его порядковый номер для вычисления
							$keyImageManyLast = $key;
						}
						
						if(count($imageMany) > 1){
							
							$functionJavaScript .= '
							    (function() {
							        var prevComp = "'.$genOldName.'";
									var checked = 0;
							        nexrender.selectCompositionsByName("Render", function(comp) {
							            //Второй проход для поиска только что сдублированной композиции, и сдвигаем последующие композиции на ее длительность
							            for (var j = 1; j <= comp.numLayers; j++) {
							                var layer = comp.layer(j);
							                if(layer.name === prevComp){
                                                var checked = (layer.outPoint - layer.inPoint) * '.$keyImageManyLast.';
							                }else{
							                	if(checked > 0){
							                		var startedTimes = layer.startTime;
							                		var startedPoints = layer.inPoint;
	                                                layer.startTime = startedTimes + checked;
								                    layer.inPoint = startedPoints + checked;
								                    layer.outPoint = layer.outPoint + checked;
							                    }
							                }
							            }
							        })
							    
							        return true;
							    })();';
						}
						
						//Добавляем в базе плейсхолдер
						$this->manager->persist( $newPlaceholder );
						
						//Добавляем к рендерингу плейсхолдер
						$newVideoRender->addPlaceholder( $newPlaceholder );
						
						$this->manager->persist( $newVideoRender );
						$this->manager->flush();

						//Получаем текущие настройки и файл Script.jsx
						$file = file_get_contents( 'project/' . $newOrder->getId() . '/script.jsx' );
						
						//Меняем в контенте файла уже известный нам ID рендеринга
						$content = $file . $functionJavaScript;
						$content = str_replace( 'ID_PROJECT', $projectID, $content );
						
						//Добавляем в него функции клонирования и замены картинок
						file_put_contents( 'project/' . $newOrder->getId() . '/script.jsx', $content );
					}
					
					//Создаем файл assets.json с новыми плейсхолдерами и настройками
					$fileSystem->appendToFile( 'project/' . $newOrder->getId() . '/assets.json', json_encode( $placeholderAssetsJson ) );
					
					//Записываем название рендеринга в заказ для идентификации ответа по рендерингу
					$newVideoRender->setProjectUid( $projectID );
					$this->manager->flush( $newVideoRender );
					
					//Полный путь к проекту
					$root        = $_SERVER['DOCUMENT_ROOT'];
					$pathProject = $root . '/project/' . $newOrder->getId();
					
				}
				
				$newOrder->addRender( $newVideoRender );
				$this->manager->flush( $newOrder );
				
				//Создаем архив
				$iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $pathProject ) );
				
				$zip = new \ZipArchive();
				
				$archiveName = 'project/' . $newOrder->getId() . '/' . $newVideoRender->getId() . '.zip';
				
				if ( $zip->open( $archiveName, \ZipArchive::CREATE ) === true ) {
					
					foreach ( $iterator as $key => $value ) {
						if ( file_exists( $key ) && is_file( $key ) ) {
							if($value->getExtension() !== 'zip' and $value->getExtension() !== 'mp4') {
								if($value->getExtension() !== 'zip' and $value->getExtension() !== 'mp4') {
									$zip->addFile( $key, $value->getFilename() );
								}
							}
						}
					}
					
					$zip->close();
				}

                //Если у пользователя активная подписка, сразу отправляем видео на полный рендеринг
                if( $activeSubscription ) {
                    $newOrder->setActive( ActiveConstants::ORDER_SUBSCRIPTION_VALUE );
                    $newOrder->setPaymentMethod('subscription');
                    $this->renderFullVideo($request, $newOrder);
                //Если ролик с пропуском демо версии - не передаем данные рендеринга под рендеринг
                } else if($video->getSkipDemo() === false) {
                    $this->uploadArchiveToServer($request, $newVideoRender->getId(), $archiveName);
                }else{
				    //Если видео с пропуском, то проверяем на цену, если она пуста - сразу отправляем видео на полный рендеринг
                    if(empty($video->getPriceUsd()) and empty($video->getPriceRub()) and empty($video->getPriceEur()) and empty($video->getPriceUah())){
                        $newOrder->setActive( ActiveConstants::ORDER_PAID_VALUE );
                        $newOrder->setPaymentMethod('free');
                        $this->renderFullVideo($request, $newOrder);
                    }
                }

				return $newOrder->getId();

			}
		}

		return false;
	}

	public function getPriceVideo($isoCode, $video)
	{
		$price = '';

		switch ($isoCode) {
			case 'UAH':
				$price = $video->getPriceUah();
				break;
			case 'RUB':
				$price = $video->getPriceRub();
				break;
			case 'EUR':
				$price = $video->getPriceEur();
				break;
			default:
				$price = $video->getPriceUsd();
				break;
		}

		return $price;
	}

	public function renderEdit( Request $request, Order $order, User $user, $placeholders_render, $demo = false )
	{
		/** @var Video * */
		$video = $order->getVideo();

		//Массивы для формирования файлов для рендеринга
		$placeholderAssetsJson = [];
        
        //Получаем версию, она контролирует обмен данными с windows-сервером
        $version = $this->container->getParameter('app_version');
        
        //Если версия не пустая (а она такая для основной ветки), то добавляем слэш что бы структоризировать данные по версиям на стороне windows сервера
        $version = !empty($version) ? $version.'/' : '';
		
		//Статус существования в прейсхолдерах массива изображений.
		//Важен для определения и внесения дополнительных данных в скрипты
		$imageMany     = $manyPlaceholder = [];
		$imageManyPush = false;
		
		if( $demo ) { $demo = '_demo'; } else { $demo = ''; }
		
		//Если заполнены плейсхолдеры, "проходимся" по ним и записываем новые значения для занесению данных в рендеринга
		if ( $video->getPlaceholder()->isEmpty() == false ) {
			
			//Добавляем в assets.json путь
			$placeholderAssetsJson['template'] = [
				'src'         => 'file:///C:/Apache24/htdocs/project/' . $version . $video->getId() . '/project'.$demo.'.aepx',
				'composition' => 'Render',
				'outputExt'   => 'mp4'
			];
			
			//Начинаем создание очереди заказов видео рендеринга
			$newVideoRender = new VideoRender();
			$newVideoRender->setUsers( $user );
			$newVideoRender->setVideo( $video );
			$newVideoRender->setStatus( 'queued' );
			$newVideoRender->setType( VideoConstants::ONE );
			
			//Получаем список плейсхолдеров
			$placeholders = $video->getPlaceholder()->toArray();
			
			$temp = $temp_two = [];
			
			if ( ! empty( $placeholders ) ) {

				$array_involved_assets = $childNameList = []; $sexList = $postCardJsx = ''; $countPlaceholderSex = 0;
				
				foreach ( $placeholders as $placeholder ) {
					
					//Проверка на существование новых данных для плейсхолдера
					if ( ! empty( $request->request->has( $placeholder->getId() ) ) or ! empty( $request->files->has( $placeholder->getId() ) ) ) {
						
						//Записываем значение текстовых плейсхолдеров
						$pls = ! empty( $request->request->get( $placeholder->getId() ) ) ? $request->request->get( $placeholder->getId() ) : '';
						
						//Если текстовые не были найдены, то ищем файлы
						if ( empty( $pls ) ) {
							$pls = ! empty( $request->files->get( $placeholder->getId() ) ) ? $request->files->get( $placeholder->getId() ) : '';
						}
						
						//Создаем плейсхолдеры к видео
						$newPlaceholder = new VideoRenderPlaceholder();
						$newPlaceholder->setType( $placeholder->getType() );
						$newPlaceholder->setRender( $newVideoRender );
						$newPlaceholder->setLayerName( $placeholder->getLayerName() );
						$newPlaceholder->setLayerIndex( $placeholder->getLayerIndex() );
						$newPlaceholder->setComposition( $placeholder->getComposition() );
						$newPlaceholder->setPlaceholderParent( $placeholder );
						
						//Проверка на тип плейсхолдера и соответствующая запись по методам
						if ( $placeholder->getType() == VideoConstants::TEXT ) {
							
							$newPlaceholder->setText( $pls );
							
							$temp             = [];
							$temp["type"]     = "data";
							$temp["property"] = "Source Text";
							$temp["value"]    = $pls;
							if ( ! empty( $placeholder->getLayerName() ) ) {
								$temp["layerName"] = $placeholder->getLayerName();
							} elseif ( ! empty( $placeholder->getLayerIndex() ) ) {
								$temp["layerIndex"] = $placeholder->getLayerIndex();
							}
							if ( ! empty( $placeholder->getComposition() ) ) {
								$temp["composition"] = $placeholder->getComposition();
							}
							
						} elseif ( $placeholder->getType() == VideoConstants::IMAGE_MANY ) {
							if ( ! empty( $pls ) ) {
								for ( $i = 0; $i < count( $pls['images'] ); $i ++ ) {
									
									if ( $pls['images'][ $i ] !== "undefined" ) {
										
										$media = $this->manager->getRepository('ApplicationSonataMediaBundle:Media')->find( $pls['images'][ $i ] );
										
										$provider = $this->container->get( $media->getProviderName() );
										$url      = $provider->generatePublicUrl( $media, 'reference' );
										
										$phraseSearch = $this->manager->getRepository( 'App\Entity\Phrases' )->find( $pls['phrase'][ $i ] );
										
										//Генерируем дополнительное имя для того, что бы не было совпадающих имен
										$genName = $this->generateRandomString( 10 );
										
										$genNameAudio = $this->generateRandomString( 10 );
										
										$imageMany[] = [
											'compositionChangeImage' => $placeholder->getLayerName(),
											'urlNewImage'            =>  "C:\\\\Apache24\\\\htdocs\\\\temp\\\\ID_PROJECT\\\\" . $genName . ".jpg",
											'newCompositionImage'    => $genName,
											'myCompName'             => $placeholder->getComposition(),
											'image'                  => $media,
											'imageOrientation'       => ! empty( $pls['position'][ $i ] ) ? $pls['position'][ $i ] : 'h',
											'phrases'                => $phraseSearch,
											'phrasesName'            => $genNameAudio,
											'compositionChangeAudio' => $placeholder->getlayerNameAudio(),
											'urlNewPhrases'            => "C:\\\\Apache24\\\\htdocs\\\\temp\\\\ID_PROJECT\\\\" . $genNameAudio . ".wav",
										];
										
										$manyPlaceholder = [
											'type'              => $placeholder->getType(),
											'layerIndex'        => $placeholder->getLayerIndex(),
											'layerName'         => $placeholder->getLayerName(),
											'composition'       => $placeholder->getComposition(),
											'parentPlaceholder' => $placeholder
										];
										
										$temp              = [];
										$temp["type"]      = "image";
										$temp["src"]       = $request->getUriForPath( $url );
										$temp["layerName"] = $genName . '.jpg';
										
										array_push($array_involved_assets, $genName. '.jpg');
										
										$placeholderAssetsJson['assets'][] = $temp;
										$imageManyPush                     = true;
										
										if(!empty($phraseSearch)) {
											
											$provider = $this->container->get( $phraseSearch->getAudio()->first()->getProviderName() );
											$url      = $provider->generatePublicUrl( $phraseSearch->getAudio()->first(), 'reference' );
											
											$temp         = [];
											$temp["type"] = "audio";
											$temp["src"]  = $request->getUriForPath( $url );
											$temp["layerName"] = $genNameAudio . '.wav';
											
											$placeholderAssetsJson['assets'][] = $temp;
										}
									}
								}
							}
							
						} elseif ( $placeholder->getType() == VideoConstants::IMAGE ) {
							
							if ( ! empty( $pls ) ) {
								
								for ( $i = 0; $i < count( $pls['images'] ); $i ++ ) {
									
									if ( $pls['images'][ $i ] !== "undefined" ) {
										
										$media = $this->manager->getRepository('ApplicationSonataMediaBundle:Media')->find( $pls['images'][ $i ] );
										
										$provider = $this->container->get( $media->getProviderName() );
										$url      = $provider->generatePublicUrl( $media, 'reference' );
										
										$newPlaceholder->setImage( $media );
										$newPlaceholder->setImageOrientation( ! empty( $pls['position'][ $i ] ) ? $pls['position'][ $i ] : 'h' );
										
										$temp         = [];
										$temp["type"] = "image";
										$temp["src"]  = $request->getUriForPath( $url );
										if ( ! empty( $placeholder->getLayerName() ) ) {
											$temp["layerName"] = $placeholder->getLayerName() . '.jpg';
											array_push($array_involved_assets, $placeholder->getLayerName(). '.jpg');
										} elseif ( ! empty( $placeholder->getLayerIndex() ) ) {
											$temp["layerIndex"] = $placeholder->getLayerIndex();
											array_push($array_involved_assets, $placeholder->getLayerIndex());
										}
										if ( ! empty( $placeholder->getComposition() ) ) {
											$temp["composition"] = $placeholder->getComposition();
										}
									}
								}
							}
							
						} elseif ( $placeholder->getType() == VideoConstants::VIDEO ) {
							//Проверка на выбранные видео, в случае их отсутствия JS возвращает нам undefined.
							if ( $pls == 'undefined' ) {
								//Если не список плейсхолдеров прошлого рендеринга не пуст, ищем там значение, иначе пропускаем
								if ( ! empty( $placeholders_render ) ) {
									$placeholderHistroy = $placeholders_render[ $placeholder->getType() ][ $placeholder->getId() ];
									if ( ! empty( $placeholderHistroy ) ) {
										$newPlaceholder->setVideo( $placeholderHistroy->getVideo() );
										
										$provider = $this->container->get( $placeholderHistroy->getVideo()->getProviderName() );
										$url      = $provider->generatePublicUrl( $placeholderHistroy->getVideo(), 'reference' );
										
										$temp         = [];
										$temp["type"] = "video";
										$temp["src"]  = $request->getUriForPath( $url );
										if ( ! empty( $placeholder->getLayerName() ) ) {
											$temp["layerName"] = $placeholder->getLayerName() . '.mp4';
											array_push($array_involved_assets, $placeholder->getLayerName(). '.mp4');
										} elseif ( ! empty( $placeholder->getLayerIndex() ) ) {
											$temp["layerIndex"] = $placeholder->getLayerIndex();
											array_push($array_involved_assets, $placeholder->getLayerIndex());
										}
										if ( ! empty( $placeholder->getComposition() ) ) {
											$temp["composition"] = $placeholder->getComposition();
										}
									}
								}
							} else {
								
								$media = $this->manager->getRepository('ApplicationSonataMediaBundle:Media')->find( $pls );
								
								$provider = $this->container->get( $media->getProviderName() );
								$url      = $provider->generatePublicUrl( $media, 'reference' );
								
								$newPlaceholder->setVideo( $media );
								
								$temp         = [];
								$temp["type"] = "video";
								$temp["src"]  = $request->getUriForPath( $url );
								if ( ! empty( $placeholder->getLayerName() ) ) {
									$temp["layerName"] = $placeholder->getLayerName() . '.mp4';
									array_push($array_involved_assets, $placeholder->getLayerName(). '.mp4');
								} elseif ( ! empty( $placeholder->getLayerIndex() ) ) {
									$temp["layerIndex"] = $placeholder->getLayerIndex();
									array_push($array_involved_assets, $placeholder->getLayerIndex());
								}
								if ( ! empty( $placeholder->getComposition() ) ) {
									$temp["composition"] = $placeholder->getComposition();
								}
							}
							
						} elseif ( $placeholder->getType() == VideoConstants::URL_AUDIO ) {
							
							$phrase = null;

                            //Получаем фразу
                            $phraseAudio = $placeholder->getAudioPhrase();

                            $childNameId = trim($pls['childName']);
                            $sexList .= ' '.$pls['sex'];
                            array_push($childNameList, $pls['childName']);
							
							//Собираем критерию поиска по ArrayCollection аудио фразу относительно выбранного имени
							$expr = new Comparison( "name", "=", $childNameId.'.wav' );
							$criteria = \Doctrine\Common\Collections\Criteria::create()
                                         ->where( $expr )
                                         ->setMaxResults( 1 );
							//Применяем критерию поиска
							$phr = $phraseAudio->getAudio()->matching($criteria);
							if(!empty($phr)){
								$phrase = $phr->first();
							}
							
							$newPlaceholder->setAudioPhrases( $phraseAudio );
							if(!empty($phrase)) {
								$provider = $this->container->get( $phrase->getProviderName() );
								$url      = $provider->generatePublicUrl( $phrase, 'reference' );
								
								$temp = [];
								$temp["type"] = "audio";
								$temp["src"]  = $request->getUriForPath( $url );
								if ( ! empty( $placeholder->getLayerName() ) ) {
									$temp["layerName"] = $placeholder->getLayerName(). '.wav';
									array_push($array_involved_assets, $placeholder->getLayerName(). '.wav');
								}elseif( ! empty( $placeholder->getLayerIndex() ) ){
									$temp["layerIndex"] = $placeholder->getLayerIndex();
									array_push($array_involved_assets, $placeholder->getLayerIndex());
								}
								if(!empty($placeholder->getComposition())){
									$temp["composition"] = $placeholder->getComposition();
								}
							}
                            //Ищем плейсхолдер с данными о фразе с полом
                            $findPlaceholderSex = $this->manager->getRepository('App\Entity\VideoPlaceholder')->findBy(['video' => $video, 'type' => 8]);
                            if(!empty($findPlaceholderSex)){
                                $plcSex = isset($findPlaceholderSex[$countPlaceholderSex]) ? $findPlaceholderSex[$countPlaceholderSex] : $findPlaceholderSex[0];
                                $phrase = null;
                                if(!empty($plcSex)) {
                                    //Получаем фразу
                                    $phraseAudio = $plcSex->getAudioPhrase();
                                    if ($pls['sex'] == '1') {
                                        $sexType = 'm';
                                    } else {
                                        $sexType = 'f';
                                    }
                                    //Собираем критерию поиска по ArrayCollection аудио фразу относительно выбранного имени
                                    $expr     = new Comparison("name", "=", $sexType . '.wav');
                                    $criteria = \Doctrine\Common\Collections\Criteria::create()
                                                                                     ->where($expr)
                                                                                     ->setMaxResults(1);
                                    //Применяем критерию поиска
                                    $phr = $phraseAudio->getAudio()->matching($criteria);
                                    if ( ! empty($phr)) {
                                        $phrase = $phr->first();
                                    }
    
                                    if ( ! empty($phrase)) {
                                        $provider = $this->container->get($phrase->getProviderName());
                                        $url      = $provider->generatePublicUrl($phrase, 'reference');
        
                                        $temp_two         = [];
                                        $temp_two["type"] = "audio";
                                        $temp_two["src"]  = $request->getUriForPath($url);
                                        if ( ! empty($plcSex->getLayerName())) {
                                            $temp_two["layerName"] = $plcSex->getLayerName() . '.wav';
                                            array_push($array_involved_assets, $plcSex->getLayerName() . '.wav');
                                        } elseif ( ! empty($plcSex->getLayerIndex())) {
                                            $temp_two["layerIndex"] = $plcSex->getLayerIndex();
                                            array_push($array_involved_assets, $plcSex->getLayerIndex());
                                        }
                                        if ( ! empty($plcSex->getComposition())) {
                                            $temp_two["composition"] = $plcSex->getComposition();
                                        }
                                    }
                                }
                            }
                            
                            //Увеличиваем счетчик фраз с именем в которых предположительно есть связь с фразой с полом.
                            $countPlaceholderSex++;
							
						} elseif ( $placeholder->getType() == VideoConstants::AUDIO_PHRASE ) {
							
							$phrase = $this->manager->getRepository( 'App\Entity\Phrases' )->find( $pls );
							
							$newPlaceholder->setAudioPhrases( $phrase );
							
							$provider = $this->container->get( $phrase->getAudio()->first()->getProviderName() );
							$url      = $provider->generatePublicUrl( $phrase->getAudio()->first(), 'reference' );
							
							$temp = [];
							$temp["type"] = "audio";
							$temp["src"]  = $request->getUriForPath( $url );
							if ( ! empty( $placeholder->getLayerName() ) ) {
								$temp["layerName"] = $placeholder->getLayerName(). '.wav';
								array_push($array_involved_assets, $placeholder->getLayerName(). '.wav');
							}elseif( ! empty( $placeholder->getLayerIndex() ) ){
								$temp["layerIndex"] = $placeholder->getLayerIndex();
								array_push($array_involved_assets, $placeholder->getLayerIndex());
							}
							if(!empty($placeholder->getComposition())){
								$temp["composition"] = $placeholder->getComposition();
							}
							
						} elseif ( $placeholder->getType() == VideoConstants::POSTCARD ) {

                            if ( ! empty( $pls ) ) {

                                $media = $this->manager->getRepository('ApplicationSonataMediaBundle:Media')->find( $pls['full'] );
                                $newPlaceholder->setImage( $media );

                                if ( $pls['image'] !== "undefined" ) {

                                    $media = $this->manager->getRepository('ApplicationSonataMediaBundle:Media')->find( $pls['image'] );

                                    $provider = $this->container->get( $media->getProviderName() );
                                    $url      = $provider->generatePublicUrl( $media, 'reference' );

                                    $newPlaceholder->setImageFace( $media );

                                    $temp         = [];
                                    $temp["type"] = "image";
                                    $temp["src"]  = $request->getUriForPath( $url );
                                    if ( ! empty( $placeholder->getLayerName() ) ) {
                                        $temp["layerName"] = $placeholder->getLayerName() . '.png';
                                        array_push($array_involved_assets, $placeholder->getLayerName(). '.png');
                                    } elseif ( ! empty( $placeholder->getLayerIndex() ) ) {
                                        $temp["layerIndex"] = $placeholder->getLayerIndex();
                                        array_push($array_involved_assets, $placeholder->getLayerIndex());
                                    }
                                    if ( ! empty( $placeholder->getComposition() ) ) {
                                        $temp["composition"] = $placeholder->getComposition();
                                    }
                                }

                                if ( $pls['mouth'] !== "undefined" ) {

                                    $media = $this->manager->getRepository('ApplicationSonataMediaBundle:Media')->find( $pls['mouth'] );

                                    $provider = $this->container->get( $media->getProviderName() );
                                    $url      = $provider->generatePublicUrl( $media, 'reference' );

                                    $newPlaceholder->setImageMouth( $media );

                                    if ( ! empty( $placeholder->getLayerNameMouth() ) ) {
                                        $temp_two         = [];
                                        $temp_two["type"] = "image";
                                        $temp_two["src"]  = $request->getUriForPath( $url );
                                        $temp_two["layerName"] = $placeholder->getLayerNameMouth() . '.png';
                                        
                                        array_push($array_involved_assets, $placeholder->getLayerNameMouth(). '.png');
	
	                                    if(strpos($placeholder->getComposition(), ',') === false){
		                                    $postCardJsx .= '(function() {
		                                                    nexrender.selectLayersByName("'.$placeholder->getComposition().'", "'.$placeholder->getLayerNameMouth().'.png", function(layer) {
		                                                        layer.property("Position").setValue(['.$pls['mouth-offset-x'].', '.$pls['mouth-offset-y'].', 0]);
		                                                    });
		                                                })();';
	                                    }else{
		                                    $compositionList = explode(',', $placeholder->getComposition());
		                                    if(!empty($compositionList)) {
			                                    foreach ($compositionList as $comp) {
				                                    $postCardJsx .= '(function() {
		                                                    nexrender.selectLayersByName("' . trim($comp) . '", "' . $placeholder->getLayerNameMouth() . '.png", function(layer) {
		                                                        layer.property("Position").setValue([' . $pls['mouth-offset-x'] . ', ' . $pls['mouth-offset-y'] . ', 0]);
		                                                    });
		                                                })();';
			                                    }
		                                    }
	                                    }
                                    }
                                }
                            }
                        }
						
						if ( ! empty( $temp ) and $imageManyPush === false ) {
							$placeholderAssetsJson['assets'][] = $temp;
                            if(!empty($temp_two)){
                                $placeholderAssetsJson['assets'][] = $temp_two;
                            }
							$newVideoRender->addPlaceholder( $newPlaceholder );
						}
					}
					$imageManyPush = false;
				}
				
				$temp = $this->renderFileTempOutput($video, $placeholderAssetsJson, $array_involved_assets );
				if(!empty($temp)) {
					$placeholderAssetsJson = $temp;
				}
				
				//Заносим в базу заказ видео рендеринга и новые плейсхолдеры
				if ( ! empty( $newPlaceholder ) ) {
					$this->manager->persist( $newPlaceholder );
				}
				
				$this->manager->persist( $newVideoRender );
				$this->manager->flush();
				
				
				//После занесения данных рендеринга в базу, переходим к наполнению папки заказа (создается public/project/ID)
				if ( ! empty( $placeholderAssetsJson ) ) {

                    $sexString = trim($sexList);
					
					//Последним этапом - меняем заказ
					$order->addRender( $newVideoRender );
					$order->setActive( 0 );
					$order->setFullName( $request->request->get( 'user_name' ) );
					$order->setPhone( $request->request->get( 'user_phone' ) );
					$order->setEmail( $request->request->get( 'user_email' ) );
					$order->setCity( $request->request->get( 'user_city' ) );
                    $order->setChildSex( $sexString );
                    if(!empty($childNameList)) {
                        //Удаляем старые имена
                        $this->manager->getRepository('App\Entity\RelOrderFirstName')->deleteFirstNameByOrder( $order->getId() );
                        //Добавляем новые
                        foreach ($childNameList as $item) {
                            $child = $this->manager->getRepository('App\Entity\FirstName')->find($item);
                            $newFirstName = new RelOrderFirstName();
                            $newFirstName->setOrder( $order );
                            $newFirstName->setFirstName($child);
                            $this->manager->persist( $newFirstName );
                            $order->addFirstName($newFirstName);
                        }
                    }
					$this->manager->flush( $order );
					
					$fileSystem = new Filesystem();
					//Генерируем уникальное имя для рендеринга
					//projectID - это имя по которому будет рендерится и доступное после видео
					$projectID = $newVideoRender->getId() . $this->generateRandomString( 8 );
                    if(!empty($version)){
                        $projectID = substr($version, 0, -1).$projectID;
                    }
					
					//Записываем в Assets идентификатор проекта
					$placeholderAssetsJson["uid"] = $projectID;
                    $placeholderAssetsJson['output'] = 'file:///D:/temp/'.$projectID.'.mp4';
					
					$temp   = [];
					/*$temp[] = [
						'module' => '@nexrender/action-encode',
						'preset' => 'mp4',
						'output' => 'encoded.mp4',
                        'input' =>  'D:/temp/'.$projectID.'.avi'
					]; */
					$temp[] = [
						'module' => '@nexrender/action-copy',
						'input'  => 'D:/temp/'.$projectID.'.mp4',
						'output' => 'C:/Apache24/htdocs/results/' . $version . $projectID . '.mp4'
					];
					//И записываем actions, в котором настраиваем конвертацию видео из тяжеловестного mov в легковестный mp4
					$placeholderAssetsJson['actions'] = [ 'postrender' => $temp ];
					
					//Копируем настроеный - render.js. Он содержит все основные настройки для рендеринга
					$fileSystem->remove('project/' . $order->getId() . '/render.js');
					$fileSystem->copy( 'project/render.js', 'project/' . $order->getId() . '/render.js', true );
                    
                    //Меняем воркер для рендера
                    if(!empty($version)) {
                        //Шлем запрос на основной сервер который управляет очередью
                        $worker = $this->getWorker(1);
                        if ( ! empty($worker)) {
                            $file    = file_get_contents('project/'.$order->getId().'/render.js');
                            $content = str_replace('ip_change', $worker['ip'], $file);
                            $content = str_replace('port_change', $worker['port'], $content);
                            $content = str_replace('domains', $this->container->getParameter('app_domain'), $content);
                            file_put_contents('project/'.$order->getId().'/render.js', $content);
                            //Заносим для истории данные
                            $newVideoRender->setWorker(json_encode($worker));
                        }
                    }else {
                        //Меняем воркер для рендера
                        $worker = $this->manager->getRepository('App\Entity\WorkerLoad')->findByWorkerDemo();
                        if ( ! empty($worker)) {
                            $file    = file_get_contents('project/'.$order->getId().'/render.js');
                            $content = str_replace('ip_change', $worker->getIp(), $file);
                            $content = str_replace('port_change', $worker->getPort(), $content);
                            $content = str_replace('domains', $this->container->getParameter('app_domain'), $content);
                            file_put_contents('project/'.$order->getId().'/render.js', $content);
                            $worker->setNumberOfTasks($worker->getNumberOfTasks() + 1);
                            $this->manager->flush($worker);
                            $newVideoRender->setWorker($worker->getId());
                        }
                    }

                    if(!empty($postCardJsx)){
                        $fileSystem->remove('project/' . $order->getId() . '/script.jsx');
                        //Копируем настоеный - script.jsx. Он содержит настройки к AE рендерингу.
                        $fileSystem->copy( 'project/script.jsx', 'project/' . $order->getId() . '/script.jsx', true );

                        $placeholderAssetsJson['assets'][] = [
                            'src'  => $request->getUriForPath( '/project/' . $order->getId() . '/script.jsx' ),
                            'type' => 'script'
                        ];
                        //Получаем текущие настройки и файл Script.jsx
                        $file = file_get_contents( 'project/' . $order->getId() . '/script.jsx' );

                        //Меняем в контенте файла уже известный нам ID рендеринга
                        $content = $file . $postCardJsx;
                        $content = str_replace( 'ID_PROJECT', $projectID, $content );

                        //Добавляем в него функции клонирования и замены картинок
                        file_put_contents( 'project/' . $order->getId() . '/script.jsx', $content );
                    }

					//Если есть в плейсхолдерах массив изображений, добавляем свой JSX скрипт в котором содержится
					//1. Клонирование композиции 2. Замена изображений в композиции на новые
					if ( is_array( $imageMany ) and ! empty( $imageMany ) ) {
						
						//Копируем настоеный - script.jsx. Он содержит настройки к AE рендерингу.
                        if(empty($postCardJsx)) {
                            $fileSystem->remove('project/' . $order->getId() . '/script.jsx');
                            $fileSystem->copy('project/script.jsx', 'project/' . $order->getId() . '/script.jsx', true);

                            $placeholderAssetsJson['assets'][] = [
                                'src' => $request->getUriForPath('/project/' . $order->getId() . '/script.jsx'),
                                'type' => 'script'
                            ];
                        }
						$functionJavaScript = '';
						//*Создаем плейсхолдер
						$newPlaceholder = new VideoRenderPlaceholder();
						$newPlaceholder->setType( $manyPlaceholder['type'] );
						$newPlaceholder->setRender( $newVideoRender );
						$newPlaceholder->setLayerName( $manyPlaceholder['layerName'] );
						$newPlaceholder->setLayerIndex( $manyPlaceholder['layerIndex'] );
						$newPlaceholder->setComposition( $manyPlaceholder['composition'] );
						$newPlaceholder->setPlaceholderParent( $manyPlaceholder['parentPlaceholder'] );
						
						$searchNewChangeImage = '';
						$searchNewPhrasesName = '';
						$genOldName = '';
						
						foreach ( $imageMany as $key => $image ) {
							//Добавляем надстройки к замене изображений
							if ( $key == 0 ) {
								$searchNewChangeImage = $image['newCompositionImage'] . '.jpg';
								$searchNewPhrasesName = $image['phrasesName'].'.wav';
								$genOldName = $image['myCompName'];
								$functionJavaScript   .= '
							(function() {
							        for (var i = 1; i <= app.project.numItems; i ++){
							            if(app.project.item(i).mainSource instanceof FileSource){
							                app.project.item(i).mainSource.reload();
							           }
							        }
							        
							        var myCompName' . $key . ' = "' . $image['myCompName'] . '";
							        var newCompositionImage' . $key . ' = "' . $image['newCompositionImage'] . '";//Задаем свое новое имя
							        var newPhrasesName' . $key . ' = "' . $image['phrasesName'] . '";//Задаем свое новое имя
							        var urlNewImage' . $key . ' = "' . $image['urlNewImage'] . '";
							        var urlNewPhrases' . $key . ' = "' . $image['urlNewPhrases'] . '";
							        var compositionChangeImage' . $key . ' = "' . $image['compositionChangeImage'] . '.jpg";
							        var compositionChangePhrases' . $key . ' = "' . $image['compositionChangeAudio'].'.wav";
							    
							        //Импортируем новую картинку
							        var io = new ImportOptions(File(urlNewImage' . $key . '));
							        var newImport = app.project.importFile(io);
							        
							        //Импортируем новую фразу
							        var ioAudio = new ImportOptions(File(urlNewPhrases' . $key . '));
							        var newImportAudio = app.project.importFile(ioAudio);
							
							        nexrender.selectCompositionsByName(myCompName' . $key . ', function(comp) {
						
							            //Первый проход для поиска основной композиции, берем из нее данные продолжительности
							            for (var j = 1; j <= comp.numLayers; j++) {
							                var layer = comp.layer(j);
							                if(layer.name === compositionChangeImage' . $key . '){
							                    layer.replaceSource(newImport, false);
							                }
							                if(layer.name === compositionChangePhrases' . $key . '){
							                    layer.replaceSource(newImportAudio, false);
							                }
							            }
							    
							        })
							    
							        return true;
							    })();';
								
							} else {
								$functionJavaScript .= '
							(function() {
							        var myCompName' . $key . ' = "' . $image['myCompName'] . '";
							        var newCompositionImage' . $key . ' = "' . $image['newCompositionImage'] . '";//Задаем свое новое имя
							        var newPhrasesName' . $key . ' = "' . $image['phrasesName'] . '";//Задаем свое новое имя
							        var urlNewImage' . $key . ' = "' . $image['urlNewImage'] . '";
							        var compositionChangeImage' . $key . ' = "' . $searchNewChangeImage . '";
							        var urlNewPhrases' . $key . ' = "' . $image['urlNewPhrases'] . '";
							        var compositionChangePhrases' . $key . ' = "' . $searchNewPhrasesName . '";
							        var prevComp = "'.$genOldName.'";
							    
							        var mainComp' . $key . ' = null;
							    
							        for (var i = 1; i <= app.project.numItems; i ++){
							            if ((app.project.item(i) instanceof CompItem) && (app.project.item(i).name == myCompName' . $key . ')){
							                var mainComp' . $key . ' = app.project.item(i);
							                break;
							            }
							        }
							
							        var newComp' . $key . ' = nexrender.duplicateStructure(mainComp' . $key . ');
							        newComp' . $key . '.name = newCompositionImage' . $key . ';
							
							        //Импортируем новую картинку
							        var io' . $key . ' = new ImportOptions(File(urlNewImage' . $key . '));
							        var newImport' . $key . ' = app.project.importFile(io' . $key . ');
							        
							        //Импортируем новую фразу
							        var ioAudio' . $key . ' = new ImportOptions(File(urlNewPhrases' . $key . '));
							        var newImportAudio' . $key . ' = app.project.importFile(ioAudio' . $key . ');
							    
							        var LayerIn = null;
							        var LayerOut = null;
							
							        nexrender.selectCompositionsByName("Render", function(comp) {
							
							            //Добавляем новую композицию
							            var mylayer = comp.layers.add(newComp' . $key . ');
							
							            //Первый проход для поиска основной композиции, берем из нее данные продолжительности
							            for (var j = 1; j <= comp.numLayers; j++) {
							                var layer = comp.layer(j);
							                if(layer.name === myCompName' . $key . '){
							                    var LayerIn = layer.inPoint;
							                    var LayerOut = layer.outPoint;
										
							                	var res = LayerOut - LayerIn;
                                                mylayer.startTime = LayerIn + (res * ' . $key . ');
							                    mylayer.inPoint = LayerIn + (res * ' . $key . ');
							                    mylayer.outPoint = LayerOut + (res * ' . $key . ');
							
							                    nexrender.selectLayersByName(mylayer.name, compositionChangeImage' . $key . ', function(layers) {
							                        layers.replaceSource(newImport' . $key . ', false);
							                    });
							
							                    nexrender.selectLayersByName(mylayer.name, compositionChangePhrases' . $key . ', function(layers) {
							                        layers.replaceSource(newImportAudio' . $key . ', false);
							                    });
							                    
							                    nexrender.selectLayersByName("Render", prevComp, function(layers) {
													mylayer.moveAfter(layers); //Устанавливаем новую композицию сразу после скопированной
							                    });
							
							                    break;
							                }
							            }

							        	var resDuration = LayerOut - LayerIn;
										comp.duration = resDuration + comp.duration;
							    
							        })
							    
							        return true;
							    })();';
								$genOldName = $image['newCompositionImage'];
							}
							
							//Прикрепляем изображения к заказу и рендерингу
							$newManyImage = new VideoRenderImageManyPlaceholder();
							$newManyImage->setImage( $image['image'] );
							$newManyImage->setImageOrientation( $image['imageOrientation'] );
							$newManyImage->setAudioPhrases( $image['phrases'] );
							$this->manager->persist( $newManyImage );
							$this->manager->flush();
							
							//Добавляем изображения в плейсхолдер
							$newPlaceholder->addImageMany( $newManyImage );
							
							//Записываем последнюю итерацию что бы высчитать продолжительность на которую нужно сдвинуть композциии
							$keyImageManyLast = $key;
						}
						
						if(count($imageMany) > 1){
							
							$functionJavaScript .= '
							    (function() {
							        var prevComp = "'.$genOldName.'";
									var checked = 0;
							        nexrender.selectCompositionsByName("Render", function(comp) {
							            //Второй проход для поиска только что сдублированной композиции, и сдвигаем последующие композиции на ее длительность
							            for (var j = 1; j <= comp.numLayers; j++) {
							                var layer = comp.layer(j);
							                if(layer.name === prevComp){
                                                var checked = (layer.outPoint - layer.inPoint) * '.$keyImageManyLast.';
							                }else{
							                	if(checked > 0){
							                		var startedTimes = layer.startTime;
							                		var startedPoints = layer.inPoint;
	                                                layer.startTime = startedTimes + checked;
								                    layer.inPoint = startedPoints + checked;
								                    layer.outPoint = layer.outPoint + checked;
							                    }
							                }
							            }
							        })
							    
							        return true;
							    })();';
						}
						//Добавляем в базе плейсхолдер
						$this->manager->persist( $newPlaceholder );
						
						//Добавляем к рендерингу плейсхолдер
						$newVideoRender->addPlaceholder( $newPlaceholder );
						
						$this->manager->persist( $newVideoRender );
						$this->manager->flush();
						
						//Получаем текущие настройки и файл Script.jsx
						$file = file_get_contents( 'project/' . $order->getId() . '/script.jsx' );
						
						//Меняем в контенте файла уже известный нам ID рендеринга
						$content = $file . $functionJavaScript;
						$content = str_replace( 'ID_PROJECT', $projectID, $content );
						
						//Добавляем в него функции клонирования и замены картинок
						file_put_contents( 'project/' . $order->getId() . '/script.jsx', $content );
					}
					
					//Создаем файл assets.json с новыми плейсхолдерами и настройками
					$fileSystem->remove('project/' . $order->getId() . '/assets.json');
					$fileSystem->appendToFile( 'project/' . $order->getId() . '/assets.json', json_encode( $placeholderAssetsJson ) );
					
					//Записываем название рендеринга в заказ для идентификации ответа по рендерингу
					$newVideoRender->setProjectUid( $projectID );
					$this->manager->flush( $newVideoRender );
					
					//Полный путь к проекту
					$root        = $_SERVER['DOCUMENT_ROOT'];
					$pathProject = $root . '/project/' . $order->getId();
					
				}
				
				//Создаем архив
				$iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $pathProject ) );
				
				$zip = new \ZipArchive();
				
				$archiveName = 'project/' . $order->getId() . '/' . $newVideoRender->getId() . '.zip';
				
				if ( $zip->open( $archiveName, \ZipArchive::CREATE ) === true ) {
					
					foreach ( $iterator as $key => $value ) {
						if ( file_exists( $key ) && is_file( $key ) ) {
							if($value->getExtension() !== 'zip' and $value->getExtension() !== 'mp4') {
								$zip->addFile( $key, $value->getFilename() );
							}
						}
					}
					
					$zip->close();
				}
				
				$this->uploadArchiveToServer( $request, $newVideoRender->getId(), $archiveName );
				
				return $order->getId();
				
			}
		}
		
		return false;
	}
	
	public function uploadArchiveToServer( Request $request, $renderId, $archiveName )
	{
		$file = $request->getUriForPath( '/' . $archiveName );
		
		$post = array(
			'file'   => $file,
			'worker' => $renderId,
            'version'  => $this->container->getParameter('app_version')
		);
		
		$url = $this->container->getParameter( 'win_server' ) . "/api/controller.php";
		
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_FRESH_CONNECT, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT_MS, 1000 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
		curl_exec( $ch );
		curl_close( $ch );
	}
	
	public function createUsers( Request $request )
	{
		//Определяем IP адрес пользователя
		$ip = $request->getClientIp();
        // добавляем код страны к номеру телефона
        $user_phone = $request->request->get('user_phone');
        $prefix_phone = $request->request->get('__phone_prefix');
        $full_phone = '+' . $prefix_phone . $user_phone;
        
        $country = null;
        
        //Определяем страну пользователя
        try {
            $readerCountry = new Reader('GeoLite2-Country.mmdb');
            $geo           = $readerCountry->country($ip);
        }catch (\Exception $exception){
            $geo = (object)array('country' => (object)['isoCode' => 'UA']);
        }
        
        if ( ! empty($geo)) {
            $country = $this->manager->getRepository('App\Entity\Country')->findOneBy(['isoCode' => $geo->country->isoCode]);
        }
        
        $pass = $this->generateRandomString(10);
        $user = $this->userManager->createUser();
        $user->setUsername($request->request->get('user_email', ''));
        $user->setEmail($request->request->get('user_email', ''));
        $user->setFullName($request->request->get('user_name', ''));
        $user->setCity($request->request->get('user_city', ''));
        $user->setEmailCanonical($request->request->get('user_email'));
//        if ( ! empty($request->request->get('user_phone'))) {
//            $user->setPhone($request->request->get('user_phone'));
//        }
        if ( ! empty($full_phone)) {
            $user->setPhone($full_phone);
        }
        if ( ! empty($country)) {
            $user->setCountry($country);
        }
        $user->setPlainPassword($pass);
        $user->setEnabled(true);
        
        $validatorErrors = $this->validator->validate($user);
        
        if (count($validatorErrors) == 0) {
            
            $urlAvatar = $this->get_gravatar($user->getEmail());
            
            $urlNewFile = 'upload/tmp/' . uniqid() . rand(1111, 9999) . '.jpg';
            
            file_put_contents($urlNewFile, file_get_contents($urlAvatar));
            
            $avatar = $this->saveFileMediaBundle($urlNewFile, 'avatar');
            
            unlink($urlNewFile);
            
            $user->setAvatar($avatar);
            
            $this->userManager->updateUser($user);
            
            $this->manager->flush();
            
            //Handle getting or creating the user entity likely with a posted form
            // The third parameter "main" can change according to the name of your firewall in security.yml
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            
            // If the firewall name is not main, then the set value would be instead:
            // $this->get('session')->set('_security_XXXFIREWALLNAMEXXX', serialize($token));
            $this->container->get('session')->set('_security_main', serialize($token));
            
            // Fire the login event manually
            $event = new InteractiveLoginEvent($request, $token);
            $this->container->get("event_dispatcher")->dispatch("security.interactive_login", $event);
            
            return (object)array('user' => $user, 'password' => $pass);
            
        } else {
            
            $errors = [];
            
            foreach ($validatorErrors as $error) {
                
                $errors[] = [
                    'field'   => $error->getPropertyPath(),
                    'message' => $error->getMessage()
                ];
            }
            
            return $errors;
        }
	}
	
	/**
	 * Get either a Gravatar URL or complete image tag for a specified email address.
	 *
	 * @param string $email The email address
	 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
	 * @param string $d Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
	 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
	 * @param bool $img True to return a complete IMG tag False for just the URL
	 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
	 * @return String containing either just a URL or a complete image tag
	 * @source https://gravatar.com/site/implement/images/php/
	 */
	public function get_gravatar( $email, $s = 80, $d = 'mp', $r = 'g', $img = false, $atts = array() ) {
		$url = 'https://www.gravatar.com/avatar/';
		$url .= md5( strtolower( trim( $email ) ) );
		$url .= "?s=$s&d=$d&r=$r";
		if ( $img ) {
			$url = '<img src="' . $url . '"';
			foreach ( $atts as $key => $val )
				$url .= ' ' . $key . '="' . $val . '"';
			$url .= ' />';
		}
		return $url;
	}
	
	public function renderFullVideo( Request $request, Order $order )
	{
        //Получаем версию, она контролирует обмен данными с windows-сервером
        $version = $this->container->getParameter('app_version');
        
        //Если версия не пустая (а она такая для основной ветки), то добавляем слэш что бы структоризировать данные по версиям на стороне windows сервера
        $version = !empty($version) ? $version.'/' : '';
        
		$time = new \DateTime( 'NOW' );
		$prerender = null;
		//УЗнаем ID последнего рендеринга для точных значений
		$videoRenderLast = $order->getRender()->last();
		
		$clone = clone $videoRenderLast;
		$clone->setProjectUid( "" );
		$clone->setType( VideoConstants::TWO );
		$clone->setStatus('');
		$clone->setStartAt( $time );
		$clone->setEndAt( $time );
		$this->manager->persist( $clone );
		$this->manager->flush();
		
		if($clone->getPlaceholder()->isEmpty() == false){
			foreach ($clone->getPlaceholder()->toArray() as $pl){
				$newPlaceholder = clone $pl;
				$newPlaceholder->setRender( $clone );
				
//				if($pl->getType() == VideoConstants::VIDEO){
//					$prerender[] = $pl->getLayerName();
//				}
				
				if($pl->getImageMany()->isEmpty() == false){
					foreach ($pl->getImageMany()->toArray() as $im) {
						$newManyImage = clone $im;
						$this->manager->persist( $newManyImage );
						$this->manager->flush();
						
						//Добавляем изображения в плейсхолдер
						$newPlaceholder->addImageMany( $newManyImage );
					}
				}
				
				$this->manager->persist( $newPlaceholder );
			}
			$this->manager->flush();
		}
		
		//Генерируем новоеуникальное имя для нового рендеринга
		$projectID = $clone->getId() . $this->generateRandomString( 7 );
        if(!empty($version)){
            $projectID = substr($version, 0, -1).$projectID;
        }
		
		//Записываем в нову запись о рендеринге, новое имя проекта
		$clone->setProjectUid( $projectID );
		$this->manager->flush( $clone );
		
		//Добавляем рендеринг в заказ
		$order->addRender( $clone );
		
		//Полный путь к проекту
		$root        = $_SERVER['DOCUMENT_ROOT'];
		$pathProject = 'project/' . $order->getId();
		
		//Забираем старый UID проекта для замены его на новый
		$oldProjectUid = $videoRenderLast->getProjectUid();
		
		//Ищем файл, в нем ищем старый UID проекта и заменяем его на новый
		$renderJS = file_get_contents( $pathProject . '/assets.json' );
		$renderJS = str_replace( array($oldProjectUid, '_demo'), array($projectID, ''), $renderJS );
		
		if(!empty($prerender)) {
			$temp   = [];
			foreach ($prerender as $pr) {
				$temp[] = [
					'module' => '@nexrender/action-encode',
					'preset' => 'mp4',
					'input' => $pr.'1.mp4',
					'output' => $pr.'.mp4'
				];
				$renderJS = str_replace( array($pr.'.mp4'), array($pr.'1.mp4'), $renderJS );
			}
			
			$decode = json_decode( $renderJS, true );
			$decode['actions']['prerender'] = $temp;
			$renderJS = json_encode( $decode );
		}
		
		$tempEncode[] = [
			'module' => '@nexrender/action-copy',
			'input'  => 'D:/temp/'.$projectID.'.mp4',
			'output' => 'C:/Apache24/htdocs/results/' . $version . $projectID . '.mp4'
		];
		$tempEncode[] = [
			'module' => '@nexrender/action-encode',
			'preset' => 'mp4',
			'output' => 'C:/Apache24/htdocs/results/' . $version . $projectID . '_480p.mp4',
			'input'  => 'D:/temp/'.$projectID.'.mp4',
			'params' => ['-vf' => 'scale=854:480' ]
		];
//		$tempEncode[] = [
//			'module' => '@nexrender/action-encode',
//			'preset' => 'mp4',
//			'output' => 'C:/Apache24/htdocs/results/' . $projectID . '_720p.mp4',
//			'input'  => 'D:/temp/'.$projectID.'.mp4',
//			'params' => ['-vf' => 'scale=1280:720' ]
//		];
		$decode = json_decode( $renderJS, true );
		$decode['actions']['postrender'] = $tempEncode;
		$renderJS = json_encode( $decode );
		
		file_put_contents( $pathProject . '/assets.json', $renderJS );
		
		$filesystem = new Filesystem();
		if($filesystem->exists( $pathProject . '/script.jsx')){
			//Меняем в скрипте так же старый UID проекта на новый
			$renderJS = file_get_contents( $pathProject . '/script.jsx' );
			$renderJS = str_replace( $oldProjectUid, $projectID, $renderJS );
			file_put_contents( $pathProject . '/script.jsx', $renderJS );
		}
		
		//Удаляем старый render.js и добавляем новый с заменами очереди к воркеру
		$filesystem->remove('project/' . $order->getId() . '/render.js');
		$filesystem->copy( 'project/render.js', 'project/' . $order->getId() . '/render.js', true );
        //Меняем воркер для рендера
        if(!empty($version)) {
            //Шлем запрос на основной сервер который управляет очередью
            $worker = $this->getWorker(1);
            if ( ! empty($worker)) {
                $file    = file_get_contents('project/'.$order->getId().'/render.js');
                $content = str_replace('ip_change', $worker['ip'], $file);
                $content = str_replace('port_change', $worker['port'], $content);
                $content = str_replace('domains', $this->container->getParameter('app_domain'), $content);
                file_put_contents('project/'.$order->getId().'/render.js', $content);
                //Заносим для истории данные
                $clone->setWorker(json_encode($worker));
            }
        }else {
            $worker = $this->manager->getRepository('App\Entity\WorkerLoad')->findByWorkerFull();
            if ( ! empty($worker)) {
                $file    = file_get_contents('project/'.$order->getId().'/render.js');
                $content = str_replace('ip_change', $worker->getIp(), $file);
                $content = str_replace('port_change', $worker->getPort(), $content);
                $content = str_replace('domains', $this->container->getParameter('app_domain'), $content);
                file_put_contents('project/'.$order->getId().'/render.js', $content);
                $worker->setNumberOfTasks($worker->getNumberOfTasks() + 1);
                $this->manager->flush($worker);
                $clone->setWorker($worker->getId());
            }
        }
		$this->manager->flush( $clone );
		
		//Создаем архив
		$iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $root . '/' . $pathProject ) );
		
		$zip = new \ZipArchive();
		
		$archiveName = 'project/' . $order->getId() . '/' . $clone->getId() . '.zip';
		
		if ( $zip->open( $archiveName, \ZipArchive::CREATE ) === true ) {
			
			foreach ( $iterator as $key => $value ) {
				if ( file_exists( $key ) && is_file( $key ) ) {
					if($value->getExtension() !== 'zip' and $value->getExtension() !== 'mp4') {
						$zip->addFile( $key, $value->getFilename() );
					}
				}
			}
			
			$zip->close();
		}
		
		$this->uploadArchiveToServer( $request, $clone->getId(), $archiveName );
		
		return $order->getId();
	}
	
	public function reloadRender( Request $request, Order $order )
	{
		
		$time = new \DateTime( 'NOW' );
		//УЗнаем ID последнего рендеринга для точных значений
		$videoRenderLast = $order->getRender()->last();
		$clone           = clone $videoRenderLast;
		$clone->setProjectUid( "" );
		$clone->setStartAt( $time );
		$clone->setEndAt( $time );
		$this->manager->persist( $clone );
		$this->manager->flush();
		
		//Генерируем новоеуникальное имя для нового рендеринга
		$projectID = $clone->getId() . $this->generateRandomString( 7 );
        if(!empty($version)){
            $projectID = substr($version, 0, -1).$projectID;
        }
		
		//Записываем в нову запись о рендеринге, новое имя проекта
		$clone->setProjectUid( $projectID );
		$this->manager->flush( $clone );
		
		//Добавляем рендеринг в заказ
		$order->addRender( $clone );
		$this->manager->flush( $order );
		
		//Полный путь к проекту
		$root        = $_SERVER['DOCUMENT_ROOT'];
		$pathProject = 'project/' . $order->getId();
		
		//Забираем старый UID проекта для замены его на новый
		$oldProjectUid = $videoRenderLast->getProjectUid();
		
		//Ищем файл, в нем ищем старый UID проекта и заменяем его на новый
		$renderJS = file_get_contents( $pathProject . '/assets.json' );
		$renderJS = str_replace( $oldProjectUid, $projectID, $renderJS );
		file_put_contents( $pathProject . '/assets.json', $renderJS );
		
		$filesystem = new Filesystem();
		if($filesystem->exists( $pathProject . '/script.jsx')){
			//Меняем в скрипте так же старый UID проекта на новый
			$renderJS = file_get_contents( $pathProject . '/script.jsx' );
			$renderJS = str_replace( $oldProjectUid, $projectID, $renderJS );
			file_put_contents( $pathProject . '/script.jsx', $renderJS );
		}
		
		//Удаляем старый render.js и добавляем новый с заменами очереди к воркеру
		$filesystem->remove('project/' . $order->getId() . '/render.js');
		$filesystem->copy( 'project/render.js', 'project/' . $order->getId() . '/render.js', true );
		//Меняем воркер для рендера
        if(!empty($version)) {
            //Шлем запрос на основной сервер который управляет очередью
            if ($clone->getType() == VideoConstants::TWO) {
                $worker = $this->getWorker(2);
            }else{
                $worker = $this->getWorker(1);
            }
            if ( ! empty($worker)) {
                $file    = file_get_contents('project/'.$order->getId().'/render.js');
                $content = str_replace('ip_change', $worker['ip'], $file);
                $content = str_replace('port_change', $worker['port'], $content);
                $content = str_replace('domains', $this->container->getParameter('app_domain'), $content);
                file_put_contents('project/'.$order->getId().'/render.js', $content);
                
                //Заносим для истории данные
                $clone->setWorker(json_encode($worker));
            }
        }else {
            if ($clone->getType() == VideoConstants::TWO) {
                $worker = $this->manager->getRepository('App\Entity\WorkerLoad')->findByWorkerFull();
            } else {
                $worker = $this->manager->getRepository('App\Entity\WorkerLoad')->findByWorkerDemo();
            }
            if ( ! empty($worker)) {
                $file    = file_get_contents('project/'.$order->getId().'/render.js');
                $content = str_replace('ip_change', $worker->getIp(), $file);
                $content = str_replace('port_change', $worker->getPort(), $content);
                $content = str_replace('domains', $this->container->getParameter('app_domain'), $content);
                file_put_contents('project/'.$order->getId().'/render.js', $content);
                $worker->setNumberOfTasks($worker->getNumberOfTasks() + 1);
                $this->manager->flush($worker);
                $clone->setWorker($worker->getId());
            }
        }
		$this->manager->flush( $clone );
		
		//Создаем архив
		$iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $root . '/' . $pathProject ) );
		
		$zip = new \ZipArchive();
		
		$archiveName = 'project/' . $order->getId() . '/' . $clone->getId() . '.zip';
		
		if ( $zip->open( $archiveName, \ZipArchive::CREATE ) === true ) {
			
			foreach ( $iterator as $key => $value ) {
				if ( file_exists( $key ) && is_file( $key ) ) {
					if($value->getExtension() !== 'zip' and $value->getExtension() !== 'mp4') {
						$zip->addFile( $key, $value->getFilename() );
					}
				}
			}
			
			$zip->close();
		}
		
		$this->uploadArchiveToServer( $request, $clone->getId(), $archiveName );
		
		return $order->getId();
	}
	
	/*
	 * Запуск рендеринга в тестовом режиме (без каких либо введенных данных)
	 */
	public function testRender( Request $request, Video $video, $user )
	{
		//Если видео демо, добавляем к папке проект _demo.
		$demo = '_demo';
		
        //Получаем версию, она контролирует обмен данными с windows-сервером
        $version = $this->container->getParameter('app_version');
        
        //Если версия не пустая (а она такая для основной ветки), то добавляем слэш что бы структоризировать данные по версиям на стороне windows сервера
        $version = !empty($version) ? $version.'/' : '';
		
		//Массивы для формирования файлов для рендеринга
		$placeholderAssetsJson = [];
		
		//Добавляем в assets.json путь
		$placeholderAssetsJson['template'] = [
			'src'         => 'file:///C:/Apache24/htdocs/project/' . $version . $video->getId() . '/project.aepx',
			'composition' => 'Render',
			'outputExt'   => 'mp4'
		];
		
		//Начинаем создание очереди заказов видео рендеринга
		$newVideoRender = new VideoRender();
		$newVideoRender->setUsers( $user );
		$newVideoRender->setVideo( $video );
		$newVideoRender->setStatus( 'queued' );
		$newVideoRender->setType( !empty($demo) ? VideoConstants::ONE : VideoConstants::TWO);

		$temp = $this->renderFileTempOutput($video, $placeholderAssetsJson );
		if(!empty($temp)) {
			$placeholderAssetsJson = $temp;
		}
		
		$this->manager->persist( $newVideoRender );
		$this->manager->flush();
		
		
		//После занесения данных рендеринга в базу, переходим к наполнению папки заказа (создается public/project/ID)
		if ( ! empty( $placeholderAssetsJson ) ) {
			
			$currency = null;
			
			//Если у пользователя определена страна, то определяем курс валюты привязаный к стране (если их несколько то берем лишь первую)
			if(!empty($user->getCountry())){
				if($user->getCountry()->getCurrency()->isEmpty() == false){
					$currency = $user->getCountry()->getCurrency()->first();
					$price = $this->getPriceVideo($currency->getCodeISO(), $video);
				}
			}
			
			if(empty($currency)){
				$currency  = $this->manager->getRepository( 'App\Entity\Currency' )->findOneBy( [ 'defaultCurrency' => 1 ] );
				$price = $this->getPriceVideo($currency->getCodeISO(), $video);
			}
			
			//Последним этапом - создаем заказ
			$newOrder = new Order();
			$newOrder->setUsers( $user );
			$newOrder->setVideo( $video );
			$newOrder->addRender( $newVideoRender );
			$newOrder->setActive( 0 );
			$newOrder->setPrice( !empty($price) ? $price : $video->getPriceUsd() );
			$newOrder->setPriceCurrency( $currency->getName() );
			$newOrder->setCurrencyDefault( $video->getPriceUsd() * $currency->getCourse() );
			$newOrder->setFullName( $user->getFullName() );
			$newOrder->setPhone( $user->getPhone() );
			$newOrder->setEmail( $user->getEmail() );
			$newOrder->setCity( $user->getCity() );
			$newOrder->setChildSex( SexConstants::MALE );
			
			$this->manager->persist( $newOrder );

            $childName = $this->manager->getRepository( 'App\Entity\FirstName' )->findOneBy(['active' => 1, 'sex' => SexConstants::MALE ], [], 1);
            //Добавляем новое имя
            $newFirstName = new RelOrderFirstName();
            $newFirstName->setOrder( $newOrder );
            $newFirstName->setFirstName( $childName );
            $this->manager->persist( $newFirstName );
            $newOrder->addFirstName($newFirstName);

			$this->manager->flush();
			
			$fileSystem = new Filesystem();
			
			//Генерируем уникальное имя для рендеринга
			//projectID - это имя по которому будет рендерится и доступное после видео
			$projectID = $newVideoRender->getId() . $this->generateRandomString( 7 );
            if(!empty($version)){
                $projectID = substr($version, 0, -1).$projectID;
            }
			
			//Записываем в Assets идентификатор проекта
			$placeholderAssetsJson["uid"] = $projectID;
			$placeholderAssetsJson['output'] = 'file:///D:/temp/'.$projectID.'.mp4';
			
			$temp   = [];
			
			$temp[] = [
				'module' => '@nexrender/action-copy',
				'input'  => 'D:/temp/'.$projectID.'.mp4',
				'output' => 'C:/Apache24/htdocs/results/' . $version . $projectID . '.mp4'
			];
			
			//И записываем actions, в котором настраиваем конвертацию видео из тяжеловестного mov в легковестный mp4
			$placeholderAssetsJson['actions'] = [ 'postrender' => $temp ];
			
			//Копируем настроеный - render.js. Он содержит все основные настройки для рендеринга
			$fileSystem->copy( 'project/render.js', 'project/' . $newOrder->getId() . '/render.js', true );
			
			//Меняем воркер для рендера
            if(!empty($version)) {
                //Шлем запрос на основной сервер который управляет очередью
                $worker = $this->getWorker(2);
                if ( ! empty($worker)) {
                    $file    = file_get_contents('project/'.$newOrder->getId().'/render.js');
                    $content = str_replace('ip_change', $worker['ip'], $file);
                    $content = str_replace('port_change', $worker['port'], $content);
                    $content = str_replace('domains', $this->container->getParameter('app_domain'), $content);
                    file_put_contents('project/'.$newOrder->getId().'/render.js', $content);
                    //Заносим для истории данные
                    $newVideoRender->setWorker(json_encode($worker));
                }
            }else {
                $worker = $this->manager->getRepository('App\Entity\WorkerLoad')->findByWorkerFull();
                if ( ! empty($worker)) {
                    $file    = file_get_contents('project/'.$newOrder->getId().'/render.js');
                    $content = str_replace('ip_change', $worker->getIp(), $file);
                    $content = str_replace('port_change', $worker->getPort(), $content);
                    $content = str_replace('domains', $this->container->getParameter('app_domain'), $content);
                    file_put_contents('project/'.$newOrder->getId().'/render.js', $content);
                    $worker->setNumberOfTasks($worker->getNumberOfTasks() + 1);
                    $this->manager->flush($worker);
                    $newVideoRender->setWorker($worker->getId());
                }
            }
			
			//Создаем файл assets.json с новыми плейсхолдерами и настройками
			$fileSystem->appendToFile( 'project/' . $newOrder->getId() . '/assets.json', json_encode( $placeholderAssetsJson ) );
			
			//Записываем название рендеринга в заказ для идентификации ответа по рендерингу
			$newVideoRender->setProjectUid( $projectID );
			$this->manager->flush( $newVideoRender );
			
			//Полный путь к проекту
			$root        = $_SERVER['DOCUMENT_ROOT'];
			$pathProject = $root . '/project/' . $newOrder->getId();
			
		}
		
		$newOrder->addRender( $newVideoRender );
		$this->manager->flush( $newOrder );
		
		//Создаем архив
		$iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $pathProject ) );
		
		$zip = new \ZipArchive();
		
		$archiveName = 'project/' . $newOrder->getId() . '/' . $newVideoRender->getId() . '.zip';
		
		if ( $zip->open( $archiveName, \ZipArchive::CREATE ) === true ) {
			
			foreach ( $iterator as $key => $value ) {
				if ( file_exists( $key ) && is_file( $key ) ) {
					if($value->getExtension() !== 'zip' and $value->getExtension() !== 'mp4') {
						$zip->addFile( $key, $value->getFilename() );
					}
				}
			}
			
			$zip->close();
		}
		
		$this->uploadArchiveToServer( $request, $newVideoRender->getId(), $archiveName );
		
		return (object)['order' => $newOrder->getId(), 'video' => $video->getSlug()];
	}
	
	public function uploadingPlaceholderImage( $file )
	{
		
		$media    = $this->saveFileMediaBundle( $file );
		$provider = $this->container->get( $media->getProviderName() );
		$url      = $provider->generatePublicUrl( $media, 'reference' );
		
		return array( 'url' => $url, 'media' => $media->getId() );
	}
	
	/*
	 * Uploading files sonata media
	 * */
	public function saveFileMediaBundle( $file, $context = 'placeholder', $type = null )
	{
		$media = new Media();
		$media->setContext( $context );
		if ( ! empty( $type ) ) {
			$media->setProviderName( 'sonata.media.provider.' . $type );
		} else {
			$media->setProviderName( 'sonata.media.provider.image' );
		}
		$media->setBinaryContent( $file );
		$this->manager->persist( $media );
		$this->manager->flush();
		
		return $media;
	}
	
	/*
	 * Generate key
	 */
	public function generateRandomString( $length = 16, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' )
	{
		$charactersLength = strlen( $characters );
		$randomString     = '';
		for ( $i = 0; $i < $length; $i ++ ) {
			$randomString .= $characters[ rand( 0, $charactersLength - 1 ) ];
		}
		
		return $randomString;
	}

	public function youTubeUploadingVideo( $path, $uid, $name, $email, Order $orders, $youTube = true, $render )
	{
		//Забираем youtube паблик
		//$key = file_get_contents( $this->container->getParameter( 'kernel.project_dir' ) . '/public/youtube_key.txt' );
		$setting = $this->manager->getRepository('App\Entity\Setting')->find(1);
		
		$version = $this->container->getParameter('app_version');

		/*$application_name = $setting->getApiYoutubeApplicationName();
		$client_secret    = $setting->getApiYoutubeClientSecret();
		$client_id        = $setting->getApiYoutubeClientId();
		$scope            = array(
			'https://www.googleapis.com/auth/youtube.upload',
			'https://www.googleapis.com/auth/youtube',
			'https://www.googleapis.com/auth/youtubepartner'
		);
		
		//Достаем последнее видео
		$lastVideo = $orders->getVideo()->getRender()->last();*/
		
		$name_video = 'project/' . $orders->getId() . '/' . $uid . '.mp4';
		
		//Скачиваем видео и ложим в папку заказа
		$fp = fopen($name_video, 'w');
		$ch = curl_init($path. '.mp4');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		$data = curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		
		//Другие размеры есть только в полной версии видео
		if($youTube) {
			$name_video480 = 'project/' . $orders->getId() . '/' . $uid . '_480p.mp4';
			//$name_video720 = 'project/' . $orders->getId() . '/' . $uid . '_720p.mp4';
			
			$fp480 = fopen( $name_video480, 'w' );
			$ch480 = curl_init( $path . '_480p.mp4' );
			curl_setopt( $ch480, CURLOPT_FILE, $fp480 );
			curl_exec( $ch480 );
			curl_close( $ch480 );
			fclose( $fp480 );
			
			/*$fp720 = fopen( $name_video720, 'w' );
			$ch720 = curl_init( $path . '_720p.mp4' );
			curl_setopt( $ch720, CURLOPT_FILE, $fp720 );
			curl_exec( $ch720 );
			curl_close( $ch720 );
			fclose( $fp720 );*/
		}
		/*if($youTube){
			//Меняем на processing - значит что мы загружаем видео на Ютуб
			$render->setStatus( 'processing' );
			$render->setEndAt( new \DateTime( 'NOW' ) );
			//Обновляем количество задач у воркера
			$render->getWorker()->setNumberOfTasks( $render->getWorker()->getNumberOfTasks() - 1 );
			$this->manager->flush();
		}else{*/
			$render->setStatus( 'finished' );
			$render->setEndAt( new \DateTime( 'NOW' ) );
			//Обновляем количество задач у воркера
            if(!empty($version)){
                //Удаляем очередь на основном сервере
                $this->ridQueueWorker($render->getWorker());
            }else {
                $worker = $this->manager->getRepository('App\Entity\WorkerLoad')->find($render->getWorker());
                if(!empty($worker)){
                    $worker->setNumberOfTasks($worker->getNumberOfTasks() - 1);
                }
            }
			$this->manager->flush();
		//}
		return $name_video;
		//Не на все видео нужна выгрузка (демка не грузится)
//		if ( $youTube ) {
//            try {
//                $path = $name_video;
//
//                $videoPath        = $path;
//                $videoTitle       = "#" . $orders->getId() . ' ' . $orders->getVideo()->getTitle();
//                $videoDescription = $orders->getVideo()->getTitle() . " для $name ($email)";
//                $videoCategory    = "22";
//                $videoTags        = array( $orders->getVideo()->getTitle(), $orders->getId(), "$name ($email)" );
//
//                // Client init
//                $client = new \Google_Client();
//                $client->setApplicationName( $application_name );
//                $client->setClientId( $client_id );
//                $client->setAccessType( 'offline' );
//                $client->setAccessToken( $key );
//                $client->setScopes( $scope );
//                $client->setClientSecret( $client_secret );
//                /*
//                        $client->setClassConfig('Google_IO_Curl', 'options',
//                            array(
//                                CURLOPT_CONNECTTIMEOUT => 30,
//                                CURLOPT_TIMEOUT => 30
//                            )
//                        );
//                */
//                if ( $client->getAccessToken() ) {
//
//                    /**
//                     * Check to see if our access token has expired. If so, get a new one and save it to file for future use.
//                     */
//                    if ( $client->isAccessTokenExpired() ) {
//                        $newToken = $client->getAccessToken();
//                        $client->refreshToken( $newToken['refresh_token'] );
//                        file_put_contents( $this->container->getParameter( 'kernel.project_dir' ) . '/public/youtube_key.txt', json_encode( $client->getAccessToken() ) );
//                    }
//
//                    $youtube = new \Google_Service_YouTube( $client );
//
//                    // Create a snipet with title, description, tags and category id
//                    $snippet = new \Google_Service_YouTube_VideoSnippet();
//                    $snippet->setTitle( $videoTitle );
//                    $snippet->setDescription( $videoDescription );
//                    $snippet->setCategoryId( $videoCategory );
//                    $snippet->setTags( $videoTags );
//
//                    // Create a video status with privacy status. Options are "public", "private" and "unlisted".
//                    $status = new \Google_Service_YouTube_VideoStatus();
//                    $status->setPrivacyStatus( 'unlisted' );
//
//                    // Create a YouTube video with snippet and status
//                    $video = new \Google_Service_YouTube_Video();
//                    $video->setSnippet( $snippet );
//                    $video->setStatus( $status );
//
//                    // Size of each chunk of data in bytes. Setting it higher leads faster upload (less chunks,
//                    // for reliable connections). Setting it lower leads better recovery (fine-grained chunks)
//                    $chunkSizeBytes = 1 * 1024 * 1024;
//
//                    // Setting the defer flag to true tells the client to return a request which can be called
//                    // with ->execute(); instead of making the API call immediately.
//                    $client->setDefer( true );
//
//                    // Create a request for the API's videos.insert method to create and upload the video.
//                    $insertRequest = $youtube->videos->insert( "status,snippet", $video );
//
//                    // Create a MediaFileUpload object for resumable uploads.
//                    $media = new \Google_Http_MediaFileUpload(
//                        $client,
//                        $insertRequest,
//                        'video/*',
//                        null,
//                        true,
//                        $chunkSizeBytes
//                    );
//
//                    $media->setFileSize( filesize( $videoPath ) );
//
//                    // Read the media file and upload it chunk by chunk.
//                    $status = false;
//                    $handle = fopen( $videoPath, "rb" );
//                    while ( ! $status && ! feof( $handle ) ) {
//                        $chunk  = fread( $handle, $chunkSizeBytes );
//                        $status = $media->nextChunk( $chunk );
//                    }
//
//                    fclose( $handle );
//
//                    /**
//                     * Video has successfully been upload, now lets perform some cleanup functions for this video
//                     */
//                    if ( $status->status['uploadStatus'] == 'uploaded' ) {
//                        // Actions to perform for a successful upload
//                        if ( ! empty( $status['id'] ) ) {
//                            $lastVideo->setYoutubeLink( 'https://youtu.be/' . $status['id'] );
//                            $lastVideo->setStatus( 'finished' );
//                            $this->manager->flush( $lastVideo );
//                        }
//                    }
//
//                    // If you want to make other calls after the file upload, set setDefer back to false
//                    $client->setDefer( true );
//
//                    return $lastVideo->getYoutubeLink();
//                }
//            } catch (\Exception $e) {
//                $this->logger->info('youtube-test '.$e->getMessage() );
//                return false;
//            }
//		}
	}
	
	public function renderFileTempOutput(Video $video, $placeholderAssetsJson, $involved = array() ){
		
		if($video->getRenderFile()->isEmpty() == false){
			foreach ($video->getRenderFile()->toArray() as $key => $file) {
				if(in_array($file->getFileName(), $involved) == false) {
					$tempFile              = [];
					$tempFile["type"]      = $file->getFileExt();
					$tempFile["src"]       = $file->getFile();
					$tempFile["layerName"] = $file->getFileName();
					$placeholderAssetsJson['assets'][] = $tempFile;
				}
			}
		}
		
		return $placeholderAssetsJson;
	}
	
	public function renderFileHistory( Video $video, $pathZIP, $persist = false )
	{
		//PATH приходит типа /upload/media... для ZipArchive первый слэш не нужен, по этому сразу его убираем
		$pathZIP = substr($pathZIP, 1);
		//Сначала удаляем старые файлы видео архивов
		if($video->getRenderFile()->isEmpty() == false) {
			foreach ( $video->getRenderFile()->toArray() as $render ) {
				$r = $this->manager->getRepository( 'App\Entity\VideoRenderFile' )->find( $render->getId() );
				$this->manager->remove( $r );
				
			}
		}
		
		//Заходим в массив и добавляем в базу новые файлы
		$zip = new \ZipArchive;
		
		if ( $zip->open( $pathZIP ) ) {
			
			for ( $i = 0; $i < $zip->numFiles; $i ++ ) {
				
				$ext = explode('.', $zip->getNameIndex( $i ));
				
				if($ext[1] != 'aep' and $ext[1] != 'aepx') {
					$newFile = new VideoRenderFile();
					$newFile->setFile( "file:///C:/Apache24/htdocs/project/" . $video->getId() . "/" . $zip->getNameIndex( $i ) );
					if ( ! empty( $ext[1] ) ) {
						if ( $ext[1] == 'mp3' ) {
							$newFile->setFileExt( 'audio' );
						} elseif ( $ext[1] == 'mp4' ) {
							$newFile->setFileExt( 'video' );
						} else {
							$newFile->setFileExt( 'image' );
						}
					}
					$newFile->setFileName( $zip->getNameIndex( $i ) );
					
					$this->manager->persist( $newFile );
					
					$video->addRenderFile( $newFile );
					
					if ( $persist ) {
						$this->manager->flush();
					}
				}
			}
		}
		$zip->close();
		
		return $video;
	}
	
	/*
	 * Запрашиваем данные по воркеру на который запускать видео
	 */
	public function getWorker($type)
    {
        $domainMain = $this->container->getParameter('app_domain_main');
        $url = $domainMain."/api/v1/worker?type=".$type;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);
        $array = json_decode($result, true);
        if($array['success'] == true){
            return $array['worker'];
        }
        return false;
    }
    
    /*
     * Убираем активную очередь на воркер после окончания рендеринга
     */
    public function ridQueueWorker($json)
    {
        $json = json_decode($json, true);
        $domainMain = $this->container->getParameter('app_domain_main');
        $data_json = json_encode(array('worker'=>$json['id']));
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $domainMain.'/api/v1/worker');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_json)));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

}