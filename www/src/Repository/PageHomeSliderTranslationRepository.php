<?php

namespace App\Repository;

use App\Entity\PageHomeSliderTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PageHomeSliderTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageHomeSliderTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageHomeSliderTranslation[]    findAll()
 * @method PageHomeSliderTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageHomeSliderTranslationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PageHomeSliderTranslation::class);
    }

    // /**
    //  * @return PageHomeSliderTranslation[] Returns an array of PageHomeSliderTranslation objects
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
    public function findOneBySomeField($value): ?PageHomeSliderTranslation
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
