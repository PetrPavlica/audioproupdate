<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="menu")
 */
class WebMenu extends ABaseEntity {

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
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Název'
     * FORM required="Název je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     *
     * GRID type='text'
     * GRID title="Slug"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $slug;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Titulek"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Titulek'
     *
     * GRID type='text'
     * GRID title="Titulek"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $title;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Keywords"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Keywords'
     *
     * GRID type='text'
     * GRID title="Keywords"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $keywords;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Description"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Description'
     *
     * GRID type='text'
     * GRID title="Description"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $description;

    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Pořadí (priorita)"
     * FORM attribute-placeholder='Pořadí'
     * FORM required="Toto je je povinné pole!"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Pořadí"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $orderPage;

    /**
     * @ORM\ManyToOne(targetEntity="WebTemplate", inversedBy="menu")
     * FORM type='select'
     * FORM title='Šablona stránky'
     * FORM prompt='-- vyberte šablonu stránky'
     * FORM data-entity=Intra\Model\Database\Entity\WebTemplate[name]
     * FORM attribute-class="form-control"
     * FORM required="Toto je je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Šablona"
     * GRID sortable='true'
     * GRID entity-link='name'
     * GRID visible='false'
     */
    protected $template;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Aktivní (zobrazeno v eshopu)"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Aktivní"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $visible;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Zobrazit v menu"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Zobrazit v menu"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $showInMenu;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='select'
     * FORM title="Zobrazeno v menu"
     * FORM prompt='-- vyberte'
     * FORM data-own=['footer' > 'Patička']
     * FORM attribute-class="form-control toggle-check"
     *
     * GRID type='translate-text'
     * GRID title="Zobrazeno v menu"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'footer' > 'Patička']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $inMenu;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Stránka informace pro cookies"
     *
     * GRID type='bool'
     * GRID title="Stránka cookies"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $forCookies;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Stránka pro Obchodní podmínky"
     *
     * GRID type='bool'
     * GRID title="Stránka obchodní podmínky"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $forTerms;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Stránka pro Zásady ochrany osobních údajů"
     *
     * GRID type='bool'
     * GRID title="Zásady ochrany osobních údajů"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $forPrinciples;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Stránka - K stažení v sekci zákazníka"
     *
     * GRID type='bool'
     * GRID title="K stažení (sekce zákazník)"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $forDownload;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     *
     * GRID type='datetime'
     * GRID title="Datum založení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $created;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     *
     * GRID type='datetime'
     * GRID title="Datum založení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $updated;

    /**
     * @ORM\ManyToOne(targetEntity="WebMenu", inversedBy="childMenu")
     * FORM type='select'
     * FORM title='Rodičovské menu'
     * FORM prompt='-- zvolte menu'
     * FORM data-entity=Intra\Model\Database\Entity\WebMenu[name]
     * FORM attribute-class="form-control selectpicker"
     * FORM attribute-data-live-search="true"
     *
     * GRID type='text'
     * GRID title="Rodičovské menu"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\WebMenu'
     * GRID entity-alias='wm'
     * GRID filter=single-entity #['name']
     */
    protected $parentMenu;

    /**
     * @ORM\OneToMany(targetEntity="WebMenu", mappedBy="parentMenu")
     */
    protected $childMenu;

    /**
     * @ORM\OneToMany(targetEntity="WebArticlesInMenu", mappedBy="menu")
     */
    protected $articles;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $image;

    public function __construct($data = null) {
        $this->visible = TRUE;
        $this->created = new DateTime();
        $this->updated = new DateTime();
        parent::__construct($data);
    }

}

?>