<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RelOrderFirstName
 *
 * @ORM\Entity(repositoryClass="App\Repository\RelOrderFirstNameRepository")
 * @ORM\Table(name="rel_order_first_name")
 */
class RelOrderFirstName
{
	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Order", inversedBy="firstName", fetch="EAGER")
	 * @ORM\JoinColumn(name="order_id", referencedColumnName="order_id")
	 */
	protected $order;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\FirstName", inversedBy="order", fetch="EAGER")
	 * @ORM\JoinColumn(name="first_name_id", referencedColumnName="id")
	 */
	protected $firstName;
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getOrder(): ?Order
	{
		return $this->order;
	}
	
	public function setOrder( ?Order $order ): self
	{
		$this->order = $order;
		
		return $this;
	}
	
	public function getFirstName(): ?FirstName
	{
		return $this->firstName;
	}
	
	public function setFirstName( ?FirstName $firstName ): self
	{
		$this->firstName = $firstName;
		
		return $this;
	}
}
