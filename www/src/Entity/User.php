<?php

namespace App\Entity;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Traits\TimeTrackTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping\AttributeOverrides;
use Doctrine\ORM\Mapping\AttributeOverride;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * User
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users")
 * @UniqueEntity(
 *     fields={"email"},
 *     message="front.sorry_mail_is_busy"
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class User extends BaseUser
{
	use TimeTrackTrait;
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Review", mappedBy="users", cascade={"REMOVE"})
	 * @ORM\JoinColumn(name="review", nullable=true, referencedColumnName="id_review")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $review;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\ReviewVideo", mappedBy="users", cascade={"REMOVE"})
	 * @ORM\JoinColumn(name="review_video", nullable=true, referencedColumnName="id_review")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $reviewVideo;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\VideoRender", mappedBy="users", cascade={"REMOVE"})
	 * @ORM\JoinColumn(name="render", nullable=true, referencedColumnName="render_id")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $render;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="users", cascade={"REMOVE"})
	 * @ORM\JoinColumn(name="orders", nullable=true, referencedColumnName="order_id")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $orders;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="city", type="string", nullable=true, length=190)
	 */
	protected $city;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Country", inversedBy="users", fetch="EAGER")
	 * @ORM\JoinColumn(name="country", referencedColumnName="id", nullable=true)
	 */
	protected $country;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="full_name", type="string", nullable=true, length=190)
	 */
	protected $fullName;
	
	/**
	 * @var string
	 * @ORM\Column(name="phone", type="string", nullable=true, length=190)
	 */
	protected $phone;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"},
	 *     fetch="LAZY")
	 */
	protected $avatar;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Feedback", mappedBy="users", cascade={"REMOVE"})
	 * @ORM\JoinColumn(name="feedback_id", nullable=true, referencedColumnName="feedback_id")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $feedback;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Review", mappedBy="authorCommentReply", cascade={"REMOVE"})
	 * @ORM\JoinColumn(name="review_reply", nullable=true, referencedColumnName="review_id")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $reviewReply;
	
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="subscribed", type="integer", nullable=true, length=1)
	 */
	protected $subscribed = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Subscription", mappedBy="user")
     */
    protected $subscriptions;

	public function __construct()
	{
		parent::__construct();
		$this->createdAt   = new \DateTime();
		$this->updatedAt   = new \DateTime();
		$this->review      = new ArrayCollection();
		$this->orders      = new ArrayCollection();
		$this->feedback    = new ArrayCollection();
		$this->render      = new ArrayCollection();
		$this->reviewReply = new ArrayCollection();
		$this->reviewVideo = new ArrayCollection();
	}
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function setUsername($username)
	{
		if(!empty($username)) {
			$this->username = $username;
		}else{
			$this->username = $this->email;
		}
		return $this;
	}
	
	/**
	 * @return Collection|Review[]
	 */
	public function getReview(): Collection
	{
		return $this->review;
	}
	
	public function addReview( Review $review ): self
	{
		if ( ! $this->review->contains( $review ) ) {
			$this->review[] = $review;
			$review->setUsers( $this );
		}
		
		return $this;
	}
	
	public function removeReview( Review $review ): self
	{
		if ( $this->review->contains( $review ) ) {
			$this->review->removeElement( $review );
			// set the owning side to null (unless already changed)
			if ( $review->getUsers() === $this ) {
				$review->setUsers( null );
			}
		}
		
		return $this;
	}
	
	/**
	 * @return Collection|Order[]
	 */
	public function getOrders(): Collection
	{
		return $this->orders;
	}
	
	public function addOrder( Order $order ): self
	{
		if ( ! $this->orders->contains( $order ) ) {
			$this->orders[] = $order;
			$order->setUsers( $this );
		}
		
		return $this;
	}
	
	public function removeOrder( Order $order ): self
	{
		if ( $this->orders->contains( $order ) ) {
			$this->orders->removeElement( $order );
			// set the owning side to null (unless already changed)
			if ( $order->getUsers() === $this ) {
				$order->setUsers( null );
			}
		}
		
		return $this;
	}
	
	public function getCity(): ?string
	{
		return $this->city;
	}
	
	public function setCity( ?string $city ): self
	{
		$this->city = $city;
		
		return $this;
	}
	
	public function getAvatar(): ?Media
	{
		return $this->avatar;
	}
	
	public function setAvatar( ?Media $avatar ): self
	{
		$this->avatar = $avatar;
		
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
	
	/**
	 * @ORM\PrePersist
	 */
	public function setCreatedAtValue()
	{
		$this->createdAt = new \DateTime();
	}
	
	/**
	 * @ORM\PreUpdate
	 */
	public function setUpdateAtValue()
	{
		$this->updatedAt = new \DateTime();
	}
	
	/**
	 * @return Collection|Feedback[]
	 */
	public function getFeedback(): Collection
	{
		return $this->feedback;
	}
	
	public function addFeedback( Feedback $feedback ): self
	{
		if ( ! $this->feedback->contains( $feedback ) ) {
			$this->feedback[] = $feedback;
			$feedback->setUsers( $this );
		}
		
		return $this;
	}
	
	public function removeFeedback( Feedback $feedback ): self
	{
		if ( $this->feedback->contains( $feedback ) ) {
			$this->feedback->removeElement( $feedback );
			// set the owning side to null (unless already changed)
			if ( $feedback->getUsers() === $this ) {
				$feedback->setUsers( null );
			}
		}
		
		return $this;
	}
	
	/**
	 * @return Collection|VideoRender[]
	 */
	public function getRender(): Collection
	{
		return $this->render;
	}
	
	public function addRender( VideoRender $render ): self
	{
		if ( ! $this->render->contains( $render ) ) {
			$this->render[] = $render;
			$render->setUsers( $this );
		}
		
		return $this;
	}
	
	public function removeRender( VideoRender $render ): self
	{
		if ( $this->render->contains( $render ) ) {
			$this->render->removeElement( $render );
			// set the owning side to null (unless already changed)
			if ( $render->getUsers() === $this ) {
				$render->setUsers( null );
			}
		}
		
		return $this;
	}
	
	public function getFullName(): ?string
	{
		return $this->fullName;
	}
	
	public function setFullName( ?string $fullName ): self
	{
		$this->fullName = $fullName;
		
		return $this;
	}
	
	/**
	 * @return Collection|Review[]
	 */
	public function getReviewReply(): Collection
	{
		return $this->reviewReply;
	}
	
	public function addReviewReply( Review $reviewReply ): self
	{
		if ( ! $this->reviewReply->contains( $reviewReply ) ) {
			$this->reviewReply[] = $reviewReply;
			$reviewReply->setAuthorCommentReply( $this );
		}
		
		return $this;
	}
	
	public function removeReviewReply( Review $reviewReply ): self
	{
		if ( $this->reviewReply->contains( $reviewReply ) ) {
			$this->reviewReply->removeElement( $reviewReply );
			// set the owning side to null (unless already changed)
			if ( $reviewReply->getAuthorCommentReply() === $this ) {
				$reviewReply->setAuthorCommentReply( null );
			}
		}
		
		return $this;
	}
	
	public function getPhone(): ?string
	{
		return $this->phone;
	}
	
	public function setPhone( ?string $phone ): self
	{
		$this->phone = $phone;
		
		return $this;
	}
	
	/**
	 * @Assert\Callback
	 */
	public function validate( ExecutionContextInterface $context, $payload )
	{
		if ( ! empty( $this->phone ) ) {
			
			$plusSign  = substr( $this->phone, 0, 1 );
			$otherSign = substr( $this->phone, 1 );
			$otherSign = str_replace( [ '(', ')', '-', ' ' ], [ '', '', '', '' ], $otherSign );
			$strLen    = strlen( $otherSign );
			
			if ( $plusSign != "+" or ctype_digit( $otherSign ) == false or ( $strLen < 7 and $strLen > 14 ) ) {
				$context->buildViolation( 'Некорректно введен номер телефона' )
				        ->atPath( 'phone' )
				        ->addViolation();
			}
		}
	}
	
	public function getSubscribed(): ?int
	{
		return $this->subscribed;
	}
	
	public function setSubscribed( ?int $subscribed ): self
	{
		$this->subscribed = $subscribed;
		
		return $this;
	}
	
	public function getCountry(): ?Country
	{
		return $this->country;
	}
	
	public function setCountry( ?Country $country ): self
	{
		$this->country = $country;
		
		return $this;
	}
	
	/**
	 * @return Collection|ReviewVideo[]
	 */
	public function getReviewVideo(): Collection
	{
		return $this->reviewVideo;
	}
	
	public function addReviewVideo( ReviewVideo $reviewVideo ): self
	{
		if ( ! $this->reviewVideo->contains( $reviewVideo ) ) {
			$this->reviewVideo[] = $reviewVideo;
			$reviewVideo->setUsers( $this );
		}
		
		return $this;
	}
	
	public function removeReviewVideo( ReviewVideo $reviewVideo ): self
	{
		if ( $this->reviewVideo->contains( $reviewVideo ) ) {
			$this->reviewVideo->removeElement( $reviewVideo );
			// set the owning side to null (unless already changed)
			if ( $reviewVideo->getUsers() === $this ) {
				$reviewVideo->setUsers( null );
			}
		}
		
		return $this;
	}

	public function getSubscriptions()
    {
        if( !empty($this->subscriptions) && !$this->subscriptions->isEmpty() ) {
            $now = new \DateTime('NOW');
            $lastActiveSubscription = $this->subscriptions->filter(function(Subscription $item) use ($now) {
                return ( $item->getActive() > 0 && $item->getExpiredAt() > $now);
            })->last();
            $this->subscriptions->clear();
            if(!empty($lastActiveSubscription)) {
                $this->subscriptions->add($lastActiveSubscription);
            }
        }
        return $this->subscriptions;
    }

    public function getSubscriptionExpiredAt()
    {
        $lastSubscription = $this->getSubscriptions();
        if(!$lastSubscription->isEmpty()) {
            return $lastSubscription->toArray()[0]->getExpiredAtString();
        }
        return false;
    }
	
}