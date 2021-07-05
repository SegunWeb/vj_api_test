<?php

namespace App\Repository;

use App\Entity\WorkerLoad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WorkerLoad|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkerLoad|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkerLoad[]    findAll()
 * @method WorkerLoad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkerLoadRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WorkerLoad::class);
    }
	
	public function findByWorkerFull()
	{
		return $this->createQueryBuilder('w')
		            ->where('w.type = 0')
		            ->orWhere('w.type = 2')
		            ->orderBy('w.numberOfTasks', 'ASC')
		            ->setMaxResults(1)
		            ->getQuery()
		            ->getSingleResult()
			;
	}
	
	public function findByWorkerDemo()
	{
		return $this->createQueryBuilder('w')
		            ->where('w.type = 0')
		            ->orWhere('w.type = 1')
		            ->orderBy('w.numberOfTasks', 'ASC')
		            ->setMaxResults(1)
		            ->getQuery()
		            ->getSingleResult()
			;
	}
}
