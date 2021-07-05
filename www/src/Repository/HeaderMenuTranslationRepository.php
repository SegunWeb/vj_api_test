<?php

namespace App\Repository;

use App\Entity\HeaderMenuTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HeaderMenuTranslation|null find( $id, $lockMode = null, $lockVersion = null )
 * @method HeaderMenuTranslation|null findOneBy( array $criteria, array $orderBy = null )
 * @method HeaderMenuTranslation[]    findAll()
 * @method HeaderMenuTranslation[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class HeaderMenuTranslationRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, HeaderMenuTranslation::class );
	}
	
	// /**
	//  * @return HeaderMenuTranslation[] Returns an array of HeaderMenuTranslation objects
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
	public function findOneBySomeField($value): ?HeaderMenuTranslation
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
