<?php

namespace App\Repository;

use App\Constants\ActiveConstants;
use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Order|null find( $id, $lockMode = null, $lockVersion = null )
 * @method Order|null findOneBy( array $criteria, array $orderBy = null )
 * @method Order[]    findAll()
 * @method Order[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class OrderRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, Order::class );
	}
	
	/*
	 * Ищем не оплаченные заказы
	 */
	public function findByOrderNotPaid( $send )
	{
		
		$qb = $this->createQueryBuilder( 'o' );
		if($send == 1){
			$date = new \DateTime( date( 'd.m.Y 00:00:00' ) . ' - 3 days' );
		}else {
			$date = new \DateTime( date( 'd.m.Y 00:00:00' ) . ' - 24 hours' );
		}
		
		$query = $qb->select( 'o' )
		            ->where( 'o.active != 1' )
		            ->andWhere( 'o.sentEmail = :send' )
					->andWhere('o.updatedAt BETWEEN :date_start AND :date_end')
					->setParameter( 'date_start', $date->format('Y.m.d 00:00:00') )
					->setParameter( 'date_end', $date->format('Y.m.d 23:59:59') )
					->setParameter('send', $send);
		
		return $query->getQuery()->getResult();
	}

    /*
     * Не оплаченные заказы старше месяца
     */
    public function findByOrderNotPaidWithMonth()
    {

        $qb = $this->createQueryBuilder( 'o' );

        $date = new \DateTime( date( 'd.m.Y 00:00:00' ) . ' -1 month' );
        
        $query = $qb->select( 'o' )
            ->where( 'o.active = :active' )
            ->andWhere('o.createdAt < :date')
            ->setParameter( 'date', $date->format('Y.m.d 00:00:00') )
            ->setParameter('active', ActiveConstants::ORDER_NOT_PAID_VALUE);
	    
        return $query->getQuery()->getResult();
    }

    /*
     * Оплаченные заказы старше двух месяцев
     */
    public function findByOrderPaidWithMonth()
    {

        $qb = $this->createQueryBuilder( 'o' );

        $date = new \DateTime( date( 'd.m.Y 00:00:00' ) . ' -2 month' );
        
        $query = $qb->select( 'o' )
            ->where( 'o.active = :active' )
            ->andWhere('o.createdAt < :date')
            ->setParameter( 'date', $date->format('Y.m.d 00:00:00') )
            ->setParameter('active', ActiveConstants::ORDER_PAID_VALUE);

        return $query->getQuery()->getResult();
    }

    /*
     * Последний не оплаченный заказ юзера
     */

    public function getLastNotPaidOrderByUser(User $user)
    {
        $query = $this->createQueryBuilder( 'o' )
                      ->select( 'o' )
                      ->setMaxResults(1)
                      ->orderBy("o.createdAt", "DESC")
                      ->andWhere('o.users = :user')
                      ->setParameter('user', $user)
                      ->andWhere( 'o.active != 1' );

        return $query->getQuery()->getOneOrNullResult();
    }
	
}
