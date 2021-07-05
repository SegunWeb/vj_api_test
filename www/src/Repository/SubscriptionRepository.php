<?php

namespace App\Repository;

use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Subscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method Subscription[]    findAll()
 * @method Subscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    // /**
    //  * @return Subscriber[] Returns an array of Subscriber objects
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
    public function findOneBySomeField($value): ?Subscriber
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
     * Получаем все активные подписки с датой окончания меньше сегодняшней
     *
     * @return Subscription
     * @throws \Exception
     */
    public function findForDisable()
    {
        return $this->createQueryBuilder('s')
                    ->andWhere('s.active = :val')
                    ->setParameter('val', 1)
                    ->andWhere('s.expired_at < :nowDate OR s.expired_at is NULL')
                    ->setParameter('nowDate', new \DateTime('NOW'))
                    ->getQuery()
                    ->getResult();
    }

    /**
     * Получаем последню активную подписку по дате окончания (expired_at)
     *
     * @param  User  $user
     *
     * @return Subscription
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastActiveByUser(User $user): ?Subscription
    {
        return $this->createQueryBuilder('s')
                    ->orderBy("s.expired_at", "DESC")
                    ->setMaxResults(1)
                    ->andWhere('s.active = :val')
                    ->setParameter('val', 1)
                    ->andWhere('s.user = :user')
                    ->setParameter('user', $user)
                    ->andWhere('s.expired_at >= :nowDate')
                    ->setParameter('nowDate', new \DateTime('NOW'))
                    ->getQuery()
                    ->getOneOrNullResult();
    }

}
