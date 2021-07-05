<?php

namespace App\Admin;

use App\Constants\PaymentConstants;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SettingAdmin extends AbstractAdmin
{
	
	protected function configureRoutes( RouteCollection $collection )
	{
		$collection->remove( 'delete' )->remove( 'create' )->remove( 'list' )->remove( 'show' );
	}
	
	/**
	 * @param FormMapper $formMapper
	 */
	protected function configureFormFields( FormMapper $formMapper )
	{
		$formMapper
			->tab( $this->trans( 'tab.common_info' ) )
			->with( $this->trans( 'tab.setting' ), [ 'class' => 'col-md-9' ] )
			->add( 'logoHeader', ModelListType::class, array(
				'label'    => $this->trans( 'object.logo_header' ),
				'required' => false
			), array( 'link_parameters' => array( 'context' => 'logo' ) ) )
			->add( 'logoFooter', ModelListType::class, array(
				'label'    => $this->trans( 'object.logo_footer' ),
				'required' => false
			), array( 'link_parameters' => array( 'context' => 'logo' ) ) )
			->add( 'logoPreloader', ModelListType::class, array(
				'label'    => $this->trans( 'object.logo_preloader' ),
				'required' => false
			), array( 'link_parameters' => array( 'context' => 'logo' ) ) )
			->add( 'email', TextType::class, [ 'label' => $this->trans( 'object.email' ), 'required' => false ] )
			->add( 'skype', TextType::class, [ 'label' => $this->trans( 'object.skype' ), 'required' => false ] )
			->add( 'phone', TextType::class, [ 'label' => $this->trans( 'object.phone' ), 'required' => false ] )
			->add( 'discountEmailMarketing', NumberType::class, [
				'label'    => $this->trans( 'object.discount_email_marketing' ),
				'required' => false
			] )
			->add( 'updatedAt', DatePickerType::class, [
				'required' => false,
				'label'    => $this->trans( 'object.updated' ),
				'disabled' => true,
				'format'   => 'dd.MM.yyyy, HH:mm'
			] )
			->end()
			->with( $this->trans( 'tab.soc_network' ), [ 'class' => 'col-md-3' ] )
			->add( 'socialFbLink', TextType::class, [ 'label' => $this->trans( 'object.fb' ), 'required' => false ] )
			->add( 'socialYtLink', TextType::class, [ 'label' => $this->trans( 'object.yt' ), 'required' => false ] )
			->add( 'socialInLink', TextType::class, [ 'label' => $this->trans( 'object.in' ), 'required' => false ] )
			->end()
			->end()
			->tab( $this->trans( 'tab.api_key_info' ) )
			->with( $this->trans( 'admin.block.youtube' ), [ 'class' => 'col-md-4' ] )
			->add( 'apiYoutubeApplicationName', TextType::class, array(
				'label'    => $this->trans( 'object.api_youtube_application_name' ),
				'required' => false
			) )
			->add( 'apiYoutubeClientSecret', TextType::class, array(
				'label'    => $this->trans( 'object.api_youtube_client_secret' ),
				'required' => false
			) )
			->add( 'apiYoutubeClientId', TextType::class, array(
				'label'    => $this->trans( 'object.api_youtube_client_id' ),
				'required' => false
			) )
			->end()
			->with( $this->trans( 'admin.block.paypal' ), [ 'class' => 'col-md-4' ] )
			->add( 'apiKeyPayPalClientId', null, [
				'label'    => $this->trans( 'object.api_key_pay_pal_client_id' ),
				'required' => false
			] )
			->add( 'apiKeyPayPalClientSecret', null, [
				'label'    => $this->trans( 'object.api_key_pay_pal_client_secret' ),
				'required' => false
			] )
            ->add( 'isPayPalSandbox', CheckboxType::class, [
                'label'    => "Sandbox mode",
                'required' => false
            ] )
			->end()
			->with( $this->trans( 'admin.block.turbosms' ), [ 'class' => 'col-md-4' ] )
			->add( 'apiTurbosmsLogin', null, [
				'label'    => $this->trans( 'object.api_turbosms_login' ),
				'required' => false
			] )
			->add( 'apiTurbosmsPassword', null, [
				'label'    => $this->trans( 'object.api_turbosms_password' ),
				'required' => false
			] )
			->add( 'apiTurbosmsSignature', null, [
				'label'    => $this->trans( 'object.api_turbosms_signature' ),
				'required' => false
			] )
			->end()
			->with( $this->trans( 'admin.block.platon' ), [ 'class' => 'col-md-3' ] )
			->add( 'apiKeyPlatonMerchantId', null, [
				'label'    => $this->trans( 'object.api_key_platon_merchant_id' ),
				'required' => false
			] )
			->add( 'apiKeyPlatonMerchantPassword', null, [
				'label'    => $this->trans( 'object.api_key_platon_merchant_password' ),
				'required' => false
			] )
			->add( 'apiKeyPlatonMerchantUrl', null, [
				'label'    => $this->trans( 'object.api_key_platon_merchant_url' ),
				'required' => false,
				'help'     => $this->trans( 'object.api_key_platon_merchant_url_help' )
			] )
			->end()
			->with( $this->trans( 'admin.block.interkassa' ), [ 'class' => 'col-md-3' ] )
			->add( 'apiKeyInterkassaMerchantId', null, [
				'label'    => $this->trans( 'object.api_key_platon_merchant_id' ),
				'required' => false
			] )
			->add( 'apiKeyInterkassaSecretKey', null, [
				'label'    => $this->trans( 'object.api_key_platon_merchant_secret_key' ),
				'required' => false
			] )
			->add( 'apiKeyInterkassaTestKey', null, [
				'label'    => $this->trans( 'object.api_key_platon_merchant_test_key' ),
				'required' => false,
				'help'     => $this->trans( 'object.api_key_platon_merchant_url_help' )
			] )
			->end()
			->with( $this->trans( 'admin.block.liqpay' ), [ 'class' => 'col-md-3' ] )
			->add( 'apiKeyLiqPayPrivatKey', null, [
				'label'    => $this->trans( 'object.api_key_liqpay_privat_key' ),
				'required' => false
			] )
			->add( 'apiKeyLiqPayPublicKey', null, [
				'label'    => $this->trans( 'object.api_key_liqpay_public_key' ),
				'required' => false
			] )
			->end()
			->with( $this->trans( 'admin.block.facebook' ), [ 'class' => 'col-md-3' ] )
			->add( 'apiKeyFacebookClientId', null, [
				'label'    => $this->trans( 'object.api_key_pay_pal_client_id' ),
				'required' => false
			] )
			->add( 'apiKeyFacebookClientSecret', null, [
				'label'    => $this->trans( 'object.api_key_pay_pal_client_secret' ),
				'required' => false
			] )
			->end()
			->end()
			->tab( $this->trans( 'tab.analytics' ) )
			->with( '' )
			->add( 'googleAnalytics', null, [
				'label'    => $this->trans( 'object.google_analytics' ),
				'required' => false
			] )
			->add( 'facebookPixel', null, [ 'label' => $this->trans( 'object.facebook_pixel' ), 'required' => false ] )
			->end()
			->end()
			->tab( $this->trans( 'tab.meta' ) )
			->with( $this->trans( 'tab.meta' ), [ 'class' => 'col-md-8' ] )
			->add( 'metaTitle', null, [
				'label'    => $this->trans( 'object.meta.title' ),
				'required' => false
			] )
			->add( 'metaKeywords', null, [
				'label'    => $this->trans( 'object.meta.keywords' ),
				'required' => false
			] )
			->add( 'metaDescription', null, [
				'label'    => $this->trans( 'object.meta.description' ),
				'required' => false
			] )
			->add( 'metaCanonical', null, [
				'label'    => $this->trans( 'object.meta.canonical' ),
				'required' => false
			] )
			->end()
			->with( $this->trans( 'tab.image' ), [ 'class' => 'col-md-4' ] )
			->add( 'meta_image', ModelListType::class, array(
				'label'    => $this->trans( 'object.meta.image' ),
				'required' => false
			), array( 'link_parameters' => array( 'context' => 'meta' ) ) )
			->add( "created_at", DatePickerType::class, [
				'required' => false,
				"label"    => $this->trans( 'object.created' ),
				'format'   => 'dd.MM.yyyy, HH:mm',
				"disabled" => true
			] )
			->add( "updated_at", DatePickerType::class, [
				'required' => false,
				"label"    => $this->trans( 'object.updated' ),
				'format'   => 'dd.MM.yyyy, HH:mm',
				"disabled" => true
			] )
			->end()
			->end()
			->tab( $this->trans( 'tab.robots_txt' ) )
			->with( $this->trans( 'tab.robots_txt' ), [ 'class' => 'col-md-12' ] )
			->add( 'robotsTxt', TextareaType::class, [
				'label'    => $this->trans( 'object.robots_txt' ),
				'required' => false
			] )
			->end()
			->end()
            ->tab( $this->trans( 'tab.payment_settings' ) )
            ->with( $this->trans( 'tab.payment_settings' ), [ 'class' => 'col-md-12' ] )
            ->add( 'paymentType', ChoiceType::class, [
                'required' => true,
                'label'    => $this->trans( 'object.payment_types' ),
                'choices'  => array_flip( PaymentConstants::loadPaymentsTypes() )
            ] )
            ->end()
            ->with( $this->trans( 'tab.purchase_settings' ), [ 'class' => 'col-md-12' ] )
            ->add( 'invitationPurchase', CKEditorType::class, [
                'label'    => $this->trans( 'object.invitation_purchase' ),
                'required' => false
            ] )
            ->add( 'descriptionPurchase', CKEditorType::class, [
                'label'    => $this->trans( 'object.description_purchase' ),
                'required' => false
            ] )
            ->end()
            ->end();
	}
	
	public function toString( $object )
	{
		return 'Настройки';
	}
	
	public function preUpdate( $args )
	{
		file_put_contents('robots.txt', $args->getRobotsTxt());
	}
}
