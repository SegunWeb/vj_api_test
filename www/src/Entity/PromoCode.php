<?php

namespace App\Entity;

use App\Traits\ActivityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * PromoCode
 *
 * @ORM\Entity(repositoryClass="App\Repository\PromoCodeRepository")
 * @ORM\Table(name="promo_code")
 * @UniqueEntity("promoCode")
 */
class PromoCode
{
	use ActivityTrait;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="promo_code_id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="promo_code", type="string", length=190, nullable=false, unique=true)
	 */
	protected $promoCode;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="number_of_uses", type="integer", length=190, nullable=true)
	 */
	protected $numberOfUses;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="discount", type="integer", nullable=true)
	 */
	protected $discount;
	
	/**
	 * @var \DateTime
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="created_at", type="datetime", nullable=false)
	 */
	protected $createdAt;
	
	/**
	 * @var \DateTime
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="date_end_of_action", type="datetime", nullable=true)
	 */
	protected $dateEndOfAction;
	
	public function __construct()
	{
		if ( $this->createdAt == null ) {
			$this->setCreatedAt( new \DateTime( 'NOW' ) );
		}
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
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getNumberOfUses(): ?int
	{
		return $this->numberOfUses;
	}
	
	public function setNumberOfUses( ?int $numberOfUses ): self
	{
		$this->numberOfUses = $numberOfUses;
		
		return $this;
	}

	public function getDiscount(): ?int
	{
		return $this->discount;
	}

	public function setDiscount( ?int $discount ): self
	{
		$this->discount = $discount;

		return $this;
	}
	
	public function __toString()
	{
		return $this->getPromoCode() ?: 'Промокод';
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
	
	public function getOrder(): ?Order
	{
		return $this->order;
	}
	
	public function setOrder( ?Order $order ): self
	{
		$this->order = $order;
		
		return $this;
	}
	
	public function getPromoCode(): ?string
	{
		return $this->promoCode;
	}
	
	public function setPromoCode( string $promoCode ): self
	{
		$this->promoCode = $promoCode;
		
		return $this;
	}
	
	public function getDateEndOfAction(): ?\DateTimeInterface
	{
		return $this->dateEndOfAction;
	}
	
	public function setDateEndOfAction( $dateEndOfAction ): self
	{
		$this->dateEndOfAction = $dateEndOfAction;
		
		return $this;
	}
}
