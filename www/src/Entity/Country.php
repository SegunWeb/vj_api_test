<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * Country
 *
 * @ORM\Entity(repositoryClass="App\Repository\CountryRepository")
 * @ORM\Table(name="country")
 * @ORM\HasLifecycleCallbacks
 */
class Country implements TranslatableInterface
{
	use ORMBehaviors\Translatable\Translatable;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="iso_code", type="string", length=3, nullable=false)
	 */
	protected $isoCode;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="default_country_locale", type="string", length=2, nullable=false, options={"default" : "en"})
	 */
	protected $defaultCountryLocale = 'en';
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Currency", mappedBy="country")
	 */
	protected $currency;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="country" )
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $users;
	
	public function __construct()
	{
		$this->currency = new ArrayCollection();
		$this->users    = new ArrayCollection();
	}
	
	public function __toString()
	{
		return $this->getName() ?: 'Страны';
	}
	
	public function getLaveledTitle()
	{
		return $this->getName() ?: 'Страны';
	}
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getName(): ?string
	{
		return $this->translate( null, false )->getName();
	}
	
	public function setName( string $name ): self
	{
		$this->translate( null, false )->setName( $name );
		
		return $this;
	}
	
	/**
	 * @return Collection|Currency[]
	 */
	public function getCurrency(): Collection
	{
		return $this->currency;
	}
	
	public function addCurrency( Currency $currency ): self
	{
		if ( ! $this->currency->contains( $currency ) ) {
			$this->currency[] = $currency;
			$currency->addCountry( $this );
		}
		
		return $this;
	}
	
	public function removeCurrency( Currency $currency ): self
	{
		if ( $this->currency->contains( $currency ) ) {
			$this->currency->removeElement( $currency );
			$currency->removeCountry( $this );
		}
		
		return $this;
	}
	
	public function getIsoCode(): ?string
	{
		return $this->isoCode;
	}
	
	public function setIsoCode( string $isoCode ): self
	{
		$this->isoCode = $isoCode;
		
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
	 * @return Collection|User[]
	 */
	public function getUsers(): Collection
	{
		return $this->users;
	}
	
	public function addUser( User $user ): self
	{
		if ( ! $this->users->contains( $user ) ) {
			$this->users[] = $user;
			$user->setCountry( $this );
		}
		
		return $this;
	}
	
	public function removeUser( User $user ): self
	{
		if ( $this->users->contains( $user ) ) {
			$this->users->removeElement( $user );
			// set the owning side to null (unless already changed)
			if ( $user->getCountry() === $this ) {
				$user->setCountry( null );
			}
		}
		
		return $this;
	}
	
	public function getDefaultCountryLocale(): ?string
	{
		return $this->defaultCountryLocale;
	}
	
	public function setDefaultCountryLocale( string $defaultCountryLocale ): self
	{
		$this->defaultCountryLocale = $defaultCountryLocale;
		
		return $this;
	}
	
	
}
