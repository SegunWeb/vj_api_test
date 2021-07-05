<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Application\Sonata\MediaBundle\Entity\Media;

/**
 * VideoRenderImageManyPlaceholder
 *
 * @ORM\Entity(repositoryClass="App\Repository\VideoRenderImageManyPlaceholderRepository")
 * @ORM\Table(name="video_render_image_many_placeholder")
 */
class VideoRenderImageManyPlaceholder
{
	
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
	 */
	protected $image;
	
	/**
	 * @var integer
	 * @ORM\Column(name="image_orientation", nullable=true, type="string", length=190)
	 */
	protected $imageOrientation;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Phrases", inversedBy="videoRender", fetch="EAGER")
	 * @ORM\JoinColumn(name="audio_phrases", referencedColumnName="id")
	 */
	protected $audioPhrases;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\VideoRenderPlaceholder", inversedBy="imageMany",
	 *     fetch="EAGER",cascade={"persist"})
	 * @ORM\JoinColumn(name="video_render", referencedColumnName="id", nullable=true, onDelete="SET NULL")
	 */
	protected $videoRender;
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getImageOrientation(): ?string
	{
		return $this->imageOrientation;
	}
	
	public function setImageOrientation( ?string $imageOrientation ): self
	{
		$this->imageOrientation = $imageOrientation;
		
		return $this;
	}
	
	public function getImage(): ?Media
	{
		return $this->image;
	}
	
	public function setImage( ?Media $image ): self
	{
		$this->image = $image;
		
		return $this;
	}
	
	public function getAudioPhrases(): ?Phrases
	{
		return $this->audioPhrases;
	}
	
	public function setAudioPhrases( ?Phrases $audioPhrases ): self
	{
		$this->audioPhrases = $audioPhrases;
		
		return $this;
	}
	
	public function getVideoRender(): ?VideoRenderPlaceholder
	{
		return $this->videoRender;
	}
	
	public function setVideoRender( ?VideoRenderPlaceholder $videoRender ): self
	{
		$this->videoRender = $videoRender;
		
		return $this;
	}
	
}
