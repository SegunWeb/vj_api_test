<?php

namespace App\Service;

use App\Constants\ActiveConstants;
use Doctrine\ORM\EntityManager;
use App\Constants\MailEventConstants;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OrderService
{
	
	protected $manager;
	
	protected $container;
	
	protected $router;
	
	public function __construct( EntityManager $manager, ContainerInterface $container, RouterInterface $router )
	{
		$this->router    = $router;
		$this->manager   = $manager;
		$this->container = $container;
	}
	
	public function order($path)
	{
		$template = $this->container->get( MailTemplate::class );
		
		$setting = $this->manager->getRepository( 'App\Entity\Setting' )->find( 1 );
		
		//Получаем заказы которые не оплачены и уже было отправлено одно сообщение
		$ordersOfThreeDay = $this->manager->getRepository( 'App\Entity\Order' )->findByOrderNotPaid( 1 );
		if ( ! empty( $ordersOfThreeDay ) ) {
			foreach ( $ordersOfThreeDay as $order ) {
				
				//Меняем дату что бы отталкиваться от нее насколько дана скидка
				$order->setUpdatedAt( new \DateTime( date( 'd.m.Y 00:00:00' ) ) );
				$order->setSentEmail( 2 );
				$this->manager->flush( $order );
				
				if ( $order->getUsers()->getSubscribed() != 1 ) {
					
					$unsubscribe = base64_encode($order->getUsers()->getId().'|'.$order->getUsers()->getEmail().'|'.$order->getUsers()->getCreatedAt()->format('d.m.Y'));
					
					$object = array(
						'discount'   => $setting->getDiscountEmailMarketing(),
						'user_email' => $order->getUsers()->getEmail(),
						'user_name' => $order->getFullName(),
						'video_url'  => $path.$this->router->generate( 'video_demo_viewing', [ 'id' => $order->getId(), 'slug' => $order->getVideo()->getSlug() ])
					);
					
					$template->sendMailMessages( MailEventConstants::DISCOUNT_LETTER, MailEventConstants::DISCOUNT_LETTER_VALUES, (object) $object, $order->getUsers()->getEmail(), $unsubscribe );
				}
			}
		}
		
		//Получаем заказы которые не оплачены и не было еще ни одно e-mail уведомление
		$ordersOfOneDay = $this->manager->getRepository( 'App\Entity\Order' )->findByOrderNotPaid( 0 );
		if ( ! empty( $ordersOfOneDay ) ) {
			foreach ( $ordersOfOneDay as $order ) {
				
				//Меняем дату что бы отталкиваться от нее насколько дана скидка
				$order->setSentEmail( 1 );
				$this->manager->flush( $order );
				
				if ( $order->getUsers()->getSubscribed() != 1 ) {
					
					$unsubscribe = base64_encode($order->getUsers()->getId().'|'.$order->getUsers()->getEmail().'|'.$order->getUsers()->getCreatedAt()->format('d.m.Y'));
					
					$object = array(
						'discount'   => $setting->getDiscountEmailMarketing(),
						'user_email' => $order->getUsers()->getEmail(),
						'user_name' => $order->getFullName(),
						'video_url'  => $path.$this->router->generate( 'video_demo_viewing', [ 'id' => $order->getId(), 'slug' => $order->getVideo()->getSlug() ] )
					);
					
					$template->sendMailMessages( MailEventConstants::EMAIL_MARKETING_ONE_MESSAGE, MailEventConstants::EMAIL_MARKETING_ONE_MESSAGE_VALUES, (object) $object, $order->getUsers()->getEmail(), $unsubscribe );
				}
			}
		}
	}

	public function clearOrder($path){
		
	    //Получаем оплаченные товары
        $paidOrders = $this->manager->getRepository('App\Entity\Order')->findByOrderPaidWithMonth();
        
        if(!empty($paidOrders)){
        	$this->removeFilesOrderList( $paidOrders, $path );
        }
        
        //Получаем не оплаченные товары
        $unpaidOrders = $this->manager->getRepository('App\Entity\Order')->findByOrderNotPaidWithMonth();
        
		if(!empty($unpaidOrders)){
			$this->removeFilesOrderList( $unpaidOrders, $path );
		}
        
    }
    
    public function removeFilesOrderList( $orders, $pathDirectoryProject ){
		
	    $filesystem = new Filesystem();
		
	    if(!empty($orders)){
	    	
		    foreach ($orders as $order) {
		    	
			    //Если есть рендеринг видео
			    if ( $order->getRender()->isEmpty() == false ) {
				    foreach ( $order->getRender()->toArray() as $render ) {
				    	
					    //Проверяем есть ли плейсхолдеры
					    if ( $render->getPlaceholder()->isEmpty() == false ) {
					    	
						    foreach ($render->getPlaceholder()->toArray() as $placeholder){
							    //Проверка на наличие картинки
							    if(!empty($placeholder->getImage())) {
								    //Удаляем объект
								    $this->manager->remove($placeholder->getImage());
							    }
							    //Проверка на наличие массива изображений
							    if($placeholder->getImageMany()->isEmpty() == false){
								    foreach ($placeholder->getImageMany()->toArray() as $imageMany){
									    //Удаляем картинку
									    if(!empty($imageMany->getImage())){
										    //Удаляем объект
										    $this->manager->remove($imageMany->getImage());
										
									    }
								    }
							    }
							
							    //Проверка на наличие видео
							    if(!empty($placeholder->getVideo())){
								    //Удаляем объект
								    $this->manager->remove($placeholder->getVideo());
							    }
							
							    //Проверка на наличие лица видеооткрытки
							    if(!empty($placeholder->getImageFace())){
								    //Удаляем объект
								    $this->manager->remove($placeholder->getImageFace());
							    }
							
							    //Проверка на наличие рта видеооткрытки
							    if(!empty($placeholder->getImageMouth())){
								    //Удаляем объект
								    $this->manager->remove($placeholder->getImageMouth());
							    }
							
						    }
					    }
				    }
			    }
			    //Ставим метку удаленных файлов
			    $order->setRemoveFiles(1);
			    
			    //удаляем папку заказа
			    $this->deleteAll($pathDirectoryProject.'project/'.$order->getId());
		    }
		    $this->manager->flush();
	    }
    }
	
    public function deleteAll($dir) {
		if(is_dir($dir)) {
			$it    = new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS );
			$files = new \RecursiveIteratorIterator( $it,
				\RecursiveIteratorIterator::CHILD_FIRST );
			foreach ( $files as $file ) {
				if ( $file->isDir() ) {
					rmdir( $file->getRealPath() );
				} else {
					unlink( $file->getRealPath() );
				}
			}
			rmdir( $dir );
		}
    }
}