<?php

namespace App\Validator\Constraints;

use App\Constants\MenuTypeConstants;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class MenuTypeValidator extends ConstraintValidator
{
	
	public function validate( $value, Constraint $constraint )
	{
		
		if ( $value->getType() === MenuTypeConstants::STATIC_PAGE && is_null( $value->getStaticPageId() ) ) {
			$this->context->buildViolation( $constraint->message )
			              ->atPath( 'static_page_id' )
			              ->addViolation();
		}
		
		if ( $value->getType() === MenuTypeConstants::LINK && is_null( $value->getLink() ) ) {
			$this->context->buildViolation( $constraint->message )
			              ->atPath( 'link' )
			              ->addViolation();
		}
	}
	
}
