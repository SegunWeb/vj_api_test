<?php

namespace App\Repository;

use App\Entity\Phrases;
use App\Constants\ActiveConstants;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Phrases|null find( $id, $lockMode = null, $lockVersion = null )
 * @method Phrases|null findOneBy( array $criteria, array $orderBy = null )
 * @method Phrases[]    findAll()
 * @method Phrases[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class PhrasesRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, Phrases::class );
	}
	
	 /**
	  * @return Phrases[] Returns an array of Phrases objects
	  */
	public function findByPhrasesCategory($category, $typePhrases, $locale)
	{
		return $this->createQueryBuilder('p')
            ->join( 'App\Entity\PhrasesTranslation', 'pt' )
            ->where( 'pt.translatable = p.id' )
			->andWhere('p.active = :active')
			->andWhere('p.category = :category')
			->andWhere('p.type = :type')
			->andWhere( 'pt.locale = :locale' )
			->setParameter( 'locale', $locale )
			->setParameter('active', ActiveConstants::ACTIVE)
			->setParameter('category', $category)
			->setParameter('type', $typePhrases)
			->getQuery()
			->getResult();
	}
}
