<?php
namespace App\Application\Sonata\AdminBundle\Controller;

use App\Constants\ActiveConstants;
use Sonata\AdminBundle\Controller\CRUDController as BaseController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class CRUDController extends BaseController
{
    /**
     * @param ProxyQueryInterface $selectedModelQuery
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function batchActionActive(ProxyQueryInterface $selectedModelQuery, Request $request = null)
    {
        
        $modelManager = $this->admin->getModelManager();
        
        $selectedModels = $selectedModelQuery->execute();
        
        try {
            foreach ($selectedModels as $selectedModel) {
                $selectedModel->setActive(ActiveConstants::ACTIVE);
                $modelManager->update($selectedModel);
            }
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', $this->trans('flash_batch_active_error') );
            
            return new RedirectResponse(
                $this->admin->generateUrl(
                    'list',
                    [
                        'filter' => $this->admin->getFilterParameters(),
                    ]
                )
            );
        }
        
        $this->addFlash('sonata_flash_success', $this->trans('flash_batch_active_success') );
        
        return new RedirectResponse(
            $this->admin->generateUrl(
                'list',
                [
                    'filter' => $this->admin->getFilterParameters(),
                ]
            )
        );
    }
    
    /**
     * @param ProxyQueryInterface $selectedModelQuery
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function batchActionNotActive(ProxyQueryInterface $selectedModelQuery, Request $request = null)
    {
        $modelManager = $this->admin->getModelManager();
    
        $selectedModels = $selectedModelQuery->execute();
    
        try {
            foreach ($selectedModels as $selectedModel) {
                $selectedModel->setActive(ActiveConstants::INACTIVE);
                $modelManager->update($selectedModel);
            }
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', $this->trans('flash_batch_not_active_error') );
        
            return new RedirectResponse(
                $this->admin->generateUrl(
                    'list',
                    [
                        'filter' => $this->admin->getFilterParameters(),
                    ]
                )
            );
        }
    
        $this->addFlash('sonata_flash_success', $this->trans('flash_batch_not_active_success') );
    
        return new RedirectResponse(
            $this->admin->generateUrl(
                'list',
                [
                    'filter' => $this->admin->getFilterParameters(),
                ]
            )
        );
    }
}
