<?php

namespace App\Repository;

use App\Entity\VideoRenderImageManyPlaceholder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VideoRenderImageManyPlaceholder|null find($id, $lockMode = null, $lockVersion = null)
 * @method VideoRenderImageManyPlaceholder|null findOneBy(array $criteria, array $orderBy = null)
 * @method VideoRenderImageManyPlaceholder[]    findAll()
 * @method VideoRenderImageManyPlaceholder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRenderImageManyPlaceholderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VideoRenderImageManyPlaceholder::class);
    }

    // /**
    //  * @return VideoRenderImageManyPlaceholder[] Returns an array of VideoRenderImageManyPlaceholder objects
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
    public function findOneBySomeField($value): ?VideoRenderImageManyPlaceholder
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
