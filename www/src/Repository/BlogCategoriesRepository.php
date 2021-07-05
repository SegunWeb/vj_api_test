<?php

namespace App\Repository;

use App\Constants\ActiveConstants;
use App\Entity\BlogCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BlogCategories|null find( $id, $lockMode = null, $lockVersion = null )
 * @method BlogCategories|null findOneBy( array $criteria, array $orderBy = null )
 * @method BlogCategories[]    findAll()
 * @method BlogCategories[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class BlogCategoriesRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, BlogCategories::class );
	}
	
	public function getCategories( $locale )
	{
		
		$qb = $this->createQueryBuilder( 'bc' );
		
		$query = $qb->select( 'bc' )
		            ->join( 'App\Entity\BlogCategoriesTranslation', 'bct' )
		            ->where( 'bct.translatable = bc.id' )
		            ->andWhere( 'bct.active = :active' )
		            ->andWhere( 'bct.locale = :locale' )
		            ->setParameter( 'active', ActiveConstants::ACTIVE )
		            ->setParameter( 'locale', $locale )
		            ->getQuery();
		
		return $query->getResult();
	}
}
