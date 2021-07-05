<?php

namespace App\Admin;

use App\Constants\ActiveConstants;
use App\Entity\MailTemplate;
use App\Constants\MailEventConstants;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MailTemplateAdmin extends AbstractAdmin
{
	
	/**
	 * @param ListMapper $listMapper
	 */
	protected function configureListFields( ListMapper $listMapper )
	{
		$listMapper
			->add( 'event', 'choice', [
				'editable' => false,
				'label'    => $this->trans( 'object.event' ),
				'choices'  => MailEventConstants::loadValues()
			] )
			->add( 'subjectMessageAdmin', TextType::class, [ 'label' => $this->trans( 'object.mail_theme_admin' ) ] )
			->add( 'subjectMessageUsers', TextType::class, [ 'label' => $this->trans( 'object.mail_theme_users' ) ] )
			->add( 'active', 'choice', [
				'sortable' => false,
				'editable' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => array_flip( ActiveConstants::loadActivityValues() )
			] )
			->add( '_action', null, array(
				'label'   => $this->trans( 'list.actions' ),
				'actions' => array(
					'edit'   => array(),
					'delete' => array(),
				)
			) );
	}
	
	/**
	 * @param FormMapper $formMapper
	 */
	protected function configureFormFields( FormMapper $formMapper )
	{
		$formMapper
			->tab( $this->trans( 'tab.common_info' ) )
			->with( $this->trans( 'tab.common_info' ), [] )
			->add( 'fromEmail', TextType::class, [ 'label'    => $this->trans( 'object.from_email' ),
			                                       'required' => true
			] )
			->add( 'adminEmail', TextType::class, [
				'label'    => 'object.admin_email',
				'required' => true,
				'help'     => $this->trans( 'object.help.admin_email' )
			] )
			->add( "event", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.event' ),
				'choices'  => array_flip( MailEventConstants::loadValues() )
			] )
			->add( "active", ChoiceType::class, [
				'required' => true,
				'label'    => $this->trans( 'object.activity' ),
				'choices'  => ActiveConstants::loadActivityValues()
			] )
			->end()
			->end()
			->tab( $this->trans( 'tab.content_template_admin' ) )
			->with( $this->trans( 'tab.content_template_admin' ), [] )
			->add( 'subjectMessageAdmin', TextType::class, array(
				'label'    => $this->trans( 'object.mail_theme_admin' ),
				'required' => false,
				'help'     => $this->trans( 'object.help_subject' )
			) )
			->add( 'bodyMessageAdmin', TextareaType::class, array(
				'attr'     => array( 'cols' => '5', 'rows' => '10' ),
				'required' => false,
				'help'     => 'Возможные данные...',
				'label'    => $this->trans( 'object.content_template' )
			) )
			->end()
			->end()
			->tab( $this->trans( 'tab.content_template_users' ) )
			->with( $this->trans( 'tab.content_template_users' ), [] )
			->add( 'subjectMessageUsers', TextType::class, array(
				'label'    => $this->trans( 'object.mail_theme_users' ),
				'required' => false,
				'help'     => $this->trans( 'object.help_subject' )
			) )
			->add( 'bodyMessageUsers', TextareaType::class, array(
				'attr'     => array( 'cols' => '5', 'rows' => '10' ),
				'required' => false,
				'help'     => 'Возможные данные...',
				'label'    => $this->trans( 'object.content_template' )
			) )
			->end()
			->end();
	}
	
	public function toString( $object )
	{
		return $object instanceof MailTemplate
			? $object->getSubjectMessageAdmin() ?: $object->getSubjectMessageUsers()
			: 'Шаблон письма';
	}
}
