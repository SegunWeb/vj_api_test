<?php

namespace App\Repository;

use App\Entity\VideoRender;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VideoRender|null find( $id, $lockMode = null, $lockVersion = null )
 * @method VideoRender|null findOneBy( array $criteria, array $orderBy = null )
 * @method VideoRender[]    findAll()
 * @method VideoRender[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class VideoRenderRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, VideoRender::class );
	}
	
	/**
	 * @return VideoRender[] Returns an array of VideoRender objects
	 */
	public function findByActiveRenderingVideoLast( $users )
	{
		return $this->createQueryBuilder( 'v' )
		            ->where( 'v.users = :users' )
		            ->andWhere( 'v.status != :status' )
		            ->setParameter( 'users', $users )
		            ->setParameter( 'status', 'finished' )
		            ->orderBy( 'v.id', 'DESC' )
		            ->setMaxResults( 1 )
		            ->getQuery()
		            ->getOneOrNullResult();
	}
	
	
	/*
	public function findOneBySomeField($value): ?VideoRender
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
