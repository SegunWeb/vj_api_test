<?php

namespace App\Repository;

use App\Entity\ReviewVideo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ReviewVideo|null find( $id, $lockMode = null, $lockVersion = null )
 * @method ReviewVideo|null findOneBy( array $criteria, array $orderBy = null )
 * @method ReviewVideo[]    findAll()
 * @method ReviewVideo[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class ReviewVideoRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, ReviewVideo::class );
	}
	
	/**
	 * AJAX пагинация по видео отзывам
	 */
	public function findByReview( $offset = 0, $limit = 10, $locale = 'ru' )
	{
		
		$queryCount = $this->createQueryBuilder( 'rv' )
		                   ->select( 'count(rv.id)' )
		                   ->where( 'rv.active= 1' )
		                   ->andWhere('rv.locale = :locale')
		                   ->setParameter('locale', $locale)
		                   ->getQuery()
		                   ->getSingleScalarResult();
		
		$query = $this->createQueryBuilder( 'rv' )
		              ->where( 'rv.active= 1' )
		              ->andWhere('rv.locale = :locale')
		              ->setParameter('locale', $locale)
		              ->orderBy( 'rv.id', 'DESC' )
		              ->setFirstResult( $offset )
		              ->setMaxResults( $limit )
		              ->getQuery()
		              ->getResult();
		
		return (object) array( 'query' => $query, 'count' => $queryCount );
	}
}
