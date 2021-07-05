<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;

trait MetaTrait
{
	
	/**
	 * @var string $metaTitle
	 *
	 * @ORM\Column(name="meta_title", type="string", nullable=true)
	 */
	private $metaTitle;
	
	/**
	 * @var string $metaKeywords
	 *
	 * @ORM\Column(name="meta_keywords", type="string", nullable=true)
	 */
	private $metaKeywords;
	
	/**
	 * @var string $metaDescription
	 *
	 * @ORM\Column(name="meta_description", type="string", length=400, nullable=true)
	 */
	private $metaDescription;
	
	/**
	 * @var string $metaCanonical
	 *
	 * @ORM\Column(name="meta_canonical", type="string", nullable=true)
	 */
	private $metaCanonical;
	
	
	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 *
	 * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
	 */
	public function prePersist( LifecycleEventArgs $args )
	{
		$entity = $args->getEntity();
		if ( $entity->getMetaTitle() === null ) {
			$entity->setMetaTitle( $entity->getTitle() );
		}
		if ( $entity->getMetaKeywords() === null ) {
			$entity->setMetaKeywords( $entity->getMetaKeywords() );
		}
		if ( $entity->getMetaCanonical() === null ) {
			$entity->setMetaCanonical( $entity->getMetaCanonical() );
		}
		if ( $entity->getMetaDescription() === null ) {
			if ( property_exists( static::class, 'shortContent' ) ) {
				$value = $entity->getShortContent();
			} else {
				$value = $entity->getTitle();
			}
			$entity->setMetaDescription( $value );
		}
	}
	
	/**
	 * Set metaTitle
	 *
	 * @param string $metaTitle
	 */
	public function setMetaTitle( $metaTitle )
	{
		if ( empty( $metaTitle ) ) {
			$metaTitle = $this->title;
		}
		$this->metaTitle = $metaTitle;
		
		return $this;
	}
	
	/**
	 * Set metaDescription
	 *
	 * @param string $metaDescription
	 */
	public function setMetaDescription( $metaDescription )
	{
		if ( empty( $metaDescription ) ) {
			if ( property_exists( static::class, 'shortContent' ) ) {
				$metaDescription = $this->getShortContent();
			} else {
				$metaDescription = $metaDescription = $this->getTitle();
			};
		}
		$this->metaDescription = $metaDescription;
		
		return $this;
	}
	
	/**
	 * Set metaKeywords
	 *
	 * @param string $metaKeywords
	 */
	public function setMetaKeywords( $metaKeywords )
	{
		$this->metaKeywords = $metaKeywords;
		
		return $this;
	}
	
	/**
	 * Set metaCanonical
	 *
	 * @param string $metaCanonical
	 */
	public function setMetaCanonical( $metaCanonical )
	{
		$this->metaCanonical = $metaCanonical;
		
		return $this;
	}
}