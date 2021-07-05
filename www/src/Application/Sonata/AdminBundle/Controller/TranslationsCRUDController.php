<?php
namespace App\Application\Sonata\AdminBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Intl\Intl;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\MessageCatalogue;
use Translation\Bundle\Model\CatalogueMessage;

class TranslationsCRUDController extends CRUDController
{
	/**
	 * Show a dashboard for the configuration.
	 *
	 * @param string|null $configName
	 *
	 * @return Response
	 */
	public function listAction($configName = null)
	{
		if (!$this->getParameter('php_translation.webui.enabled')) {
			return new Response('You are not allowed here. Check you config. ', 400);
		}

		$configManager = $this->get('php_translation.configuration_manager');
		$config = $configManager->getConfiguration($configName);
		$localeMap = $this->getLocale2LanguageMap();
		$catalogues = $this->get('php_translation.catalogue_fetcher')->getCatalogues($config);

		$catalogueSize = [];
		$maxDomainSize = [];
		$maxCatalogueSize = 1;

		// For each catalogue (or locale)
		/** @var MessageCatalogue $catalogue */
		foreach ($catalogues as $catalogue) {
			$locale = $catalogue->getLocale();
			$domains = $catalogue->all();
			ksort($domains);
			$catalogueSize[$locale] = 0;
			foreach ($domains as $domain => $messages) {
				$count = count($messages);
				$catalogueSize[$locale] += $count;
				if (!isset($maxDomainSize[$domain]) || $count > $maxDomainSize[$domain]) {
					$maxDomainSize[$domain] = $count;
				}
			}

			if ($catalogueSize[$locale] > $maxCatalogueSize) {
				$maxCatalogueSize = $catalogueSize[$locale];
			}
		}

		return $this->renderWithExtraParams('ApplicationSonataAdminBundle:TranslationsCRUD:translations_list.html.twig', [
			'catalogues' => $catalogues,
			'catalogueSize' => $catalogueSize,
			'maxDomainSize' => $maxDomainSize,
			'maxCatalogueSize' => $maxCatalogueSize,
			'localeMap' => $localeMap,
			'configName' => $config->getName(),
			'configNames' => $configManager->getNames(),
		]);
	}

	/**
	 * Show a catalogue.
	 *
	 * @param string $configName
	 * @param string $locale
	 * @param string $domain
	 *
	 * @return Response
	 */
	public function showTranslationsAction($configName, $locale, $domain)
	{
		if (!$this->getParameter('php_translation.webui.enabled')) {
			return new Response('You are not allowed here. Check you config. ', 400);
		}
		$configManager = $this->get('php_translation.configuration_manager');
		$config = $configManager->getConfiguration($configName);

		// Get a catalogue manager and load it with all the catalogues
		$catalogueManager = $this->get('php_translation.catalogue_manager');
		$catalogueManager->load($this->get('php_translation.catalogue_fetcher')->getCatalogues($config));

		/** @var CatalogueMessage[] $messages */
		$messages = $catalogueManager->getMessages($locale, $domain);
		usort($messages, function (CatalogueMessage $a, CatalogueMessage $b) {
			return strcmp($a->getKey(), $b->getKey());
		});

		return $this->renderWithExtraParams('ApplicationSonataAdminBundle:TranslationsCRUD:translations_show.html.twig', [
			'messages' => $messages,
			'domains' => $catalogueManager->getDomains(),
			'currentDomain' => $domain,
			'locales' => $this->getParameter('php_translation.locales'),
			'currentLocale' => $locale,
			'configName' => $config->getName(),
			'configNames' => $configManager->getNames(),
			'allow_create' => $this->getParameter('php_translation.webui.allow_create'),
			'allow_delete' => $this->getParameter('php_translation.webui.allow_delete'),
			'file_base_path' => $this->getParameter('php_translation.webui.file_base_path'),
		]);
	}

	/**
	 * This will return a map of our configured locales and their language name.
	 *
	 * @return array locale => language
	 */
	private function getLocale2LanguageMap()
	{
		$configuredLocales = $this->getParameter('php_translation.locales');
		$names = Intl::getLocaleBundle()->getLocaleNames('en');
		$map = [];
		foreach ($configuredLocales as $l) {
			$map[$l] = isset($names[$l]) ? $names[$l] : $l;
		}

		return $map;
	}


}
