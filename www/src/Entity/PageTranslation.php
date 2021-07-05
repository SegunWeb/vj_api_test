<?php

namespace App\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * PageTranslation
 *
 * @ORM\Entity(repositoryClass="App\Repository\PageTranslationRepository")
 * @ORM\Table(name="page_translation")
 * @ORM\HasLifecycleCallbacks
 */
class PageTranslation
{
	use ORMBehaviors\Translatable\Translation;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=255)
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
	 * @ORM\Column(name="home_content_our_advantages", type="text", nullable=true)
	 */
	protected $homeContentOurAdvantages;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="content_title", type="string", nullable=true)
	 */
	protected $pageContentTitle;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="content_seo", type="text", nullable=true)
	 */
	protected $pageContentSeo;
	
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
	 * @ORM\Column(name="advantage_one_title", type="string", nullable=true)
	 */
	protected $advantageOneTitle;
	
	/**
	 * @ORM\Column(name="advantage_one_text", type="text", nullable=true)
	 */
	protected $advantageOneText;
	
	/**
	 * @ORM\Column(name="advantage_two_title", type="string", nullable=true)
	 */
	protected $advantageTwoTitle;
	
	/**
	 * @ORM\Column(name="advantage_two_text", type="text", nullable=true)
	 */
	protected $advantageTwoText;
	
	/**
	 * @ORM\Column(name="advantage_three_title", type="string", nullable=true)
	 */
	protected $advantageThreeTitle;
	
	/**
	 * @ORM\Column(name="advantage_three_text", type="text", nullable=true)
	 */
	protected $advantageThreeText;
	
	/**
	 * @ORM\Column(name="advantage_four_title", type="string", nullable=true)
	 */
	protected $advantageFourTitle;
	
	/**
	 * @ORM\Column(name="advantage_four_text", type="text", nullable=true)
	 */
	protected $advantageFourText;
	
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
	
	public function getActive(): ?int
	{
		return $this->active;
	}
	
	public function setActive( int $active ): self
	{
		$this->active = $active;
		
		return $this;
	}
	
	public function getHomeContentOurAdvantages(): ?string
	{
		return $this->homeContentOurAdvantages;
	}
	
	public function setHomeContentOurAdvantages( ?string $homeContentOurAdvantages ): self
	{
		$this->homeContentOurAdvantages = $homeContentOurAdvantages;
		
		return $this;
	}
	
	public function getAdvantageOneTitle(): ?string
	{
		return $this->advantageOneTitle;
	}
	
	public function setAdvantageOneTitle( ?string $advantageOneTitle ): self
	{
		$this->advantageOneTitle = $advantageOneTitle;
		
		return $this;
	}
	
	public function getAdvantageOneText(): ?string
	{
		return $this->advantageOneText;
	}
	
	public function setAdvantageOneText( ?string $advantageOneText ): self
	{
		$this->advantageOneText = $advantageOneText;
		
		return $this;
	}
	
	public function getAdvantageTwoTitle(): ?string
	{
		return $this->advantageTwoTitle;
	}
	
	public function setAdvantageTwoTitle( ?string $advantageTwoTitle ): self
	{
		$this->advantageTwoTitle = $advantageTwoTitle;
		
		return $this;
	}
	
	public function getAdvantageTwoText(): ?string
	{
		return $this->advantageTwoText;
	}
	
	public function setAdvantageTwoText( ?string $advantageTwoText ): self
	{
		$this->advantageTwoText = $advantageTwoText;
		
		return $this;
	}
	
	public function getAdvantageThreeTitle(): ?string
	{
		return $this->advantageThreeTitle;
	}
	
	public function setAdvantageThreeTitle( ?string $advantageThreeTitle ): self
	{
		$this->advantageThreeTitle = $advantageThreeTitle;
		
		return $this;
	}
	
	public function getAdvantageThreeText(): ?string
	{
		return $this->advantageThreeText;
	}
	
	public function setAdvantageThreeText( ?string $advantageThreeText ): self
	{
		$this->advantageThreeText = $advantageThreeText;
		
		return $this;
	}
	
	public function getAdvantageFourTitle(): ?string
	{
		return $this->advantageFourTitle;
	}
	
	public function setAdvantageFourTitle( ?string $advantageFourTitle ): self
	{
		$this->advantageFourTitle = $advantageFourTitle;
		
		return $this;
	}
	
	public function getAdvantageFourText(): ?string
	{
		return $this->advantageFourText;
	}
	
	public function setAdvantageFourText( ?string $advantageFourText ): self
	{
		$this->advantageFourText = $advantageFourText;
		
		return $this;
	}
	
	public function getPageContentTitle(): ?string
	{
		return $this->pageContentTitle;
	}
	
	public function setPageContentTitle( ?string $pageContentTitle ): self
	{
		$this->pageContentTitle = $pageContentTitle;
		
		return $this;
	}
	
	public function getPageContentSeo(): ?string
	{
		return $this->pageContentSeo;
	}
	
	public function setPageContentSeo( ?string $pageContentSeo ): self
	{
		$this->pageContentSeo = $pageContentSeo;
		
		return $this;
	}
	
}
