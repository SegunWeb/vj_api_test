<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class TreeValidator extends ConstraintValidator
{
	
	public function validate( $value, Constraint $constraint )
	{
		if ( $value->getParent() && $value->getId() == $value->getParent()->getId() ) {
			$this->context->buildViolation( $constraint->message_parent )
			              ->atPath( 'parent' )
			              ->addViolation();
		}
	}
	
}
