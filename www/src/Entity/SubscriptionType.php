<?php

namespace App\Entity;

use App\Traits\ActivityTrait;
use App\Traits\TimeTrackTrait as TimeTrack;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriptionTypeRepository")
 */
class SubscriptionType
{
    use TimeTrack;
    use ActivityTrait;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="price_uah", nullable=true, type="float", length=255)
     */
    private $priceUah;

    /**
     * @ORM\Column(name="price_rub", nullable=true, type="float", length=255)
     */
    private $priceRub;

    /**
     * @ORM\Column(name="price_eur", nullable=true, type="float", length=255)
     */
    private $priceEur;

    /**
     * @ORM\Column(name="price_usd", nullable=true, type="float", length=255)
     */
    private $priceUsd;

    /**
     * @ORM\Column(type="integer")
     */
    private $period;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $discount;

    /**
     * @ORM\Column(name="front_price_uah", nullable=true, type="float", length=255)
     */
    private $frontPriceUah;

    /**
     * @ORM\Column(name="front_price_rub", nullable=true, type="float", length=255)
     */
    private $frontPriceRub;

    /**
     * @ORM\Column(name="front_price_eur", nullable=true, type="float", length=255)
     */
    private $frontPriceEur;

    /**
     * @ORM\Column(name="front_price_usd", nullable=true, type="float", length=255)
     */
    private $frontPriceUsd;

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
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param  string  $title
     *
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param  string|null  $description
     *
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return float
     */
    public function getPriceUah(): ?float
    {
        return $this->priceUah;
    }

    /**
     * @param  mixed  $priceUah
     *
     * @return SubscriptionType
     */
    public function setPriceUah(float $priceUah): self
    {
        $this->priceUah = $priceUah;

        return $this;
    }

    /**
     * @return float
     */
    public function getPriceRub(): ?float
    {
        return $this->priceRub;
    }

    /**
     * @param  mixed  $priceRub
     *
     * @return SubscriptionType
    */
    public function setPriceRub($priceRub): self
    {
        $this->priceRub = $priceRub;

        return $this;
    }

    /**
     * @return float
     */
    public function getPriceEur(): ?float
    {
        return $this->priceEur;
    }

    /**
     * @param  mixed  $priceEur
     *
     * @return SubscriptionType
     */
    public function setPriceEur($priceEur): self
    {
        $this->priceEur = $priceEur;

        return $this;
    }

    /**
     * @return float
     */
    public function getPriceUsd(): ?float
    {
        return $this->priceUsd;
    }

    /**
     * @param  mixed  $priceUsd
     *
     * @return SubscriptionType
     */
    public function setPriceUsd($priceUsd): self
    {
        $this->priceUsd = $priceUsd;

        return $this;
    }


    /**
     * @return int|null
     */
    public function getActive(): ?int
    {
        return $this->active;
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
     * @return int|null
     */
    public function getPeriod(): ?int
    {
        return $this->period;
    }

    /**
     * @param  int  $period
     *
     * @return $this
     */
    public function setPeriod(int $period): self
    {
        $this->period = $period;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    /**
     * @param  int  $discount
     *
     * @return $this
     */
    public function setDiscount(?int $discount): self
    {
        $this->discount = $discount;

        return $this;
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
     * @return float
     */
    public function getFrontPriceUah(): ?float
    {
        return $this->frontPriceUah;
    }

    /**
     * @param  mixed  $frontPriceUah
     *
     * @return SubscriptionType
     */
    public function setFrontPriceUah($frontPriceUah): self
    {
        $this->frontPriceUah = $frontPriceUah;

        return $this;
    }

    /**
     * @return float
     */
    public function getFrontPriceRub(): ?float
    {
        return $this->frontPriceRub;
    }

    /**
     * @param  mixed  $frontPriceRub
     *
     * @return SubscriptionType
     */
    public function setFrontPriceRub($frontPriceRub): self
    {
        $this->frontPriceRub = $frontPriceRub;

        return $this;
    }

    /**
     * @return float
     */
    public function getFrontPriceEur(): ?float
    {
        return $this->frontPriceEur;
    }

    /**
     * @param  mixed  $frontPriceEur
     *
     * @return SubscriptionType
     */
    public function setFrontPriceEur($frontPriceEur): self
    {
        $this->frontPriceEur = $frontPriceEur;

        return $this;
    }

    /**
     * @return float
     */
    public function getFrontPriceUsd(): ?float
    {
        return $this->frontPriceUsd;
    }

    /**
     * @param  mixed  $frontPriceUsd
     *
     * @return SubscriptionType
     */
    public function setFrontPriceUsd($frontPriceUsd): self
    {
        $this->frontPriceUsd = $frontPriceUsd;

        return $this;
    }

    /**
     * @return string|null
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    public function getPriceByISO($isoCode)
    {
        $price = '';

        switch ($isoCode) {
            case 'UAH':
                $price = $this->getPriceUah();
                break;
            case 'RUB':
                $price = $this->getPriceRub();
                break;
            case 'EUR':
                $price = $this->getPriceEur();
                break;
            default:
                $price = $this->getPriceUsd();
                break;
        }

        return $price;
    }

    public function getFrontPriceByISO($isoCode)
    {
        $price = '';

        switch ($isoCode) {
            case 'UAH':
                $price = $this->getFrontPriceUah();
                break;
            case 'RUB':
                $price = $this->getFrontPriceRub();
                break;
            case 'EUR':
                $price = $this->getFrontPriceEur();
                break;
            default:
                $price = $this->getFrontPriceUsd();
                break;
        }

        return $price;
    }

}
