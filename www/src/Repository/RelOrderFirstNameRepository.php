<?php

namespace App\Repository;

use App\Entity\RelOrderFirstName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RelOrderFirstName|null find($id, $lockMode = null, $lockVersion = null)
 * @method RelOrderFirstName|null findOneBy(array $criteria, array $orderBy = null)
 * @method RelOrderFirstName[]    findAll()
 * @method RelOrderFirstName[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RelOrderFirstNameRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RelOrderFirstName::class);
    }

    // /**
    //  * @return RelOrderFirstName[] Returns an array of RelOrderFirstName objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
     * Удаление всех прикрепленных имен к заказу
     */
    public function deleteFirstNameByOrder($orderId)
    {
        $q = $this->getEntityManager()->createQuery('delete from App:RelOrderFirstName r where r.order = '.intval($orderId));
        $q->execute();
    }
}
