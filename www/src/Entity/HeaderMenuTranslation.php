<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * HeaderMenuTranslation
 *
 * @ORM\Entity(repositoryClass="App\Repository\HeaderMenuTranslationRepository")
 * @ORM\Table(name="header_menu_translation")
 * @ORM\HasLifecycleCallbacks
 */
class HeaderMenuTranslation
{
	use ORMBehaviors\Translatable\Translation;
	
	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $title;
	
	public function getTitle(): ?string
	{
		return $this->title;
	}
	
	public function setTitle( string $title ): self
	{
		$this->title = $title;
		
		return $this;
	}
}
