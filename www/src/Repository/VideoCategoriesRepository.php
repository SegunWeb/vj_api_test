<?php

namespace App\Repository;

use App\Constants\ActiveConstants;
use App\Entity\VideoCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VideoCategories|null find( $id, $lockMode = null, $lockVersion = null )
 * @method VideoCategories|null findOneBy( array $criteria, array $orderBy = null )
 * @method VideoCategories[]    findAll()
 * @method VideoCategories[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class VideoCategoriesRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, VideoCategories::class );
	}
	
	public function getOneBySlug( $slug, $locale )
	{
		
		$qb = $this->createQueryBuilder( 'vc' );
		
		$query = $qb->select( 'vc' )
		            ->where( 'vc.slug = :slug' )
		            ->join( 'App\Entity\VideoCategoriesTranslation', 'vct' )
		            ->andWhere( 'vct.translatable = vc.id' )
		            ->andWhere( 'vct.active = :active' )
		            ->andWhere( 'vct.locale = :locale' )
		            ->setParameter( 'slug', $slug )
		            ->setParameter( 'active', ActiveConstants::ACTIVE )
		            ->setParameter( 'locale', $locale )
		            ->getQuery();
		
		return $query->getOneOrNullResult();
	}

	/**
	 * @return VideoCategories[] Returns an array of Phrases objects
	 */
	public function findByCategory($locale)
	{
		return $this->createQueryBuilder('vc')
		            ->join( 'App\Entity\VideoCategoriesTranslation', 'vct' )
		            ->where( 'vct.translatable = vc.id' )
		            ->andWhere('vct.active = :active')
		            ->andWhere( 'vct.locale = :locale' )
		            ->setParameter( 'locale', $locale )
		            ->setParameter('active', ActiveConstants::ACTIVE)
                    ->orderBy('vct.sortOrder', 'ASC')
		            ->getQuery()
		            ->getResult();
	}
	
	// /**
	//  * @return VideoCategories[] Returns an array of VideoCategories objects
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
	public function findOneBySomeField($value): ?VideoCategories
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
