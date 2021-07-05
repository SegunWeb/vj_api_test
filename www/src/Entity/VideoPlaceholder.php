<?php

namespace App\Entity;

use App\Traits\ActivityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\TimeTrackTrait as TimeTrack;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * VideoPlaceholder
 *
 * @ORM\Entity(repositoryClass="App\Repository\VideoPlaceholderRepository")
 * @ORM\Table(name="video_placeholder")
 */
class VideoPlaceholder
{
	use TimeTrack;
	
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="layer_name", nullable=true, type="string", length=190)
	 */
	protected $layerName;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="layer_index", nullable=true, type="string", length=190)
	 */
	protected $layerIndex;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="composition", nullable=true, type="string", length=190)
	 */
	protected $composition;
	
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="type", nullable=true, type="integer", length=190)
	 */
	protected $type;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", nullable=true, type="text", length=190)
	 */
	protected $description;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="max_files", nullable=true, type="integer", length=190)
	 */
	protected $maxFiles;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="layer_name_audio", nullable=true, type="text", length=190)
	 */
	protected $layerNameAudio;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="layer_name_mouth", nullable=true, type="text", length=190)
	 */
	protected $layerNameMouth;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\PhrasesCategories", inversedBy="placeholder")
	 * @ORM\JoinColumn(name="audio_phrase_category", nullable=true, referencedColumnName="id")
	 */
	protected $audioPhraseCategory;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Phrases", inversedBy="placeholder")
     * @ORM\JoinColumn(name="audio_phrase", nullable=true, referencedColumnName="id", onDelete="SET NULL")
     */
    protected $audioPhrase;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="video_max_size", nullable=true, type="string", length=190)
	 */
	protected $videoMaxSize;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="video_max_length", nullable=true, type="string", length=190)
	 */
	protected $videoMaxLength;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="image_width", nullable=true, type="string", length=190)
	 */
	protected $imageWidth;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="image_height", nullable=true, type="string", length=190)
	 */
	protected $imageHeight;
	
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="image_orientation", nullable=true, type="integer", length=190)
	 */
	protected $imageOrientation;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Video", inversedBy="placeholder", fetch="EAGER")
	 * @ORM\JoinColumn(name="video_id", referencedColumnName="video_id")
	 */
	protected $video;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\VideoRenderPlaceholder", mappedBy="placeholderParent")
	 * @ORM\JoinColumn(name="render_placeholder", nullable=true, referencedColumnName="id", onDelete="SET NULL")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $renderPlaceholder;
	
	/**
	 * @var integer $position
	 *
	 * @Gedmo\SortablePosition
	 * @ORM\Column(name="position", type="integer")
	 */
	protected $position;
	
	public function __construct()
	{
		if ( $this->createdAt == null ) {
			$this->setCreatedAt( new \DateTime( 'NOW' ) );
		}
		$this->setUpdatedAt( new \DateTime( 'NOW' ) );
		$this->renderPlaceholder = new ArrayCollection();
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
		return $this->getPlaceholder() ?: 'Плейсхолдеры';
	}
	
	public function getLaveledTitle()
	{
		return (string) $this->getPlaceholder();
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
	
	public function getVideo(): ?Video
	{
		return $this->video;
	}
	
	public function setVideo( ?Video $video ): self
	{
		$this->video = $video;
		
		return $this;
	}
	
	public function getDescription(): ?string
	{
		return $this->description;
	}
	
	public function setDescription( string $description ): self
	{
		$this->description = $description;
		
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
	
	public function getVideoMaxSize(): ?string
	{
		return $this->videoMaxSize;
	}
	
	public function setVideoMaxSize( ?string $videoMaxSize ): self
	{
		$this->videoMaxSize = $videoMaxSize;
		
		return $this;
	}
	
	public function getVideoMaxLength(): ?string
	{
		return $this->videoMaxLength;
	}
	
	public function setVideoMaxLength( ?string $videoMaxLength ): self
	{
		$this->videoMaxLength = $videoMaxLength;
		
		return $this;
	}
	
	public function getImageOrientation(): ?int
	{
		return $this->imageOrientation;
	}
	
	public function setImageOrientation( ?int $imageOrientation ): self
	{
		$this->imageOrientation = $imageOrientation;
		
		return $this;
	}
	
	public function getAudioPhraseCategory(): ?PhrasesCategories
	{
		return $this->audioPhraseCategory;
	}
	
	public function setAudioPhraseCategory( ?PhrasesCategories $audioPhraseCategory ): self
	{
		$this->audioPhraseCategory = $audioPhraseCategory;
		
		return $this;
	}
	
	public function getPosition(): ?int
	{
		return $this->position;
	}
	
	public function setPosition( int $position ): self
	{
		$this->position = $position;
		
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
	
	/**
	 * @return Collection|VideoRenderPlaceholder[]
	 */
	public function getRenderPlaceholder(): Collection
	{
		return $this->renderPlaceholder;
	}
	
	public function addRenderPlaceholder( VideoRenderPlaceholder $renderPlaceholder ): self
	{
		if ( ! $this->renderPlaceholder->contains( $renderPlaceholder ) ) {
			$this->renderPlaceholder[] = $renderPlaceholder;
			$renderPlaceholder->setPlaceholderParent( $this );
		}
		
		return $this;
	}
	
	public function removeRenderPlaceholder( VideoRenderPlaceholder $renderPlaceholder ): self
	{
		if ( $this->renderPlaceholder->contains( $renderPlaceholder ) ) {
			$this->renderPlaceholder->removeElement( $renderPlaceholder );
			// set the owning side to null (unless already changed)
			if ( $renderPlaceholder->getPlaceholderParent() === $this ) {
				$renderPlaceholder->setPlaceholderParent( null );
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
	
	public function getImageWidth(): ?string
	{
		return $this->imageWidth;
	}
	
	public function setImageWidth( ?string $imageWidth ): self
	{
		$this->imageWidth = $imageWidth;
		
		return $this;
	}
	
	public function getImageHeight(): ?string
	{
		return $this->imageHeight;
	}
	
	public function setImageHeight( ?string $imageHeight ): self
	{
		$this->imageHeight = $imageHeight;
		
		return $this;
	}
	
	public function getMaxFiles(): ?int
	{
		return $this->maxFiles;
	}
	
	public function setMaxFiles( ?int $maxFiles ): self
	{
		$this->maxFiles = $maxFiles;
		
		return $this;
	}
	
	public function getLayerNameMouth(): ?string
	{
		return $this->layerNameMouth;
	}
	
	public function setLayerNameMouth( ?string $layerNameMouth ): self
	{
		$this->layerNameMouth = $layerNameMouth;
		
		return $this;
	}
	
	public function getAudioPhrase(): ?Phrases
	{
		return $this->audioPhrase;
	}
	
	public function setAudioPhrase( ?Phrases $audioPhrase ): self
	{
		$this->audioPhrase = $audioPhrase;
		
		return $this;
	}
}
