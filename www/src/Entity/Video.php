<?php

namespace App\Entity;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Traits\ActivityTrait;
use App\Traits\TimeTrackTrait as TimeTrack;
use Beelab\TagBundle\Tag\TaggableInterface;
use Beelab\TagBundle\Tag\TagInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Video
 *
 * @ORM\Entity(repositoryClass="App\Repository\VideoRepository")
 * @ORM\Table(name="video")
 */
class Video implements TaggableInterface
{
    use TimeTrack;
    use ActivityTrait;
    
    /**
     * @ORM\Id
     * @ORM\Column(name="video_id", type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=128, unique=true, nullable=true)
     */
    protected $slug;
    
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;
    
    /**
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;
    
    /**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     */
    protected $images;

    /**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     */
    protected $preloader;
    
    /**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     */
    protected $banner;
    
    /**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     */
    protected $trailer;
    
    /**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     */
    protected $trailerImage;
    
    /**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     */
    protected $congratulationExample;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\VideoCategories", inversedBy="video")
     * @ORM\JoinTable(name="rel_video_categories",
     *      joinColumns={@ORM\JoinColumn(name="video_id", referencedColumnName="video_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     *      )
     */
    protected $category;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Holidays", inversedBy="video")
     * @ORM\JoinTable(name="rel_event_holidays",
     *      joinColumns={@ORM\JoinColumn(name="video_id", referencedColumnName="video_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="holidays_id", referencedColumnName="id")}
     *      )
     */
    protected $event;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="video")
     * @ORM\JoinColumn(name="orders", nullable=true, referencedColumnName="order_id")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $orders;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="sex", type="integer", length=255)
     */
    protected $sex;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="age_from", nullable=true, type="integer", length=255)
     */
    protected $ageFrom;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="age_up", nullable=true, type="integer", length=255)
     */
    protected $ageUp;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\VideoPlaceholder", mappedBy="video", cascade={"persist"},
     *     orphanRemoval=true )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $placeholder;
    
