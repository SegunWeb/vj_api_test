<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * PromoCode
 *
 * @ORM\Entity(repositoryClass="App\Repository\WorkerLoadRepository")
 * @ORM\Table(name="worder_load")
 */
class WorkerLoad
{
	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="ip", type="string", length=190, nullable=false)
	 */
	protected $ip;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="port", type="integer", length=190, nullable=true)
	 */
	protected $port;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="number_of_tasks", type="integer", length=190, nullable=true)
	 */
	protected $numberOfTasks;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="type", type="integer", length=190, nullable=true)
	 */
	protected $type;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\VideoRender", mappedBy="worker" )
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $videoRender;
	
	public function __construct()
	{
		$this->videoRender = new ArrayCollection();
	}
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getIp(): ?string
	{
		return $this->ip;
	}
	
	public function setIp( string $ip ): self
	{
		$this->ip = $ip;
		
		return $this;
	}
	
	public function getPort(): ?int
	{
		return $this->port;
	}
	
	public function setPort( ?int $port ): self
	{
		$this->port = $port;
		
		return $this;
	}
	
	public function getNumberOfTasks(): ?int
	{
		return $this->numberOfTasks;
	}
	
	public function setNumberOfTasks( ?int $numberOfTasks ): self
	{
		$this->numberOfTasks = $numberOfTasks;
		
		return $this;
	}
	
	/**
	 * @return Collection|VideoRender[]
	 */
	public function getVideoRender(): Collection
	{
		return $this->videoRender;
	}
	
	public function addVideoRender( VideoRender $videoRender ): self
	{
		if ( ! $this->videoRender->contains( $videoRender ) ) {
			$this->videoRender[] = $videoRender;
			$videoRender->setWorker( $this );
		}
		
		return $this;
	}
	
	public function removeVideoRender( VideoRender $videoRender ): self
	{
		if ( $this->videoRender->contains( $videoRender ) ) {
			$this->videoRender->removeElement( $videoRender );
			// set the owning side to null (unless already changed)
			if ( $videoRender->getWorker() === $this ) {
				$videoRender->setWorker( null );
			}
		}
		
		return $this;
	}
	
	public function getType(): ?int
	{
		return $this->type;
	}
	
	public function setType( ?int $type ): self
	{
		$this->type = $type;
		
		return $this;
	}
}
