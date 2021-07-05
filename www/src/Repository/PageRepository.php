<?php

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Constants\ActiveConstants;

/**
 * @method Page|null find( $id, $lockMode = null, $lockVersion = null )
 * @method Page|null findOneBy( array $criteria, array $orderBy = null )
 * @method Page[]    findAll()
 * @method Page[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class PageRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, Page::class );
	}
	
	public function getOneBySlug( $slug, $locale )
	{
		
		$qb = $this->createQueryBuilder( 'p' );
		
		$query = $qb->select( 'p' )
		            ->where( 'p.slug = :slug' )
		            ->join( 'App\Entity\PageTranslation', 'pt' )
		            ->andWhere( 'pt.translatable = p.id' )
		            ->andWhere( 'pt.active = :active' )
		            ->andWhere( 'pt.locale = :locale' )
		            ->setParameter( 'slug', $slug )
		            ->setParameter( 'active', ActiveConstants::ACTIVE )
		            ->setParameter( 'locale', $locale )
		            ->getQuery();
		
		return $query->getOneOrNullResult();
	}
	
	public function getSlugPage( $type, $locale )
	{
		
		$qb = $this->createQueryBuilder( 'p' );
		
		$query = $qb->select( 'p.slug' )
		            ->where( 'p.type = :type' )
		            ->join( 'App\Entity\PageTranslation', 'pt' )
		            ->andWhere( 'pt.translatable = p.id' )
		            ->andWhere( 'pt.active = :active' )
		            ->andWhere( 'pt.locale = :locale' )
		            ->setParameter( 'type', $type )
		            ->setParameter( 'active', ActiveConstants::ACTIVE )
		            ->setParameter( 'locale', $locale )
		            ->getQuery();
		
		return $query->getOneOrNullResult();
	}
}

