<?php

namespace App\Entity;

use App\Traits\ActivityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MailTemplate
 *
 * @ORM\Table(name="mail_template")
 * @UniqueEntity("event")
 * @ORM\Entity(repositoryClass="App\Repository\MailTemplateRepository")
 */
class MailTemplate
{
	use ActivityTrait;
	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @var string
	 * @Assert\NotNull()
	 * @Assert\Email(
	 *     message = "The email '{{ value }}' is not a valid email."
	 * )
	 * @ORM\Column(name="from_email", type="string", length=190)
	 */
	private $fromEmail;
	
	/**
	 * @var string
	 * @Assert\NotNull()
	 * @Assert\Email(
	 *     message = "The email '{{ value }}' is not a valid email."
	 * )
	 * @ORM\Column(name="admin_email", type="string", length=190)
	 */
	private $adminEmail;
	
	/**
	 * @var int
	 * @Assert\NotNull()
	 * @ORM\Column(name="event", type="integer", unique=true)
	 */
	private $event;
	
	/**
	 * @var string
	 * @ORM\Column(name="body_message_admin", type="text", nullable=true)
	 */
	private $bodyMessageAdmin;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="body_message_users", type="text", nullable=true)
	 */
	public $bodyMessageUsers;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="subject_message_admin", type="string", length=190, nullable=true)
	 */
	public $subjectMessageAdmin;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="subject_message_users", type="string", length=190, nullable=true)
	 */
	public $subjectMessageUsers;
	
	/**
	 * Get id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Set fromEmail.
	 *
	 * @param string $fromEmail
	 *
	 * @return MailTemplate
	 */
	public function setFromEmail( $fromEmail )
	{
		$this->fromEmail = $fromEmail;
		
		return $this;
	}
	
	/**
	 * Get fromEmail.
	 *
	 * @return string
	 */
	public function getFromEmail()
	{
		return $this->fromEmail;
	}
	
	/**
	 * Set adminEmail.
	 *
	 * @param string $adminEmail
	 *
	 * @return MailTemplate
	 */
	public function setAdminEmail( $adminEmail )
	{
		$this->adminEmail = $adminEmail;
		
		return $this;
	}
	
	/**
	 * Get adminEmail.
	 *
	 * @return string
	 */
	public function getAdminEmail()
	{
		return $this->adminEmail;
	}
	
	/**
	 * Set event.
	 *
	 * @param int $event
	 *
	 * @return MailTemplate
	 */
	public function setEvent( $event )
	{
		$this->event = $event;
		
		return $this;
	}
	
	/**
	 * Get event.
	 *
	 * @return int
	 */
	public function getEvent()
	{
		return $this->event;
	}
	
	/**
	 * Set bodyMessageAdmin.
	 *
	 * @param string|null $bodyMessageAdmin
	 *
	 * @return MailTemplate
	 */
	public function setBodyMessageAdmin( $bodyMessageAdmin = null )
	{
		$this->bodyMessageAdmin = $bodyMessageAdmin;
		
		return $this;
	}
	
	/**
	 * Get bodyMessageAdmin.
	 *
	 * @return string|null
	 */
	public function getBodyMessageAdmin()
	{
		return $this->bodyMessageAdmin;
	}
	
	/**
	 * Set bodyMessageUsers.
	 *
	 * @param string|null $bodyMessageUsers
	 *
	 * @return MailTemplate
	 */
	public function setBodyMessageUsers( $bodyMessageUsers = null )
	{
		$this->bodyMessageUsers = $bodyMessageUsers;
		
		return $this;
	}
	
	/**
	 * Get bodyMessageUsers.
	 *
	 * @return string|null
	 */
	public function getBodyMessageUsers()
	{
		return $this->bodyMessageUsers;
	}
	
	/**
	 * Set subjectMessageAdmin.
	 *
	 * @param string|null $subjectMessageAdmin
	 *
	 * @return MailTemplate
	 */
	public function setSubjectMessageAdmin( $subjectMessageAdmin = null )
	{
		$this->subjectMessageAdmin = $subjectMessageAdmin;
		
		return $this;
	}
	
	/**
	 * Get subjectMessageAdmin.
	 *
	 * @return string|null
	 */
	public function getSubjectMessageAdmin()
	{
		return $this->subjectMessageAdmin;
	}
	
	/**
	 * Set subjectMessageUsers.
	 *
	 * @param string|null $subjectMessageUsers
	 *
	 * @return MailTemplate
	 */
	public function setSubjectMessageUsers( $subjectMessageUsers = null )
	{
		$this->subjectMessageUsers = $subjectMessageUsers;
		
		return $this;
	}
	
	/**
	 * Get subjectMessageUsers.
	 *
	 * @return string|null
	 */
	public function getSubjectMessageUsers()
	{
		return $this->subjectMessageUsers;
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
}
