<?php

namespace App\Application\Sonata\MediaBundle\Provider;

use Sonata\MediaBundle\Provider\FileProvider as BaseFileProvider;
use Gaufrette\Filesystem;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class VideoProvider extends BaseFileProvider
{
	protected $allowedMimeTypes;
	protected $allowedExtensions;
	protected $metadata;
	
	public function __construct( $name, Filesystem $filesystem, CDNInterface $cdn, GeneratorInterface $pathGenerator, ThumbnailInterface $thumbnail, array $allowedExtensions = array(), array $allowedMimeTypes = array(), MetadataBuilderInterface $metadata = null )
	{
		parent::__construct( $name, $filesystem, $cdn, $pathGenerator, $thumbnail );
		
		$this->allowedExtensions = $allowedExtensions;
		$this->allowedMimeTypes  = $allowedMimeTypes;
		$this->metadata          = $metadata;
	}
	
	public function buildCreateForm( FormMapper $formMapper )
	{
		$formMapper->add( 'binaryContent', FileType::class, array(
			'label'       => 'Upload mp4 file only',
			'constraints' => array(
				new NotBlank(),
				new NotNull(),
			),
		) );
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function validate( ErrorElement $errorElement, MediaInterface $media )
	{
		
		if ( ! $media->getBinaryContent() instanceof \SplFileInfo ) {
			return;
		}
		
		if ( $media->getBinaryContent() instanceof UploadedFile ) {
			$fileName = $media->getBinaryContent()->getClientOriginalName();
		} elseif ( $media->getBinaryContent() instanceof File ) {
			$fileName = $media->getBinaryContent()->getFilename();
		} else {
			throw new \RuntimeException( sprintf( 'Invalid binary content type: %s', get_class( $media->getBinaryContent() ) ) );
		}
		
		if ( !in_array( strtolower( pathinfo( $fileName, PATHINFO_EXTENSION ) ), $this->allowedExtensions ) === FALSE ) {
			$errorElement
				->with( 'binaryContent' )
				->addViolation( 'Invalid extensions' )
				->end();
		}
		
		if ( !in_array( $media->getBinaryContent()->getMimeType(), $this->allowedMimeTypes ) === FALSE ) {
			$errorElement
				->with( 'binaryContent' )
				->addViolation( 'Invalid mime type : ' . $media->getBinaryContent()->getMimeType() )
				->end();
		}
	}
}