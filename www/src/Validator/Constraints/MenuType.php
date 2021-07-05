<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class MenuType extends Constraint
{
	
	public $message = 'The field can not be empty';
	
	public function getTargets()
	{
		return self::CLASS_CONSTRAINT;
	}
	
	public function validatedBy()
	{
		return get_class( $this ) . 'Validator';
	}
	
}