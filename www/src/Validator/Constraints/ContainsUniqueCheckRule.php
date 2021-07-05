<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsUniqueCheckRule extends Constraint
{
	
	public $message = 'The string "{{ string }}" contains an illegal character: it can only contain letters or numbers.';
	
	public function validatedBy()
	{
		return \get_class( $this ) . 'Validator';
	}
	
	public function getTargets()
	{
		return self::CLASS_CONSTRAINT;
	}
	
}
