<?php

namespace App\Entity;

use App\Traits\ActivityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\Collection;
use App\Traits\TimeTrackTrait as TimeTrack;
use App\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use App\Validator\Constraints\Tree as TreeValidator;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * PhrasesCategories
 *
 * @ORM\Entity(repositoryClass="App\Repository\PhrasesCategoriesRepository")
 * @ORM\Table(name="phrases_categories")
 * @Gedmo\Tree(type="nested")
 * @ORM\HasLifecycleCallbacks
 * @TreeValidator(groups={"admin"})
 * @AppAssert\Tree
 */
class PhrasesCategories implements TranslatableInterface
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
	 * @ORM\OneToMany(targetEntity="App\Entity\Phrases", mappedBy="category")
	 * @ORM\JoinColumn(name="phrases", nullable=true, referencedColumnName="id", onDelete="SET NULL")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $phrases;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\VideoPlaceholder", mappedBy="audioPhraseCategory")
	 * @ORM\JoinColumn(name="placeholder", nullable=true, referencedColumnName="id")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $placeholder;
	
	/**
	 * @Gedmo\TreeParent
	 * @ORM\ManyToOne(targetEntity="App\Entity\PhrasesCategories", inversedBy="children" )
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id" , onDelete="SET NULL")
	 */
	protected $parent;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\PhrasesCategories", mappedBy="parent")
	 * @ORM\OrderBy({"lft" = "ASC"})
	 */
	protected $children;
	
	/**
	 * @Gedmo\TreeLeft
	 * @ORM\Column(type="integer")
	 */
	protected $lft;
	
	/**
	 * @Gedmo\TreeLevel
	 * @ORM\Column(type="integer")
	 */
	protected $lvl;
	
	/**
	 * @Gedmo\TreeRight
	 * @ORM\Column(type="integer")
	 */
	protected $rgt;
	
	/**
	 * @Gedmo\SortablePosition
	 * @ORM\Column(name="position", type="integer")
	 */
	private $position;
	
	public function __construct()
	{
		if ( $this->createdAt == null ) {
			$this->setCreatedAt( new \DateTime( 'NOW' ) );
		}
		$this->setUpdatedAt( new \DateTime( 'NOW' ) );
		$this->phrases     = new ArrayCollection();
		$this->placeholder = new ArrayCollection();
		$this->children    = new ArrayCollection();
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
		return $this->getTitle() ?: 'Категория фраз';
	}
	
	public function getLaveledTitle()
	{
		return (string) $this->getTitle();
	}
	
	/**
	 * @return Collection|Phrases[]
	 */
	public function getPhrases(): Collection
	{
		return $this->phrases;
	}
	
	public function addPhrase( Phrases $phrase ): self
	{
		if ( ! $this->phrases->contains( $phrase ) ) {
			$this->phrases[] = $phrase;
			$phrase->setCategory( $this );
		}
		
		return $this;
	}
	
	public function removePhrase( Phrases $phrase ): self
	{
		if ( $this->phrases->contains( $phrase ) ) {
			$this->phrases->removeElement( $phrase );
			// set the owning side to null (unless already changed)
			if ( $phrase->getCategory() === $this ) {
				$phrase->setCategory( null );
			}
		}
		
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
			$placeholder->setAudioPhraseCategory( $this );
		}
		
		return $this;
	}
	
	public function removePlaceholder( VideoPlaceholder $placeholder ): self
	{
		if ( $this->placeholder->contains( $placeholder ) ) {
			$this->placeholder->removeElement( $placeholder );
			// set the owning side to null (unless already changed)
			if ( $placeholder->getAudioPhraseCategory() === $this ) {
				$placeholder->setAudioPhraseCategory( null );
			}
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
	
	public function getLft(): ?int
	{
		return $this->lft;
	}
	
	public function setLft( int $lft ): self
	{
		$this->lft = $lft;
		
		return $this;
	}
	
	public function getLvl(): ?int
	{
		return $this->lvl;
	}
	
	public function setLvl( int $lvl ): self
	{
		$this->lvl = $lvl;
		
		return $this;
	}
	
	public function getRgt(): ?int
	{
		return $this->rgt;
	}
	
	public function setRgt( int $rgt ): self
	{
		$this->rgt = $rgt;
		
		return $this;
	}
	
	public function getParent(): ?self
	{
		return $this->parent;
	}
	
	public function setParent( ?self $parent ): self
	{
		$this->parent = $parent;
		
		return $this;
	}
	
	/**
	 * @return Collection|PhrasesCategories[]
	 */
	public function getChildren(): Collection
	{
		return $this->children;
	}
	
	public function addChild( PhrasesCategories $child ): self
	{
		if ( ! $this->children->contains( $child ) ) {
			$this->children[] = $child;
			$child->setParent( $this );
		}
		
		return $this;
	}
	
	public function removeChild( PhrasesCategories $child ): self
	{
		if ( $this->children->contains( $child ) ) {
			$this->children->removeElement( $child );
			// set the owning side to null (unless already changed)
			if ( $child->getParent() === $this ) {
				$child->setParent( null );
			}
		}
		
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
	
}
