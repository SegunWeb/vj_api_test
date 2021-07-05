<?php

namespace App\Admin;

use App\Constants\ActiveConstants;
use App\Constants\PhrasesTypeConstants;
use App\Entity\Phrases;
use App\Entity\PhrasesCategories;
use App\Form\Type\MultipleUploadType;
use App\Service\PhrasesService;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\Form\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PhrasesAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('translations.title', null, ['label' => $this->trans('object.phrases')])
            ->add('category', null, ['label' => $this->trans('object.phrases_categories')])
            ->add('type', 'doctrine_orm_choice', [
                'label'       => $this->trans('object.type_phrases'),
                'show_filter' => true
            ], ChoiceType::class, [
                    'choices'  => array_flip(PhrasesTypeConstants::loadPhrasesValues()),
                    'expanded' => false,
                    'multiple' => true
                ]
            );
    }
    
    protected function configureRoutes(RouteCollection $collection): void
    {
        $collection->add('create_gallery', 'create/gallery/uploaded/medias');
    }
    
    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('_action', null, array(
                'label'   => $this->trans('list.actions'),
                'actions' => array(
                    'edit'   => array(),
                    'delete' => array()
                )
            ))
            ->add('title', null, array('label' => $this->trans('object.phrases')))
            ->add('category', null, ['label' => $this->trans('object.phrases_categories')])
            ->add('type', 'choice', [
                'sortable' => false,
                'editable' => true,
                'label'    => $this->trans('object.type_phrases'),
                'choices'  => PhrasesTypeConstants::loadPhrasesValues()
            ])
            ->add('active', 'choice', [
                'sortable' => false,
                'editable' => true,
                'label'    => $this->trans('object.activity'),
                'choices'  => array_flip(ActiveConstants::loadActivityValues())
            ]);
    }
    
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with($this->trans('tab.common_info'))
            ->add('title', TextType::class, ['label' => $this->trans('object.phrases')])
            ->add('category', EntityType::class, [
                'class'         => PhrasesCategories::class,
                'placeholder'   => $this->trans('object.selected_phrases'),
                'required'      => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('v')
                              ->orderBy('v.id', 'ASC');
                },
                'choice_label'  => 'laveled_title',
                'label'         => $this->trans('object.phrases_categories')
            ])
            ->add("type", ChoiceFieldMaskType::class, [
                'required' => true,
                'label'    => $this->trans('object.type_phrases'),
                'choices'  => array_flip(PhrasesTypeConstants::loadPhrasesValues())
            ])
            ->add('audio', MultipleUploadType::class, array(
                'label'    => $this->trans('object.audio'),
                'required' => false,
                'mapped'   => false,
                'attr'     => [
                    'subject' => $this->getSubject()->getAudio()->toArray(),
                    'type'    => $this->getSubject()->getType()
                ],
                'help'     => 'К обычной фразе можно добавить лишь 1 аудио файл, для фразы с именем можно добавить до 1000 аудио файлов'
            ))
            ->add("active", ChoiceType::class, [
                'required' => true,
                'label'    => $this->trans('object.activity'),
                'choices'  => ActiveConstants::loadActivityValues()
            ])
            ->end();
    }
    
    public function preUpdate($args)
    {
        $this->formattingAudioList();
    }
    
    public function prePersist($args)
    {
        $this->formattingAudioList();
    }
    
    public function formattingAudioList()
    {
        $form    = $this->getForm()->getConfig()->getName();
        $request = $this->getRequest()->request->get($form);
        $audio   = $request['audio'];
        
        $subject = $this->getConfigurationPool()->getContainer()->get(PhrasesService::class)->addMediaToAudioList($audio,
            $this->getSubject());
        
        $this->setSubject($subject);
    }
    
    public function toString($object)
    {
        return $object instanceof Phrases
            ? $object->getTitle()
            : 'Фразы';
    }
    
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        
        $query->addSelect('tl as translations');
        $query->innerJoin($query->getRootAlias() . ".translations", "tl");
        
        return $query;
    }
}
