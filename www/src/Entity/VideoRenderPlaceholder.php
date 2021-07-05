<?php

namespace App\Entity;

use App\Traits\ActivityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Application\Sonata\MediaBundle\Entity\Media;

/**
 * VideoRenderPlaceholder
 *
 * @ORM\Entity(repositoryClass="App\Repository\VideoRenderPlaceholderRepository")
 * @ORM\Table(name="video_render_placeholder")
 */
class VideoRenderPlaceholder
{
	use ActivityTrait;
	
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="layer_name", type="string", length=190)
	 */
	protected $layerName;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="layer_index", type="string", length=190)
	 */
	protected $layerIndex;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="composition", type="string", length=190)
	 */
	protected $composition;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="layer_name_audio", nullable=true, type="text", length=190)
	 */
	protected $layerNameAudio;
	
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="type", type="integer", length=190)
	 */
	protected $type;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="text", nullable=true, type="text", length=190)
	 */
	protected $text;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
	 */
	protected $image;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
	 */
	protected $imageMouth;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
	 */
	protected $imageFace;
	
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="image_orientation", nullable=true, type="string", length=190)
	 */
	protected $imageOrientation;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\VideoRenderImageManyPlaceholder", mappedBy="videoRender",
	 *     cascade={"persist"} )
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $imageMany;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\VideoPlaceholder", inversedBy="renderPlaceholder", fetch="EAGER")
	 * @ORM\JoinColumn(name="placeholder_parent", referencedColumnName="id", onDelete="SET NULL")
	 */
	protected $placeholderParent;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\VideoRender", inversedBy="placeholder", fetch="EAGER")
	 * @ORM\JoinColumn(name="render", referencedColumnName="render_id")
	 */
	protected $render;
	
	/**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(nullable=true,onDelete="SET NULL")
	 */
	protected $video;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Phrases", inversedBy="videoRender", fetch="EAGER")
	 * @ORM\JoinColumn(name="audio_phrases", referencedColumnName="id", onDelete="SET NULL")
	 */
	protected $audioPhrases;
	
	public function __construct()
	{
		$this->imageMany = new ArrayCollection();
	}
	
	public function __toString()
	{
		return $this->getLayerName() ?: 'Плейсхолдеры';
	}
	
	public function getLaveledTitle()
	{
		return (string) $this->getLayerName();
	}
	
	public function getId(): ?int
	{
		return $this->id;
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
	
	public function getText(): ?string
	{
		return $this->text;
	}
	
	public function setText( ?string $text ): self
	{
		$this->text = $text;
		
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
	
	public function getRender(): ?VideoRender
	{
		return $this->render;
	}
	
	public function setRender( ?VideoRender $render ): self
	{
		$this->render = $render;
		
		return $this;
	}
	
	public function getVideo(): ?Media
	{
		return $this->video;
	}
	
	public function setVideo( ?Media $video ): self
	{
		$this->video = $video;
		
		return $this;
	}
	
	public function getLayerName(): ?string
	{
		return $this->layerName;
	}
	
	public function setLayerName( string $layerName ): self
	{
		$this->layerName = $layerName;
		
		return $this;
	}
	
	public function getLayerIndex(): ?string
	{
		return $this->layerIndex;
	}
	
	public function setLayerIndex( string $layerIndex ): self
	{
		$this->layerIndex = $layerIndex;
		
		return $this;
	}
	
	public function getComposition(): ?string
	{
		return $this->composition;
	}
	
	public function setComposition( string $composition ): self
	{
		$this->composition = $composition;
		
		return $this;
	}
	
	public function getPlaceholderParent(): ?VideoPlaceholder
	{
		return $this->placeholderParent;
	}
	
	public function setPlaceholderParent( ?VideoPlaceholder $placeholderParent ): self
	{
		$this->placeholderParent = $placeholderParent;
		
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
	
	public function getImageOrientation(): ?string
	{
		return $this->imageOrientation;
	}
	
	public function setImageOrientation( ?string $imageOrientation ): self
	{
		$this->imageOrientation = $imageOrientation;
		
		return $this;
	}
	
	/**
	 * @return Collection|VideoRenderImageManyPlaceholder[]
	 */
	public function getImageMany(): Collection
	{
		return $this->imageMany;
	}
	
	public function addImageMany( VideoRenderImageManyPlaceholder $imageMany ): self
	{
		if ( ! $this->imageMany->contains( $imageMany ) ) {
			$this->imageMany[] = $imageMany;
			$imageMany->setVideoRender( $this );
		}
		
		return $this;
	}
	
	public function removeImageMany( VideoRenderImageManyPlaceholder $imageMany ): self
	{
		if ( $this->imageMany->contains( $imageMany ) ) {
			$this->imageMany->removeElement( $imageMany );
			// set the owning side to null (unless already changed)
			if ( $imageMany->getVideoRender() === $this ) {
				$imageMany->setVideoRender( null );
			}
		}
		
		return $this;
	}
	
	public function getLayerNameAudio(): ?string
	{
		return $this->layerNameAudio;
	}
	
	public function setLayerNameAudio( ?string $layerNameAudio ): self
	{
		$this->layerNameAudio = $layerNameAudio;
		
		return $this;
	}
	
	public function getImageMouth(): ?Media
	{
		return $this->imageMouth;
	}
	
	public function setImageMouth( ?Media $imageMouth ): self
	{
		$this->imageMouth = $imageMouth;
		
		return $this;
	}
	
	public function getImageFace(): ?Media
	{
		return $this->imageFace;
	}
	
	public function setImageFace( ?Media $imageFace ): self
	{
		$this->imageFace = $imageFace;
		
		return $this;
	}
	
}
