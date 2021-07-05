<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class Tree extends Constraint
{
	
	public $message_parent = 'Недопустимый родительский объект';
	
	public function getTargets()
	{
		return self::CLASS_CONSTRAINT;
	}
	
	public function validatedBy()
	{
		return get_class( $this ) . 'Validator';
	}
	
}