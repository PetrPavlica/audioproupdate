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
class DeliveryMethod extends ABaseEntity
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
     * FORM title="Název "
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
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Heureka kód dopravy"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Heureka kód'
     *
     * GRID type='text'
     * GRID title="Heureka kód"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $heurekaCode;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Info poznámka pod nadpisem"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Poznámka pod nadpisem'
     *
     * GRID type='text'
     * GRID title="Poznámka pod nadpisem"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $info;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Info poznámka v bublině"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Poznámka bublina'
     *
     * GRID type='text'
     * GRID title="Poznámka bublina"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $bubbleInfo;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Cena"
     * FORM attribute-placeholder='Cena včetně DPH'
     * FORM required='Cena je povinné pole!'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Cena"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $selingPrice;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Doba dodání (dny)"
     * FORM attribute-placeholder='Doba dodání (dny)'
     * FORM required='0'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Doba dodání (dny)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $days;

    /**
     * @ORM\ManyToOne(targetEntity="Vat", inversedBy="id")
     * FORM type='select'
     * FORM title='Vyberte sazbu DPH '
     * FORM prompt='-- vyberte DPH'
     * FORM required='Sazba DPH je povinné pole'
     * FORM data-entity=Intra\Model\Database\Entity\Vat[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Sazba DPH"
     * GRID sortable='true'
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\Vat'
     * GRID entity-alias='vatt'
     * GRID filter=single-entity #['name']
     */
    protected $vat;

    /**
     * @ORM\ManyToOne(targetEntity="DeliverySection", inversedBy="id")
     * FORM type='select'
     * FORM title='Vyberte zařazení '
     * FORM prompt='-- vyberte zařazení'
     * FORM required='Zařazení je povinné pole'
     * FORM data-entity=Intra\Model\Database\Entity\DeliverySection[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Zařazení"
     * GRID sortable='true'
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\DeliverySection'
     * GRID entity-alias='section'
     * GRID filter=single-entity #['name']
     */
    protected $section;

    /**
     * @ORM\ManyToOne(targetEntity="BalikobotTypeDelivery", inversedBy="id")
     * FORM type='select'
     * FORM title='Balíkobot - typ dopravy'
     * FORM prompt='-- vyberte typ'
     * FORM data-entity-values=Intra\Model\Database\Entity\BalikobotTypeDelivery[$name$]['active' > 1][]
     * FORM attribute-class="form-control selectpicker"
     * FORM attribute-data-live-search="true"
     *
     * GRID type='text'
     * GRID title="Balíkobot - typ dopravy"
     * GRID sortable='true'
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\BalikobotTypeDelivery'
     * GRID entity-alias='balikobt'
     * GRID filter=single-entity #['name']
     */
    protected $balikobotDelivery;

    /**
     * @ORM\ManyToOne(targetEntity="HeurekaCartDeliveryTypes")
     * FORM type='select'
     * FORM title='Heuréka - typ dopravy'
     * FORM prompt='-- vyberte typ'
     * FORM data-entity-values=Intra\Model\Database\Entity\HeurekaCartDeliveryTypes[$name$][][]
     * FORM attribute-class="form-control selectpicker"
     * FORM attribute-data-live-search="true"
     *
     * GRID type='text'
     * GRID title="Heuréka - typ dopravy"
     * GRID sortable='true'
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\HeurekaCartDeliveryTypes'
     * GRID entity-alias='heurekat'
     * GRID filter=single-entity #['name']
     */
    protected $heurekaDelivery;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='text'
     * FORM title="Heuréka - ID pobočky"
     * FORM attribute-placeholder='Heuréka - ID pobočky (v případě osobního odběru nutno vyplnit)'
     * FORM required='0'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Heuréka - ID pobočky"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $heurekaStore;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Aktivní (zobrazena v eshopu)"
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
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="DPD Pickup?"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="DPD Pickup?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $isDPD;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="DPD ?"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="DPD ?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $isDPDClassic;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Uloženka Pickup?"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Uloženka Pickup?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $isUlozenka;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Zásilkovna"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Zásilkovna"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $isZasilkovna;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $image;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}

?>