<?php

namespace App\Admin;

use App\Constants\LocaleConstants;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\TranslationBundle\Filter\TranslationFieldFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CountryAdmin extends AbstractAdmin
{
	/**
	 * @param DatagridMapper $datagridMapper
	 */
	protected function configureDatagridFilters( DatagridMapper $datagridMapper ): void
	{
		$datagridMapper
			->add( 'translations.name', null, [ 'label' => $this->trans( 'object.country_name' ) ] )
            ->add( 'isoCode', null, [ 'label' => $this->trans( 'object.iso_code' ) ] )
            ->add( 'defaultCountryLocale', 'doctrine_orm_choice', [
	            'label'       => $this->trans( 'object.default_country_locale' ),
	            'show_filter' => true
            ], ChoiceType::class, [
		            'choices'  => LocaleConstants::loadLocaleValues(),
		            'expanded' => false,
		            'multiple' => true
	            ]
            );
	}
	
	/**
	 * @param ListMapper $listMapper
	 */
	protected function configureListFields( ListMapper $listMapper ): void
	{
		$listMapper
			->add( '_action', null, array(
				'label'   => $this->trans( 'list.actions' ),
				'actions' => array(
					'edit'   => array(),
					'delete' => array()
				)
			) )
            ->add( 'name', null, array( 'sortable' => false, 'label' => $this->trans( 'object.country_name' ) ) )
            ->add( 'defaultCountryLocale', null, array( 'sortable' => false, 'label' => $this->trans( 'object.default_country_locale' ) ) )
			->add( 'isoCode', null, array( 'sortable' => false, 'label' => $this->trans( 'object.iso_code' ) ) );
	}
	
	/**
	 * @param FormMapper $formMapper
	 */
	protected function configureFormFields( FormMapper $formMapper ): void
	{
		$formMapper
			->with( $this->trans( 'tab.common_info' ) )
			->add( 'name', TextType::class, [ 'label' => $this->trans( 'object.country_name' ) ] )
			->add( 'isoCode', TextType::class, [ 'label' => $this->trans( 'object.iso_code' ) ] )
            ->add( "defaultCountryLocale", ChoiceType::class, [
                'required' => true,
                'label'    => $this->trans( 'object.default_country_locale' ),
                'choices'  => LocaleConstants::loadLocaleValues()
            ] )
			->end();
	}
	
	public function createQuery( $context = 'list' )
	{
		$query = parent::createQuery( $context );
		
		$query->addSelect( 'tl as translations' );
		$query->innerJoin( $query->getRootAlias() . ".translations", "tl" );
		
		return $query;
	}
}