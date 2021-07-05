<?php

namespace App\Repository;

use App\Entity\MailTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MailTemplate|null find( $id, $lockMode = null, $lockVersion = null )
 * @method MailTemplate|null findOneBy( array $criteria, array $orderBy = null )
 * @method MailTemplate[]    findAll()
 * @method MailTemplate[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class MailTemplateRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, MailTemplate::class );
	}
	
	// /**
	//  * @return MailTemplate[] Returns an array of MailTemplate objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('m')
			->andWhere('m.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('m.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/
	
	/*
	public function findOneBySomeField($value): ?MailTemplate
	{
		return $this->createQueryBuilder('m')
			->andWhere('m.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
