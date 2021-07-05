<?php

namespace App\Admin;

use App\Constants\PhrasesTypeConstants;
use App\Constants\VideoConstants;
use App\Entity\Phrases;
use App\Entity\PhrasesCategories;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class VideoPlaceholderAdmin extends AbstractAdmin
{
	protected $datagridValues = [
		'_page'       => 1,
		'_sort_order' => 'ASC',
		'_sort_by'    => 'position',
	];
	
	protected function configureFormFields( FormMapper $formMapper )
	{
		
		$formMapper
			->add( 'layerName', TextType::class, [
				'required'   => false,
				'label'      => $this->trans( 'object.video_layer_name' ),
				'empty_data' => ''
			] )
			->add('layerNameMouth', TextType::class, [
				'required'   => false,
				'label'      => $this->trans( 'object.video_layer_name_mouth' ),
				'empty_data' => ''
			] )
			->add( 'layerIndex', TextType::class, [
				'required'   => false,
				'label'      => $this->trans( 'object.video_layer_index' ),
				'empty_data' => '',
				'help'       => $this->trans( 'object.video_layer_index_help' )
			] )
			->add( 'composition', TextType::class, [
				'required'   => false,
				'label'      => $this->trans( 'object.video_placeholder_composition' ),
				'empty_data' => '',
				'help'       => $this->trans( 'object.video_placeholder_composition_help' )
			] )
			->add( 'type', ChoiceFieldMaskType::class, [
				'label'       => $this->trans( 'object.type_video_placeholder' ),
				'required'    => true,
				'choices'     => VideoConstants::loadVideoPlaceholderValues(),
				'map'         => [
					VideoConstants::IMAGE            => [ 'layerName', 'imageOrientation', 'layerIndex', 'composition', 'imageWidth', 'imageHeight' ],
					VideoConstants::IMAGE_MANY       => [ 'layerName', 'imageOrientation', 'composition', 'layerNameAudio', 'audioPhraseCategory', 'imageWidth', 'imageHeight', 'maxFiles' ],
                    VideoConstants::URL_AUDIO        => [ 'layerName', 'audioPhrase', 'layerIndex', 'composition' ],
                    VideoConstants::AUDIO_SEX_PHRASE => [ 'layerName', 'audioPhrase', 'layerIndex', 'composition' ],
					VideoConstants::AUDIO_PHRASE     => [ 'layerName', 'audioPhraseCategory', 'layerIndex', 'composition' ],
					VideoConstants::VIDEO            => [ 'layerName', 'imageWidth', 'imageHeight', 'videoMaxSize', 'videoMaxLength' ],
					VideoConstants::TEXT             => [ 'layerIndex', 'composition' ],
					VideoConstants::POSTCARD         => [ 'layerName', 'layerNameMouth', 'composition', 'imageWidth', 'imageHeight' ]
				],
				'placeholder' => $this->trans( 'object.choice_type_video_placeholder' ),
				'help'        => $this->trans( 'object.type_video_placeholder_help' )
			] )
			->add( 'layerNameAudio', TextType::class, [
				'required'   => false,
				'label'      => $this->trans( 'object.video_layer_name_audio' ),
				'empty_data' => ''
			] )
			->add( 'audioPhraseCategory', EntityType::class, [
				'class'         => PhrasesCategories::class,
				'placeholder'   => $this->trans( 'object.selected_categories_phrase' ),
				'required'      => false,
				'query_builder' => function ( EntityRepository $er ) {
					return $er->createQueryBuilder( 'v' )
					          ->orderBy( 'v.id', 'ASC' );
				},
				'choice_label'  => 'laveled_title',
				'label'         => $this->trans( 'object.audio_phrase_category' )
			] )
			->add( 'audioPhrase', EntityType::class, [
				'class'         => Phrases::class,
				'placeholder'   => $this->trans( 'object.selected_phrase' ),
				'required'      => false,
				'query_builder' => function ( EntityRepository $er ) {
						return $er->createQueryBuilder( 'v' )
								->where('v.type = :type')
                                ->orWhere('v.type = :typeTwo')
								->setParameter('type', PhrasesTypeConstants::PHRASES_AND_NAME)
                                ->setParameter('typeTwo', PhrasesTypeConstants::PHRASES_AND_SEX)
								->orderBy( 'v.id', 'ASC' );
				},
				'choice_label'  => 'laveled_title',
				'label'         => $this->trans( 'object.audio_phrase' )
			] )
			->add( 'imageWidth', TextType::class, [
				'label'    => $this->trans( 'object.video_placeholder_image_width' ),
				'required' => false
			] )
			->add( 'imageHeight', TextType::class, [
				'label'    => $this->trans( 'object.video_placeholder_image_height' ),
				'required' => false
			] )
			->add( 'maxFiles', TextType::class, [
				'label'    => $this->trans( 'object.video_placeholder_max_files' ),
				'required' => false
			] )
			->add( 'videoMaxSize', TextType::class, [
				'label'    => $this->trans( 'object.video_placeholder_video_max_size' ),
				'required' => false
			] )
			->add( 'videoMaxLength', TextType::class, [
				'label'    => $this->trans( 'object.video_placeholder_video_max_length' ),
				'required' => false
			] )
			->add( 'imageOrientation', ChoiceType::class, array(
				'label'    => $this->trans( 'object.video_placeholder_image_orientation' ),
				'required' => false,
				'choices'  => VideoConstants::loadVideoOrientation()
			) )
			->add( 'description', TextType::class, [
				'label'    => $this->trans( 'object.video_placeholder_description' ),
				'required' => false
			] );
	}
	
}