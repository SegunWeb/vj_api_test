<?php

namespace App\Entity;

use App\Traits\ActivityTrait;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * VideoCategoriesTranslation
 *
 * @ORM\Entity(repositoryClass="App\Repository\VideoCategoriesTranslationRepository")
 * @ORM\Table(name="video_categories_translation")
 */
class VideoCategoriesTranslation
{
	use ORMBehaviors\Translatable\Translation;
	use ActivityTrait;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=255)
	 */
	protected $title;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="title_abbreviated", type="string", length=255)
	 */
	protected $titleAbbreviated;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="content", type="text", nullable=true)
	 */
	protected $content;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_title", type="text", nullable=true)
     */
    protected $subTitle;
	
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
     * @var integer
     *
     * @ORM\Column(name="sort_order", type="integer", options={"default" : 100})
     */
    protected $sortOrder;


	
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

    public function getSubTitle(): ?string
    {
        return $this->subTitle;
    }

    public function setSubTitle( string $subTitle ): self
    {
        $this->subTitle = $subTitle;

        return $this;
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
	
	public function getActive(): ?int
	{
		return $this->active;
	}
	
	public function setActive( int $active ): self
	{
		$this->active = $active;
		
		return $this;
	}
	
	public function getTitleAbbreviated(): ?string
	{
		return $this->titleAbbreviated;
	}
	
	public function setTitleAbbreviated( string $titleAbbreviated ): self
	{
		$this->titleAbbreviated = $titleAbbreviated;
		
		return $this;
	}

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder( int $sortOrder ): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
	
}
