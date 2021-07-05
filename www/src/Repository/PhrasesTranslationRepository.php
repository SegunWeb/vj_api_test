<?php

namespace App\Repository;

use App\Entity\PhrasesTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PhrasesTranslation|null find( $id, $lockMode = null, $lockVersion = null )
 * @method PhrasesTranslation|null findOneBy( array $criteria, array $orderBy = null )
 * @method PhrasesTranslation[]    findAll()
 * @method PhrasesTranslation[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class PhrasesTranslationRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, PhrasesTranslation::class );
	}
	
	// /**
	//  * @return PhrasesTranslation[] Returns an array of PhrasesTranslation objects
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
	public function findOneBySomeField($value): ?PhrasesTranslation
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
