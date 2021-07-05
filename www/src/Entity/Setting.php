<?php

namespace App\Entity;

use App\Traits\MetaTrait;
use App\Traits\MetaImageTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Application\Sonata\MediaBundle\Entity\Media;

/**
 * Setting
 *
 * @ORM\Table(name="setting")
 * @ORM\Entity(repositoryClass="App\Repository\SettingRepository")
 */
class Setting
{
    use MetaTrait;
    use MetaImageTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=190, nullable=true)
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=190, nullable=true)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="skype", type="string", length=190, nullable=true)
     */
    protected $skype;

    /**
     * @var string
     *
     * @ORM\Column(name="discount_email_marketing", type="float", length=190, nullable=true)
     */
    protected $discountEmailMarketing;

    /**
     * @var integer
     *
     * @ORM\Column(name="payment_type", type="smallint", nullable=true, options={"default" : 1})
     */
    protected $paymentType;

    /**
     * @ORM\Column(name="invitation_purchase", type="text", nullable=true)
     */
    protected $invitationPurchase;

    /**
     * @ORM\Column(name="description_purchase", type="text", nullable=true)
     */
    protected $descriptionPurchase;

    /**
     * @var string
     *
     * @ORM\Column(name="social_fb_link", type="string", length=190, nullable=true)
     */
    protected $socialFbLink;

    /**
     * @var string
     *
     * @ORM\Column(name="social_yt_link", type="string", length=190, nullable=true)
     */
    protected $socialYtLink;

    /**
     * @var string
     *
     * @ORM\Column(name="social_in_link", type="string", length=255, nullable=true)
     */
    protected $socialInLink;

    /**
     * @var string
     *
     * @ORM\Column(name="google_analytics",  type="text", nullable=true)
     */
    protected $googleAnalytics;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_pixel",  type="text", nullable=true)
     */
    protected $facebookPixel;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     */
    protected $logoHeader;

    /**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     */
    protected $logoFooter;

    /**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     */
    protected $logoPreloader;

    /**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     */
    protected $apiGoogleClientSecretFile;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key_pay_pal_client_id", type="string", length=255, nullable=true)
     */
    protected $apiKeyPayPalClientId;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key_pay_pal_client_secret", type="string", length=255, nullable=true)
     */
    protected $apiKeyPayPalClientSecret;

    /**
     * @var int
     *
     * @ORM\Column(name="is_pay_pal_sandbox", type="smallint", nullable=true, options={"default" : 0})
     */
    private $isPayPalSandbox;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key_facebook_client_id", type="string", length=255, nullable=true)
     */
    protected $apiKeyFacebookClientId;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key_facebook_client_secret", type="string", length=255, nullable=true)
     */
    protected $apiKeyFacebookClientSecret;

    /**
     * @var string
     *
     * @ORM\Column(name="api_turbosms_login", type="string", length=255, nullable=true)
     */
    protected $apiTurbosmsLogin;

    /**
     * @var string
     *
     * @ORM\Column(name="api_turbosms_password", type="string", length=255, nullable=true)
     */
    protected $apiTurbosmsPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="api_turbosms_signature", type="string", length=255, nullable=true)
     */
    protected $apiTurbosmsSignature;

    /**
     * @var string
     *
     * @ORM\Column(name="api_youtube_application_name", type="string", length=255, nullable=true)
     */
    protected $apiYoutubeApplicationName;

    /**
     * @var string
     *
     * @ORM\Column(name="api_youtube_client_secret", type="string", length=255, nullable=true)
     */
    protected $apiYoutubeClientSecret;

    /**
     * @var string
     *
     * @ORM\Column(name="api_youtube_client_id", type="string", length=255, nullable=true)
     */
    protected $apiYoutubeClientId;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key_platon_merchant_id", type="string", length=255, nullable=true)
     */
    protected $apiKeyPlatonMerchantId;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key_platon_merchant_password", type="string", length=255, nullable=true)
     */
    protected $apiKeyPlatonMerchantPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key_platon_merchant_url", type="string", length=255, nullable=true)
     */
    protected $apiKeyPlatonMerchantUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key_interkassa_merchant_id", type="string", length=255, nullable=true)
     */
    protected $apiKeyInterkassaMerchantId;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key_interkassa_secret_key", type="string", length=255, nullable=true)
     */
    protected $apiKeyInterkassaSecretKey;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key_interkassa_test_key", type="string", length=255, nullable=true)
     */
    protected $apiKeyInterkassaTestKey;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key_liq_pay_privat_key", type="string", length=255, nullable=true)
     */
    protected $apiKeyLiqPayPrivatKey;


    /**
     * @var string
     *
     * @ORM\Column(name="api_key_liq_pay_public_key", type="string", length=255, nullable=true)
     */
    protected $apiKeyLiqPayPublicKey;

    /**
     * @var string
     *
     * @ORM\Column(name="robots_txt", type="text", length=255, nullable=true)
     */
    protected $robotsTxt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSkype(): ?string
    {
        return $this->skype;
    }

    public function setSkype(?string $skype): self
    {
        $this->skype = $skype;

        return $this;
    }

    public function getGoogleAnalytics(): ?string
    {
        return $this->googleAnalytics;
    }

    public function setGoogleAnalytics(string $googleAnalytics): self
    {
        $this->googleAnalytics = $googleAnalytics;

        return $this;
    }

    public function getYandexMetrika(): ?string
    {
        return $this->yandexMetrika;
    }

    public function setYandexMetrika(string $yandexMetrika): self
    {
        $this->yandexMetrika = $yandexMetrika;

        return $this;
    }

    public function getFacebookPixel(): ?string
    {
        return $this->facebookPixel;
    }

    public function setFacebookPixel(string $facebookPixel): self
    {
        $this->facebookPixel = $facebookPixel;

        return $this;
    }

    public function getRobotsTxt(): ?string
    {
        return $this->robotsTxt;
    }

    public function setRobotsTxt(string $robotsTxt): self
    {
        $this->robotsTxt = $robotsTxt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords): self
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaCanonical(): ?string
    {
        return $this->metaCanonical;
    }

    public function setMetaCanonical(?string $metaCanonical): self
    {
        $this->metaCanonical = $metaCanonical;

        return $this;
    }

    public function getMetaImage(): ?Media
    {
        return $this->meta_image;
    }

    public function setMetaImage(?Media $meta_image): self
    {
        $this->meta_image = $meta_image;

        return $this;
    }

    public function getDiscountEmailMarketing(): ?float
    {
        return $this->discountEmailMarketing;
    }

    public function setDiscountEmailMarketing(?float $discountEmailMarketing): self
    {
        $this->discountEmailMarketing = $discountEmailMarketing;

        return $this;
    }

    public function getPaymentType(): ?int
    {
        return $this->paymentType;
    }

    public function setPaymentType(?int $paymentType): self
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    public function getInvitationPurchase(): ?string
    {
        return $this->invitationPurchase;
    }

    public function setInvitationPurchase(?string $invitationPurchase): self
    {
        $this->invitationPurchase = $invitationPurchase;

        return $this;
    }

    public function getDescriptionPurchase(): ?string
    {
        return $this->descriptionPurchase;
    }

    public function setDescriptionPurchase(?string $descriptionPurchase): self
    {
        $this->descriptionPurchase = $descriptionPurchase;

        return $this;
    }

    public function getApiKeyPayPalClientId(): ?string
    {
        return $this->apiKeyPayPalClientId;
    }

    public function setApiKeyPayPalClientId(?string $apiKeyPayPalClientId): self
    {
        $this->apiKeyPayPalClientId = $apiKeyPayPalClientId;

        return $this;
    }

    public function getApiKeyPayPalClientSecret(): ?string
    {
        return $this->apiKeyPayPalClientSecret;
    }

    public function setApiKeyPayPalClientSecret(?string $apiKeyPayPalClientSecret): self
    {
        $this->apiKeyPayPalClientSecret = $apiKeyPayPalClientSecret;

        return $this;
    }

    public function getIsPayPalSandbox(): ?bool
    {
      return $this->isPayPalSandbox;
    }

    public function setIsPayPalSandbox(?bool $isPayPalSandbox): self
    {
       $this->isPayPalSandbox = $isPayPalSandbox;

       return $this;
    }

	public function getApiTurbosmsLogin(): ?string
	{
		return $this->apiTurbosmsLogin;
	}
	
	public function setApiTurbosmsLogin( ?string $apiTurbosmsLogin ): self
	{
		$this->apiTurbosmsLogin = $apiTurbosmsLogin;
		
		return $this;
	}
	
	public function getApiTurbosmsPassword(): ?string
	{
		return $this->apiTurbosmsPassword;
	}
	
	public function setApiTurbosmsPassword( ?string $apiTurbosmsPassword ): self
	{
		$this->apiTurbosmsPassword = $apiTurbosmsPassword;
		
		return $this;
	}
	
	public function getApiGoogleClientSecretFile(): ?Media
	{
		return $this->apiGoogleClientSecretFile;
	}
	
	public function setApiGoogleClientSecretFile( ?Media $apiGoogleClientSecretFile ): self
	{
		$this->apiGoogleClientSecretFile = $apiGoogleClientSecretFile;
		
		return $this;
	}
	
	public function getApiKeyPlatonMerchantId(): ?string
	{
		return $this->apiKeyPlatonMerchantId;
	}
	
	public function setApiKeyPlatonMerchantId( ?string $apiKeyPlatonMerchantId ): self
	{
		$this->apiKeyPlatonMerchantId = $apiKeyPlatonMerchantId;
		
		return $this;
	}
	
	public function getApiKeyPlatonMerchantPassword(): ?string
	{
		return $this->apiKeyPlatonMerchantPassword;
	}
	
	public function setApiKeyPlatonMerchantPassword( ?string $apiKeyPlatonMerchantPassword ): self
	{
		$this->apiKeyPlatonMerchantPassword = $apiKeyPlatonMerchantPassword;
		
		return $this;
	}
	
	public function getApiKeyPlatonMerchantUrl(): ?string
	{
		return $this->apiKeyPlatonMerchantUrl;
	}
	
	public function setApiKeyPlatonMerchantUrl( ?string $apiKeyPlatonMerchantUrl ): self
	{
		$this->apiKeyPlatonMerchantUrl = $apiKeyPlatonMerchantUrl;
		
		return $this;
	}
	
	public function getSocialFbLink(): ?string
	{
		return $this->socialFbLink;
	}
	
	public function setSocialFbLink( ?string $socialFbLink ): self
	{
		$this->socialFbLink = $socialFbLink;
		
		return $this;
	}
	
	public function getSocialYtLink(): ?string
	{
		return $this->socialYtLink;
	}
	
	public function setSocialYtLink( ?string $socialYtLink ): self
	{
		$this->socialYtLink = $socialYtLink;
		
		return $this;
	}
	
	public function getSocialInLink(): ?string
	{
		return $this->socialInLink;
	}
	
	public function setSocialInLink( ?string $socialInLink ): self
	{
		$this->socialInLink = $socialInLink;
		
		return $this;
	}
	
	public function getApiTurbosmsSignature(): ?string
	{
		return $this->apiTurbosmsSignature;
	}
	
	public function setApiTurbosmsSignature( ?string $apiTurbosmsSignature ): self
	{
		$this->apiTurbosmsSignature = $apiTurbosmsSignature;
		
		return $this;
	}
	
	public function getApiKeyInterkassaMerchantId(): ?string
	{
		return $this->apiKeyInterkassaMerchantId;
	}
	
	public function setApiKeyInterkassaMerchantId( ?string $apiKeyInterkassaMerchantId ): self
	{
		$this->apiKeyInterkassaMerchantId = $apiKeyInterkassaMerchantId;
		
		return $this;
	}
	
	public function getApiKeyInterkassaSecretKey(): ?string
	{
		return $this->apiKeyInterkassaSecretKey;
	}
	
	public function setApiKeyInterkassaSecretKey( ?string $apiKeyInterkassaSecretKey ): self
	{
		$this->apiKeyInterkassaSecretKey = $apiKeyInterkassaSecretKey;
		
		return $this;
	}
	
	public function getApiKeyInterkassaTestKey(): ?string
	{
		return $this->apiKeyInterkassaTestKey;
	}
	
	public function setApiKeyInterkassaTestKey( ?string $apiKeyInterkassaTestKey ): self
	{
		$this->apiKeyInterkassaTestKey = $apiKeyInterkassaTestKey;
		
		return $this;
	}
	
	public function getApiYoutubeApplicationName(): ?string
	{
		return $this->apiYoutubeApplicationName;
	}
	
	public function setApiYoutubeApplicationName( ?string $apiYoutubeApplicationName ): self
	{
		$this->apiYoutubeApplicationName = $apiYoutubeApplicationName;
		
		return $this;
	}
	
	public function getApiYoutubeClientSecret(): ?string
	{
		return $this->apiYoutubeClientSecret;
	}
	
	public function setApiYoutubeClientSecret( ?string $apiYoutubeClientSecret ): self
	{
		$this->apiYoutubeClientSecret = $apiYoutubeClientSecret;
		
		return $this;
	}
	
	public function getApiYoutubeClientId(): ?string
	{
		return $this->apiYoutubeClientId;
	}
	
	public function setApiYoutubeClientId( ?string $apiYoutubeClientId ): self
	{
		$this->apiYoutubeClientId = $apiYoutubeClientId;
		
		return $this;
	}
	
	public function getApiKeyFacebookClientId(): ?string
	{
		return $this->apiKeyFacebookClientId;
	}
	
	public function setApiKeyFacebookClientId( ?string $apiKeyFacebookClientId ): self
	{
		$this->apiKeyFacebookClientId = $apiKeyFacebookClientId;
		
		return $this;
	}
	
	public function getApiKeyFacebookClientSecret(): ?string
	{
		return $this->apiKeyFacebookClientSecret;
	}
	
	public function setApiKeyFacebookClientSecret( ?string $apiKeyFacebookClientSecret ): self
	{
		$this->apiKeyFacebookClientSecret = $apiKeyFacebookClientSecret;
		
		return $this;
	}
	
	public function getLogoHeader(): ?Media
	{
		return $this->logoHeader;
	}
	
	public function setLogoHeader( ?Media $logoHeader ): self
	{
		$this->logoHeader = $logoHeader;
		
		return $this;
	}
	
	public function getLogoFooter(): ?Media
	{
		return $this->logoFooter;
	}
	
	public function setLogoFooter( ?Media $logoFooter ): self
	{
		$this->logoFooter = $logoFooter;
		
		return $this;
	}
	
	public function getLogoPreloader(): ?Media
	{
		return $this->logoPreloader;
	}
	
	public function setLogoPreloader( ?Media $logoPreloader ): self
	{
		$this->logoPreloader = $logoPreloader;
		
		return $this;
	}
	
	public function getApiKeyLiqPayPrivatKey(): ?string
	{
		return $this->apiKeyLiqPayPrivatKey;
	}
	
	public function setApiKeyLiqPayPrivatKey( ?string $apiKeyLiqPayPrivatKey ): self
	{
		$this->apiKeyLiqPayPrivatKey = $apiKeyLiqPayPrivatKey;
		
		return $this;
	}
	
	public function getApiKeyLiqPayPublicKey(): ?string
	{
		return $this->apiKeyLiqPayPublicKey;
	}
	
	public function setApiKeyLiqPayPublicKey( ?string $apiKeyLiqPayPublicKey ): self
	{
		$this->apiKeyLiqPayPublicKey = $apiKeyLiqPayPublicKey;
		
		return $this;
	}
}
