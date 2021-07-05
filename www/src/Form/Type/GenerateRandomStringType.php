<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;

class GenerateRandomStringType extends AbstractType
{
	public function getParent()
	{
		return \Symfony\Component\Form\Extension\Core\Type\TextType::class;
	}
}