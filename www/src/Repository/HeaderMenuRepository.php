<?php

namespace App\Repository;

use App\Entity\HeaderMenu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HeaderMenu|null find( $id, $lockMode = null, $lockVersion = null )
 * @method HeaderMenu|null findOneBy( array $criteria, array $orderBy = null )
 * @method HeaderMenu[]    findAll()
 * @method HeaderMenu[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class HeaderMenuRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, HeaderMenu::class );
	}
	
	// /**
	//  * @return HeaderMenu[] Returns an array of HeaderMenu objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('h')
			->andWhere('h.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('h.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/
	
	/*
	public function findOneBySomeField($value): ?HeaderMenu
	{
		return $this->createQueryBuilder('h')
			->andWhere('h.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
