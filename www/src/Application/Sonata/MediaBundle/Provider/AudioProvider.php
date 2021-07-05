<?php

namespace App\Application\Sonata\MediaBundle\Provider;

use SilasJoisten\Sonata\MultiUploadBundle\Traits\MultiUploadTrait;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\Metadata;

class AudioProvider extends FileProvider
{
	use MultiUploadTrait;
	
	public function getProviderMetadata()
	{
		return new Metadata($this->getName(),
			$this->getName().'.description',
			null,
			'SonataMediaBundle',
			['class' => 'fa fa-file-audio-o']
		);
	}
}