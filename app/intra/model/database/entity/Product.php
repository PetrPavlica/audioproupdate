<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={@ORM\Index(name="code_idx", columns={"code", "ean"})})
 */
class Product extends ABaseEntity
{
    const HOME = 1;
    const BUSINESS = 2;

    const TYPES = [
        'home' => self::HOME,
        'business' => self::BUSINESS
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * FORM type="hidden"
     *
     * GRID type='number'
     * GRID title="Id"
     * GRID sortable='true'
     * GRID visible='true'
     * GRID align='left'
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Kód/čárový kód"
     * FORM attribute-placeholder='Kód produktu'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Kód"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $code;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="PLU"
     * FORM attribute-placeholder='PLU'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="PLU"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $plu;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="EAN"
     * FORM attribute-placeholder='EAN'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="EAN"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $ean;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-placeholder='Název produktu'
     * FORM required='Název je povinné pole!'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    protected $slug;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Jednotka"
     * FORM attribute-placeholder='(ks, metry, role...)'
     * FORM required='Jednotka je povinné pole!'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Jednotka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $unit;

    /**
     * @ORM\ManyToOne(targetEntity="ProductCategory", inversedBy="id")
     * FORM type='select'
     * FORM title='Kategorie produktu'
     * FORM prompt='-- vyberte kategorii'
     * FORM data-entity=Intra\Model\Database\Entity\ProductCategory[name]
     * FORM attribute-class="form-control selectpicker"
     * FORM attribute-data-live-search="true"
     *
     * GRID type='translate-text'
     * GRID title="Kategorie"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\ProductCategory'
     * GRID entity-alias='pcat'
     * GRID filter=select-entity #[name]['name' > 'ASC']
     */
    //protected $category;

    /**
     * @ORM\ManyToOne(targetEntity="ProductMark", inversedBy="id")
     * FORM type='select'
     * FORM title='Značka produktu'
     * FORM prompt='-- vyberte značku'
     * FORM data-entity=Intra\Model\Database\Entity\ProductMark[publicName]
     * FORM attribute-class="form-control selectpicker"
     * FORM attribute-data-live-search="true"
     *
     * GRID type='text'
     * GRID title="Značka"
     * GRID entity-link='publicName'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\ProductMark'
     * GRID entity-alias='pmark'
     * GRID filter=single-entity #['publicName']
     */
    protected $productMark;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORsM type='textarea'
     * FORsM title="Krátký popis (zobrazení na úvodu nebo v slideru)"
     * FORsM attribute-placeholder='krátký popis (zobrazuje se v slideru nebo na úvodu)'
     * FORsM attribute-class='form-control input-md'
     *
     * GRIsD type='text'
     * GRIsD title="Popis"
     * GRIsD sortable='true'
     * GRIsD filter='single'
     * GRIsD visible='false'
     */
    protected $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Seo (meta) titulek"
     * FORM attribute-placeholder='Seo titulek'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Seo (meta) titulek"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $seoTitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Seo (meta) popis"
     * FORM attribute-placeholder='Seo popis'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Seo (meta) popis"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $seoDescription;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Prodejní cena"
     * FORM attribute-placeholder='Prodejní cena včetně DPH'
     * FORM required='Prodejní cena je povinné pole!'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Prodejní cena"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $selingPrice;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Cena před slevou"
     * FORM attribute-placeholder='Cena před slevou včetně DPH'
     * FORM required='0'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Cena před slevou"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $lastPrice;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='text'
     * FORM title="Počet kusů na skladu"
     * FORM attribute-placeholder='0'
     * FORM disabled="true"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Počet kusů na skladu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $count;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='text'
     * FORM title="Počet kusů na skladu (rezervovaných)"
     * FORM attribute-placeholder='0'
     * FORM disabled="true"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Počet kusů na skladu (rezervovaných)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $rezerveCount;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Minimální zásoba"
     * FORM attribute-placeholder='Minimální zásoba'
     * FORM required='0'
     * FORM rule-integer="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Minimální zásoba"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $minCount;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Maximální zásoba"
     * FORM attribute-placeholder='Maximální zásoba'
     * FORM required='0'
     * FORM rule-integer="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Maximální zásoba"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $maxCount;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Poslední pořizovací cena"
     * FORM attribute-placeholder='Poslední pořizovací cena'
     * FORM disabled=true
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Poslední pořizovací cena"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $lastPurchasePrice;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Průměrná pořizovací cena"
     * FORM attribute-placeholder='Průměrná pořizovací cena'
     * FORM disabled=true
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Průměrná pořizovací cena"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $avaragePurchasePrice;

    /**
     * @ORM\ManyToOne(targetEntity="Vat", inversedBy="id")
     * FORM type='select'
     * FORM title='Sazba DPH '
     * FORM prompt='-- vyberte DPH'
     * FORM required='Sazba DPH je povinné pole'
     * FORM data-entity=Intra\Model\Database\Entity\Vat[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Sazba DPH"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\Vat'
     * GRID entity-alias='vatt'
     * GRID filter=single-entity #['name']
     */
    protected $vat;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Typ"
     * FORM prompt='-- vyberte'
     * FORM data-own=['1' > 'Home'|'2' > 'Business']
     * FORM attribute-class="form-control"
     *
     * GRID type='translate-text'
     * GRID title="Typ"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'1' > 'Home'|'2' > 'Business']
     * GRID visible='true'
     * GRID align='center'
     * GRID replacement=#['1' > 'Home'|'2' > 'Business']
     */
    protected $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Text pro eshop kratší"
     *
     * GRID type='text'
     * GRID title="Text kratší pro eshop"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $shortWebDescription;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Text pro eshop:"
     *
     * GRID type='text'
     * GRID title="Text pro eshop"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $webDescription;

