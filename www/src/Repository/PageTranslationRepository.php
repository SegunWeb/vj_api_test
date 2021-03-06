<?php

namespace App\Repository;

use App\Entity\PageTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PageTranslation|null find( $id, $lockMode = null, $lockVersion = null )
 * @method PageTranslation|null findOneBy( array $criteria, array $orderBy = null )
 * @method PageTranslation[]    findAll()
 * @method PageTranslation[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class PageTranslationRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, PageTranslation::class );
	}
	
	// /**
	//  * @return PageTranslation[] Returns an array of PageTranslation objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('p')
			->andWhere('p.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('p.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/
	
	/*
	public function findOneBySomeField($value): ?PageTranslation
	{
		return $this->createQueryBuilder('p')
			->andWhere('p.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
