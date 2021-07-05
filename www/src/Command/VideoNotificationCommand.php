<?php
namespace App\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class VideoNotificationCommand extends Command
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
			->setName('notification')
			->setDescription('Video alert')
			->addArgument(
				'path',
				InputArgument::REQUIRED,
				'Need to provide a full URL to the site'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$path = $input->getArgument('path');
		
		$order = $this->container->get('app.service.order');
		
		$order->order($path);

		return true;
	}
}