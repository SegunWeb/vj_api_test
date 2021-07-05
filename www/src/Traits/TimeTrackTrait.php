<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait TimeTrackTrait
{
	
	/**
	 * @var \DateTime
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="created_at", type="datetime")
	 */
	private $createdAt;
	
	/**
	 * @var \DateTime
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(name="updated_at", type="datetime")
	 */
	private $updatedAt;
	
}