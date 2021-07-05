<?php

namespace App\Entity;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Traits\ActivityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\TimeTrackTrait as TimeTrack;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * Phrases
 *
 * @ORM\Entity(repositoryClass="App\Repository\PhrasesRepository")
 * @ORM\Table(name="phrases")
 * @ORM\HasLifecycleCallbacks
 */
class Phrases implements TranslatableInterface
{
	use ORMBehaviors\Translatable\Translatable;
	use TimeTrack;
	use ActivityTrait;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\PhrasesCategories", inversedBy="phrases")
	 * @ORM\JoinColumn(name="category", nullable=true, referencedColumnName="id", onDelete="SET NULL")
	 */
	protected $category;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="type", type="integer", length=255)
	 */
	protected $type;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\VideoRenderPlaceholder", mappedBy="audioPhrases", cascade={"persist"} )
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $videoRender;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\VideoPlaceholder", mappedBy="audioPhrase")
	 * @ORM\JoinColumn(name="placeholder", nullable=true, referencedColumnName="id", onDelete="SET NULL")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $placeholder;
	
	public function __construct()
	{
		if ( $this->createdAt == null ) {
			$this->setCreatedAt( new \DateTime( 'NOW' ) );
		}
		$this->setUpdatedAt( new \DateTime( 'NOW' ) );
		$this->videoRender = new ArrayCollection();
		$this->placeholder = new ArrayCollection();
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
		return $this->getTitle() ?: 'Фразы';
	}
	
	public function getLaveledTitle()
	{
		return (string) $this->getTitle();
	}
	
	public function getCategory(): ?PhrasesCategories
	{
		return $this->category;
	}
	
	public function setCategory( ?PhrasesCategories $category ): self
	{
		$this->category = $category;
		
		return $this;
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
	
	/**
	 * @return Collection|Media[]
	 */
	public function getAudio(): Collection
	{
		return $this->translate( null, false )->getAudio();
	}
	
	public function addAudio( Media $audio ): self
	{
		$this->translate( null, false )->addAudio( $audio );
		
		return $this;
	}
	
	public function removeAudio( Media $audio ): self
	{
		$this->translate( null, false )->removeAudio( $audio );
		
		return $this;
	}
	
	public function removeAudioAll()
	{
		$this->translate( null, false )->removeAudioAll();
		
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
	
	/**
	 * @return Collection|VideoRenderPlaceholder[]
	 */
	public function getVideoRender(): Collection
	{
		return $this->videoRender;
	}
	
	public function addVideoRender( VideoRenderPlaceholder $videoRender ): self
	{
		if ( ! $this->videoRender->contains( $videoRender ) ) {
			$this->videoRender[] = $videoRender;
			$videoRender->setAudioPhrases( $this );
		}
		
		return $this;
	}
	
	public function removeVideoRender( VideoRenderPlaceholder $videoRender ): self
	{
		if ( $this->videoRender->contains( $videoRender ) ) {
			$this->videoRender->removeElement( $videoRender );
			// set the owning side to null (unless already changed)
			if ( $videoRender->getAudioPhrases() === $this ) {
				$videoRender->setAudioPhrases( null );
			}
		}
		
		return $this;
	}
	
	public function getType(): ?int
	{
		return $this->type;
	}
	
	public function setType( int $type ): self
	{
		$this->type = $type;
		
		return $this;
	}
	
	/**
	 * @return Collection|VideoPlaceholder[]
	 */
	public function getPlaceholder(): Collection
	{
		return $this->placeholder;
	}
	
	public function addPlaceholder( VideoPlaceholder $placeholder ): self
	{
		if ( ! $this->placeholder->contains( $placeholder ) ) {
			$this->placeholder[] = $placeholder;
			$placeholder->setAudioPhrase( $this );
		}
		
		return $this;
	}
	
	public function removePlaceholder( VideoPlaceholder $placeholder ): self
	{
		if ( $this->placeholder->contains( $placeholder ) ) {
			$this->placeholder->removeElement( $placeholder );
			// set the owning side to null (unless already changed)
			if ( $placeholder->getAudioPhrase() === $this ) {
				$placeholder->setAudioPhrase( null );
			}
		}
		
		return $this;
	}
	
}
