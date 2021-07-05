<?php

namespace App\Entity;

use App\Traits\TimeTrackTrait;
use App\Traits\ActivityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Feedback
 *
 * @ORM\Table(name="feedback")
 * @ORM\Entity(repositoryClass="App\Repository\FeedbackRepository")
 */
class Feedback
{
	use TimeTrackTrait;
	use ActivityTrait;
	
	const IMPROVEMENT = 0; //Предложение улучшения
	const PROBLEMS = 1; //Сообщение о проблеме
	const QUESTION = 2; //Новый вопрос
	
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="feedback_id", type="integer", options={"unsigned"=true})
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="feedback")
	 * @ORM\JoinColumn(name="id_users", nullable=true)
	 */
	protected $users;
	
	/**
	 * @Assert\NotBlank()
	 * @ORM\Column(name="text", type="text")
	 */
	protected $text;
	
	/**
	 * @ORM\Column(name="full_name", type="string", nullable=true)
	 */
	protected $fullName;
	
	/**
	 * @ORM\Column(name="reply", type="text", nullable=true)
	 */
	protected $reply;
	
	/**
	 * @ORM\Column(name="phone", type="string", nullable=true)
	 */
	protected $phone;
	
	/**
	 * @Assert\NotBlank()
	 * @ORM\Column(name="email", type="string", nullable=true)
	 */
	protected $email;
	
	/**
	 * @Assert\NotBlank()
	 * @ORM\Column(name="type", type="integer", length=190)
	 */
	protected $type;
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getText(): ?string
	{
		return $this->text;
	}
	
	public function setText( string $text ): self
	{
		$this->text = $text;
		
		return $this;
	}
	
	public function getPhone(): ?string
	{
		return $this->phone;
	}
	
	public function setPhone( string $phone ): self
	{
		$this->phone = $phone;
		
		return $this;
	}
	
	public function getEmail(): ?string
	{
		return $this->email;
	}
	
	public function setEmail( string $email ): self
	{
		$this->email = $email;
		
		return $this;
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
	
	public function getActive(): ?int
	{
		return $this->active;
	}
	
	public function setActive( int $active ): self
	{
		$this->active = $active;
		
		return $this;
	}
	
	public function getReply(): ?string
	{
		return $this->reply;
	}
	
	public function setReply( ?string $reply ): self
	{
		$this->reply = $reply;
		
		return $this;
	}
	
	public function getFullName(): ?string
	{
		return $this->fullName;
	}
	
	public function setFullName( ?string $fullName ): self
	{
		$this->fullName = $fullName;
		
		return $this;
	}
	
	public function getUsers(): ?User
	{
		return $this->users;
	}
	
	public function setUsers( ?User $users ): self
	{
		$this->users = $users;
		
		return $this;
	}
    
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
    
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        
        return $this;
    }
    
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
    
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        
        return $this;
    }
}
