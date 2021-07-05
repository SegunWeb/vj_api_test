<?php

namespace App\Entity;

use App\Traits\ActivityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\TimeTrackTrait as TimeTrack;

/**
 * FirstName
 *
 * @ORM\Entity(repositoryClass="App\Repository\FirstNameRepository")
 * @ORM\Table(name="first_name")
 */
class FirstName
{
	use TimeTrack;
	use ActivityTrait;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=190)
	 */
	protected $title;
	
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="sex", type="integer", length=190)
	 */
	protected $sex;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\RelOrderFirstName", mappedBy="firstName", cascade={"persist"},
	 *     orphanRemoval=true )
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $order;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="locale", type="string", length=2)
	 */
	protected $locale;
	
	public function __construct()
	{
		if ( $this->createdAt == null ) {
			$this->setCreatedAt( new \DateTime( 'NOW' ) );
		}
		$this->setUpdatedAt( new \DateTime( 'NOW' ) );
		$this->order = new ArrayCollection();
	}
	
	public function getId(): ?int
	{
		return $this->id;
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
	
	
	public function getSex(): ?int
	{
		return $this->sex;
	}
	
	public function setSex( int $sex ): self
	{
		$this->sex = $sex;
		
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
		return $this->getTitle() ?: 'Праздник';
	}
	
	/**
	 * @return Collection|RelOrderFirstName[]
	 */
	public function getOrder(): Collection
	{
		return $this->order;
	}
	
	public function addOrder( RelOrderFirstName $order ): self
	{
		if ( ! $this->order->contains( $order ) ) {
			$this->order[] = $order;
			$order->setFirstName( $this );
		}
		
		return $this;
	}
	
	public function removeOrder( RelOrderFirstName $order ): self
	{
		if ( $this->order->contains( $order ) ) {
			$this->order->removeElement( $order );
			// set the owning side to null (unless already changed)
			if ( $order->getFirstName() === $this ) {
				$order->setFirstName( null );
			}
		}
		
		return $this;
	}
	
	public function getLocale(): ?string
	{
		return $this->locale;
	}
	
	public function setLocale( string $locale ): self
	{
		$this->locale = $locale;
		
		return $this;
	}
}
