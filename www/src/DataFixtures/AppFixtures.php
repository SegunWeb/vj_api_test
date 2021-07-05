<?php

namespace App\DataFixtures;

use App\Constants\TypePageConstants;
use App\Entity\BlogCategories;
use App\Entity\BlogCategoriesTranslation;
use App\Entity\Country;
use App\Entity\CountryTranslation;
use App\Entity\Currency;
use App\Entity\FirstName;
use App\Entity\FirstNameTranslation;
use App\Entity\FooterMenu;
use App\Entity\FooterMenuTranslation;
use App\Entity\HeaderMenu;
use App\Entity\HeaderMenuTranslation;
use App\Entity\Holidays;
use App\Entity\HolidaysTranslation;
use App\Entity\Page;
use App\Entity\PageTranslation;
use App\Entity\PhrasesCategories;
use App\Entity\PhrasesCategoriesTranslation;
use App\Entity\Setting;
use App\Entity\VideoCategories;
use App\Entity\VideoCategoriesTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
	public function load( ObjectManager $manager )
	{
		
		$default = 'ru';
		
		$countryList = $this->getDataCountry();
		if ( ! empty( $countryList ) ) {
			foreach ( $countryList as $key => $country ) {
				$cn = new Country();
				$cn->setIsoCode( $key );
				$manager->persist( $cn );
				
				$cnTr = new CountryTranslation();
				$cnTr->setName( $country );
				$cnTr->setLocale( $default );
				$cnTr->setTranslatable( $cn );
				$manager->persist( $cnTr );
			}
		}
		
		$currencyList = $this->getDataCurrency();
		if ( ! empty( $currencyList ) ) {
			foreach ( $currencyList as $currency ) {
				$curr = new Currency();
				$curr->setName( $currency[0] );
				$curr->setAbbreviation( $currency[1] );
				$curr->setSing( $currency[2] );
				$curr->setCourse( $currency[3] );
				$curr->setDefaultCurrency( $currency[4] );
				$curr->setActive( 1 );
				$curr->setCodeISO( $currency[5] );
				$manager->persist( $curr );
			}
		}
		
		$categoryVideo = $this->getDataCategoryVideo();
		if ( ! empty( $categoryVideo ) ) {
			foreach ( $categoryVideo as $category ) {
				$cat = new VideoCategories();
				$cat->setTitle( $category );
				$cat->setActive( 1 );
				$manager->persist( $cat );
				
				$catTr = new VideoCategoriesTranslation();
				$catTr->setTitle( $category );
				$catTr->setLocale( $default );
				$catTr->setTranslatable( $cat );
				$manager->persist( $catTr );
			}
		}
		
		/*$firstName = $this->getDataFirstName();
		if ( ! empty( $firstName ) ) {
			foreach ( $firstName as $name ) {
				$first = new FirstName();
				$first->setSex( $name[1] );
				$first->setActive( 1 );
				$manager->persist( $first );
				
				$firstTr = new FirstNameTranslation();
				$firstTr->setTitle( $name[0] );
				$firstTr->setLocale( $default );
				$firstTr->setTranslatable( $first );
				$manager->persist( $firstTr );
			}
		}*/
		
		$categoryBlog = $this->getDataCategoryBlog();
		if ( ! empty( $categoryBlog ) ) {
			foreach ( $categoryBlog as $category ) {
				$cat = new BlogCategories();
				$cat->setTitle( $category );
				$cat->setActive( 1 );
				$manager->persist( $cat );
				
				$catTr = new BlogCategoriesTranslation();
				$catTr->setTitle( $category );
				$catTr->setLocale( $default );
				$catTr->setTranslatable( $cat );
				$manager->persist( $catTr );
			}
		}
		
		$categoryPhrase = $this->getDataCategoryPhrase();
		if ( ! empty( $categoryPhrase ) ) {
			foreach ( $categoryPhrase as $category ) {
				$cat = new PhrasesCategories();
				$cat->setTitle( $category );
				$cat->setActive( 1 );
				$manager->persist( $cat );
				
				$catTr = new PhrasesCategoriesTranslation();
				$catTr->setTitle( $category );
				$catTr->setLocale( $default );
				$catTr->setTranslatable( $cat );
				$manager->persist( $catTr );
			}
		}
		
		$holidaysList = $this->getDataHolidays();
		if ( ! empty( $holidaysList ) ) {
			foreach ( $holidaysList as $holidays ) {
				$hol = new Holidays();
				$manager->persist( $hol );
				
				$holTr = new HolidaysTranslation();
				$holTr->setTitle( $holidays );
				$holTr->setActive( 1 );
				$holTr->setLocale( $default );
				$holTr->setTranslatable( $hol );
				$manager->persist( $holTr );
				
			}
		}
		
		$pagesList = $this->getDataPage();
		if ( ! empty( $pagesList ) ) {
			foreach ( $pagesList as $pages ) {
				$page = new Page();
				$page->setType( $pages['type'] );
				$page->setTitleForSlug( $pages['name'] );
				$manager->persist( $page );
				
				$pageTr = new PageTranslation();
				$pageTr->setTitle( $pages['name'] );
				$pageTr->setActive( 1 );
				$pageTr->setLocale( $default );
				$pageTr->setTranslatable( $page );
				$manager->persist( $pageTr );
			}
		}
		
		$setting = new Setting();
		$setting->setCreatedAt( new \DateTime( 'now' ) );
		$setting->setUpdatedAt( new \DateTime( 'now' ) );
		$manager->persist( $setting );
		
		$manager->flush();
		
		
		$menuHead = $this->getDataMenuHeader();
		if ( ! empty( $menuHead ) ) {
			foreach ( $menuHead as $item ) {
				$menuHeader = new HeaderMenu();
				$menuHeader->setTitle( $item['name'] );
				$menuHeader->setActive( 1 );
				if ( $item['type'] == 'page' ) {
					$menuHeader->setTypeMenuItem( 1 );
					$page = $manager->getRepository( Page::class )->findOneBy( [ 'type' => $item['type_val'] ] );
					$menuHeader->setStaticPageId( $page );
				} elseif ( $item['type'] == 'cat' ) {
					$menuHeader->setTypeMenuItem( 2 );
					if ( $item['type_val'] == 0 ) {
						$menuHeader->setLink( 'children' );
					} else {
						$menuHeader->setLink( 'adults' );
					}
				} else {
					$menuHeader->setTypeMenuItem( 2 );
					$menuHeader->setLink( '/' );
				}
				
				$manager->persist( $menuHeader );
				
				$menuHeaderTr = new HeaderMenuTranslation();
				$menuHeaderTr->setTitle( $item['name'] );
				$menuHeaderTr->setLocale( $default );
				$menuHeaderTr->setTranslatable( $menuHeader );
				
				$manager->persist( $menuHeaderTr );
			}
		}
		
		$menuFoo = $this->getDataMenuFooter();
		if ( ! empty( $menuFoo ) ) {
			foreach ( $menuFoo as $item ) {
				$menuFooter = new FooterMenu();
				$menuFooter->setTitle( $item['name'] );
				$menuFooter->setActive( 1 );
				if ( $item['type'] == 'page' ) {
					$menuFooter->setTypeMenuItem( 1 );
					$page = $manager->getRepository( Page::class )->findOneBy( [ 'type' => $item['type_val'] ] );
					$menuFooter->setStaticPageId( $page );
				} elseif ( $item['type'] == 'cat' ) {
					$menuFooter->setTypeMenuItem( 2 );
					if ( $item['type_val'] == 0 ) {
						$menuFooter->setLink( 'children' );
					} else {
						$menuFooter->setLink( 'adults' );
					}
				} else {
					$menuFooter->setTypeMenuItem( 2 );
					$menuFooter->setLink( '/' );
				}
				
				$manager->persist( $menuFooter );
				
				$menuFooterTr = new FooterMenuTranslation();
				$menuFooterTr->setTitle( $item['name'] );
				$menuFooterTr->setLocale( $default );
				$menuFooterTr->setTranslatable( $menuFooter );
				
				$manager->persist( $menuFooterTr );
			}
		}
		$manager->flush();
		
	}
	
	public function getDataCategoryVideo()
	{
		
		return [
			'Детские ролики',
			'Взрослые ролики'
		];
		
	}
	
	public function getDataCategoryBlog()
	{
		
		return [
			'Рендеринг',
			'Объявления'
		];
		
	}
	
	public function getDataCategoryPhrase()
	{
		
		return [
			'Детские фразы',
			'Взрослые фразы'
		];
		
	}
	
	public function getDataFirstName()
	{
		
		return [
			[ 'Василий', 1 ],
			[ 'Петр', 1 ],
			[ 'Ирина', 2 ],
			[ 'Ника', 2 ]
		];
		
	}
	
	public function getDataCurrency()
	{
		
		return [
			[ 'Гривня', 'грн', '₴', '0.037', false, 'UAH' ],
			[ 'Рубль', 'руб', '₽', '0.015', false, 'RUB' ],
			[ 'Евро', 'евро', '€', '1.14', false, 'EUR' ],
			[ 'Доллар', 'долл', '$', '1', true, 'USD' ]
		];
		
	}
	
	public function getDataHolidays()
	{
		return [
			'Новый год',
			'День рождения'
		];
	}
	
	public function getDataCountry()
	{
		return [
			"AA" => "Aruba",
			"AC" => "Antigua and Barbuda",
			"AE" => "United Arab Emirates",
			"AF" => "Afghanistan",
			"AG" => "Algeria",
			"AJ" => "Azerbaijan",
			"AL" => "Albania",
			"AM" => "Armenia",
			"AN" => "Andorra",
			"AO" => "Angola",
			"AQ" => "American Samoa",
			"AR" => "Argentina",
			"AS" => "Australia",
			"AT" => "Ashmore and Cartier Islands",
			"AU" => "Austria",
			"AV" => "Anguilla",
			"AY" => "Antarctica",
			"BA" => "Bahrain",
			"BB" => "Barbados",
			"BC" => "Botswana",
			"BD" => "Bermuda",
			"BE" => "Belgium",
			"BF" => "Bahamas, The",
			"BG" => "Bangladesh",
			"BH" => "Belize",
			"BK" => "Bosnia and Herzegovina",
			"BL" => "Bolivia",
			"BM" => "Myanmar",
			"BN" => "Benin",
			"BO" => "Belarus",
			"BP" => "Solomon Islands",
			"BQ" => "Navassa Island",
			"BR" => "Brazil",
			"BS" => "Bassas da India",
			"BT" => "Bhutan",
			"BU" => "Bulgaria",
			"BV" => "Bouvet Island",
			"BX" => "Brunei",
			"BY" => "Burundi",
			"CA" => "Canada",
			"CB" => "Cambodia",
			"CD" => "Chad",
			"CE" => "Sri Lanka",
			"CF" => "Congo, Republic of the",
			"CG" => "Congo, Democratic Republic of the",
			"CH" => "China",
			"CI" => "Chile",
			"CJ" => "Cayman Islands",
			"CK" => "Cocos (Keeling) Islands",
			"CM" => "Cameroon",
			"CN" => "Comoros",
			"CO" => "Colombia",
			"CQ" => "Northern Mariana Islands",
			"CR" => "Coral Sea Islands",
			"CS" => "Costa Rica",
			"CT" => "Central African Republic",
			"CU" => "Cuba",
			"CV" => "Cape Verde",
			"CW" => "Cook Islands",
			"CY" => "Cyprus",
			"DA" => "Denmark",
			"DJ" => "Djibouti",
			"DO" => "Dominica",
			"DR" => "Dominican Republic",
			"DX" => "Dhekelia Sovereign Base Area",
			"EC" => "Ecuador",
			"EG" => "Egypt",
			"EI" => "Ireland",
			"EK" => "Equatorial Guinea",
			"EN" => "Estonia",
			"ER" => "Eritrea",
			"ES" => "El Salvador",
			"ET" => "Ethiopia",
			"EU" => "Europa Island",
			"EZ" => "Czech Republic",
			"FG" => "French Guiana",
			"FI" => "Finland",
			"FJ" => "Fiji",
			"FK" => "Falkland Islands",
			"FM" => "Micronesia, Federated States of",
			"FO" => "Faroe Islands",
			"FP" => "French Polynesia",
			"FR" => "France",
			"FS" => "French Southern and Antarctic Lands",
			"GA" => "Gambia, The",
			"GB" => "Gabon",
			"GG" => "Georgia",
			"GH" => "Ghana",
			"GI" => "Gibraltar",
			"GJ" => "Grenada",
			"GK" => "Guernsey",
			"GL" => "Greenland",
			"GM" => "Germany",
			"GO" => "Glorioso Islands",
			"GP" => "Guadeloupe",
			"GQ" => "Guam",
			"GR" => "Greece",
			"GT" => "Guatemala",
			"GV" => "Guinea",
			"GY" => "Guyana",
			"GZ" => "Gaza Strip",
			"HA" => "Haiti",
			"HK" => "Hong Kong",
			"HM" => "Heard Island and McDonald Islands",
			"HO" => "Honduras",
			"HR" => "Croatia",
			"HU" => "Hungary",
			"IC" => "Iceland",
			"ID" => "Indonesia",
			"IM" => "Isle of Man",
			"IN" => "India",
			"IO" => "British Indian Ocean Territory",
			"IP" => "Clipperton Island",
			"IR" => "Iran",
			"IS" => "Israel",
			"IT" => "Italy",
			"IV" => "Cote d'Ivoire",
			"IZ" => "Iraq",
			"JA" => "Japan",
			"JE" => "Jersey",
			"JM" => "Jamaica",
			"JN" => "Jan Mayen",
			"JO" => "Jordan",
			"JU" => "Juan de Nova Island",
			"KE" => "Kenya",
			"KG" => "Kyrgyzstan",
			"KN" => "Korea, North",
			"KR" => "Kiribati",
			"KS" => "Korea, South",
			"KT" => "Christmas Island",
			"KU" => "Kuwait",
			"KV" => "Kosovo",
			"KZ" => "Kazakhstan",
			"LA" => "Laos",
			"LE" => "Lebanon",
			"LG" => "Latvia",
			"LH" => "Lithuania",
			"LI" => "Liberia",
			"LO" => "Slovakia",
			"LS" => "Liechtenstein",
			"LT" => "Lesotho",
			"LU" => "Luxembourg",
			"LY" => "Libya",
			"MA" => "Madagascar",
			"MB" => "Martinique",
			"MC" => "Macau",
			"MD" => "Moldova, Republic of",
			"MF" => "Mayotte",
			"MG" => "Mongolia",
			"MH" => "Montserrat",
			"MI" => "Malawi",
			"MJ" => "Montenegro",
			"MK" => "The Former Yugoslav Republic of Macedonia",
			"ML" => "Mali",
			"MN" => "Monaco",
			"MO" => "Morocco",
			"MP" => "Mauritius",
			"MR" => "Mauritania",
			"MT" => "Malta",
			"MU" => "Oman",
			"MV" => "Maldives",
			"MX" => "Mexico",
			"MY" => "Malaysia",
			"MZ" => "Mozambique",
			"NC" => "New Caledonia",
			"NE" => "Niue",
			"NF" => "Norfolk Island",
			"NG" => "Niger",
			"NH" => "Vanuatu",
			"NI" => "Nigeria",
			"NL" => "Netherlands",
			"NM" => "No Man's Land",
			"NN" => "Sint Maarten",
			"NO" => "Norway",
			"NP" => "Nepal",
			"NR" => "Nauru",
			"NS" => "Suriname",
			"NU" => "Nicaragua",
			"NZ" => "New Zealand",
			"PA" => "Paraguay",
			"PC" => "Pitcairn Islands",
			"PE" => "Peru",
			"PF" => "Paracel Islands",
			"PG" => "Spratly Islands",
			"PK" => "Pakistan",
			"PL" => "Poland",
			"PM" => "Panama",
			"PO" => "Portugal",
			"PP" => "Papua New Guinea",
			"PS" => "Palau",
			"PU" => "Guinea-Bissau",
			"QA" => "Qatar",
			"RE" => "Reunion",
			"RI" => "Serbia",
			"RM" => "Marshall Islands",
			"RN" => "Saint Martin",
			"RO" => "Romania",
			"RP" => "Philippines",
			"RQ" => "Puerto Rico",
			"RU" => "Russia",
			"RW" => "Rwanda",
			"SA" => "Saudi Arabia",
			"SB" => "Saint Pierre and Miquelon",
			"SC" => "Saint Kitts and Nevis",
			"SE" => "Seychelles",
			"SF" => "South Africa",
			"SG" => "Senegal",
			"SH" => "Saint Helena",
			"SI" => "Slovenia",
			"SL" => "Sierra Leone",
			"SM" => "San Marino",
			"SN" => "Singapore",
			"SO" => "Somalia",
			"SP" => "Spain",
			"ST" => "Saint Lucia",
			"SU" => "Sudan",
			"SV" => "Svalbard",
			"SW" => "Sweden",
			"SX" => "South Georgia and the Islands",
			"SY" => "Syrian Arab Republic",
			"SZ" => "Switzerland",
			"TB" => "Saint Barthelemy",
			"TD" => "Trinidad and Tobago",
			"TE" => "Tromelin Island",
			"TH" => "Thailand",
			"TI" => "Tajikistan",
			"TK" => "Turks and Caicos Islands",
			"TL" => "Tokelau",
			"TN" => "Tonga",
			"TO" => "Togo",
			"TP" => "Sao Tome and Principe",
			"TS" => "Tunisia",
			"TT" => "East Timor",
			"TU" => "Turkey",
			"TV" => "Tuvalu",
			"TW" => "Taiwan",
			"TX" => "Turkmenistan",
			"TZ" => "Tanzania, United Republic of",
			"UC" => "Curacao",
			"UG" => "Uganda",
			"UK" => "United Kingdom",
			"UA" => "Ukraine",
			"US" => "United States",
			"UV" => "Burkina Faso",
			"UY" => "Uruguay",
			"UZ" => "Uzbekistan",
			"VC" => "Saint Vincent and the Grenadines",
			"VE" => "Venezuela",
			"VI" => "British Virgin Islands",
			"VM" => "Vietnam",
			"VQ" => "Virgin Islands (US)",
			"VT" => "Holy See (Vatican City)",
			"WA" => "Namibia",
			"WE" => "Palestine, State of",
			"WF" => "Wallis and Futuna",
			"WI" => "Western Sahara",
			"WS" => "Samoa",
			"WZ" => "Swaziland",
			"YI" => "Serbia and Montenegro",
			"YM" => "Yemen",
			"ZA" => "Zambia",
			"ZI" => "Zimbabwe"
		];
	}
	
	public function getDataMenuHeader()
	{
		return [
			[ "name" => 'Детям', "type" => 'cat', "type_val" => 0 ],
			[ "name" => 'Взрослым', "type" => 'cat', "type_val" => 1 ],
			[ "name" => 'Отзывы', "type" => 'page', 'type_val' => TypePageConstants::REVIEW_VALUES ],
			[ "name" => 'Блог', "type" => 'page', 'type_val' => TypePageConstants::BLOG_VALUES ]
		];
	}
	
	public function getDataMenuFooter()
	{
		return [
			[ "name" => 'Детям', "type" => 'cat', "type_val" => 0 ],
			[ "name" => 'Взрослым', "type" => 'cat', "type_val" => 1 ],
			[ "name" => 'Отзывы', "type" => 'page', 'type_val' => TypePageConstants::REVIEW_VALUES ],
			[ "name" => 'Блог', "type" => 'page', 'type_val' => TypePageConstants::BLOG_VALUES ],
			[ "name" => 'Помощь', "type" => 'page', 'type_val' => TypePageConstants::HELP_VALUES ],
			[ "name" => 'О нас', "type" => 'page', 'type_val' => TypePageConstants::ABOUT_VALUES ]
		];
	}
	
	public function getDataPage()
	{
		return [
			[ "name" => 'Главная', "type" => TypePageConstants::INDEX_VALUES ],
			[ "name" => 'Отзывы', "type" => TypePageConstants::REVIEW_VALUES ],
			[ "name" => 'Помощь', "type" => TypePageConstants::HELP_VALUES ],
			[ "name" => 'Все ролики', "type" => TypePageConstants::ALL_VIDEO_VALUES ],
			[ "name" => 'Страница благодарности', "type" => TypePageConstants::THANK_YOU_VALUES ],
			[ "name" => 'Пользовательское соглашение', "type" => TypePageConstants::USER_AGREEMENT_VALUES ],
            [ "name" => 'Кабинет пользователя', "type" => TypePageConstants::USER_ACCOUNT_VALUES ],
            [ "name" => 'О нас', "type" => TypePageConstants::ABOUT_VALUES ],
            [ "name" => 'Страница 404', "type" => TypePageConstants::PAGE_NOT_FOUND_VALUES ],
            [ "name" => 'Блог', "type" => TypePageConstants::BLOG_VALUES ],
            [ "name" => 'Видео', "type" => TypePageConstants::VIDEO_VALUES ],
			[ "name" => 'Возврат средств', "type" => TypePageConstants::REFUND_VALUES ]
		];
	}
}