    /**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     */
    protected $project;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\VideoRender", mappedBy="video")
     * @ORM\JoinColumn(name="render", nullable=true, referencedColumnName="render_id")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $render;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\VideoRenderFile", mappedBy="video")
     * @ORM\JoinColumn(name="render_file", nullable=true, referencedColumnName="id")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $renderFile;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="variation", nullable=true, type="integer", length=255)
     */
    protected $variation;
    
    /**
     * @var float
     *
     * @ORM\Column(name="price_uah", nullable=true, type="float", length=255)
     */
    protected $priceUah;
    
    /**
     * @var float
     *
     * @ORM\Column(name="price_rub", nullable=true, type="float", length=255)
     */
    protected $priceRub;
    
    /**
     * @var float
     *
     * @ORM\Column(name="price_eur", nullable=true, type="float", length=255)
     */
    protected $priceEur;
    
    /**
     * @var float
     *
     * @ORM\Column(name="price_usd", nullable=true, type="float", length=255)
     */
    protected $priceUsd;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="discount", nullable=true, type="integer", length=255)
     */
    protected $discount;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="hide_price", nullable=true, type="boolean", length=255)
     */
    protected $hidePrice;
    
    /**
     * @var string
     *
     * @ORM\Column(name="locale", nullable=true, type="string", length=255)
     */
    protected $locale;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="number_persons", nullable=true, type="integer", length=255)
     */
    protected $numberPersons;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Page", mappedBy="homeVideoGreetings")
     */
    protected $displayHomeVideo;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\VideoCategories", mappedBy="videoTop")
     */
    protected $categoryVideoTop;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Page", mappedBy="homeVideoNovelty")
     */
    protected $displayHomeVideoNovelty;
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag")
     * @ORM\JoinTable(name="rel_video_tags",
     *      joinColumns={@ORM\JoinColumn(name="video_id", referencedColumnName="video_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     *      ))
     */
    protected $tags;
    
    protected $tagsText;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="position", nullable=true, type="integer", length=255)
     */
    protected $position = 0;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="position_category", nullable=true, type="integer", length=255)
     */
    protected $positionCategory = 0;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="skip_demo", nullable=true, type="boolean", length=255)
     */
    protected $skipDemo = false;
    
    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated;
    
    /**
     * @var string
     *
     * @ORM\Column(name="content_seo", type="text", nullable=true)
     */
    protected $pageContentSeo;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Review", inversedBy="displayVideoReview")
     * @ORM\JoinTable(name="rel_review_display_video",
     *      joinColumns={@ORM\JoinColumn(name="video_id", referencedColumnName="video_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="review_id", referencedColumnName="review_id")}
     *      )
     */
    protected $review;
    
    public function __construct()
    {
        if ($this->createdAt == null) {
            $this->setCreatedAt(new \DateTime('NOW'));
        }
        $this->setUpdatedAt(new \DateTime('NOW'));
        $this->orders                  = new ArrayCollection();
        $this->placeholder             = new ArrayCollection();
        $this->render                  = new ArrayCollection();
        $this->event                   = new ArrayCollection();
        $this->displayHomeVideo        = new ArrayCollection();
        $this->renderFile              = new ArrayCollection();
        $this->displayHomeVideoNovelty = new ArrayCollection();
        $this->categoryVideoTop        = new ArrayCollection();
        $this->category                = new ArrayCollection();
        $this->tags                    = new ArrayCollection();
        $this->review                  = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getTitle(): ?string
    {
        return $this->title;
    }
    
    public function setTitle(string $title): self
    {
        $this->title = $title;
        
        return $this;
    }
    
    public function getActive(): ?int
    {
        return $this->active;
    }
    
    public function setActive(int $active): self
    {
        $this->active = $active;
        
        return $this;
    }
    
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
    
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        
        return $this;
    }
    
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
    
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        
        return $this;
    }
    
    public function __toString()
    {
        return $this->getTitle() ?: 'Видео';
    }
    
    public function getLaveledTitle()
    {
        return (string)$this->getTitle();
    }
    
    public function getImages(): ?Media
    {
        return $this->images;
    }
    
    public function setImages(?Media $images): self
    {
        $this->images = $images;
        
        return $this;
    }

    public function getPreloader(): ?Media
    {
        return $this->preloader;
    }

    public function setPreloader(?Media $preloader): self
    {
        $this->preloader = $preloader;

        return $this;
    }
    
    public function getTrailer(): ?Media
    {
        return $this->trailer;
    }
    
    public function setTrailer(?Media $trailer): self
    {
        $this->trailer = $trailer;
        
        return $this;
    }
    
    public function getCongratulationExample(): ?Media
    {
        return $this->congratulationExample;
    }
    
    public function setCongratulationExample(?Media $congratulationExample): self
    {
        $this->congratulationExample = $congratulationExample;
        
        return $this;
    }
    
    public function getSex(): ?int
    {
        return $this->sex;
    }
    
    public function setSex(int $sex): self
    {
        $this->sex = $sex;
        
        return $this;
    }
    
    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }
    
    public function addOrder(Order $order): self
    {
        if ( ! $this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setVideo($this);
        }
        
        return $this;
    }
    
    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getVideo() === $this) {
                $order->setVideo(null);
            }
        }
        
        return $this;
    }
    
    public function getAgeFrom(): ?int
    {
        return $this->ageFrom;
    }
    
    public function setAgeFrom(?int $ageFrom): self
    {
        $this->ageFrom = $ageFrom;
        
        return $this;
    }
    
    public function getAgeUp(): ?int
    {
        return $this->ageUp;
    }
    
    public function setAgeUp(?int $ageUp): self
    {
        $this->ageUp = $ageUp;
        
        return $this;
    }
    
    /**
     * @return Collection|VideoPlaceholder[]
     */
    public function getPlaceholder(): Collection
    {
        return $this->placeholder;
    }
    
    public function addPlaceholder(VideoPlaceholder $placeholder): self
    {
        if ( ! $this->placeholder->contains($placeholder)) {
            $this->placeholder[] = $placeholder;
            $placeholder->setVideo($this);
        }
        
        return $this;
    }
    
    public function removePlaceholder(VideoPlaceholder $placeholder): self
    {
        if ($this->placeholder->contains($placeholder)) {
            $this->placeholder->removeElement($placeholder);
            // set the owning side to null (unless already changed)
            if ($placeholder->getVideo() === $this) {
                $placeholder->setVideo(null);
            }
        }
        
        return $this;
    }
    
    public function getProject(): ?Media
    {
        return $this->project;
    }
    
    public function setProject(?Media $project): self
    {
        $this->project = $project;
        
        return $this;
    }
    
    /**
     * @return Collection|VideoRender[]
     */
    public function getRender(): Collection
    {
        return $this->render;
    }
    
    public function addRender(VideoRender $render): self
    {
        if ( ! $this->render->contains($render)) {
            $this->render[] = $render;
            $render->setVideo($this);
        }
        
        return $this;
    }
    
    public function removeRender(VideoRender $render): self
    {
        if ($this->render->contains($render)) {
            $this->render->removeElement($render);
            // set the owning side to null (unless already changed)
            if ($render->getVideo() === $this) {
                $render->setVideo(null);
            }
        }
        
        return $this;
    }
    
    public function getPriceUah(): ?float
    {
        return $this->priceUah;
    }
    
    public function setPriceUah(?float $priceUah): self
    {
        $this->priceUah = $priceUah;
        
        return $this;
    }
    
    public function getPriceRub(): ?float
    {
        return $this->priceRub;
    }
    
    public function setPriceRub(?float $priceRub): self
    {
        $this->priceRub = $priceRub;
        
        return $this;
    }
    
    public function getPriceEur(): ?float
    {
        return $this->priceEur;
    }
    
    public function setPriceEur(?float $priceEur): self
    {
        $this->priceEur = $priceEur;
        
        return $this;
    }
    
    public function getPriceUsd(): ?float
    {
        return $this->priceUsd;
    }
    
    public function setPriceUsd(?float $priceUsd): self
    {
        $this->priceUsd = $priceUsd;
        
        return $this;
    }
    
    public function getDiscount(): ?int
    {
        return $this->discount;
    }
    
    public function setDiscount(?int $discount): self
    {
        $this->discount = $discount;
        
        return $this;
    }
    
    public function getHidePrice(): ?bool
    {
        return $this->hidePrice;
    }
    
    public function setHidePrice(?bool $hidePrice): self
    {
        $this->hidePrice = $hidePrice;
        
        return $this;
    }
    
    /**
     * @return Collection|Holidays[]
     */
    public function getEvent(): Collection
    {
        return $this->event;
    }
    
    public function addEvent(Holidays $event): self
    {
        if ( ! $this->event->contains($event)) {
            $this->event[] = $event;
        }
        
        return $this;
    }
    
    public function removeEvent(Holidays $event): self
    {
        if ($this->event->contains($event)) {
            $this->event->removeElement($event);
        }
        
        return $this;
    }
    
    public function getLocale(): ?string
    {
        return $this->locale;
    }
    
    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;
        
        return $this;
    }
    
    public function getNumberPersons(): ?int
    {
        return $this->numberPersons;
    }
    
    public function setNumberPersons(?int $numberPersons): self
    {
        $this->numberPersons = $numberPersons;
        
        return $this;
    }
    
    /**
     * @return Collection|Page[]
     */
    public function getDisplayHomeVideo(): Collection
    {
        return $this->displayHomeVideo;
    }
    
    public function addDisplayHomeVideo(Page $displayHomeVideo): self
    {
        if ( ! $this->displayHomeVideo->contains($displayHomeVideo)) {
            $this->displayHomeVideo[] = $displayHomeVideo;
            $displayHomeVideo->addHomeVideoGreeting($this);
        }
        
        return $this;
    }
    
    public function removeDisplayHomeVideo(Page $displayHomeVideo): self
    {
        if ($this->displayHomeVideo->contains($displayHomeVideo)) {
            $this->displayHomeVideo->removeElement($displayHomeVideo);
            $displayHomeVideo->removeHomeVideoGreeting($this);
        }
        
        return $this;
    }
    
    /**
     * @return Collection|VideoRenderFile[]
     */
    public function getRenderFile(): Collection
    {
        return $this->renderFile;
    }
    
    public function addRenderFile(VideoRenderFile $renderFile): self
    {
        if ( ! $this->renderFile->contains($renderFile)) {
            $this->renderFile[] = $renderFile;
            $renderFile->setVideo($this);
        }
        
        return $this;
    }
    
    public function removeRenderFile(VideoRenderFile $renderFile): self
    {
        if ($this->renderFile->contains($renderFile)) {
            $this->renderFile->removeElement($renderFile);
            // set the owning side to null (unless already changed)
            if ($renderFile->getVideo() === $this) {
                $renderFile->setVideo(null);
            }
        }
        
        return $this;
    }
    
    public function getVariation(): ?int
    {
        return $this->variation;
    }
    
    public function setVariation(?int $variation): self
    {
        $this->variation = $variation;
        
        return $this;
    }
    
    /**
     * @return Collection|Page[]
     */
    public function getDisplayHomeVideoNovelty(): Collection
    {
        return $this->displayHomeVideoNovelty;
    }
    
    public function addDisplayHomeVideoNovelty(Page $displayHomeVideoNovelty): self
    {
        if ( ! $this->displayHomeVideoNovelty->contains($displayHomeVideoNovelty)) {
            $this->displayHomeVideoNovelty[] = $displayHomeVideoNovelty;
            $displayHomeVideoNovelty->addHomeVideoNovelty($this);
        }
        
        return $this;
    }
    
    public function removeDisplayHomeVideoNovelty(Page $displayHomeVideoNovelty): self
    {
        if ($this->displayHomeVideoNovelty->contains($displayHomeVideoNovelty)) {
            $this->displayHomeVideoNovelty->removeElement($displayHomeVideoNovelty);
            $displayHomeVideoNovelty->removeHomeVideoNovelty($this);
        }
        
        return $this;
    }
    
    /**
     * @return Collection|VideoCategories[]
     */
    public function getCategoryVideoTop(): Collection
    {
        return $this->categoryVideoTop;
    }
    
    public function addCategoryVideoTop(VideoCategories $categoryVideoTop): self
    {
        if ( ! $this->categoryVideoTop->contains($categoryVideoTop)) {
            $this->categoryVideoTop[] = $categoryVideoTop;
            $categoryVideoTop->addVideoTop($this);
        }
        
        return $this;
    }
    
    public function removeCategoryVideoTop(VideoCategories $categoryVideoTop): self
    {
        if ($this->categoryVideoTop->contains($categoryVideoTop)) {
            $this->categoryVideoTop->removeElement($categoryVideoTop);
            $categoryVideoTop->removeVideoTop($this);
        }
        
        return $this;
    }
    
    /**
     * @return Collection|VideoCategories[]
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }
    
    public function addCategory(VideoCategories $category): self
    {
        if ( ! $this->category->contains($category)) {
            $this->category[] = $category;
        }
        
        return $this;
    }
    
    public function removeCategory(VideoCategories $category): self
    {
        if ($this->category->contains($category)) {
            $this->category->removeElement($category);
        }
        
        return $this;
    }
    
    public function addTag(TagInterface $tag): void
    {
        if ( ! $this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }
    
    public function removeTag(TagInterface $tag): void
    {
        $this->tags->removeElement($tag);
    }
    
    public function hasTag(TagInterface $tag): bool
    {
        return $this->tags->contains($tag);
    }
    
    public function getTags(): iterable
    {
        return $this->tags;
    }
    
    public function getTagNames(): array
    {
        return empty($this->tagsText) ? [] : \array_map('trim', explode(',', $this->tagsText));
    }
    
    public function setTagsText(?string $tagsText): void
    {
        $this->tagsText = $tagsText;
        $this->updated  = new \DateTimeImmutable();
    }
    
    public function getTagsText(): ?string
    {
        $this->tagsText = \implode(', ', $this->tags->toArray());
        
        return $this->tagsText;
    }
    
    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }
    
    public function setUpdated(?\DateTimeInterface $updated): self
    {
        $this->updated = $updated;
        
        return $this;
    }
    
    public function getPageContentSeo(): ?string
    {
        return $this->pageContentSeo;
    }
    
    public function setPageContentSeo(?string $pageContentSeo): self
    {
        $this->pageContentSeo = $pageContentSeo;
        
        return $this;
    }
    
    public function getBanner(): ?Media
    {
        return $this->banner;
    }
    
    public function setBanner(?Media $banner): self
    {
        $this->banner = $banner;
        
        return $this;
    }
    
    /**
     * @return Collection|Review[]
     */
    public function getReview(): Collection
    {
        return $this->review;
    }
    
    public function addReview(Review $review): self
    {
        if ( ! $this->review->contains($review)) {
            $this->review[] = $review;
        }
        
        return $this;
    }
    
    public function removeReview(Review $review): self
    {
        if ($this->review->contains($review)) {
            $this->review->removeElement($review);
        }
        
        return $this;
    }
    
    public function getSlug(): ?string
    {
        return $this->slug;
    }
    
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        
        return $this;
    }
    
    public function getTrailerImage(): ?Media
    {
        return $this->trailerImage;
    }
    
    public function setTrailerImage(?Media $trailerImage): self
    {
        $this->trailerImage = $trailerImage;
        
        return $this;
    }
    
    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        
        return $this;
    }
    
    public function getPosition(): ?int
    {
        return $this->position;
    }
    
    public function setPosition(?int $position): self
    {
        $this->position = $position;
        
        return $this;
    }

    public function getPositionCategory(): ?int
    {
        return $this->positionCategory;
    }

    public function setPositionCategory(?int $positionCategory): self
    {
        $this->positionCategory = $positionCategory;

        return $this;
    }

    public function getSkipDemo(): ?bool
    {
        return $this->skipDemo;
    }

    public function setSkipDemo(?bool $skipDemo): self
    {
        $this->skipDemo = $skipDemo;

        return $this;
    }
}
