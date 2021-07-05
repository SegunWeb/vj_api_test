<?php

namespace App\Repository;

use App\Entity\FooterMenuTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FooterMenuTranslation|null find( $id, $lockMode = null, $lockVersion = null )
 * @method FooterMenuTranslation|null findOneBy( array $criteria, array $orderBy = null )
 * @method FooterMenuTranslation[]    findAll()
 * @method FooterMenuTranslation[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class FooterMenuTranslationRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, FooterMenuTranslation::class );
	}
	
	// /**
	//  * @return FooterMenuTranslation[] Returns an array of FooterMenuTranslation objects
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
	public function findOneBySomeField($value): ?FooterMenuTranslation
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
