<?php

namespace App\Application\Sonata\MediaBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sonata\MediaBundle\Admin\GalleryAdmin as ParentGalleryAdmin;

class GalleryAdmin extends ParentGalleryAdmin
{
    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        $parameters = parent::getPersistentParameters();

        if (!$this->hasRequest()) {
            return $parameters;
        }

        return array_merge($parameters, [
            'context' => $this->getRequest()->get('context', 'gallery'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($gallery)
    {
        $context = $this->getPersistentParameter('context');

        $gallery->setContext($context);

        // fix weird bug with setter object not being call
        $gallery->setGalleryHasMedias($gallery->getGalleryHasMedias());
    }
    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        // define group zoning
        $formMapper
            ->with('Gallery', array('class' => 'col-md-9'))->end()
            ->with('Options', array('class' => 'col-md-3'))->end()
        ;


        $context = $this->getPersistentParameter('context');

        if (!$context) {
            $context = $this->pool->getDefaultContext();
        }

        $formats = [];
        foreach ((array) $this->pool->getFormatNamesByContext($context) as $name => $options) {
            $formats[$name] = $name;
        }

        $contexts = [];
        foreach ((array) $this->pool->getContexts() as $contextItem => $format) {
            $contexts[$contextItem] = $contextItem;
        }

        // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+.
        $choiceType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\ChoiceType'
            : 'choice';

        // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+.
        $collectionType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Sonata\CoreBundle\Form\Type\CollectionType'
            : 'sonata_type_collection';

        $formMapper
            ->with('Options')
            ->add('context', $choiceType, array('choices' => $contexts))
            ->add('enabled', null, array('required' => false))
            ->add('name')
            ->ifTrue($formats)
            ->add('defaultFormat', $choiceType, array('choices' => $formats))
            ->ifEnd()
            ->end()
            ->with('Gallery')
            ->add('galleryHasMedias', $collectionType, array(), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable' => 'position',
                'link_parameters' => array('context' => $context),
                'admin_code' => 'sonata.media.admin.gallery_has_media',
            ))
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('enabled')
            ->add('context', null, [
                'show_filter' => false,
            ])
        ;
    }
}