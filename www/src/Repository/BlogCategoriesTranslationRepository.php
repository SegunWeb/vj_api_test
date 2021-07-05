<?php

namespace App\Repository;

use App\Entity\BlogCategoriesTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BlogCategoriesTranslation|null find( $id, $lockMode = null, $lockVersion = null )
 * @method BlogCategoriesTranslation|null findOneBy( array $criteria, array $orderBy = null )
 * @method BlogCategoriesTranslation[]    findAll()
 * @method BlogCategoriesTranslation[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null
 *     )
 */
class BlogCategoriesTranslationRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, BlogCategoriesTranslation::class );
	}
	
	// /**
	//  * @return BlogCategoriesTranslation[] Returns an array of BlogCategoriesTranslation objects
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
	public function findOneBySomeField($value): ?BlogCategoriesTranslation
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
