<?php

namespace App\Entity;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Traits\MetaImageTrait as MetaImage;
use App\Traits\TimeTrackTrait as TimeTrack;
use App\Validator\Constraints as ConstraintsAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * Page
 *
 * @ORM\Entity(repositoryClass="App\Repository\PageRepository")
 * @ORM\Table(name="page")
 * @ORM\HasLifecycleCallbacks
 * @ConstraintsAssert\ContainsUniqueCheckRule()
 */
class Page implements TranslatableInterface
{
	use ORMBehaviors\Translatable\Translatable;
	use MetaImage;
	use TimeTrack;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
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
	 * @var Page
	 * @ORM\ManyToOne(targetEntity="Page", inversedBy="children" )
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id" , onDelete="SET NULL")
	 */
	protected $parent;
	
	/**
	 * @var Page[]
	 *
	 * @ORM\OneToMany(targetEntity="Page", mappedBy="parent")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $children;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="type", type="integer", length=255)
	 */
	protected $type;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\HeaderMenu", mappedBy="staticPageId")
	 * @ORM\JoinColumn(name="header_menu", nullable=true, referencedColumnName="id")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $headerMenu;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\FooterMenu", mappedBy="staticPageId")
	 * @ORM\JoinColumn(name="footer_menu", nullable=true, referencedColumnName="id")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $footerMenu;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\PageHomeSlider", mappedBy="page", cascade={"persist"},
	 *     orphanRemoval=true )
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $homeImageHeader;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Video", inversedBy="displayHomeVideo")
	 * @ORM\JoinTable(name="rel_video_display_home",
	 *      joinColumns={@ORM\JoinColumn(name="page_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="video_id", referencedColumnName="video_id")}
	 *      )
	 */
	protected $homeVideoGreetings;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Video", inversedBy="displayHomeVideoNovelty")
	 * @ORM\JoinTable(name="rel_video_display_home_novelty",
	 *      joinColumns={@ORM\JoinColumn(name="page_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="video_id", referencedColumnName="video_id")}
	 *      )
	 */
	protected $homeVideoNovelty;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\ReviewVideo", inversedBy="displayHomeReview")
	 * @ORM\JoinTable(name="rel_review_video_display_home",
	 *      joinColumns={@ORM\JoinColumn(name="page_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="review_video_id", referencedColumnName="review_video_id")}
	 *      )
	 */
	protected $homeVideoReview;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Review", inversedBy="displayHomeReview")
	 * @ORM\JoinTable(name="rel_review_display_home",
	 *      joinColumns={@ORM\JoinColumn(name="page_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="review_id", referencedColumnName="review_id")}
	 *      )
	 */
	protected $homeReview;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $homeVideoHowWeDoIt;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $homeImageHowWeDoIt;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $catalogExamplesVideo;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $catalogExamplesImageOnVideo;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $advantageOneIcon;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $advantageTwoIcon;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $advantageThreeIcon;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $advantageFourIcon;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $QaExamplesVideo;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $QaExamplesImageOnVideo;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $aboutExamplesVideo;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $aboutExamplesImageOnVideo;
	
	public function __construct()
	{
		$this->children = new ArrayCollection();
		if ( $this->createdAt == null ) {
			$this->setCreatedAt( new \DateTime( 'NOW' ) );
		}
		$this->setUpdatedAt( new \DateTime( 'NOW' ) );
		$this->headerMenu         = new ArrayCollection();
		$this->footerMenu         = new ArrayCollection();
		$this->homeVideoGreetings = new ArrayCollection();
		$this->homeVideoReview    = new ArrayCollection();
		$this->homeReview         = new ArrayCollection();
		$this->homeImageHeader    = new ArrayCollection();
		$this->homeVideoNovelty   = new ArrayCollection();
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
	
	public function getParent(): ?self
	{
		return $this->parent;
	}
	
	public function setParent( ?self $parent ): self
	{
		$this->parent = $parent;
		
		return $this;
	}
	
	/**
	 * @return Collection|Page[]
	 */
	public function getChildren(): Collection
	{
		return $this->children;
	}
	
	public function addChild( Page $child ): self
	{
		if ( ! $this->children->contains( $child ) ) {
			$this->children[] = $child;
			$child->setParent( $this );
		}
		
		return $this;
	}
	
	public function removeChild( Page $child ): self
	{
		if ( $this->children->contains( $child ) ) {
			$this->children->removeElement( $child );
			// set the owning side to null (unless already changed)
			if ( $child->getParent() === $this ) {
				$child->setParent( null );
			}
		}
		
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
	
	public function getType(): ?int
	{
		return $this->type;
	}
	
	public function setType( int $type ): self
	{
		$this->type = $type;
		
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
			$headerMenu->setStaticPageId( $this );
		}
		
		return $this;
	}
	
	public function removeHeaderMenu( HeaderMenu $headerMenu ): self
	{
		if ( $this->headerMenu->contains( $headerMenu ) ) {
			$this->headerMenu->removeElement( $headerMenu );
			// set the owning side to null (unless already changed)
			if ( $headerMenu->getStaticPageId() === $this ) {
				$headerMenu->setStaticPageId( null );
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
			$footerMenu->setStaticPageId( $this );
		}
		
		return $this;
	}
	
	public function removeFooterMenu( FooterMenu $footerMenu ): self
	{
		if ( $this->footerMenu->contains( $footerMenu ) ) {
			$this->footerMenu->removeElement( $footerMenu );
			// set the owning side to null (unless already changed)
			if ( $footerMenu->getStaticPageId() === $this ) {
				$footerMenu->setStaticPageId( null );
			}
		}
		
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
	 * @return Collection|Video[]
	 */
	public function getHomeVideoGreetings(): Collection
	{
		return $this->homeVideoGreetings;
	}
	
	public function addHomeVideoGreeting( Video $homeVideoGreeting ): self
	{
		if ( ! $this->homeVideoGreetings->contains( $homeVideoGreeting ) ) {
			$this->homeVideoGreetings[] = $homeVideoGreeting;
		}
		
		return $this;
	}
	
	public function removeHomeVideoGreeting( Video $homeVideoGreeting ): self
	{
		if ( $this->homeVideoGreetings->contains( $homeVideoGreeting ) ) {
			$this->homeVideoGreetings->removeElement( $homeVideoGreeting );
		}
		
		return $this;
	}
	
	/**
	 * @return Collection|ReviewVideo[]
	 */
	public function getHomeVideoReview(): Collection
	{
		return $this->homeVideoReview;
	}
	
	public function addHomeVideoReview( ReviewVideo $homeVideoReview ): self
	{
		if ( ! $this->homeVideoReview->contains( $homeVideoReview ) ) {
			$this->homeVideoReview[] = $homeVideoReview;
		}
		
		return $this;
	}
	
	public function removeHomeVideoReview( ReviewVideo $homeVideoReview ): self
	{
		if ( $this->homeVideoReview->contains( $homeVideoReview ) ) {
			$this->homeVideoReview->removeElement( $homeVideoReview );
		}
		
		return $this;
	}
	
	/**
	 * @return Collection|Review[]
	 */
	public function getHomeReview(): Collection
	{
		return $this->homeReview;
	}
	
	public function addHomeReview( Review $homeReview ): self
	{
		if ( ! $this->homeReview->contains( $homeReview ) ) {
			$this->homeReview[] = $homeReview;
		}
		
		return $this;
	}
	
	public function removeHomeReview( Review $homeReview ): self
	{
		if ( $this->homeReview->contains( $homeReview ) ) {
			$this->homeReview->removeElement( $homeReview );
		}
		
		return $this;
	}
	
	public function getHomeContentOurAdvantages(): ?string
	{
		return $this->translate( null, false )->getHomeContentOurAdvantages();
	}
	
	public function setHomeContentOurAdvantages( ?string $homeContentOurAdvantages ): self
	{
		$this->translate( null, false )->setHomeContentOurAdvantages( $homeContentOurAdvantages );
		
		return $this;
	}
	
	public function getAdvantageOneTitle(): ?string
	{
		return $this->translate( null, false )->getAdvantageOneTitle();
	}
	
	public function setAdvantageOneTitle( ?string $advantageOneTitle ): self
	{
		$this->translate( null, false )->setAdvantageOneTitle( $advantageOneTitle );
		
		return $this;
	}
	
	public function getAdvantageOneText(): ?string
	{
		return $this->translate( null, false )->getAdvantageOneText();
	}
	
	public function setAdvantageOneText( ?string $advantageOneText ): self
	{
		$this->translate( null, false )->setAdvantageOneText( $advantageOneText );
		
		return $this;
	}
	
	public function getAdvantageTwoTitle(): ?string
	{
		return $this->translate( null, false )->getAdvantageTwoTitle();
	}
	
	public function setAdvantageTwoTitle( ?string $advantageTwoTitle ): self
	{
		$this->translate( null, false )->setAdvantageTwoTitle( $advantageTwoTitle );
		
		return $this;
	}
	
	public function getAdvantageTwoText(): ?string
	{
		return $this->translate( null, false )->getAdvantageTwoText();
	}
	
	public function setAdvantageTwoText( ?string $advantageTwoText ): self
	{
		$this->translate( null, false )->setAdvantageTwoText( $advantageTwoText );
		
		return $this;
	}
	
	public function getAdvantageThreeTitle(): ?string
	{
		return $this->translate( null, false )->getAdvantageThreeTitle();
	}
	
	public function setAdvantageThreeTitle( ?string $advantageThreeTitle ): self
	{
		$this->translate( null, false )->setAdvantageThreeTitle( $advantageThreeTitle );
		
		return $this;
	}
	
	public function getAdvantageThreeText(): ?string
	{
		return $this->translate( null, false )->getAdvantageThreeText();
	}
	
	public function setAdvantageThreeText( ?string $advantageThreeText ): self
	{
		$this->translate( null, false )->setAdvantageThreeText( $advantageThreeText );
		
		return $this;
	}
	
	public function getAdvantageFourTitle(): ?string
	{
		return $this->translate( null, false )->getAdvantageFourTitle();
	}
	
	public function setAdvantageFourTitle( ?string $advantageFourTitle ): self
	{
		$this->translate( null, false )->setAdvantageFourTitle( $advantageFourTitle );
		
		return $this;
	}
	
	public function getAdvantageFourText(): ?string
	{
		return $this->translate( null, false )->getAdvantageFourText();
	}
	
	public function setAdvantageFourText( ?string $advantageFourText ): self
	{
		$this->translate( null, false )->setAdvantageFourText( $advantageFourText );
		
		return $this;
	}
	
	public function getPageContentTitle(): ?string
	{
		return $this->translate( null, false )->getPageContentTitle();
	}
	
	public function setPageContentTitle( ?string $pageContentTitle ): self
	{
		$this->translate( null, false )->setPageContentTitle( $pageContentTitle );
		
		return $this;
	}
	
	public function getPageContentSeo(): ?string
	{
		return $this->translate( null, false )->getPageContentSeo();
	}
	
	public function setPageContentSeo( ?string $pageContentSeo ): self
	{
		$this->translate( null, false )->setPageContentSeo( $pageContentSeo );
		
		return $this;
	}
	
	public function getAdvantageOneIcon(): ?Media
	{
		return $this->advantageOneIcon;
	}
	
	public function setAdvantageOneIcon( ?Media $advantageOneIcon ): self
	{
		$this->advantageOneIcon = $advantageOneIcon;
		
		return $this;
	}
	
	public function getAdvantageTwoIcon(): ?Media
	{
		return $this->advantageTwoIcon;
	}
	
	public function setAdvantageTwoIcon( ?Media $advantageTwoIcon ): self
	{
		$this->advantageTwoIcon = $advantageTwoIcon;
		
		return $this;
	}
	
	public function getAdvantageThreeIcon(): ?Media
	{
		return $this->advantageThreeIcon;
	}
	
	public function setAdvantageThreeIcon( ?Media $advantageThreeIcon ): self
	{
		$this->advantageThreeIcon = $advantageThreeIcon;
		
		return $this;
	}
	
	public function getAdvantageFourIcon(): ?Media
	{
		return $this->advantageFourIcon;
	}
	
	public function setAdvantageFourIcon( ?Media $advantageFourIcon ): self
	{
		$this->advantageFourIcon = $advantageFourIcon;
		
		return $this;
	}
	
	/**
	 * @return Collection|PageHomeSlider[]
	 */
	public function getHomeImageHeader(): Collection
	{
		return $this->homeImageHeader;
	}
	
	public function addHomeImageHeader( PageHomeSlider $homeImageHeader ): self
	{
		if ( ! $this->homeImageHeader->contains( $homeImageHeader ) ) {
			$this->homeImageHeader[] = $homeImageHeader;
			$homeImageHeader->setPage( $this );
		}
		
		return $this;
	}
	
	public function removeHomeImageHeader( PageHomeSlider $homeImageHeader ): self
	{
		if ( $this->homeImageHeader->contains( $homeImageHeader ) ) {
			$this->homeImageHeader->removeElement( $homeImageHeader );
			// set the owning side to null (unless already changed)
			if ( $homeImageHeader->getPage() === $this ) {
				$homeImageHeader->setPage( null );
			}
		}
		
		return $this;
	}
	
	public function getHomeVideoHowWeDoIt(): ?Media
	{
		return $this->homeVideoHowWeDoIt;
	}
	
	public function setHomeVideoHowWeDoIt( ?Media $homeVideoHowWeDoIt ): self
	{
		$this->homeVideoHowWeDoIt = $homeVideoHowWeDoIt;
		
		return $this;
	}
	
	public function getHomeImageHowWeDoIt(): ?Media
	{
		return $this->homeImageHowWeDoIt;
	}
	
	public function setHomeImageHowWeDoIt( ?Media $homeImageHowWeDoIt ): self
	{
		$this->homeImageHowWeDoIt = $homeImageHowWeDoIt;
		
		return $this;
	}
	
	public function getCatalogExamplesVideo(): ?Media
	{
		return $this->catalogExamplesVideo;
	}
	
	public function setCatalogExamplesVideo( ?Media $catalogExamplesVideo ): self
	{
		$this->catalogExamplesVideo = $catalogExamplesVideo;
		
		return $this;
	}
	
	public function getCatalogExamplesImageOnVideo(): ?Media
	{
		return $this->catalogExamplesImageOnVideo;
	}
	
	public function setCatalogExamplesImageOnVideo( ?Media $catalogExamplesImageOnVideo ): self
	{
		$this->catalogExamplesImageOnVideo = $catalogExamplesImageOnVideo;
		
		return $this;
	}
	
	public function getQaExamplesVideo(): ?Media
	{
		return $this->QaExamplesVideo;
	}
	
	public function setQaExamplesVideo( ?Media $QaExamplesVideo ): self
	{
		$this->QaExamplesVideo = $QaExamplesVideo;
		
		return $this;
	}
	
	public function getQaExamplesImageOnVideo(): ?Media
	{
		return $this->QaExamplesImageOnVideo;
	}
	
	public function setQaExamplesImageOnVideo( ?Media $QaExamplesImageOnVideo ): self
	{
		$this->QaExamplesImageOnVideo = $QaExamplesImageOnVideo;
		
		return $this;
	}
	
	public function getAboutExamplesVideo(): ?Media
	{
		return $this->aboutExamplesVideo;
	}
	
	public function setAboutExamplesVideo( ?Media $aboutExamplesVideo ): self
	{
		$this->aboutExamplesVideo = $aboutExamplesVideo;
		
		return $this;
	}
	
	public function getAboutExamplesImageOnVideo(): ?Media
	{
		return $this->aboutExamplesImageOnVideo;
	}
	
	public function setAboutExamplesImageOnVideo( ?Media $aboutExamplesImageOnVideo ): self
	{
		$this->aboutExamplesImageOnVideo = $aboutExamplesImageOnVideo;
		
		return $this;
	}
	
	/**
	 * @return Collection|Video[]
	 */
	public function getHomeVideoNovelty(): Collection
	{
		return $this->homeVideoNovelty;
	}
	
	public function addHomeVideoNovelty( Video $homeVideoNovelty ): self
	{
		if ( ! $this->homeVideoNovelty->contains( $homeVideoNovelty ) ) {
			$this->homeVideoNovelty[] = $homeVideoNovelty;
		}
		
		return $this;
	}
	
	public function removeHomeVideoNovelty( Video $homeVideoNovelty ): self
	{
		if ( $this->homeVideoNovelty->contains( $homeVideoNovelty ) ) {
			$this->homeVideoNovelty->removeElement( $homeVideoNovelty );
		}
		
		return $this;
	}
}
