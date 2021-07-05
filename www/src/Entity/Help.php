<?php

namespace App\Entity;

use App\Traits\ActivityTrait;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * Help
 *
 * @ORM\Entity(repositoryClass="App\Repository\HelpRepository")
 * @ORM\Table(name="help")
 * @ORM\HasLifecycleCallbacks
 */
class Help implements TranslatableInterface
{
	use ORMBehaviors\Translatable\Translatable;
	use ActivityTrait;
	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getQuestion(): ?string
	{
		return $this->translate( null, false )->getQuestion();
	}
	
	public function setQuestion( string $question ): self
	{
		$this->translate( null, false )->setQuestion( $question );
		
		return $this;
	}
	
	public function getReply(): ?string
	{
		return $this->translate( null, false )->getReply();
	}
	
	public function setReply( string $reply ): self
	{
		$this->translate( null, false )->setReply( $reply );
		
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
	
	public function __toString()
	{
		return $this->getQuestion() ?: 'Вопрос-ответ';
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
}
