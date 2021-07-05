<?php

namespace App\Repository;

use App\Entity\PhrasesCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PhrasesCategories|null find( $id, $lockMode = null, $lockVersion = null )
 * @method PhrasesCategories|null findOneBy( array $criteria, array $orderBy = null )
 * @method PhrasesCategories[]    findAll()
 * @method PhrasesCategories[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class PhrasesCategoriesRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, PhrasesCategories::class );
	}
	
	// /**
	//  * @return PhrasesCategories[] Returns an array of PhrasesCategories objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('p')
			->andWhere('p.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('p.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/
	
	/*
	public function findOneBySomeField($value): ?PhrasesCategories
	{
		return $this->createQueryBuilder('p')
			->andWhere('p.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
