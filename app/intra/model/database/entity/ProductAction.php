<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity
 */
class ProductAction extends ABaseEntity
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
     * FORM title=Procentuální sleva"
     * FORM required='false'
     * FORM rule-number='Hodnota musí být celé číslo!'
     * FORM rule-range='Číslo může být v rozpětí pouze 0 - 100 #[0, 100],
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Procentuální sleva'
     *
     * GRID type='text'
     * GRID title="Procentuální sleva"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $percent;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Prodejní cena včetně DPH"
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
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='datetime'
     * FORM title="Datum a čas od"
     * FORM attribute-class='form-control input-md'
     * FORM required='false'
     * FORM attribute-placeholder='Datum a čas od'
     *
     * GRID type='datetime'
     * GRID title="Datum do"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateForm;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='datetime'
     * FORM title="Datum a čas do"
     * FORM attribute-class='form-control input-md'
     * FORM required='false'
     * FORM attribute-placeholder='Datum a čas do'
     *
     * GRID type='datetime'
     * GRID title="Datum do"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateTo;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Aktivní?"
     *
     * GRID type='bool'
     * GRID title="Aktivní?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $active;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='hidden'
     *
     * GRID type='bool'
     * GRID title="Jedná se o cenu?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $isTypeOfPrice;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="actions")
     * FORM type='hidden'
     * FORM data-entity=Intra\Model\Database\Entity\Product[name]
     *
     * GRID type='text'
     * GRID title="Produkt"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\Product'
     * GRID entity-alias='prod'
     * GRID filter=single-entity #['name']
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Currency", inversedBy="id")
     * FORM type='select'
     * FORM title='Měna'
     * FORM data-entity=Intra\Model\Database\Entity\Currency[code]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Měna"
     * GRID entity-link='code'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\Currency'
     * GRID entity-alias='currr'
     * GRID filter=single-entity #['code']
     */
    protected $currency;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Zobrazit akci ve srovnávačích?"
     *
     * GRID type='bool'
     * GRID title="Akce ve srovnávači cen"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $feedShow;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='hidden'
     *
     * GRID type='bool'
     * GRID title="Speciální akce"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $special;

    public function __construct($data = null)
    {
        $this->active = false;
        $this->isTypeOfPrice = false;
        $this->feedShow = true;
        $this->special = false;
        parent::__construct($data);
    }

}

?>