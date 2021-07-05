<?php

namespace App\Repository;

use App\Constants\ActiveConstants;
use App\Entity\Blog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Blog|null find( $id, $lockMode = null, $lockVersion = null )
 * @method Blog|null findOneBy( array $criteria, array $orderBy = null )
 * @method Blog[]    findAll()
 * @method Blog[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class BlogRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, Blog::class );
	}
	
	public function getBlogList( $locale = 'ru', $limit = 10, $offset = 0 )
	{
		
		$qb = $this->createQueryBuilder( 'b' );
		
		$query = $qb->select( 'b' )
		            ->join( 'App\Entity\BlogTranslation', 'bt' )
		            ->where( 'bt.translatable = b.id' )
		            ->andWhere( 'bt.active = :active' )
		            ->andWhere( 'bt.locale = :locale' )
		            ->setParameter( 'active', ActiveConstants::ACTIVE )
		            ->setParameter( 'locale', $locale )
		            ->orderBy( 'b.updatedAt', 'DESC' )
		            ->setFirstResult( $offset )
		            ->setMaxResults( $limit )
		            ->getQuery();
		
		$qb = $this->createQueryBuilder( 'b' );
		
		$queryCount = $qb->select( 'count(b.id)' )
		                 ->join( 'App\Entity\BlogTranslation', 'bt' )
		                 ->where( 'bt.translatable = b.id' )
		                 ->andWhere( 'bt.active = :active' )
		                 ->andWhere( 'bt.locale = :locale' )
		                 ->setParameter( 'active', ActiveConstants::ACTIVE )
		                 ->setParameter( 'locale', $locale )
		                 ->getQuery()
		                 ->getSingleScalarResult();
		
		return (object) array( 'query' => $query->getResult(), 'count' => $queryCount );
	}
	
	public function getPost( $slug, $locale )
	{
		
		$qb = $this->createQueryBuilder( 'b' );
		
		$query = $qb->select( 'b' )
		            ->join( 'App\Entity\BlogTranslation', 'bt' )
		            ->where( 'b.slug = :slug' )
		            ->andWhere( 'bt.translatable = b.id' )
		            ->andWhere( 'bt.active = :active' )
		            ->andWhere( 'bt.locale = :locale' )
		            ->setParameter( 'active', ActiveConstants::ACTIVE )
		            ->setParameter( 'locale', $locale )
		            ->setParameter( 'slug', $slug )
		            ->getQuery();
		
		return $query->getOneOrNullResult();
	}
	
	public function getNextArticle( $slug, $locale )
	{
		
		$qb = $this->createQueryBuilder( 'b' );
		
		$query = $qb->select( 'b.slug' )
		            ->join( 'App\Entity\BlogTranslation', 'bt' )
		            ->where( 'b.slug > :slug' )
		            ->andWhere( 'bt.translatable = b.id' )
		            ->andWhere( 'bt.active = :active' )
		            ->andWhere( 'bt.locale = :locale' )
		            ->setParameter( 'active', ActiveConstants::ACTIVE )
		            ->setParameter( 'locale', $locale )
		            ->setParameter( 'slug', $slug )
		            ->setMaxResults( 1 )
		            ->getQuery();
		
		return $query->getResult();
	}
	
	public function getPrevArticle( $slug, $locale )
	{
		
		$qb = $this->createQueryBuilder( 'b' );
		
		$query = $qb->select( 'b.slug' )
		            ->join( 'App\Entity\BlogTranslation', 'bt' )
		            ->where( 'b.slug < :slug' )
		            ->andWhere( 'bt.translatable = b.id' )
		            ->andWhere( 'bt.active = :active' )
		            ->andWhere( 'bt.locale = :locale' )
		            ->setParameter( 'active', ActiveConstants::ACTIVE )
		            ->setParameter( 'locale', $locale )
		            ->setParameter( 'slug', $slug )
		            ->orderBy( 'b.id', 'DESC' )
		            ->setMaxResults( 1 )
		            ->getQuery();
		
		return $query->getResult();
	}
}
