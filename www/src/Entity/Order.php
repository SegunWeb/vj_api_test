<?php

namespace App\Entity;

use App\Traits\ActivityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Order
 *
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @ORM\Table(name="orders")
 */
class Order
{
	use ActivityTrait;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="order_id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Video", inversedBy="orders")
	 * @ORM\JoinColumn(name="video", nullable=true, referencedColumnName="video_id", onDelete="SET NULL")
	 */
	protected $video;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="video_title", type="string", length=255, nullable=true)
	 */
	protected $videoTitle;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="orders")
	 * @ORM\JoinColumn(name="users", nullable=true, referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $users;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\VideoRender", mappedBy="order", cascade={"persist"}, orphanRemoval=true )
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $render;
	
	/**
	 * @var string
	 * @ORM\Column(name="price", type="float", nullable=true)
	 */
	protected $price;
	
	/**
	 * @var string
	 * @ORM\Column(name="price_currency", type="string", nullable=true)
	 */
	protected $priceCurrency;
	
	/**
	 * @var string
	 * @ORM\Column(name="currency_default", type="float", nullable=true)
	 */
	protected $currencyDefault;
	
	/**
	 * @var string
	 * @ORM\Column(name="full_name", type="string", nullable=true)
	 */
	protected $fullName;
	
	/**
	 * @var string
	 * @ORM\Column(name="email", type="string", nullable=true)
	 */
	protected $email;
	
	/**
	 * @var string
	 * @ORM\Column(name="phone", type="string", nullable=true)
	 */
	protected $phone;
	
	/**
	 * @var string
	 * @ORM\Column(name="city", type="string", nullable=true)
	 */
	protected $city;
	
	/**
	 * @var integer
	 * @ORM\Column(name="child_sex", type="string", nullable=true)
	 */
	protected $childSex;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\RelOrderFirstName", mappedBy="order", cascade={"persist"},
	 *     orphanRemoval=true )
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $firstName;
	
	/**
	 * @var string
	 * @ORM\Column(name="promo_code", type="string", nullable=true)
	 */
	protected $promoCode;

	/**
	 * @var integer
	 * @ORM\Column(name="promo_code_discount", type="integer", nullable=true)
	 */
	protected $promoCodeDiscount;
	
	/**
	 * @var string
	 * @ORM\Column(name="payment_method", type="string", nullable=true)
	 */
	protected $paymentMethod;
	
	/**
	 * @var string
	 * @ORM\Column(name="payment_id_order", type="string", nullable=true)
	 */
	protected $paymentIdOrder;
	
	/**
	 * @var string
	 * @ORM\Column(name="sent_email", type="integer", nullable=true)
	 */
	protected $sentEmail = 0;
	
	/**
	 * @var string
	 * @ORM\Column(name="remove_files", type="integer", nullable=true)
	 */
	protected $removeFiles = 0;
	
	/**
	 * @var \DateTime
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="created_at", type="datetime")
	 */
	protected $createdAt;
	
	/**
	 * @var \DateTime
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="updated_at", type="datetime")
	 */
	protected $updatedAt;
	
	public function __construct()
	{
		if ( $this->createdAt == null ) {
			$this->setCreatedAt( new \DateTime( 'NOW' ) );
		}
		if ( $this->updatedAt == null ) {
			$this->setUpdatedAt( new \DateTime( 'NOW' ) );
		}
		$this->render    = new ArrayCollection();
		$this->firstName = new ArrayCollection();
	}
	
	public function getId(): ?int
	{
		return $this->id;
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
		return ! empty( $this->getVideo() ) ? $this->getVideo()->getTitle() : 'Заказ';
	}
	
	public function getVideo(): ?Video
	{
		return $this->video;
	}
	
	public function setVideo( ?Video $video ): self
	{
		$this->video = $video;
		
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
			$render->setOrder( $this );
		}
		
		return $this;
	}
	
	public function removeRender( VideoRender $render ): self
	{
		if ( $this->render->contains( $render ) ) {
			$this->render->removeElement( $render );
			// set the owning side to null (unless already changed)
			if ( $render->getOrder() === $this ) {
				$render->setOrder( null );
			}
		}
		
		return $this;
	}
	
	public function getPrice(): ?float
	{
		return $this->price;
	}
	
	public function setPrice( ?float $price ): self
	{
		$this->price = $price;
		
		return $this;
	}
	
	public function getCurrencyDefault(): ?float
	{
		return $this->currencyDefault;
	}
	
	public function setCurrencyDefault( ?float $currencyDefault ): self
	{
		$this->currencyDefault = $currencyDefault;
		
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
	
	public function getEmail(): ?string
	{
		return $this->email;
	}
	
	public function setEmail( ?string $email ): self
	{
		$this->email = $email;
		
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
	
	public function getPriceCurrency(): ?string
	{
		return $this->priceCurrency;
	}
	
	public function setPriceCurrency( ?string $priceCurrency ): self
	{
		$this->priceCurrency = $priceCurrency;
		
		return $this;
	}
	
	public function getPromoCode(): ?string
	{
		return $this->promoCode;
	}
	
	public function setPromoCode( ?string $promoCode ): self
	{
		$this->promoCode = $promoCode;
		
		return $this;
	}

	public function getPromoCodeDiscount(): ?int
	{
		return $this->promoCodeDiscount;
	}

	public function setPromoCodeDiscount( ?int $promoCodeDiscount): self
	{
		$this->promoCodeDiscount = $promoCodeDiscount;

		return $this;
	}
	
	public function getPaymentMethod(): ?string
	{
		return $this->paymentMethod;
	}
	
	public function setPaymentMethod( ?string $paymentMethod ): self
	{
		$this->paymentMethod = $paymentMethod;
		
		return $this;
	}
	
	public function getPaymentIdOrder(): ?string
	{
		return $this->paymentIdOrder;
	}
	
	public function setPaymentIdOrder( ?string $paymentIdOrder ): self
	{
		$this->paymentIdOrder = $paymentIdOrder;
		
		return $this;
	}
	
	public function getSentEmail(): ?int
	{
		return $this->sentEmail;
	}
	
	public function setSentEmail( ?int $sentEmail ): self
	{
		$this->sentEmail = $sentEmail;
		
		return $this;
	}
	
	/*public function removeFirstNameAll()
	{
		if ( $this->firstName->isEmpty() == false ) {
			foreach ( $this->firstName->toArray() as $item ) {
				$this->removeFirstName( $item );
			}
		}
		
		return $this;
	}*/
	
	public function getChildSex(): ?string
	{
		return $this->childSex;
	}
	
	public function setChildSex( ?string $childSex ): self
	{
		$this->childSex = $childSex;
		
		return $this;
	}
	
	public function getRemoveFiles(): ?int
	{
		return $this->removeFiles;
	}
	
	public function setRemoveFiles( ?int $removeFiles ): self
	{
		$this->removeFiles = $removeFiles;
		
		return $this;
	}
	
	/**
	 * @return Collection|RelOrderFirstName[]
	 */
	public function getFirstName(): Collection
	{
		return $this->firstName;
	}
	
	public function addFirstName( RelOrderFirstName $firstName ): self
	{
		if ( ! $this->firstName->contains( $firstName ) ) {
			$this->firstName[] = $firstName;
			$firstName->setOrder( $this );
		}
		
		return $this;
	}
	
	public function removeFirstName( RelOrderFirstName $firstName ): self
	{
		if ( $this->firstName->contains( $firstName ) ) {
			$this->firstName->removeElement( $firstName );
			// set the owning side to null (unless already changed)
			if ( $firstName->getOrder() === $this ) {
				$firstName->setOrder( null );
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
	
	public function getVideoTitle(): ?string
	{
		return $this->videoTitle;
	}
	
	public function setVideoTitle( string $videoTitle ): self
	{
		$this->videoTitle = $videoTitle;
		
		return $this;
	}

	public function getPriceRub()
    {
        return $this->getVideo()->getPriceRub();
    }

    public function getPriceUah()
    {
        return $this->getVideo()->getPriceUah();
    }

    public function getPriceEur()
    {
        return $this->getVideo()->getPriceEur();
    }

    public function getPriceUsd()
    {
        return $this->getVideo()->getPriceUsd();
    }
	
}
