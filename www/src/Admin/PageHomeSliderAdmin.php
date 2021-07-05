<?php

namespace App\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PageHomeSliderAdmin extends AbstractAdmin
{
	protected $datagridValues = [
		'_page'       => 1,
		'_sort_order' => 'ASC',
		'_sort_by'    => 'position',
	];
	
	protected function configureFormFields( FormMapper $formMapper )
	{
		
		$formMapper
			->add( 'image',ModelListType::class, array( 'label' => $this->trans( 'object.home_image_header' ), 'required' => false), array( 'link_parameters' => array( 'context' => 'home_image_header' ) ) )
			->add( 'imageTablet',ModelListType::class, array( 'label' => $this->trans( 'object.home_image_tablet_header' ), 'required' => false), array( 'link_parameters' => array( 'context' => 'home_image_tablet_header' ) ) )
			->add( 'imageMobile',ModelListType::class, array( 'label' => $this->trans( 'object.home_image_mobile_header' ), 'required' => false), array( 'link_parameters' => array( 'context' => 'home_image_mobile_header' ) ) )
			->add( 'imageCircle',ModelListType::class, array( 'label'    => $this->trans( 'object.home_image_header_circle' ),'required' => false ), array( 'link_parameters' => array( 'context' => 'home_image_header_circle' ) ) )
			->add( 'homeTitleHeader', TextType::class, ['required'   => false, 'empty_data' => '', 'help' => $this->trans('object.home_header_help'), 'label' => $this->trans( 'object.home_title_header' ) ] )
			->add( 'homeDescriptionHeader', TextType::class, [ 'required'   => false, 'empty_data' => '', 'help' => $this->trans('object.home_header_help'), 'label' => $this->trans( 'object.home_description_header' ) ] )
			->add( 'homeLinkExamples', TextType::class, [ 'required'   => false, 'empty_data' => '', 'label' => $this->trans( 'object.home_link_examples' ) ] )
		;
	}
	
}