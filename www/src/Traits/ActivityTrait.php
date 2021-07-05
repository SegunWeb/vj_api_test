<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ActivityTrait
{
	
	/**
	 * @var int
	 *
	 * @ORM\Column(name="active", type="smallint", nullable=true, options={"default" : 0})
	 */
	private $active;
	
}