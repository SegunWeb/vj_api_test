<?php

namespace App\Repository;

use App\Entity\SubscriptionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SubscriptionType|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubscriptionType|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubscriptionType[]    findAll()
 * @method SubscriptionType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubscriptionTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubscriptionType::class);
    }

    // /**
    //  * @return Subscription[] Returns an array of Subscription objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Subscription
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * Получаем последний активный тип подписки
     *
     * @return SubscriptionType
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastSubscriptionType()
    {
        return $this->createQueryBuilder('s')
                    ->orderBy("s.id", "DESC")
                    ->setMaxResults(1)
                    ->andWhere('s.active = :val')
                    ->setParameter('val', 1)
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}
