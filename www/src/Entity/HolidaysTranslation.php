<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * HolidaysTranslation
 *
 * @ORM\Entity(repositoryClass="App\Repository\HolidaysTranslationRepository")
 * @ORM\Table(name="holidays_translation")
 */
class HolidaysTranslation
{
	use ORMBehaviors\Translatable\Translation;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=190)
	 */
	protected $title;
	
	/**
	 * @var int
	 *
	 * @ORM\Column(name="active", type="smallint", nullable=true, options={"default" : 0})
	 */
	private $active;
	
	public function getTitle(): ?string
	{
		return $this->title;
	}
	
	public function setTitle( string $title ): self
	{
		$this->title = $title;
		
		return $this;
	}
	
	public function getActive(): ?int
	{
		return $this->active;
	}
	
	public function setActive( int $active ): self
	{
		$this->active = $active;
		
		return $this;
	}
	
}
