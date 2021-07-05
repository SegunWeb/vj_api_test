<?php

namespace App\Validator\Constraints;

use App\Entity\Page;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ContainsUniqueCheckRuleValidator extends ConstraintValidator
{
	/**
	 * @var ObjectManager
	 */
	protected $em;
	
	public function __construct( ObjectManager $entityManager )
	{
		$this->em = $entityManager;
	}
	
	public function validate( $entity, Constraint $constraint )
	{
		if ( ! $constraint instanceof ContainsUniqueCheckRule ) {
			throw new UnexpectedTypeException( $constraint, ContainsUniqueCheckRule::class );
		}
		
		$findPage = $this->em->getRepository( Page::class )->findOneBy( [ 'type' => $entity->getType() ] );
		
		if ( ! empty( $findPage ) and ( $findPage->getId() != $entity->getId() ) ) {
			$this->context->buildViolation( 'Страница дублируется. Выберите "Другая страница" если желаете создать новую страницу' )
			              ->setParameter( 'type', $entity->getType() )
			              ->addViolation();
		}
	}
}