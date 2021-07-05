<?php

namespace App\Repository;

use App\Entity\VideoCategoriesTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VideoCategoriesTranslation|null find( $id, $lockMode = null, $lockVersion = null )
 * @method VideoCategoriesTranslation|null findOneBy( array $criteria, array $orderBy = null )
 * @method VideoCategoriesTranslation[]    findAll()
 * @method VideoCategoriesTranslation[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset =
 *     null )
 */
class VideoCategoriesTranslationRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, VideoCategoriesTranslation::class );
	}
	
	// /**
	//  * @return VideoCategoriesTranslation[] Returns an array of VideoCategoriesTranslation objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('v')
			->andWhere('v.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('v.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/
	
	/*
	public function findOneBySomeField($value): ?VideoCategoriesTranslation
	{
		return $this->createQueryBuilder('v')
			->andWhere('v.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
