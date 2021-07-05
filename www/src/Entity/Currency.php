<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Currency
 *
 * @ORM\Entity(repositoryClass="App\Repository\CurrencyRepository")
 * @ORM\Table(name="currency")
 */
class Currency
{
	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="currency_id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="name", type="string", length=255, nullable=false)
	 */
	protected $name;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="abbreviation", type="string", length=255, nullable=false)
	 */
	protected $abbreviation;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="code_iso", type="string", length=3, nullable=false)
	 */
	protected $codeISO;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="sing", type="string", length=255, nullable=false)
	 */
	protected $sing;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Country", inversedBy="currency")
	 * @ORM\JoinTable(name="rel_currency_country",
	 *      joinColumns={@ORM\JoinColumn(name="currency_id", referencedColumnName="currency_id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="country_id", referencedColumnName="id")}
	 *      )
	 */
	protected $country;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="course", type="string", length=255, nullable=false)
	 */
	protected $course;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="default_curency", type="boolean", nullable=true)
	 */
	protected $defaultCurrency;
	
	/**
	 * @var int
	 *
	 * @ORM\Column(name="active", type="smallint", nullable=true, options={"default" : 0})
	 */
	private $active;
	
	public function __construct()
	{
		$this->country = new ArrayCollection();
	}
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getName(): ?string
	{
		return $this->name;
	}
	
	public function setName( string $name ): self
	{
		$this->name = $name;
		
		return $this;
	}
	
	public function getAbbreviation(): ?string
	{
		return $this->abbreviation;
	}
	
	public function setAbbreviation( string $abbreviation ): self
	{
		$this->abbreviation = $abbreviation;
		
		return $this;
	}
	
	public function getSing(): ?string
	{
		return $this->sing;
	}
	
	public function setSing( string $sing ): self
	{
		$this->sing = $sing;
		
		return $this;
	}
	
	public function getCourse(): ?string
	{
		return $this->course;
	}
	
	public function setCourse( string $course ): self
	{
		$this->course = $course;
		
		return $this;
	}
	
	public function getActive(): ?int
	{
		return $this->active;
	}
	
	public function setActive( ?int $active ): self
	{
		$this->active = $active;
		
		return $this;
	}
	
	/**
	 * @return Collection|Country[]
	 */
	public function getCountry(): Collection
	{
		return $this->country;
	}
	
	public function addCountry( Country $country ): self
	{
		if ( ! $this->country->contains( $country ) ) {
			$this->country[] = $country;
		}
		
		return $this;
	}
	
	public function removeCountry( Country $country ): self
	{
		if ( $this->country->contains( $country ) ) {
			$this->country->removeElement( $country );
		}
		
		return $this;
	}
	
	public function getDefaultCurrency(): ?bool
	{
		return $this->defaultCurrency;
	}
	
	public function setDefaultCurrency( ?bool $defaultCurrency ): self
	{
		$this->defaultCurrency = $defaultCurrency;
		
		return $this;
	}
	
	public function __toString()
	{
		return $this->getName() ?: 'Валюта';
	}
	
	public function getCodeISO(): ?string
	{
		return $this->codeISO;
	}
	
	public function setCodeISO( string $codeISO ): self
	{
		$this->codeISO = $codeISO;
		
		return $this;
	}
}
