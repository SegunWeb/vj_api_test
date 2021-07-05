<?php

namespace App\Admin;

use App\Constants\VideoConstants;
use App\Form\Type\ParagraphType;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class VideoRenderAdmin extends AbstractAdmin
{
	
	protected function configureFormFields( FormMapper $formMapper )
	{
		
		$formMapper
            ->add( 'id', ParagraphType::class, [
                'required' => false,
                'label'    => "ID",
                "disabled" => true,
            ] )
			->add( "type", ChoiceType::class, [
				'label'    => $this->trans( 'object.order_type' ),
				'choices'  => VideoConstants::loadVideoType(),
				'required' => false
			] )
			->add( 'status', TextType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.render_status' ),
				"disabled" => true
			] )
			->add( 'projectUid', ParagraphType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.render_name' ),
				"disabled" => true,
				'attr'     => [
					"status" => $this->getSubject()->getStatus(),
					"type"   => $this->getSubject()->getType(),
					"order"  => $this->getSubject()->getOrder()->getId()
				]
			] )
			->add( 'youtubeLink', TextType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.render_youtube_link' )
			] )
			->add( 'startAt', DatePickerType::class, [
				'label'    => $this->trans( 'object.render_start' ),
				'format'   => 'dd.MM.yyyy, HH:mm',
				"disabled" => true
			] )
			->add( 'endAt', DatePickerType::class, [
				'label'    => $this->trans( 'object.render_finish' ),
				'format'   => 'dd.MM.yyyy, HH:mm',
				"disabled" => true
			] );
	}
	
}