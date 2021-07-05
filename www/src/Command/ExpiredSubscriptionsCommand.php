<?php
namespace App\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class ExpiredSubscriptionsCommand extends Command
{
	protected $container;
	
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		
		parent::__construct();
	}
	
	protected function configure()
	{
		$this
			->setName('expiredSubscriptions')
			->setDescription('Expired subscriptions')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

        $subscription = $this->container->get('app.controller.subscription');
        $subscription->expiredSubscriptions();
		return true;
	}
}