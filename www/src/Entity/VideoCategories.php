<?php

namespace App\Entity;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Traits\MetaImageTrait;
use App\Traits\TimeTrackTrait as TimeTrack;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * VideoCategories
 *
 * @ORM\Entity(repositoryClass="App\Repository\VideoCategoriesRepository")
 * @ORM\Table(name="video_categories")
 * @ORM\HasLifecycleCallbacks
 */
class VideoCategories implements TranslatableInterface
{
	use ORMBehaviors\Translatable\Translatable;
	use TimeTrack;
	use MetaImageTrait;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Video", mappedBy="category")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $video;
	
	/**
	 * @var string
	 * @ORM\Column(name="slug", type="string", length=255, unique=true, nullable=true)
	 * @Gedmo\Slug(fields={"titleForSlug"}, updatable=false)
	 */
	protected $slug;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="title_for_slug", type="string", length=255)
	 */
	protected $titleForSlug;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $images;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Video", inversedBy="categoryVideoTop")
	 * @ORM\JoinTable(name="rel_video_top_category",
	 *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="video_id", referencedColumnName="video_id")}
	 *      )
	 */
	protected $videoTop;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\HeaderMenu", mappedBy="staticVideoCategoryId")
	 * @ORM\JoinColumn(name="header_menu", nullable=true, referencedColumnName="id")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $headerMenu;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\FooterMenu", mappedBy="staticVideoCategoryId")
	 * @ORM\JoinColumn(name="footer_menu", nullable=true, referencedColumnName="id")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $footerMenu;
	
	public function getMetaImage(): ?Media
	{
		return $this->meta_image;
	}
	
	public function setMetaImage( ?Media $meta_image ): self
	{
		$this->meta_image = $meta_image;
		
		return $this;
	}
	
	public function __construct()
	{
		if ( $this->createdAt == null ) {
			$this->setCreatedAt( new \DateTime( 'NOW' ) );
		}
		$this->setUpdatedAt( new \DateTime( 'NOW' ) );
		$this->video      = new ArrayCollection();
		$this->videoTop   = new ArrayCollection();
		$this->headerMenu = new ArrayCollection();
		$this->footerMenu = new ArrayCollection();
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
	
	public function getTitleAbbreviated(): ?string
	{
		return $this->translate( null, false )->getTitleAbbreviated();
	}
	
	public function setTitleAbbreviated( string $titleAbbreviated ): self
	{
		$this->translate( null, false )->setTitleAbbreviated( $titleAbbreviated );
		
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
	
	public function getContent(): ?string
	{
		return $this->translate( null, false )->getContent();
	}

	public function setContent( string $content ): self
	{
		$this->translate( null, false )->setContent( $content );

		return $this;
	}

    public function getSubTitle(): ?string
    {
        return $this->translate( null, false )->getSubTitle();
    }

    public function setSubTitle( string $subTitle ): self
    {
        $this->translate( null, false )->setSubTitle( $subTitle );

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
	
	public function __toString()
	{
		return $this->getTitle() ?: 'Категория';
	}
	
	public function getLaveledTitle()
	{
		return (string) $this->getTitle();
	}
	
	/**
	 * @return Collection|Video[]
	 */
	public function getVideo(): Collection
	{
		return $this->video;
	}
	
	public function addVideo( Video $video ): self
	{
		if ( ! $this->video->contains( $video ) ) {
			$this->video[] = $video;
			$video->setCategory( $this );
		}
		
		return $this;
	}
	
	public function removeVideo( Video $video ): self
	{
		if ( $this->video->contains( $video ) ) {
			$this->video->removeElement( $video );
			// set the owning side to null (unless already changed)
			if ( $video->getCategory() === $this ) {
				$video->setCategory( null );
			}
		}
		
		return $this;
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
	
	public function getImages(): ?Media
	{
		return $this->images;
	}
	
	public function setImages( ?Media $images ): self
	{
		$this->images = $images;
		
		return $this;
	}
	
	/**
	 * @return Collection|Video[]
	 */
	public function getVideoTop(): Collection
	{
		return $this->videoTop;
	}
	
	public function addVideoTop( Video $videoTop ): self
	{
		if ( ! $this->videoTop->contains( $videoTop ) ) {
			$this->videoTop[] = $videoTop;
		}
		
		return $this;
	}
	
	public function removeVideoTop( Video $videoTop ): self
	{
		if ( $this->videoTop->contains( $videoTop ) ) {
			$this->videoTop->removeElement( $videoTop );
		}
		
		return $this;
	}
	
	/**
	 * @return Collection|HeaderMenu[]
	 */
	public function getHeaderMenu(): Collection
	{
		return $this->headerMenu;
	}
	
	public function addHeaderMenu( HeaderMenu $headerMenu ): self
	{
		if ( ! $this->headerMenu->contains( $headerMenu ) ) {
			$this->headerMenu[] = $headerMenu;
			$headerMenu->setStaticVideoCategoryId( $this );
		}
		
		return $this;
	}
	
	public function removeHeaderMenu( HeaderMenu $headerMenu ): self
	{
		if ( $this->headerMenu->contains( $headerMenu ) ) {
			$this->headerMenu->removeElement( $headerMenu );
			// set the owning side to null (unless already changed)
			if ( $headerMenu->getStaticVideoCategoryId() === $this ) {
				$headerMenu->setStaticVideoCategoryId( null );
			}
		}
		
		return $this;
	}
	
	/**
	 * @return Collection|FooterMenu[]
	 */
	public function getFooterMenu(): Collection
	{
		return $this->footerMenu;
	}
	
	public function addFooterMenu( FooterMenu $footerMenu ): self
	{
		if ( ! $this->footerMenu->contains( $footerMenu ) ) {
			$this->footerMenu[] = $footerMenu;
			$footerMenu->setStaticVideoCategoryId( $this );
		}
		
		return $this;
	}
	
	public function removeFooterMenu( FooterMenu $footerMenu ): self
	{
		if ( $this->footerMenu->contains( $footerMenu ) ) {
			$this->footerMenu->removeElement( $footerMenu );
			// set the owning side to null (unless already changed)
			if ( $footerMenu->getStaticVideoCategoryId() === $this ) {
				$footerMenu->setStaticVideoCategoryId( null );
			}
		}
		
		return $this;
	}

    public function getSortOrder(): ?int
    {
        return $this->translate( null, false )->getSortOrder();
    }

    public function setSortOrder( int $sortOrder ): self
    {
        $this->translate( null, false )->setSortOrder( $sortOrder );

        return $this;
    }
	
}
