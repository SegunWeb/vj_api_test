<?php

namespace App\Entity;

use App\Traits\ActivityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Review
 *
 * @ORM\Entity(repositoryClass="App\Repository\ReviewRepository")
 * @ORM\Table(name="review")
 */
class Review
{
    use ActivityTrait;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="review_id", type="integer", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="review")
     * @ORM\JoinColumn(name="id_users", referencedColumnName="id")
     */
    protected $users;
    
    /**
     * @ORM\Column(name="users_city", type="string")
     */
    protected $usersCity;
    
    /**
     * @Assert\NotBlank(message="messages.empty_review")
     * @Assert\Length(
     *      min = 3,
     *      max = 1000,
     * )
     * @ORM\Column(name="text", type="text")
     */
    protected $text;
    
    /**
     * @ORM\Column(name="comment_reply", type="text", nullable=true)
     */
    protected $commentReply;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="reviewReply")
     * @ORM\JoinColumn(name="author_comment_reply", nullable=true)
     */
    protected $authorCommentReply;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Page", mappedBy="homeReview")
     */
    protected $displayHomeReview;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Video", mappedBy="review")
     */
    protected $displayVideoReview;
    
    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;
    
    /**
     * @var string
     *
     * @ORM\Column(name="locale", nullable=true, type="string", length=255)
     */
    protected $locale;
    
    /**
     * @var \DateTime
     * @Gedmo\Timestampable
     * @ORM\Column(name="publish_at", type="datetime", nullable=true)
     */
    protected $publishAt;
    
    public function __construct()
    {
        $this->displayHomeReview  = new ArrayCollection();
        $this->displayVideoReview = new ArrayCollection();
    }
    
    public function __toString()
    {
        return $this->getUsers() ? $this->getUsers()->getEmail() : 'Видео-отзывы';
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getUsersCity(): ?string
    {
        return $this->usersCity;
    }
    
    public function setUsersCity(string $usersCity): self
    {
        $this->usersCity = $usersCity;
        
        return $this;
    }
    
    public function getText(): ?string
    {
        return $this->text;
    }
    
    public function setText(string $text): self
    {
        $this->text = $text;
        
        return $this;
    }
    
    public function getCommentReply(): ?string
    {
        return $this->commentReply;
    }
    
    public function setCommentReply(?string $commentReply): self
    {
        $this->commentReply = $commentReply;
        
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
    
    public function getPublishAt(): ?\DateTimeInterface
    {
        return $this->publishAt;
    }
    
    public function setPublishAt(?\DateTimeInterface $publishAt): self
    {
        $this->publishAt = $publishAt;
        
        return $this;
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
    
    public function getAuthorCommentReply(): ?User
    {
        return $this->authorCommentReply;
    }
    
    public function setAuthorCommentReply(?User $authorCommentReply): self
    {
        $this->authorCommentReply = $authorCommentReply;
        
        return $this;
    }
    
    /**
     * @return Collection|Page[]
     */
    public function getDisplayHomeReview(): Collection
    {
        return $this->displayHomeReview;
    }
    
    public function addDisplayHomeReview(Page $displayHomeReview): self
    {
        if ( ! $this->displayHomeReview->contains($displayHomeReview)) {
            $this->displayHomeReview[] = $displayHomeReview;
            $displayHomeReview->addHomeReview($this);
        }
        
        return $this;
    }
    
    public function removeDisplayHomeReview(Page $displayHomeReview): self
    {
        if ($this->displayHomeReview->contains($displayHomeReview)) {
            $this->displayHomeReview->removeElement($displayHomeReview);
            $displayHomeReview->removeHomeReview($this);
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
    
    public function getLocale(): ?string
    {
        return $this->locale;
    }
    
    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;
        
        return $this;
    }
    
    /**
     * @return Collection|Video[]
     */
    public function getDisplayVideoReview(): Collection
    {
        return $this->displayVideoReview;
    }
    
    public function addDisplayVideoReview(Video $displayVideoReview): self
    {
        if ( ! $this->displayVideoReview->contains($displayVideoReview)) {
            $this->displayVideoReview[] = $displayVideoReview;
            $displayVideoReview->addReview($this);
        }
        
        return $this;
    }
    
    public function removeDisplayVideoReview(Video $displayVideoReview): self
    {
        if ($this->displayVideoReview->contains($displayVideoReview)) {
            $this->displayVideoReview->removeElement($displayVideoReview);
            $displayVideoReview->removeReview($this);
        }
        
        return $this;
    }
    
    
}
