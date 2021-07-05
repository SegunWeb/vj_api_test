<?php

namespace App\Entity;

use App\Traits\ActivityTrait as Activity;
use App\Validator\Constraints as AppAssert;
use App\Validator\Constraints\MenuType as MenuTypeValidator;
use App\Validator\Constraints\Tree as TreeValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FooterMenu
 *
 * @ORM\Entity(repositoryClass="App\Repository\FooterMenuRepository")
 * @ORM\Table(name="footer_menu")
 * @Gedmo\Tree(type="nested")
 * @AppAssert\Tree
 * @ORM\HasLifecycleCallbacks
 * @MenuTypeValidator(groups={"admin"})
 * @TreeValidator(groups={"admin"})
 */
class FooterMenu implements TranslatableInterface
{
	use ORMBehaviors\Translatable\Translatable;
	use Activity;
	
	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @var FooterMenu
	 * @Gedmo\TreeParent
	 * @ORM\ManyToOne(targetEntity="FooterMenu", inversedBy="children" )
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id" , onDelete="SET NULL")
	 */
	private $parent;
	
	/**
	 * @Gedmo\TreeLeft
	 * @ORM\Column(type="integer")
	 */
	private $lft;
	
	/**
	 * @Gedmo\TreeLevel
	 * @ORM\Column(type="integer")
	 */
	private $lvl;
	
	/**
	 * @Gedmo\TreeRight
	 * @ORM\Column(type="integer")
	 */
	private $rgt;
	
	/**
	 * @var FooterMenu[]
	 *
	 * @ORM\OneToMany(targetEntity="FooterMenu", mappedBy="parent")
	 * @ORM\OrderBy({"lft" = "ASC"})
	 */
	private $children;
	
	/**
	 * @var int
	 * @Assert\NotBlank()
	 * @ORM\Column(name="type_menu_item", type="smallint", nullable=false)
	 */
	private $typeMenuItem;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Page", inversedBy="footerMenu")
	 * @ORM\JoinColumn(name="static_page_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
	 */
	private $staticPageId;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\VideoCategories", inversedBy="footerMenu")
	 * @ORM\JoinColumn(name="static_video_category_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
	 */
	private $staticVideoCategoryId;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="link", type="string", nullable=true)
	 */
	private $link;
	
	/**
	 * @Gedmo\SortablePosition
	 * @ORM\Column(name="position", type="integer")
	 */
	private $position;
	
	public function __construct()
	{
		$this->children = new ArrayCollection();
	}
	
	public function getId(): ?int
	{
		return $this->id;
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
	
	public function getTypeMenuItem(): ?int
	{
		return $this->typeMenuItem;
	}
	
	public function setTypeMenuItem( int $typeMenuItem ): self
	{
		$this->typeMenuItem = $typeMenuItem;
		
		return $this;
	}
	
	public function getLink(): ?string
	{
		return $this->link;
	}
	
	public function setLink( ?string $link ): self
	{
		$this->link = $link;
		
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
	 * @return Collection|FooterMenu[]
	 */
	public function getChildren(): Collection
	{
		return $this->children;
	}
	
	public function addChild( FooterMenu $child ): self
	{
		if ( ! $this->children->contains( $child ) ) {
			$this->children[] = $child;
			$child->setParent( $this );
		}
		
		return $this;
	}
	
	public function removeChild( FooterMenu $child ): self
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
	
	public function getStaticPageId(): ?Page
	{
		return $this->staticPageId;
	}
	
	public function setStaticPageId( ?Page $staticPageId ): self
	{
		$this->staticPageId = $staticPageId;
		
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
	
	public function getActive(): ?int
	{
		return $this->active;
	}
	
	public function setActive( int $active ): self
	{
		$this->active = $active;
		
		return $this;
	}
	
	public function getLaveledTitle()
	{
		return (string) $this->getTitle();
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
	
	public function getLocale()
	{
		return $this->getCurrentLocale();
	}
	
	public function setLocale( $locale )
	{
		$this->setCurrentLocale( $locale );
		
		return $this;
	}
	
	public function getStaticVideoCategoryId(): ?VideoCategories
	{
		return $this->staticVideoCategoryId;
	}
	
	public function setStaticVideoCategoryId( ?VideoCategories $staticVideoCategoryId ): self
	{
		$this->staticVideoCategoryId = $staticVideoCategoryId;
		
		return $this;
	}
	
}
