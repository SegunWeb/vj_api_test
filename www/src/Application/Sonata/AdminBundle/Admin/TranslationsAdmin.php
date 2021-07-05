<?php
namespace App\Application\Sonata\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

class TranslationsAdmin extends AbstractAdmin
{

	protected $baseRoutePattern = 'translations';

	protected $baseRouteName = 'translations';

	protected function configureRoutes(RouteCollection $collection)
	{
		$collection->clearExcept(['list']);
		$collection->add('showTranslations', 'show_translations/{configName}/{locale}/{domain}');
	}
}
