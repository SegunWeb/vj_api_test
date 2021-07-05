<?php

namespace App\Service;

use App\Entity\Phrases;
use Doctrine\ORM\EntityManager;

class PhrasesService
{
	protected $manager;
	
	public function __construct( EntityManager $manager )
	{
		$this->manager = $manager;
	}
	
	public function addMediaToAudioList( $audio, Phrases $phrasesAdmin )
	{
		if ( ! empty( $audio ) ) {
			
			$arrayListAudio = explode( ',', $audio );
			
			if($phrasesAdmin->getAudio()->isEmpty() == false){
				foreach ($phrasesAdmin->getAudio()->toArray() as $audio) {
					if(in_array($audio->getId(), $arrayListAudio) == false){
						$phrasesAdmin->removeAudio($audio);
					}
				}
			}
			
			if ( ! empty( $arrayListAudio ) ) {
				
				foreach ( $arrayListAudio as $audio ) {
					
					$media = $this->manager->getRepository( 'ApplicationSonataMediaBundle:Media' )->find( $audio );
					
					$phrasesAdmin->addAudio( $media );
					
				}
			}
		}else{
			//Если удалены все медиа файлы, то очищаем сущность от привязанных медиа
			if($phrasesAdmin->getAudio()->isEmpty() == false){
				$phrasesAdmin->removeAudioAll();
			}
		}
		
		return $phrasesAdmin;
	}
}