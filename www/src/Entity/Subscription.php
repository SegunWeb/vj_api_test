<?php

namespace App\Entity;

use App\Traits\ActivityTrait;
use App\Traits\TimeTrackTrait as TimeTrack;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriptionRepository")
 */
class Subscription
{
    use TimeTrack;
    use ActivityTrait;

    const ACTIVE = 1;
    const INACTIVE = 0;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubscriptionType")
     */
    private $subscriptionType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activated_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var string A "Y-m-d H:i:s" formatted value
     */
    protected $expired_at;

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
     * @ORM\Column(name="payment_id_order", type="string", nullable=true)
     */
    protected $paymentIdOrder;

    /**
     * @var string
     * @ORM\Column(name="payment_method", type="string", nullable=true)
     */
    protected $paymentMethod;

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
     * @var integer
     * @ORM\Column(name="request_from_order_id", type="integer", nullable=true)
     */
    protected $requestFromOrderId;

    public function __construct()
    {
        if ($this->createdAt == null) {
            $this->setCreatedAt(new \DateTime('NOW'));
        }
        $this->setUpdatedAt(new \DateTime('NOW'));
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return SubscriptionType|null
     */
    public function getSubscriptionType(): ?SubscriptionType
    {
        return $this->subscriptionType;
    }

    /**
     * @param  SubscriptionType|null  $subscriptionType
     *
     * @return $this
     */
    public function setSubscriptionType(?SubscriptionType $subscriptionType): self
    {
        $this->subscriptionType = $subscriptionType;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param  User|null  $user
     *
     * @return $this
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getActivatedAt(): ?\DateTimeInterface
    {
        return $this->activated_at;
    }

    /**
     * @param  \DateTimeInterface|null  $activated_at
     *
     * @return $this
     */
    public function setActivatedAt(?\DateTimeInterface $activated_at): self
    {
        $this->activated_at = $activated_at;

        return $this;
    }

    /**
     * @return string
     */
    public function getActivatedAtString(): string
    {
        if( !empty( $this->activated_at ) ) {
            return $this->activated_at->format('F j, Y H:i');
        }
        return '';
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getExpiredAt(): ?\DateTimeInterface
    {
        return $this->expired_at;
    }

    /**
     * @param  \DateTimeInterface|null  $expired_at
     *
     * @return $this
     */
    public function setExpiredAt(?\DateTimeInterface $expired_at): self
    {
        $this->expired_at = $expired_at;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpiredAtString(): string
    {
        if( !empty( $this->expired_at ) ) {
            return $this->expired_at->format('F j, Y H:i');
        }
        return '';
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param  \DateTimeInterface  $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param  \DateTimeInterface  $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getActive(): ?int
    {
        return (int)$this->active;
    }

    /**
     * @param  int|null  $active
     *
     * @return $this
     */
    public function setActive(?int $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param  float  $price
     *
     * @return $this
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriceCurrency(): ?string
    {
        return $this->priceCurrency;
    }

    /**
     * @param  string  $priceCurrency
     *
     * @return $this
     */
    public function setPriceCurrency(string $priceCurrency): self
    {
        $this->priceCurrency = $priceCurrency;

        return $this;
    }

    /**
     * @return float
     */
    public function getCurrencyDefault(): ?float
    {
        return $this->currencyDefault;
    }

    /**
     * @param  float  $currencyDefault
     *
     * @return $this
     */
    public function setCurrencyDefault(float $currencyDefault): self
    {
        $this->currencyDefault = $currencyDefault;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentIdOrder(): ?string
    {
        return $this->paymentIdOrder;
    }

    /**
     * @param  string|null  $paymentIdOrder
     *
     * @return $this
     */
    public function setPaymentIdOrder( ?string $paymentIdOrder ): self
    {
        $this->paymentIdOrder = $paymentIdOrder;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    /**
     * @param  string|null  $paymentMethod
     *
     * @return $this
     */
    public function setPaymentMethod( ?string $paymentMethod ): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPromoCode(): ?string
    {
        return $this->promoCode;
    }

    /**
     * @param  string|null  $promoCode
     *
     * @return $this
     */
    public function setPromoCode( ?string $promoCode ): self
    {
        $this->promoCode = $promoCode;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPromoCodeDiscount(): ?int
    {
        return $this->promoCodeDiscount;
    }

    /**
     * @param  int|null  $promoCodeDiscount
     *
     * @return $this
     */
    public function setPromoCodeDiscount( ?int $promoCodeDiscount): self
    {
        $this->promoCodeDiscount = $promoCodeDiscount;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRequestFromOrderId(): ?int
    {
        return $this->requestFromOrderId;
    }

    /**
     * @param  int|null  $requestFromOrderId
     *
     * @return $this
     */
    public function setRequestFromOrderId( ?int $requestFromOrderId): self
    {
        $this->requestFromOrderId = $requestFromOrderId;

        return $this;
    }

    public function __toString()
    {
        return "Подписка #" . $this->getId();
    }

    public function firstActivate(): void
    {
        if( $this->getActive() === self::INACTIVE
            && $this->getActivatedAt() === null
            && $this->getExpiredAt() === null ) {
            $subscribePeriod = $this->getSubscriptionType()->getPeriod();
            $datetime = new \DateTime('NOW');
            $datetime->modify('+'.$subscribePeriod.' day');

            $this->setActive(self::ACTIVE);
            $this->setActivatedAt(new \DateTime('NOW'));
            $this->setExpiredAt($datetime);
        }
    }

}
