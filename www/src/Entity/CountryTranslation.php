<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * CountryTranslation
 *
 * @ORM\Entity(repositoryClass="App\Repository\CountryTranslationRepository")
 * @ORM\Table(name="country_translation")
 * @ORM\HasLifecycleCallbacks
 */
class CountryTranslation
{
	use ORMBehaviors\Translatable\Translation;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="name", type="string", length=190, nullable=false)
	 */
	protected $name;
	
	public function getName(): ?string
	{
		return $this->name;
	}
	
	public function setName( string $name ): self
	{
		$this->name = $name;
		
		return $this;
	}
}
