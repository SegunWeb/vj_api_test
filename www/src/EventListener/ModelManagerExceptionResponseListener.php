<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sonata\AdminBundle\Exception\ModelManagerException;

class ModelManagerExceptionResponseListener
{
    private $session;
    private $router;
    private $em;

    public function __construct(SessionInterface $session, UrlGeneratorInterface $router, EntityManagerInterface $em)
    {
        $this->session = $session;
        $this->router = $router;
        $this->em = $em;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // get the exception
        $exception =  $event->getException();
        // we proceed only if it is ModelManagerException
        if (!$exception instanceof ModelManagerException) {
            return;
        }

        // get the route and id
        // if it wasn't a delete route we don't want to proceed
        $request = $event->getRequest();
        $route = $request->get('_route');
        $id = $request->get('id');

        if (substr($route, -6) !== 'delete' && substr($route, -6) !== '_batch') {
            return;
        }

        $object = '';
        if(strpos($route, '_delete')) {
            $route = str_replace('delete', 'edit', $route);

            // get the message
            // we proceed only if it is the desired message
            $message = $exception->getMessage();
            $failure = 'Failed to delete object: ';
            if (strpos($message, $failure) < 0) {
                return;
            }

            // get the object that can't be deleted
            $entity = str_replace($failure, '', $message);
            $repository = $this->em->getRepository($entity);
            $object = $repository->findOneById($id);
        } else if(strpos($route, '_batch')) {
            $route = str_replace('_batch', '_list', $route);

            // get the message
            // we proceed only if it is the desired message
            $message = $exception->getPrevious()->getMessage();
            $failure = 'Cannot delete or update a parent row: a foreign key constraint fails';
            if (strpos($message, $failure) < 0) {
                return;
            }
        }

        $this->session->getFlashBag()
                      ->add(
                          'sonata_flash_error',
                          sprintf('The item %s can not be deleted because other items depend on it.', $object)
                      )
        ;

        // redirect to the edit form of the object
        $url = $this->router->generate($route, ['id' => $id]);
        $response = new RedirectResponse($url);
        $event->setResponse($response);
    }
}