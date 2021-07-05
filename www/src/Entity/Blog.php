<?php

namespace App\Entity;

use App\Traits\MetaImageTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Collections\Collection;
use App\Traits\TimeTrackTrait as TimeTrack;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use App\Application\Sonata\MediaBundle\Entity\Media;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * Blog
 *
 * @ORM\Entity(repositoryClass="App\Repository\BlogRepository")
 * @ORM\Table(name="blog")
 * @ORM\HasLifecycleCallbacks
 */
class Blog implements TranslatableInterface
{
	use ORMBehaviors\Translatable\Translatable;
	use TimeTrack;
	use MetaImageTrait;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	/**
	 * @var string
	 * @ORM\Column(name="slug", type="string", length=190, unique=true, nullable=true)
	 * @Gedmo\Slug(fields={"titleForSlug"}, updatable=false)
	 */
	protected $slug;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="title_for_slug", type="string", length=190)
	 */
	protected $titleForSlug;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\BlogCategories", inversedBy="blog")
	 * @ORM\JoinTable(name="rel_blog_categories",
	 *      joinColumns={@ORM\JoinColumn(name="blog_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
	 *      )
	 */
	protected $category;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $images;
	
	public function __construct()
	{
		if ( $this->createdAt == null ) {
			$this->setCreatedAt( new \DateTime( 'NOW' ) );
		}
		$this->setUpdatedAt( new \DateTime( 'NOW' ) );
		$this->category = new ArrayCollection();
	}
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getTitle(): ?string
	{
		return $this->translate( null, false )->getTitle();
	}
	
	public function setTitle( string $title ): self
	{
		$this->translate( null, false )->setTitle( $title );
		
		return $this;
	}
	
	public function getContent(): ?string
	{
		return $this->translate( null, false )->getContent();
	}
	
	public function setContent( string $content ): self
	{
		$this->translate( null, false )->setContent( $content );
		
		return $this;
	}
	
	public function getDescription(): ?string
	{
		return $this->translate( null, false )->getDescription();
	}
	
	public function setDescription( string $description ): self
	{
		$this->translate( null, false )->setDescription( $description );
		
		return $this;
	}
	
	public function getActive(): ?int
	{
		return $this->translate( null, false )->getActive();
	}
	
	public function setActive( int $active ): self
	{
		$this->translate( null, false )->setActive( $active );
		
		return $this;
	}
	
	public function getCreatedAt(): ?\DateTimeInterface
	{
		return $this->createdAt;
	}
	
	public function setCreatedAt( \DateTimeInterface $createdAt ): self
	{
		$this->createdAt = $createdAt;
		
		return $this;
	}
	
	public function getUpdatedAt(): ?\DateTimeInterface
	{
		return $this->updatedAt;
	}
	
	public function setUpdatedAt( \DateTimeInterface $updatedAt ): self
	{
		$this->updatedAt = $updatedAt;
		
		return $this;
	}
	
	public function getMetaImage(): ?Media
	{
		return $this->meta_image;
	}
	
	public function setMetaImage( ?Media $meta_image ): self
	{
		$this->meta_image = $meta_image;
		
		return $this;
	}
	
	public function getMetaTitle(): ?string
	{
		return $this->translate( null, false )->getMetaTitle();
	}
	
	public function setMetaTitle( ?string $metaTitle ): self
	{
		$this->translate( null, false )->setMetaTitle( $metaTitle );
		
		return $this;
	}
	
	public function getMetaKeywords(): ?string
	{
		return $this->translate( null, false )->getMetaKeywords();
	}
	
	public function setMetaKeywords( ?string $metaKeywords ): self
	{
		$this->translate( null, false )->setMetaKeywords( $metaKeywords );
		
		return $this;
	}
	
	public function getMetaDescription(): ?string
	{
		return $this->translate( null, false )->getMetaDescription();
	}
	
	public function setMetaDescription( ?string $metaDescription ): self
	{
		$this->translate( null, false )->setMetaDescription( $metaDescription );
		
		return $this;
	}
	
	public function getMetaCanonical(): ?string
	{
		return $this->translate( null, false )->getMetaCanonical();
	}
	
	public function setMetaCanonical( ?string $metaCanonical ): self
	{
		$this->translate( null, false )->setMetaCanonical( $metaCanonical );
		
		return $this;
	}
	
	public function __toString()
	{
		return $this->getTitle() ?: 'Страница';
	}
	
	public function getLaveledTitle()
	{
		return (string) $this;
	}
	
	public function getSlug(): ?string
	{
		return $this->slug;
	}
	
	public function setSlug( ?string $slug ): self
	{
		$this->slug = $slug;
		
		return $this;
	}
	
	public function setTitleForSlug( $titleForSlug )
	{
		$this->titleForSlug = $titleForSlug;
		
		return $this;
	}
	
	public function getTitleForSlug()
	{
		return $this->titleForSlug;
	}
	
	/**
	 *
	 * @ORM\PrePersist
	 *
	 * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
	 */
	public function prePersist( LifecycleEventArgs $args )
	{
		$entity = $args->getEntity();
		if ( $entity->translate( 'ru' )->getTitle() ) {
			$entity->setTitleForSlug( $this->translate( 'ru' )->getTitle() );
		} else {
			foreach ( $entity->getTranslations() as $translation ) {
				$entity->setTitleForSlug( $translation->getTitle() );
			}
		}
	}
	
	/**
	 *
	 * @ORM\PreFlush()
	 * @param \Doctrine\ORM\Event\PreFlushEventArgs $args
	 *
	 */
	public function preFlush( PreFlushEventArgs $args )
	{
		$entity = $args->getEntityManager()->getRepository( get_class( $this ) )->findOneBy( [ 'id' => $this->id ] );
		if ( $entity ) {
			if ( $entity->translate( 'ru' )->getTitle() ) {
				$entity->setTitleForSlug( $this->translate( 'ru' )->getTitle() );
			} else {
				foreach ( $entity->getTranslations() as $translation ) {
					$entity->setTitleForSlug( $translation->getTitle() );
				}
			}
		}
	}
	
	public function getLocale()
	{
		return $this->getCurrentLocale();
	}
	
	public function setLocale( $locale )
	{
		$this->setCurrentLocale( $locale );
		
		return $this;
	}
	
	/**
	 * @return Collection|BlogCategories[]
	 */
	public function getCategory(): Collection
	{
		return $this->category;
	}
	
	public function addCategory( BlogCategories $category ): self
	{
		if ( ! $this->category->contains( $category ) ) {
			$this->category[] = $category;
		}
		
		return $this;
	}
	
	public function removeCategory( BlogCategories $category ): self
	{
		if ( $this->category->contains( $category ) ) {
			$this->category->removeElement( $category );
		}
		
		return $this;
	}
	
	public function getImages(): ?Media
	{
		return $this->images;
	}
	
	public function setImages( ?Media $images ): self
	{
		$this->images = $images;
		
		return $this;
	}
}
