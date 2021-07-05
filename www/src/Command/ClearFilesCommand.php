<?php
namespace App\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class ClearFilesCommand extends Command
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
			->setName('clearFiles')
			->setDescription('Files clear')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

        exec('find upload/tmp -type f -mmin +720 -delete');

		return true;
	}
}