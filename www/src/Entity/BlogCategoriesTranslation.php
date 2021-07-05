<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * BlogCategoriesTranslation
 *
 * @ORM\Entity(repositoryClass="App\Repository\BlogCategoriesTranslationRepository")
 * @ORM\Table(name="blog_categories_translation")
 */
class BlogCategoriesTranslation
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
	protected $active;
	
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
