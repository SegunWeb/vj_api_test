<?php

namespace App\Repository;

use App\Entity\HelpTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HelpTranslation|null find( $id, $lockMode = null, $lockVersion = null )
 * @method HelpTranslation|null findOneBy( array $criteria, array $orderBy = null )
 * @method HelpTranslation[]    findAll()
 * @method HelpTranslation[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class HelpTranslationRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, HelpTranslation::class );
	}
	
	// /**
	//  * @return HelpTranslation[] Returns an array of HelpTranslation objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('h')
			->andWhere('h.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('h.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/
	
	/*
	public function findOneBySomeField($value): ?HelpTranslation
	{
		return $this->createQueryBuilder('h')
			->andWhere('h.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
