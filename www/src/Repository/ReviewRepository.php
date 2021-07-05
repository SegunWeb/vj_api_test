<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Review|null find( $id, $lockMode = null, $lockVersion = null )
 * @method Review|null findOneBy( array $criteria, array $orderBy = null )
 * @method Review[]    findAll()
 * @method Review[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class ReviewRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, Review::class );
	}
	
	/**
	 * AJAX пагинация по видео отзывам
	 */
	public function findByReview( $offset = 0, $limit = 10, $locale = 'ru' )
	{
		
		$queryCount = $this->createQueryBuilder( 'r' )
		                   ->select( 'count(r.id)' )
		                   ->where( 'r.active= 1' )
						   ->andWhere('r.locale = :locale')
						   ->setParameter('locale', $locale)
		                   ->getQuery()
		                   ->getSingleScalarResult();
		
		$query = $this->createQueryBuilder( 'r' )
		              ->where( 'r.active= 1' )
		              ->andWhere('r.locale = :locale')
		              ->setParameter('locale', $locale)
		              ->orderBy( 'r.id', 'DESC' )
		              ->setFirstResult( $offset )
		              ->setMaxResults( $limit )
		              ->getQuery()
		              ->getResult();
		
		return (object) array( 'query' => $query, 'count' => $queryCount );
	}
}
