<?php

namespace App\Entity;

use App\Traits\ActivityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * VideoRender
 *
 * @ORM\Entity(repositoryClass="App\Repository\VideoRenderRepository")
 * @ORM\Table(name="video_render")
 */
class VideoRender
{
    use ActivityTrait;
    
    /**
     * @ORM\Id
     * @ORM\Column(name="render_id", type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer", length=255)
     */
    protected $type;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="render")
     * @ORM\JoinColumn(name="id_users", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $users;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\VideoRenderPlaceholder", mappedBy="render", cascade={"persist"},
     *     orphanRemoval=true )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $placeholder;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Video", inversedBy="render")
     * @ORM\JoinColumn(name="video", nullable=true, referencedColumnName="video_id", onDelete="SET NULL")
     */
    protected $video;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    protected $status;
    
    /**
     * @ORM\Column(name="project_uid", nullable=true, type="string", length=100)
     */
    protected $projectUid;
    
    /**
     * @ORM\Column(name="youtube_link", nullable=true, type="string", length=100)
     */
    protected $youtubeLink;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Order", inversedBy="render", fetch="EAGER")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="order_id")
     */
    protected $order;
    
    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="start_at", type="datetime", nullable=true)
     */
    protected $startAt;
    
    /**
     * @var \DateTime
     * @Gedmo\Timestampable
     * @ORM\Column(name="end_at", type="datetime", nullable=true)
     */
    protected $endAt;
    
    /**
     * @ORM\Column(name="worker", nullable=true, type="string", length=100)
     */
    protected $worker;
    
    public function __construct()
    {
        if ($this->startAt == null) {
            $this->setStartAt(new \DateTime('NOW'));
        }
        $this->placeholder = new ArrayCollection();
    }
    
    public function __toString()
    {
        return $this->getUsers()->getEmail() ?: 'Рендеринг видео';
    }
    
    public function __clone()
    {
        $this->id = null;
    }
    
    public function getLaveledTitle()
    {
        return (string)$this->getUsers()->getEmail();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getUsers(): ?User
    {
        return $this->users;
    }
    
    public function setUsers(?User $users): self
    {
        $this->users = $users;
        
        return $this;
    }
    
    /**
     * @return Collection|VideoRenderPlaceholder[]
     */
    public function getPlaceholder(): Collection
    {
        return $this->placeholder;
    }
    
    public function addPlaceholder(VideoRenderPlaceholder $placeholder): self
    {
        if ( ! $this->placeholder->contains($placeholder)) {
            $this->placeholder[] = $placeholder;
            $placeholder->setRender($this);
        }
        
        return $this;
    }
    
    public function removePlaceholder(VideoRenderPlaceholder $placeholder): self
    {
        if ($this->placeholder->contains($placeholder)) {
            $this->placeholder->removeElement($placeholder);
            // set the owning side to null (unless already changed)
            if ($placeholder->getRender() === $this) {
                $placeholder->setRender(null);
            }
        }
        
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
    
    public function getVideo(): ?Video
    {
        return $this->video;
    }
    
    public function setVideo(?Video $video): self
    {
        $this->video = $video;
        
        return $this;
    }
    
    public function getProjectUid(): ?string
    {
        return $this->projectUid;
    }
    
    public function setProjectUid(string $projectUid): self
    {
        $this->projectUid = $projectUid;
        
        return $this;
    }
    
    public function getOrder(): ?Order
    {
        return $this->order;
    }
    
    public function setOrder(?Order $order): self
    {
        $this->order = $order;
        
        return $this;
    }
    
    public function getType(): ?int
    {
        return $this->type;
    }
    
    public function setType(int $type): self
    {
        $this->type = $type;
        
        return $this;
    }
    
    public function getStatus(): ?string
    {
        return $this->status;
    }
    
    public function setStatus(?string $status): self
    {
        $this->status = $status;
        
        return $this;
    }
    
    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }
    
    public function setStartAt(?\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;
        
        return $this;
    }
    
    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }
    
    public function setEndAt(?\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;
        
        return $this;
    }
    
    public function getYoutubeLink(): ?string
    {
        return $this->youtubeLink;
    }
    
    public function setYoutubeLink(?string $youtubeLink): self
    {
        $this->youtubeLink = $youtubeLink;
        
        return $this;
    }
    
    public function getWorker(): ?string
    {
        return $this->worker;
    }
    
    public function setWorker(?string $worker): self
    {
        $this->worker = $worker;
        
        return $this;
    }
    
}
