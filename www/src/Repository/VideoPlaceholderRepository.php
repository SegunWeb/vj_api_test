<?php

namespace App\Repository;

use App\Entity\VideoPlaceholder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VideoPlaceholder|null find( $id, $lockMode = null, $lockVersion = null )
 * @method VideoPlaceholder|null findOneBy( array $criteria, array $orderBy = null )
 * @method VideoPlaceholder[]    findAll()
 * @method VideoPlaceholder[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class VideoPlaceholderRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, VideoPlaceholder::class );
	}
	
	// /**
	//  * @return VideoPlaceholder[] Returns an array of VideoPlaceholder objects
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
	public function findOneBySomeField($value): ?VideoPlaceholder
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
