<?php

namespace App\Entity;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Traits\ActivityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ReviewVideo
 *
 * @ORM\Entity(repositoryClass="App\Repository\ReviewVideoRepository")
 * @ORM\Table(name="review_video")
 */
class ReviewVideo
{
	use ActivityTrait;
	
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="review_video_id", type="integer", options={"unsigned"=true})
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="reviewVideo")
	 * @ORM\JoinColumn(name="id_users", referencedColumnName="id")
	 */
	protected $users;
	
	/**
	 * @ORM\Column(name="users_city", type="string")
	 */
	protected $usersCity;
	
	/**
	 * @ORM\Column(name="title", type="string")
	 */
	protected $title;
	
	/**
	 * @ORM\Column(name="description", type="text")
	 */
	protected $description;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $video;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $images;
	
	/**
	 * @ORM\Column(name="comment_reply", type="text", nullable=true)
	 */
	protected $commentReply;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Page", mappedBy="homeVideoReview")
	 */
	protected $displayHomeReview;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="locale", nullable=true, type="string", length=255)
	 */
	protected $locale;
	
	/**
	 * @var \DateTime
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="created_at", type="datetime")
	 */
	protected $createdAt;
	
	/**
	 * @var \DateTime
	 * @Gedmo\Timestampable
	 * @ORM\Column(name="publish_at", type="datetime", nullable=true)
	 */
	protected $publishAt;
	
	public function __construct()
	{
		$this->displayHomeReview = new ArrayCollection();
	}
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getUsersCity(): ?string
	{
		return $this->usersCity;
	}
	
	public function setUsersCity( string $usersCity ): self
	{
		$this->usersCity = $usersCity;
		
		return $this;
	}
	
	public function getCommentReply(): ?string
	{
		return $this->commentReply;
	}
	
	public function setCommentReply( ?string $commentReply ): self
	{
		$this->commentReply = $commentReply;
		
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
	
	public function getPublishAt(): ?\DateTimeInterface
	{
		return $this->publishAt;
	}
	
	public function setPublishAt( ?\DateTimeInterface $publishAt ): self
	{
		$this->publishAt = $publishAt;
		
		return $this;
	}
	
	public function getUsers(): ?User
	{
		return $this->users;
	}
	
	public function setUsers( ?User $users ): self
	{
		$this->users = $users;
		
		return $this;
	}
	
	public function getVideo(): ?Media
	{
		return $this->video;
	}
	
	public function setVideo( ?Media $video ): self
	{
		$this->video = $video;
		
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
	
	/**
	 * @return Collection|Page[]
	 */
	public function getDisplayHomeReview(): Collection
	{
		return $this->displayHomeReview;
	}
	
	public function addDisplayHomeReview( Page $displayHomeReview ): self
	{
		if ( ! $this->displayHomeReview->contains( $displayHomeReview ) ) {
			$this->displayHomeReview[] = $displayHomeReview;
			$displayHomeReview->addHomeVideoReview( $this );
		}
		
		return $this;
	}
	
	public function removeDisplayHomeReview( Page $displayHomeReview ): self
	{
		if ( $this->displayHomeReview->contains( $displayHomeReview ) ) {
			$this->displayHomeReview->removeElement( $displayHomeReview );
			$displayHomeReview->removeHomeVideoReview( $this );
		}
		
		return $this;
	}
	
	public function __toString()
	{
		return $this->getUsers() ? $this->getUsers()->getEmail() : 'Видео-отзывы';
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
	
	public function getDescription(): ?string
	{
		return $this->description;
	}
	
	public function setDescription( string $description ): self
	{
		$this->description = $description;
		
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
	
	public function getLocale(): ?string
	{
		return $this->locale;
	}
	
	public function setLocale( ?string $locale ): self
	{
		$this->locale = $locale;
		
		return $this;
	}
}
