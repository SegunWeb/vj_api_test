<?php

namespace App\Repository;

use App\Entity\FooterMenu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FooterMenu|null find( $id, $lockMode = null, $lockVersion = null )
 * @method FooterMenu|null findOneBy( array $criteria, array $orderBy = null )
 * @method FooterMenu[]    findAll()
 * @method FooterMenu[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class FooterMenuRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, FooterMenu::class );
	}
	
	// /**
	//  * @return FooterMenu[] Returns an array of FooterMenu objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('f')
			->andWhere('f.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('f.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/
	
	/*
	public function findOneBySomeField($value): ?FooterMenu
	{
		return $this->createQueryBuilder('f')
			->andWhere('f.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
