<?php

namespace App\Repository;

use App\Entity\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Video|null find( $id, $lockMode = null, $lockVersion = null )
 * @method Video|null findOneBy( array $criteria, array $orderBy = null )
 * @method Video[]    findAll()
 * @method Video[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class VideoRepository extends ServiceEntityRepository
{
	public function __construct( RegistryInterface $registry )
	{
		parent::__construct( $registry, Video::class );
	}
	
	/**
	 * Поиск видео по критериям фильтра
	 */
	public function findByVideoByFilter( $locale, $sex, $numberPersons, $event, $category, $option, $offset = 0, $limit = 30, $tags = null )
	{
		/*
		 * Query
		 */
		$qbc = $this->createQueryBuilder('v');
		
		$qbc->select( 'v' );
		
		if(isset($event) and $event > 0) {
			$qbc->leftJoin( 'v.event', 'ev' );
		}
		if(isset($category) and $category > 0) {
			$qbc->leftJoin('v.category', 'vc');
		}
		if(!empty($tags)){
			$qbc->leftJoin('v.tags', 't');
		}
		
		$qbc->where( 'v.locale IN (:locale)' );
		
		if(isset($sex) and $sex > 0) {
			$qbc->andWhere( 'v.sex = :sex' );
		}
		if(isset($option) and $option > 0) {
			$qbc->andWhere( 'v.variation = :option' );
		}
		if(isset($numberPersons) and $numberPersons > 0) {
			$qbc->andWhere( 'v.numberPersons = :numbers' );
		}
		if(isset($category) and $category > 0) {
			$qbc->andWhere( 'vc.id = :category' );
		}
		if(!empty($tags)){
			$qbc->andWhere('t.slug IN (:tag)');
		}
		
		$qbc->andWhere( 'v.active = 1' );
		
		if(isset($event) and $event > 0) {
			$qbc->andWhere( 'ev.id = :event' );
		}
		if(isset($sex) and $sex > 0) {
			$qbc->setParameter( 'sex', $sex );
		}
		if(isset($option) and $option > 0) {
			$qbc->setParameter( 'option', $option );
		}
		if(isset($numberPersons) and $numberPersons > 0) {
			$qbc->setParameter( 'numbers', $numberPersons );
		}
		if(isset($category) and $category > 0) {
			$qbc->setParameter( 'category', $category );
		}
		if(isset($event) and $event > 0) {
			$qbc->setParameter( 'event', $event );
		}
		if(!empty($tags)){
			$qbc->setParameter('tag', $tags);
		}
		
		$qbc->setParameter( 'locale', $locale )
		    ->orderBy( 'v.positionCategory', 'DESC' )
            ->addOrderBy('v.createdAt', 'DESC')
		    ->setFirstResult( $offset ?: 0 )
		    ->setMaxResults( $limit );
		
		$query = $qbc->getQuery();
		
		/*
		 * Count
		 */
		$qb = $this->createQueryBuilder('v');
		
		$qb->select( 'COUNT(DISTINCT(v)) as count' );
	
		if(isset($event) and $event > 0) {
			$qb->leftJoin( 'v.event', 'ev' );
		}
		if(isset($category) and $category > 0) {
			$qb->leftJoin('v.category', 'vc');
		}
		if(!empty($tags)){
			$qb->leftJoin('v.tags', 't');
		}
		
		$qb->where( 'v.locale = :locale' );

		if(isset($sex) and $sex > 0) {
			$qb->andWhere( 'v.sex = :sex' );
		}
		if(isset($option) and $option > 0) {
			$qb->andWhere( 'v.variation = :option' );
		}
		if(isset($numberPersons) and $numberPersons > 0) {
			$qb->andWhere( 'v.numberPersons = :numbers' );
		}
		if(isset($category) and $category > 0) {
			$qb->andWhere( 'vc.id = :category' );
		}
		if(!empty($tags)){
			$qb->andWhere('t.slug IN (:tag)');
		}
		
		$qb->andWhere( 'v.active = 1' );
		
		if(isset($event) and $event > 0) {
			$qb->andWhere( 'ev.id = :event' );
		}
		if(isset($sex) and $sex > 0) {
			$qb->setParameter( 'sex', $sex );
		}
		if(isset($option) and $option > 0) {
			$qb->setParameter( 'option', $option );
		}
		if(isset($numberPersons) and $numberPersons > 0) {
			$qb->setParameter( 'numbers', $numberPersons );
		}
		if(isset($category) and $category > 0) {
			$qb->setParameter( 'category', $category );
		}
		if(isset($event) and $event > 0) {
			$qb->setParameter( 'event', $event );
		
}		if(!empty($tags)){
			$qb->setParameter('tag', $tags);
		}
		
		$qb->setParameter( 'locale', $locale );

		$queryCount = $qb->getQuery();
		
		return (object) array( 'query' => $query->getResult(), 'count' => $queryCount->getSingleScalarResult());
	}
	
	/**
	 * Поиск видео по критериям фильтра
	 */
	public function findByVideoByCategory( $locale, $category )
	{
		$qbc = $this->createQueryBuilder('v');
		
		$qbc->select( 'v' )
		    ->leftJoin('v.category', 'vc')
			->where( 'v.locale IN (:locale)' )
		    ->andWhere( 'vc.id = :category' )
			->andWhere( 'v.active = 1' )
		    ->setParameter( 'category', $category )
			->setParameter( 'locale', $locale )
		    ->orderBy( 'v.positionCategory', 'DESC' );
		
		$query = $qbc->getQuery();
		
		return $query->getResult();
	}
}
