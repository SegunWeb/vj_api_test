<?php

namespace App\Repository;

use App\Entity\HolidaysTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HolidaysTranslation|null find( $id, $lockMode = null, $lockVersion = null )
 * @method HolidaysTranslation|null findOneBy( array $criteria, array $orderBy = null )
 * @method HolidaysTranslation[]    findAll()
 * @method HolidaysTranslation[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class HolidaysTranslationRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, HolidaysTranslation::class );
	}
	
	// /**
	//  * @return HolidaysTranslation[] Returns an array of HolidaysTranslation objects
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
	public function findOneBySomeField($value): ?HolidaysTranslation
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
