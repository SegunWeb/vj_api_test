<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Traits\TimeTrackTrait as TimeTrack;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * Holidays
 *
 * @ORM\Entity(repositoryClass="App\Repository\HolidaysRepository")
 * @ORM\Table(name="holidays")
 * @ORM\HasLifecycleCallbacks
 */
class Holidays implements TranslatableInterface
{
	use ORMBehaviors\Translatable\Translatable;
	use TimeTrack;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Video", mappedBy="event")
	 */
	protected $video;
	
	public function __construct()
	{
		if ( $this->createdAt == null ) {
			$this->setCreatedAt( new \DateTime( 'NOW' ) );
		}
		$this->setUpdatedAt( new \DateTime( 'NOW' ) );
		$this->video = new ArrayCollection();
	}
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getTitle(): ?string
	{
		return $this->translate( null, false )->getTitle();
	}
	
	public function setTitle( string $title ): self
	{
		$this->translate( null, false )->setTitle( $title );
		
		return $this;
	}
	
	public function getActive(): ?int
	{
		return $this->translate( null, false )->getActive();
	}
	
	public function setActive( int $active ): self
	{
		$this->translate( null, false )->setActive( $active );
		
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
	 * @return Collection|Video[]
	 */
	public function getVideo(): Collection
	{
		return $this->video;
	}
	
	public function addVideo( Video $video ): self
	{
		if ( ! $this->video->contains( $video ) ) {
			$this->video[] = $video;
			$video->addEvent( $this );
		}
		
		return $this;
	}
	
	public function removeVideo( Video $video ): self
	{
		if ( $this->video->contains( $video ) ) {
			$this->video->removeElement( $video );
			$video->removeEvent( $this );
		}
		
		return $this;
	}
	
	public function getLocale()
	{
		return $this->getCurrentLocale();
	}
	
	public function setLocale( $locale )
	{
		$this->setCurrentLocale( $locale );
		
		return $this;
	}
	
}
