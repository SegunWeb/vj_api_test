<?php
namespace App\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class ClearOrderCommand extends Command
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
			->setName('clearOrder')
			->setDescription('deleting order files a month later')
			->addArgument(
				'path',
				InputArgument::REQUIRED,
				'Project folder path'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$path = $input->getArgument('path');

        $order = $this->container->get('app.service.order');

        $order->clearOrder($path);

		return true;
	}
}