<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Application\Sonata\MediaBundle\Entity\Media;

/**
 * PageHomeSlider
 *
 * @ORM\Entity(repositoryClass="App\Repository\PageHomeSliderRepository")
 * @ORM\Table(name="page_home_slider")
 * @ORM\HasLifecycleCallbacks
 */
class PageHomeSlider
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
	 */
	protected $image;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $imageTablet;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $imageMobile;
	
	/**
	 * @var \App\Application\Sonata\MediaBundle\Entity\Media
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
	 */
	protected $imageCircle;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Page", inversedBy="homeImageHeader", fetch="EAGER")
	 * @ORM\JoinColumn(name="page_id", referencedColumnName="id")
	 */
	protected $page;
	/**
	 * @var string
	 *
	 * @ORM\Column(name="home_title_header", type="string", nullable=true, length=255)
	 */
	protected $homeTitleHeader;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="home_link_examples", type="string", nullable=true, length=255)
	 */
	protected $homeLinkExamples;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="home_description_header", type="string", nullable=true, length=255)
	 */
	protected $homeDescriptionHeader;
	
	public function __toString()
	{
		return 'Слайдер';
	}
	
	public function getLaveledTitle()
	{
		return 'Слайдер';
	}
	
	public function getId(): ?int
	{
		return $this->id;
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
	
	public function getPage(): ?Page
	{
		return $this->page;
	}
	
	public function setPage( ?Page $page ): self
	{
		$this->page = $page;
		
		return $this;
	}
	
	public function getImageCircle(): ?Media
	{
		return $this->imageCircle;
	}
	
	public function setImageCircle( ?Media $imageCircle ): self
	{
		$this->imageCircle = $imageCircle;
		
		return $this;
	}
	
	public function getImageTablet(): ?Media
	{
		return $this->imageTablet;
	}
	
	public function setImageTablet( ?Media $imageTablet ): self
	{
		$this->imageTablet = $imageTablet;
		
		return $this;
	}
	
	public function getImageMobile(): ?Media
	{
		return $this->imageMobile;
	}
	
	public function setImageMobile( ?Media $imageMobile ): self
	{
		$this->imageMobile = $imageMobile;
		
		return $this;
	}
	
	public function getHomeTitleHeader(): ?string
	{
		return $this->homeTitleHeader;
	}
	
	public function setHomeTitleHeader( ?string $homeTitleHeader ): self
	{
		$this->homeTitleHeader = $homeTitleHeader;
		
		return $this;
	}
	
	public function getHomeLinkExamples(): ?string
	{
		return $this->homeLinkExamples;
	}
	
	public function setHomeLinkExamples( ?string $homeLinkExamples ): self
	{
		$this->homeLinkExamples = $homeLinkExamples;
		
		return $this;
	}
	
	public function getHomeDescriptionHeader(): ?string
	{
		return $this->homeDescriptionHeader;
	}
	
	public function setHomeDescriptionHeader( ?string $homeDescriptionHeader ): self
	{
		$this->homeDescriptionHeader = $homeDescriptionHeader;
		
		return $this;
	}
}
