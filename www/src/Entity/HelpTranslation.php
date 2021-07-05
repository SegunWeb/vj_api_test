<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * HelpTranslation
 *
 * @ORM\Entity(repositoryClass="App\Repository\HelpTranslationRepository")
 * @ORM\Table(name="help_translation")
 * @ORM\HasLifecycleCallbacks
 */
class HelpTranslation
{
	use ORMBehaviors\Translatable\Translation;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="question", type="text", length=190, nullable=false)
	 */
	protected $question;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="reply", type="text", length=190, nullable=false)
	 */
	protected $reply;
	
	public function getQuestion(): ?string
	{
		return $this->question;
	}
	
	public function setQuestion( string $question ): self
	{
		$this->question = $question;
		
		return $this;
	}
	
	public function getReply(): ?string
	{
		return $this->reply;
	}
	
	public function setReply( string $reply ): self
	{
		$this->reply = $reply;
		
		return $this;
	}
}
