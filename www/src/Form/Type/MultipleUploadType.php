<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;

class MultipleUploadType extends AbstractType
{
	public function getParent()
	{
		return \Symfony\Component\Form\Extension\Core\Type\TextType::class;
	}
	
	public function getFormTheme()
	{
		return array( 'form/multipleUpload.html.twig' );
	}
}