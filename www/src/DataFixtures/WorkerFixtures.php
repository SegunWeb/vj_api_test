<?php

namespace App\DataFixtures;

use App\Entity\WorkerLoad;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class WorkerFixtures extends Fixture implements FixtureGroupInterface
{
	public function load( ObjectManager $manager )
	{
		
		$workerList = $this->getWorkerList();
		if ( ! empty( $workerList ) ) {
			foreach ( $workerList as $key => $worker ) {
				$load = new WorkerLoad();
                $load->setIp( $worker[0] );
                $load->setPort( $worker[1] );
                $load->setNumberOfTasks( 0 );
				$manager->persist( $load );
			}
		}

		$manager->flush();
		
	}
	
	public function getWorkerList()
	{
		return [
			['http://127.0.0.1', 3000],
            ['http://127.0.0.1', 3100],
            ['http://127.0.0.1', 3200]
		];
	}

    public static function getGroups(): array
     {
         return ['worker'];
     }
}
