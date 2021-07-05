<?php

namespace App\Controller;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Constants\ActiveConstants;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
	
	/**
	 * @Route("/admin/file_upload", name="admin_file_upload", methods={"POST"})
	 */
	public function fileUploadHandler(Request $request) {
		
		$output = null;
		
		$file = $request->files->get('file');
		
		if(!empty($file)) {
			$media = new Media();
			$media->setContext( 'phrases_audio' );
			$media->setProviderName( 'sonata.media.provider.audio.phrase' );
			$media->setBinaryContent( $file );
			$this->getDoctrine()->getManager()->persist( $media );
			$this->getDoctrine()->getManager()->flush();
			
			$output['uploaded'] = true;
			$output['fileName'] = $media->getName();
			$output['media'] = $media->getId();
		}
		
		return new JsonResponse($output);
	}
	
	/**
	 * @Route("/admin/tags.json", name="tags_list", defaults={"_format": "json"})
	 */
	public function tags()
	{
		$tags = $this->getDoctrine()->getRepository('App\Entity\Tag')->findBy([], ['name' => 'ASC']);
		
		return $this->render('form/tags.json.twig', ['tags' => $tags]);
	}

    /**
     * @Route("/admin/notify", name="admin_file_notify", methods={"POST"})
     */
    public function notify() {

        $output = null;

        $countReview = $this->getDoctrine()->getRepository('App\Entity\Review')->count(['active' => ActiveConstants::REVIEW_NOT_PROCESSED]);
        $countFeedback = $this->getDoctrine()->getRepository('App\Entity\Feedback')->count(['active' => ActiveConstants::FEEDBACK_NOT_PROCESSED]);

        $output['review'] = $countReview;
        $output['review_path'] = '/admin/app/review/list';
        $output['feedback'] = $countFeedback;
        $output['feedback_path'] = '/admin/app/feedback/list';

        return new JsonResponse($output);
    }
}
