<?php

namespace App\Repository;

use App\Entity\FirstNameTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FirstNameTranslation|null find( $id, $lockMode = null, $lockVersion = null )
 * @method FirstNameTranslation|null findOneBy( array $criteria, array $orderBy = null )
 * @method FirstNameTranslation[]    findAll()
 * @method FirstNameTranslation[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class FirstNameTranslationRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, FirstNameTranslation::class );
	}
	
	// /**
	//  * @return FirstNameTranslation[] Returns an array of FirstNameTranslation objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('f')
			->andWhere('f.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('f.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/
	
	/*
	public function findOneBySomeField($value): ?FirstNameTranslation
	{
		return $this->createQueryBuilder('f')
			->andWhere('f.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
