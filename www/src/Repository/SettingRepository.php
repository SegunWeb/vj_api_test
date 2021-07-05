<?php

namespace App\Repository;

use App\Entity\Setting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Setting|null find( $id, $lockMode = null, $lockVersion = null )
 * @method Setting|null findOneBy( array $criteria, array $orderBy = null )
 * @method Setting[]    findAll()
 * @method Setting[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class SettingRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, Setting::class );
	}
	
	/**
	 * @return Setting[] Returns an array of Setting objects
	 */
	public function dataForTheMainPage()
	  {
		return $this->createQueryBuilder( 's' )
					->select( 's as setting, hm as header, fm as footer' )
					->join( 'App\Entity\HeaderMenu', 'hm', 'WITH', 'hm.active = 1' )
					->orderBy( 'hm.position', 'ASC' )
					->join( 'App\Entity\FooterMenu', 'fm', 'WITH', 'fm.active = 1' )
					->addOrderBy( 'fm.position', 'ASC' )
					->getQuery()
					->getResult();
	  }
	
	
	/*
	public function findOneBySomeField($value): ?Setting
	{
		return $this->createQueryBuilder('s')
			->andWhere('s.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
