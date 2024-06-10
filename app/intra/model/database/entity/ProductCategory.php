<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity
 */
class ProductCategory extends ABaseEntity
{

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
     * FORM title="Název"
     * FORM attribute-placeholder='Název kategorie'
     * FORM required='Název je povinné pole.'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID align='center'
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="ProductCategory", inversedBy="childCategory")
     * FORM type='select'
     * FORM title='Rodičovská kategorie'
     * FORM prompt='-- zvolte kategorii'
     * FORM data-entity=Intra\Model\Database\Entity\ProductCategory[name]
     * FORM attribute-class="form-control selectpicker"
     * FORM attribute-data-live-search="true"
     *
     * GRID type='text'
     * GRID title="Rodičovská kategorie"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\ProductCategory'
     * GRID entity-alias='prct'
     * GRID filter=single-entity #['name']
     */
    protected $parentCategory;

    /**
     * @ORM\OneToMany(targetEntity="ProductCategory", mappedBy="parentCategory")
     */
    protected $childCategory;

    /**
     * @ORM\ManyToOne(targetEntity="ProductCategory", inversedBy="id")
     * FORM type='hidden'
     * FORM data-entity=Intra\Model\Database\Entity\ProductCategory[name]
     */
    protected $mainCategory;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Pořadí (priorita)"
     * FORM attribute-placeholder='Pořadí'
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Pořadí (priorita)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $orderCategory;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Aktivní (zobrazeno v eshopu)"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Aktivní / viditelný"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $active;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Popis"
     * FORM attribute-placeholder='Doplňující popis'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Popis"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID align='left'
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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $image;

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
     * @ORM\ManyToOne(targetEntity="WebMenu", inversedBy="id")
     * FORM type='select'
     * FORM title='Článek zobrazený místo produtků'
     * FORM prompt='-- vyberte šablonu stránky'
     * FORM data-entity=Intra\Model\Database\Entity\WebMenu[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Článek"
     * GRID sortable='true'
     * GRID entity-link='name'
     * GRID visible='false'
     */
    protected $webMenu;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $categoryZbozi;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $categoryHeureka;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $categoryGoogleMerchants;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="URL adresa"
     * FORM attribute-placeholder='URL adresa'
     * FORM required='URL adresa je povinné pole.'
     * FORM attribute-class='form-control input-md'
     */
    protected $url;

    /**
     * @ORM\OneToMany(targetEntity="ProductInCategory", mappedBy="category")
     */
    protected $products;

    /**
     * @ORM\ManyToOne(targetEntity="MallCategory")
     * FORM type='autocomplete'
     * FORM title='Kategorie pro Mall'
     * FORM attribute-placeholder='Vyhledejte kategorii'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="3"
     * FORM attribute-class="form-control"
     * FORM autocomplete-entity='Intra\Model\Database\Entity\MallCategory'
     *
     * GRID type='text'
     * GRID title="Kategorie pro Mall"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\MallCategory'
     * GRID entity-alias='mall'
     * GRID filter=single-entity #['name']
     */
    protected $mallCategory;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}