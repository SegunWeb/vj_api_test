<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use App\Application\Sonata\MediaBundle\Entity\Media;

/**
 * PhrasesTranslation
 *
 * @ORM\Entity(repositoryClass="App\Repository\PhrasesTranslationRepository")
 * @ORM\Table(name="phrases_translation")
 * @ORM\HasLifecycleCallbacks
 */
class PhrasesTranslation
{
	use ORMBehaviors\Translatable\Translation;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=190)
	 */
	protected $title;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media")
	 * @ORM\JoinTable(name="rel_phrases_audio",
	 *      joinColumns={@ORM\JoinColumn(name="phrases_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="audio_id", referencedColumnName="id")}
	 *      )
	 */
	protected $audio = null;
	
	public function __construct()
	{
		$this->audio = new ArrayCollection();
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
	
	/**
	 * @return Collection|Media[]
	 */
	public function getAudio(): Collection
	{
		return $this->audio;
	}
	
	public function addAudio( Media $audio ): self
	{
		if ( ! $this->audio->contains( $audio ) ) {
			$this->audio[] = $audio;
		}
		
		return $this;
	}
	
	public function removeAudio( Media $audio ): self
	{
		if ( $this->audio->contains( $audio ) ) {
			$this->audio->removeElement( $audio );
		}
		
		return $this;
	}
	
	public function removeAudioAll()
	{
		if ( $this->audio->isEmpty() == false ) {
			foreach ( $this->audio->toArray() as $audio ) {
				$this->audio->removeElement( $audio );
			}
		}
		
		return $this;
	}
	
}
