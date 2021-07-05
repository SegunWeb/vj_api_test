<?php

namespace App\Repository;

use App\Entity\PhrasesCategoriesTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PhrasesCategoriesTranslation|null find( $id, $lockMode = null, $lockVersion = null )
 * @method PhrasesCategoriesTranslation|null findOneBy( array $criteria, array $orderBy = null )
 * @method PhrasesCategoriesTranslation[]    findAll()
 * @method PhrasesCategoriesTranslation[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset =
 *     null )
 */
class PhrasesCategoriesTranslationRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, PhrasesCategoriesTranslation::class );
	}
	
	// /**
	//  * @return PhrasesCategoriesTranslation[] Returns an array of PhrasesCategoriesTranslation objects
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
	public function findOneBySomeField($value): ?PhrasesCategoriesTranslation
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
