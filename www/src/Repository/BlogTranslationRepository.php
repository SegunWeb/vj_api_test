<?php

namespace App\Repository;

use App\Entity\BlogTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BlogTranslation|null find( $id, $lockMode = null, $lockVersion = null )
 * @method BlogTranslation|null findOneBy( array $criteria, array $orderBy = null )
 * @method BlogTranslation[]    findAll()
 * @method BlogTranslation[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class BlogTranslationRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, BlogTranslation::class );
	}
	
	// /**
	//  * @return BlogTranslation[] Returns an array of BlogTranslation objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('b')
			->andWhere('b.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('b.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/
	
	/*
	public function findOneBySomeField($value): ?BlogTranslation
	{
		return $this->createQueryBuilder('b')
			->andWhere('b.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
