<?php

namespace App\Repository;

use App\Entity\VideoRenderPlaceholder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VideoRenderPlaceholder|null find( $id, $lockMode = null, $lockVersion = null )
 * @method VideoRenderPlaceholder|null findOneBy( array $criteria, array $orderBy = null )
 * @method VideoRenderPlaceholder[]    findAll()
 * @method VideoRenderPlaceholder[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class VideoRenderPlaceholderRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, VideoRenderPlaceholder::class );
	}
	
	// /**
	//  * @return VideoRenderPlaceholder[] Returns an array of VideoRenderPlaceholder objects
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
	public function findOneBySomeField($value): ?VideoRenderPlaceholder
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
