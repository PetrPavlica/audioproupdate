<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Core\Model\Database\Utils\ABaseEntity;

/**
 * @ORM\Entity
 */
class SaleBanner extends ABaseEntity
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
     * FORM attribute-placeholder='Název'
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
     * @ORM\ManyToOne(targetEntity="Product")
     * FORM type='autocomplete'
     * FORM title='Produkt'
     * FORM attribute-placeholder='Vyhledejte produkt'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="3"
     * FORM attribute-class="form-control"
     * FORM autocomplete-entity='Intra\Model\Database\Entity\Product'
     *
     * GRID type='text'
     * GRID title="Produkt"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\Product'
     * GRID entity-alias='prod'
     * GRID filter=single-entity #['name']
     */
    protected $product;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Odkaz"
     * FORM attribute-placeholder='Odkaz'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Odkaz"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $link;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $image;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Pořadí (priorita)"
     * FORM attribute-placeholder='Pořadí'
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Pořadí"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $orderBanner;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='select'
     * FORM title="Pozice"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Pozice'
     * FORM data-own=['left' > 'Vlevo' | 'right-top' > 'Vpravo - nahoře' | 'right-bottom' > 'Vpravo - dole']
     *
     * GRID type='translate-text'
     * GRID title="Pozice"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     * GRID filter=select #['' > 'Vše'|'left' > 'Vlevo' | 'right-top' > 'Vpravo - nahoře' | 'right-bottom' > 'Vpravo - dole']
     * GRID replacement=#['left' > 'Vlevo' | 'right-top' > 'Vpravo - nahoře' | 'right-bottom' > 'Vpravo - dole']
     */
    protected $position;

    /**
     * @ORM\Column(type="boolean")
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
     * @ORM\OneToMany(targetEntity="SaleBannerInLanguage", mappedBy="banner")
     * FORM type='multiselect'
     * FORM title='Zobrazit na'
     * FORM attribute-size='30'
     * FORM attribute-multiselect='true'
     * FORM data-entity=Intra\Model\Database\Entity\Language[name]
     * FORM multiselect-entity=Intra\Model\Database\Entity\SaleBannerInLanguage[banner][language]
     * FORM attribute-class="form-control selectpicker"
     */
    protected $languages;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}