<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * PhrasesCategoriesTranslation
 *
 * @ORM\Entity(repositoryClass="App\Repository\PhrasesCategoriesTranslationRepository")
 * @ORM\Table(name="phrases_categories_translation")
 * @ORM\HasLifecycleCallbacks
 */
class PhrasesCategoriesTranslation
{
	use ORMBehaviors\Translatable\Translation;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=190)
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
