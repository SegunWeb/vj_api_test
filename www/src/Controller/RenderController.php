<?php

namespace App\Controller;

use App\Entity\VideoRender;
use App\Service\MailTemplate;
use App\Service\RenderService;
use App\Constants\VideoConstants;
use App\Constants\MailEventConstants;
use App\Service\TurboSmsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RenderController extends AbstractController
{
	/**
	 * @Route("/render/status", name="render_status", methods={"GET", "POST"})
	 */
	public function index( Request $request, RenderService $service, UrlGeneratorInterface $generator, TurboSmsService $turboSmsService, MailTemplate $template )
	{
		if ( ! empty( $request->request->get( 'uid' ) ) ) {
            
            //Получаем версию, она контролирует обмен данными с windows-сервером
            $version = $this->getParameter('app_version');
            
            //Если версия не пустая (а она такая для основной ветки), то добавляем слэш что бы структоризировать данные по версиям на стороне windows сервера
            $version = !empty($version) ? $version.'/' : '';
			
			$uid = $request->request->get( 'uid' );
			
			$state = $request->request->get( 'state' );
			
			$render = $this->getDoctrine()->getRepository( VideoRender::class )->findOneBy( [ 'projectUid' => $uid ] );
			
			
			if ( ! empty( $render ) ) {
				
				//Когда рендеринг финиширует
				if ( $state == 'finished' ) {
					//Если демо то сразу финишируем, если полное - то грузим на ютуб
					if ( $render->getType() == VideoConstants::TWO ) {
						
						$order = $render->getOrder();
						$order->setUpdatedAt(new \DateTime( 'NOW' ) );
						$this->getDoctrine()->getManager()->flush( $order );

						$youtubeLink = $service->youTubeUploadingVideo( $this->getParameter( 'win_server' ) . '/results/' . $version . $uid, $uid, $render->getOrder()->getFullName(), $render->getOrder()->getEmail(), $render->getOrder(), true, $render );
						
						if ( $render->getUsers()->getSubscribed() != 1 ) {
							
							$unsubscribe = base64_encode($order->getUsers()->getId().'|'.$order->getUsers()->getEmail().'|'.$order->getUsers()->getCreatedAt()->format('d.m.Y'));
							
							//Переделать после того как будут известны поля отправки
							$object = array(
								'user_name'  => $render->getUsers()->getFullName(),
								'user_email' => $render->getUsers()->getEmail(),
								'url'        => $request->getUriForPath('/'.$youtubeLink),
								'url_site'   => $request->getUriForPath( $generator->generate( 'video_full_viewing', [ 'id' => $render->getOrder()->getId(), 'slug' => $order->getVideo()->getSlug() ] ) )
							);
							
							$template->sendMailMessages( MailEventConstants::FULL_VIDEO_READINESS, MailEventConstants::FULL_VIDEO_READINESS_VALUES, (object) $object, $render->getOrder()->getEmail(), $unsubscribe );
						}
						
						if(!empty($render->getOrder()->getPhone())){
							
							$template->sendSmsMessages($turboSmsService, MailEventConstants::SMS_FULL_VIDEO_READINESS_VALUES, (object) $object, $render->getOrder()->getPhone());
							
						}

						/* Раскоментировать при включении выгрузки на ютуб
						 * if($youtubeLink === false){
                            $render->setStatus( 'finished' );
                            $render->setEndAt( new \DateTime( 'NOW' ) );
                            $this->getDoctrine()->getManager()->flush( $render );
                        }*/
                        
                        $this->messageRemoveTemporaryFolder( $render->getProjectUid() );
						
					} else {
						
						$order = $render->getOrder();
						$order->setUpdatedAt(new \DateTime( 'NOW' ) );
						$this->getDoctrine()->getManager()->flush( $order );
						
						$service->youTubeUploadingVideo( $this->getParameter( 'win_server' ) . '/results/' . $version . $uid, $uid, $render->getOrder()->getFullName(), $render->getOrder()->getEmail(), $render->getOrder(), false, $render );
						
						if ( $render->getUsers()->getSubscribed() != 1 ) {
							
							$unsubscribe = base64_encode($order->getUsers()->getId().'|'.$order->getUsers()->getEmail().'|'.$order->getUsers()->getCreatedAt()->format('d.m.Y'));
							
							//Переделать после того как будут известны поля отправки
							$object = array(
								'user_name'  => $render->getUsers()->getFullName(),
								'user_email' => $render->getUsers()->getEmail(),
								'url'        => $request->getUriForPath( $generator->generate( 'video_demo_viewing', [ 'id' => $render->getOrder()->getId(), 'slug' => $render->getOrder()->getVideo()->getSlug() ] ) ),
								'password'   => null
							);
							
							$template->sendMailMessages( MailEventConstants::WILLINGNESS_DEMO, MailEventConstants::WILLINGNESS_DEMO_VALUES, (object) $object, $render->getOrder()->getEmail(), $unsubscribe );
						}
						
						$this->messageRemoveTemporaryFolder( $render->getProjectUid() );
					}
				} elseif( $state == 'error') {
                    //Обновляем количество задач у воркера
                    if(!empty($version)){
                        $service->ridQueueWorker($render->getWorker());
                    }else {
                        $worker = $this->getDoctrine()->getRepository('App\Entity\WorkerLoad')->find($render->getWorker());
                        $worker->setNumberOfTasks($worker->getNumberOfTasks() - 1);
                    }
					$render->setStatus( $request->request->get( 'state' ) ?: '' );
					$this->getDoctrine()->getManager()->flush();
					//Переделать после того как будут известны поля отправки
					$object = array(
						'order_id' => $render->getOrder()->getId(),
						'user_id' => $render->getOrder()->getUsers()->getId(),
						'video_id' => $render->getVideo()->getId(),
						'user_email' => $render->getOrder()->getUsers()->getEmail(),
						'video_url' => $request->getUriForPath($generator->generate('video', [ 'id' => $render->getVideo()->getId() ]))
					);
					$template->sendMailMessages( MailEventConstants::VIDEO_RENDER_ERROR, MailEventConstants::VIDEO_RENDER_ERROR_VALUES, (object) $object );
				} else {
					$render->setStatus( $request->request->get( 'state' ) ?: '' );
					$this->getDoctrine()->getManager()->flush( $render );
				}
			}
		}
		
		return new Response( 'Ok', '200' );
	}
	
	public function messageRemoveTemporaryFolder( $projectUid )
    {
        //Получаем версию, она контролирует обмен данными с windows-сервером
        $version = $this->getParameter('app_version');
		
		$post = [
		    'projectUid' => $projectUid,
            'version' => $version
        ];
		
		$url = $this->getParameter('win_server') . "/api/remove.php";
		
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
		curl_exec( $ch );
		curl_close( $ch );
	}
	
	/**
	 * @Route("/render/status/get", name="render_status_get", methods={"POST"})
	 */
	public function getStatus( Request $request )
	{
		if ( ! empty( $request->request->get( 'renderID' ) ) ) {
			
			$id = $request->request->get( 'renderID' );
			
			$render = $this->getDoctrine()->getRepository( 'App\Entity\VideoRender' )->find( $id );
			
			$type = $render->getType() == 1 ? 'demo' : 'full';
			$url = $render->getType() == 1 ? $this->generateUrl('video_render_processing', ['id' => $render->getOrder()->getId(), 'slug' => $render->getVideo()->getSlug()]) : $this->generateUrl('full_video_render_processing', ['id' => $render->getOrder()->getId(), 'slug' => $render->getVideo()->getSlug()]);
			
			if($render->getStatus() == 'error'){
				$type = 'error';
				$url = $this->generateUrl('video_error_rendering');
			}
			
			return new JsonResponse( [ 'code' => 200, 'status' => $render->getStatus(), 'type' => $type, 'url' => $url ], 200 );
			
		}
		
		return new JsonResponse( [ 'code' => 400 ], 200 );
	}
	
}
