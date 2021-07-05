<?php

namespace App\Repository;

use App\Entity\FirstName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FirstName|null find( $id, $lockMode = null, $lockVersion = null )
 * @method FirstName|null findOneBy( array $criteria, array $orderBy = null )
 * @method FirstName[]    findAll()
 * @method FirstName[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class FirstNameRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, FirstName::class );
	}
	
	// /**
	//  * @return FirstName[] Returns an array of FirstName objects
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
	public function findOneBySomeField($value): ?FirstName
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
