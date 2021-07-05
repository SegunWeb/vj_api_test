<?php

namespace App\Entity;

use Beelab\TagBundle\Tag\TagInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Tag
 *
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 * @ORM\Table(name="tag")
 * @ORM\HasLifecycleCallbacks
 */
class Tag implements TagInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string|null
     *
     * @ORM\Column()
     */
    protected $name;
	
	/**
	 * @var string
	 * @ORM\Column(name="slug", type="string", length=255, unique=true, nullable=true)
	 * @Gedmo\Slug(fields={"titleForSlug"}, updatable=false)
	 */
	protected $slug;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="title_for_slug", type="string", length=255)
	 */
	protected $titleForSlug;

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
	
	public function getSlug(): ?string
	{
		return $this->slug;
	}
	
	public function setSlug( ?string $slug ): self
	{
		$this->slug = $slug;
		
		return $this;
	}
	
	public function setTitleForSlug( $titleForSlug )
	{
		$this->titleForSlug = $titleForSlug;
		
		return $this;
	}
	
	public function getTitleForSlug()
	{
		return $this->titleForSlug;
	}
	
	/**
	 *
	 * @ORM\PrePersist
	 *
	 * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
	 */
	public function prePersist( LifecycleEventArgs $args )
	{
		$entity = $args->getEntity();
		$entity->setTitleForSlug( $entity->getName() );
	}
}

