<?php

namespace App\Controller;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Constants\ActiveConstants;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
	
	/**
	 * @Route("/api/v1/worker", name="api_v1_worker_get", methods={"GET"})
	 */
	public function workerGet(Request $request)
    {
        //Параметры ответа на запрос
        $success = false; $worker = null;
        
        //Получаем тип рендеринга
        $type = $request->query->getInt('type');
        
        //Версия пуста для основного сервера
        $version = $this->getParameter('app_version');
        
        if(empty($version) and !empty($type)) {
            if($type == 1){
                $workerLoad = $this->getDoctrine()->getRepository('App\Entity\WorkerLoad')->findByWorkerDemo();
                if(!empty($workerLoad)){
                    
                    //Добавляем единицу рендеринга
                    $workerLoad->setNumberOfTasks( $workerLoad->getNumberOfTasks() + 1);
                    $this->getDoctrine()->getManager()->flush();
    
                    //Добавляем данные для ответа
                    $worker = array('id' => $workerLoad->getId(), 'ip' => $workerLoad->getIp(), 'port' => $workerLoad->getPort()); $success = true;
                }
            }else{
                $workerLoad = $this->getDoctrine()->getRepository('App\Entity\WorkerLoad')->findByWorkerFull();
                if(!empty($workerLoad)){
    
                    //Добавляем единицу рендеринга
                    $workerLoad->setNumberOfTasks( $workerLoad->getNumberOfTasks() + 1);
                    $this->getDoctrine()->getManager()->flush();
    
                    //Добавляем данные для ответа
                    $worker = array('id' => $workerLoad->getId(), 'ip' => $workerLoad->getIp(), 'port' => $workerLoad->getPort()); $success = true;
                }
            }
        }
        
        return new JsonResponse(['success' => $success, 'worker' => $worker], 200);
	}
    
    /**
     * @Route("/api/v1/worker", name="api_v1_worker_put", methods={"PUT"})
     */
    public function workerPut()
    {
        //Параметры ответа на запрос
        $success = false; $data = null;
    
        //Получаем JSON строку и переводим в массив
        try {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
        } catch (\Exception $e) {
            //Сообщение для ошибки
            $success = false;
        }
        
        //Проверяем что бы был запрос
        if(!empty($data)){
            //Ищем воркер который нужно освободить от очереди
            $workerLoad = $this->getDoctrine()->getRepository('App\Entity\WorkerLoad')->findOneBy(['id' => $data['worker']]);
            if(!empty($workerLoad)){
                //Снимаем единицу очереди
                $workerLoad->setNumberOfTasks( $workerLoad->getNumberOfTasks() - 1 );
                $this->getDoctrine()->getManager()->flush();
                
                //Параметр ответа - успех!
                $success = true;
            }
            
        }
    
        return new JsonResponse(['success' => $success], 200);
    }
}
