<?php

namespace App\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use App\Application\Sonata\MediaBundle\Entity\Media;

/**
 * BlogTranslation
 *
 * @ORM\Entity(repositoryClass="App\Repository\BlogTranslationRepository")
 * @ORM\Table(name="blog_translation")
 * @ORM\HasLifecycleCallbacks
 */
class BlogTranslation
{
	use ORMBehaviors\Translatable\Translation;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=190)
	 */
	protected $title;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="content", type="text", nullable=true)
	 */
	protected $content;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="text", nullable=true)
	 */
	protected $description;
	
	/**
	 * @var int
	 *
	 * @ORM\Column(name="active", type="smallint", nullable=true, options={"default" : 0})
	 */
	protected $active;
	/**
	 * @var string $metaTitle
	 *
	 * @ORM\Column(name="meta_title", type="string", nullable=true)
	 */
	protected $metaTitle;
	
	/**
	 * @var string $metaKeywords
	 *
	 * @ORM\Column(name="meta_keywords", type="string", nullable=true)
	 */
	protected $metaKeywords;
	
	/**
	 * @var string $metaDescription
	 *
	 * @ORM\Column(name="meta_description", type="string", length=400, nullable=true)
	 */
	protected $metaDescription;
	
	/**
	 * @var string $metaCanonical
	 *
	 * @ORM\Column(name="meta_canonical", type="string", nullable=true)
	 */
	protected $metaCanonical;
	
	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 *
	 * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
	 */
	public function prePersist( LifecycleEventArgs $args )
	{
		$entity = $args->getEntity();
		if ( $entity->getMetaTitle() === null ) {
			$entity->setMetaTitle( $entity->getTitle() );
		}
		if ( $entity->getMetaKeywords() === null ) {
			$entity->setMetaKeywords( $entity->getMetaKeywords() );
		}
		if ( $entity->getMetaCanonical() === null ) {
			$entity->setMetaCanonical( $entity->getMetaCanonical() );
		}
		if ( $entity->getMetaDescription() === null ) {
			$entity->setMetaDescription( $entity->getTitle() );
		}
	}
	
	public function setMetaTitle( $metaTitle )
	{
		if ( empty( $metaTitle ) ) {
			$metaTitle = $this->title;
		}
		$this->metaTitle = $metaTitle;
		
		return $this;
	}
	
	public function setMetaDescription( $metaDescription )
	{
		if ( empty( $metaDescription ) ) {
			$metaDescription = $this->getTitle();
		}
		$this->metaDescription = $metaDescription;
		
		return $this;
	}
	
	public function setMetaKeywords( $metaKeywords )
	{
		$this->metaKeywords = $metaKeywords;
		
		return $this;
	}
	
	public function setMetaCanonical( $metaCanonical )
	{
		$this->metaCanonical = $metaCanonical;
		
		return $this;
	}
	
	public function getTitle(): ?string
	{
		return $this->title;
	}
	
	public function setTitle( string $title ): self
	{
		$this->title = $title;
		
		return $this;
	}
	
	public function getContent(): ?string
	{
		return $this->content;
	}
	
	public function setContent( string $content ): self
	{
		$this->content = $content;
		
		return $this;
	}
	
	public function getDescription(): ?string
	{
		return $this->description;
	}
	
	public function setDescription( string $description ): self
	{
		$this->description = $description;
		
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
	
	public function getMetaTitle(): ?string
	{
		return $this->metaTitle;
	}
	
	public function getMetaKeywords(): ?string
	{
		return $this->metaKeywords;
	}
	
	public function getMetaDescription(): ?string
	{
		return $this->metaDescription;
	}
	
	public function getMetaCanonical(): ?string
	{
		return $this->metaCanonical;
	}
}
