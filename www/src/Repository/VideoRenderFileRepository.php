<?php

namespace App\Repository;

use App\Entity\VideoRenderFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VideoRenderFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method VideoRenderFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method VideoRenderFile[]    findAll()
 * @method VideoRenderFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRenderFileRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VideoRenderFile::class);
    }

    // /**
    //  * @return VideoRenderFile[] Returns an array of VideoRenderFile objects
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
    public function findOneBySomeField($value): ?VideoRenderFile
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