    /**
     * @ORM\Column(type="boolean", options={"default" : 1})
     * FORM type='checkbox'
     * FORM title=" Aktivní (zobrazen v eshopu)"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Aktivní"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $active;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     * FORM type='checkbox'
     * FORM title="Prodej ukončen?"
     *
     * GRID type='bool'
     * GRID title="Prodej ukončen?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $saleTerminated;

    /**
     * @ORM\Column(type="boolean", options={"default" : 1})
     * FORM type='checkbox'
     * FORM title="Nezasílat do feedů"
     *
     * GRID type='bool'
     * GRID title="Nezasílat do feedů"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $notInFeeds;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : 0})
     * FORsM type='checkbox'
     * FORsM title="Doporučujeme - zobrazeno na úvodní straně"
     *
     * GRIsD type='bool'
     * GRIsD title="Doporučujeme"
     * GRIsD sortable='true'
     * GRIsD filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRIsD visible='false'
     * GRIsD align='center'
     */
    protected $isRecomanded;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORsM type='integer'
     * FORsM title="Pořadí v doporučeno (priorita)"
     * FORsM attribute-placeholder='Pořadí v doporučeno'
     * FORsM rule-integer='Prosím zadávejte pouze čísla'
     * FORsM attribute-class='form-control input-md'
     *
     * GRIsD type='integer'
     * GRIsD title="Pořadí v dopořučeno"
     * GRIsD sortable='true'
     * GRIsD filter='single'
     * GRIsD visible='false'
     */
    protected $orderIdRecomanded;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : 0})
     * FORsM type='checkbox'
     * FORsM title="Je produkt na úvodní stránce? (max. 4 úvodní produkty)"
     *
     * GRIsD type='bool'
     * GRIsD title="Zobrazen na úvodu"
     * GRIsD sortable='true'
     * GRIsD filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRIsD visible='false'
     * GRIsD align='center'
     */
    protected $onFront;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORsM type='integer'
     * FORsM title="Pořadí na úvodu"
     * FORsM attribute-placeholder='Pořadí na úvodu (priorita)'
     * FORsM rule-integer='Prosím zadávejte pouze čísla'
     * FORsM attribute-class='form-control input-md'
     *
     * GRIsD type='integer'
     * GRIsD title="Pořadí na úvodu"
     * GRIsD sortable='true'
     * GRIsD filter='single'
     * GRIsD visible='false'
     */
    protected $orderOnFront;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='text'
     * FORM title="Počet reálně prodaných kusů"
     * FORM attribute-placeholder='Počet reálně prodaných kusů'
     * FORM attribute-class='form-control input-md'
     * FORM disabled="true"
     *
     * GRID type='integer'
     * GRID title="Počet reálně prodaných kusů"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $countOfSell;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : 0})
     * FORsM type='checkbox'
     * FORsM title="Řídit filtr a řazení tagu Nejprodávanější"f
     *
     * GRIsD type='bool'
     * GRIsD title="Vlastní řízení tagu Nejprodávanější"
     * GRIsD sortable='true'
     * GRIsD filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRIsD visible='false'
     * GRIsD align='center'
     */
    protected $ourSortingMostSold;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORsM type='integer'
     * FORsM title="Počet prodaných kusů pro filtr"
     * FORsM attribute-placeholder='Počet kusů'
     * FORsM rule-integer='Prosím zadávejte pouze čísla'
     * FORsM attribute-class='form-control input-md'
     *
     * GRIsD type='integer'
     * GRIsD title="Počet prodaných pro Nejprodávanější"
     * GRIsD sortable='true'
     * GRIsD filter='single'
     * GRIsD visible='false'
     */
    protected $countOfSellForSearch;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="id")
     * FORsM type='autocomplete'
     * FORsM title='Nový produkt k zánovnímu'
     * FORsM attribute-placeholder='Vyhledejte produkt'
     * FORsM attribute-data-preload="false"
     * FORsM attribute-data-suggest="true"
     * FORsM attribute-data-minlen="3"
     * FORsM attribute-class="form-control"
     * FORsM autocomplete-entity='Intra\Model\Database\Entity\Product'
     */
    protected $newichProductRef;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="id")
     * FORM type='autocomplete'
     * FORM title='Lepší produkt než tento'
     * FORM attribute-placeholder='Vyhledejte produkt'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="3"
     * FORM attribute-class="form-control"
     * FORM autocomplete-entity='Intra\Model\Database\Entity\Product'
     */
    protected $betterProduct;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Výhody lepšího produktu (každý řádek jedna výhoda)"
     * FORM attribute-placeholder='Výhody lepšího produktu (každý řádek jedna výhoda)'
     * FORM attribute-class='form-control input-md'
     */
    protected $betterProductAdvantages;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     * FORM type='checkbox'
     * FORM title="Novinka (tag pro vyhledávání)"
     *
     * GRID type='bool'
     * GRID title="Novinka"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $isNews;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     * FORM type='checkbox'
     * FORM title="Top (tag pro vyhledávání)"
     *
     * GRID type='bool'
     * GRID title="Top"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $isTop;

    /**
     * @ORM\OneToMany(targetEntity="ProductInFilter", mappedBy="product")
     * FORM type='multiselect'
     * FORM title='Vyberte filtry produktu'
     * FORM attribute-size='30'
     * FORM data-entity=Intra\Model\Database\Entity\ProductFilter[name]
     * FORM multiselect-entity=Intra\Model\Database\Entity\ProductInFilter[product][filter]
     * FORM attribute-class="form-control"
     */
    protected $filter;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Hodnocení průměr"
     * FORM disabled='true'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Hodnocení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $totalRating;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='text'
     * FORM title="Počet hodnocení"
     * FORM disabled='true'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Počet hodnocení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $sumRating;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     * FORM type='checkbox'
     * FORM title="Audio Pro Multiroom (štítek)"
     *
     * GRID type='bool'
     * GRID title="Audio Pro Multiroom (štítek)"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $audioProMultiroom;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     * FORM type='checkbox'
     * FORM title="Battery Powered (štítek)"
     *
     * GRID type='bool'
     * GRID title="Battery Powered (štítek)"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $batteryPowered;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     * FORM type='checkbox'
     * FORM title="Chromecast (štítek)"
     *
     * GRID type='bool'
     * GRID title="Chromecast (štítek)"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $chromecast;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     * FORM type='checkbox'
     * FORM title="IPX4 (štítek)"
     *
     * GRID type='bool'
     * GRID title="IPX4 (štítek)"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $ipx4;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     * FORM type='checkbox'
     * FORM title="BT 4.0 (štítek)"
     *
     * GRID type='bool'
     * GRID title="BT 4.0 (štítek)"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $bt4;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     * FORM type='checkbox'
     * FORM title="BT 5.0 (štítek)"
     *
     * GRID type='bool'
     * GRID title="BT 5.0 (štítek)"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $bt5;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     * FORM type='checkbox'
     * FORM title="Hey Google (štítek)"
     *
     * GRID type='bool'
     * GRID title="Hey Google (štítek)"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $heyGoogle;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     * FORM type='checkbox'
     * FORM title="Air Play (štítek)"
     *
     * GRID type='bool'
     * GRID title="Air Play (štítek)"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $airPlay;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     * FORM type='checkbox'
     * FORM title="Náš tip (štítek)"
     *
     * GRID type='bool'
     * GRID title="Náš tip (štítek)"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $ourTip;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Heureka CPC"
     * FORM attribute-placeholder='Heureka CPC'
     * FORM required='0'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM rule-min="Zadaná hodnota musí být větší než 0" #[0]
     * FORM rule-max="Zadaná hodnota nesmí být větší než 100" #[100]
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Prodejní cena"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $heurekaCPC;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Zboží.cz CPC - max. cena za proklik v detailu produktu"
     * FORM attribute-placeholder='Zboží.cz CPC'
     * FORM required='0'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM rule-min="Zadaná hodnota musí být větší než 0" #[0]
     * FORM rule-max="Zadaná hodnota nesmí být větší než 100" #[100]
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Zboží.cz CPC - detail"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $zboziCPC;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Zboží.cz CPC - max. cena za proklik v nabídkách"
     * FORM attribute-placeholder='Zboží.cz CPC'
     * FORM required='0'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM rule-min="Zadaná hodnota musí být větší než 0" #[0]
     * FORM rule-max="Zadaná hodnota nesmí být větší než 100" #[100]
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Zboží.cz CPC - v nabídkách"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $zboziSearchCPC;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : 0})
     * FORsM type='checkbox'
     * FORsM title="Instalace a nastavení produtku?"
     *
     * GRIsD type='bool'
     * GRIsD title="Instalace a nastavení produtku?"
     * GRIsD sortable='true'
     * GRIsD filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRIsD visible='false'
     * GRIsD align='center'
     */
    protected $installSettingAdd;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORsM type='text'
     * FORsM title="Cena za instalaci a nast."
     * FORsM attribute-placeholder='Cena za instalaci a nastavení'
     * FORsM required='0'
     * FORsM rule-float="Zadaná hodnota musí být číslo!"
     * FORsM attribute-class='form-control input-md'
     *
     * GRIsD type='float'
     * GRIsD title="Cena za instalaci a nast."
     * GRIsD sortable='true'
     * GRIsD filter='single'
     * GRIsD visible='false'
     */
    protected $priceInstallSettingAdd;

    /**
     * @ORM\ManyToOne(targetEntity="ProductAction", inversedBy="id")
     */
    protected $activeAction;

    /**
     * @ORM\OneToMany(targetEntity="ProductAccessories", mappedBy="product")
     */
    protected $accessoriesProducts;

    /**
     * @ORM\OneToMany(targetEntity="ProductAlternatives", mappedBy="product")
     */
    protected $alternativesProducts;

    /**
     * @ORM\OneToMany(targetEntity="ProductAction", mappedBy="product")
     */
    protected $actions;

    /**
     * @ORM\OneToMany(targetEntity="ProductImage", mappedBy="product")
     * @ORM\OrderBy({"isMain" = "DESC", "orderImg" = "ASC"})
     */
    protected $images;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $mainImage;

    /**
     * @ORM\OneToMany(targetEntity="ProductParameter", mappedBy="product")
     */
    protected $parameters;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : 0})
     * FORM type='checkbox'
     * FORM title="Sada produktů (jedná se o sadu)"
     *
     * GRID type='bool'
     * GRID title="Sada produktů"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $isSet;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Text skladu"
     * FORM attribute-placeholder='Zobrazí se místo dostupnosti'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Text skladu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $stockText;

    /**
     * @ORM\OneToMany(targetEntity="ProductSetItems", mappedBy="product")
     */
    protected $setProducts;

    /**
     * @ORM\OneToMany(targetEntity="ProductPackage", mappedBy="product")
     */
    protected $packages;

    /**
     * @ORM\OneToMany(targetEntity="ProductInCategory", mappedBy="product")
     */
    protected $categories;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Barva"
     * FORM attribute-class='form-control input-md spectrum'
     */
    protected $color;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Barva (text)"
     * FORM attribute-class='form-control input-md'
     */
    protected $colorText;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    protected $isColorVariant = 0;

    /**
     * @ORM\OneToMany(targetEntity="ProductColorItems", mappedBy="product")
     */
    protected $colorProducts;

    /**
     * @ORM\OneToMany(targetEntity="ProductGifts", mappedBy="product")
     * @ORM\OrderBy({"rank" = "ASC"})
     */
    protected $gifts;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Informace o produktu"
     *
     * GRID type='text'
     * GRID title="Informace o produktu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $specInfo;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Rozměry"
     *
     * GRID type='text'
     * GRID title="Rozměry"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $specDimensions;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Komponenty"
     *
     * GRID type='text'
     * GRID title="Komponenty"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $specComponents;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Vstupy"
     *
     * GRID type='text'
     * GRID title="Vstupy"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $specInputs;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Výstupy"
     *
     * GRID type='text'
     * GRID title="Výstupy"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $specOutputs;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Frekvence"
     *
     * GRID type='text'
     * GRID title="Frekvence"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $specFrequency;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Přehrávané formáty"
     *
     * GRID type='text'
     * GRID title="Přehrávané formáty"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $specFormats;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Napájení"
     *
     * GRID type='text'
     * GRID title="Napájení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $specPowerSupply;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Multiroom"
     *
     * GRID type='text'
     * GRID title="Multiroom"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $specMultiroom;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Ostatní"
     *
     * GRID type='text'
     * GRID title="Ostatní"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $specOther;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Manuál"
     *
     * GRID type='text'
     * GRID title="Manuál"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $specManual;

    /**
     * @ORM\OneToMany(targetEntity="ProductArticle", mappedBy="product")
     * @ORM\OrderBy({"orderArticle" = "ASC"})
     */
    protected $articles;

    public function __construct($data = null)
    {
        $this->active = true;
        $this->saleTerminated = false;
        $this->onFront = false;
        $this->isSet = false;
        $this->isColorVariant = false;
        $this->isRecomanded = false;
        $this->isTop = false;
        $this->isNews = false;
        $this->notInFeeds = true;
        $this->ourSortingMostSold = false;
        $this->installSettingAdd = false;
        $this->audioProMultiroom = false;
        $this->batteryPowered = false;
        $this->chromecast = false;
        $this->ipx4 = false;
        $this->bt4 = false;
        $this->bt5 = false;
        $this->heyGoogle = false;
        $this->airPlay = false;
        $this->ourTip = false;
        parent::__construct($data);
    }

}