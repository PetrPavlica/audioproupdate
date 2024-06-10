<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity
 */
class ProductRating extends ABaseEntity {

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
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Plusy"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Plusy'
     *
     * GRID type='text'
     * GRID title="Plusy"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $plus;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Mínusy"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Mínusy'
     *
     * GRID type='text'
     * GRID title="Mínusy"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $minus;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='text'
     * FORM title="Hodnocení (0 - 5)"
     * FORM required='Toto pole je povinné'
     * FORM rule-range='Číslo může být v rozpětí pouze 0 - 5 #[0, 5],
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Hodnocení'
     *
     * GRID type='text'
     * GRID title="Hodnocení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $rating;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='date'
     * FORM title="Datum založení"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='datetime'
     * GRID title="Datum založení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $foundedDate;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="id")
     * FORM type='select'
     * FORM title='Zákazník'
     * FORM prompt='-- vyberte zákazníka'
     * FORM data-entity=Intra\Model\Database\Entity\Customer[name]
     * FORM attribute-class="form-control selectpicker"
     * FORM attribute-data-live-search="true"
     *
     * GRID type='text'
     * GRID title="Zákazník"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\Customer'
     * GRID entity-alias='cus'
     * GRID filter=single-entity #['name']
     */
    protected $customer;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přezdívka"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Přezdívka'
     *
     * GRID type='text'
     * GRID title="Přezdívka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $nickname;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="id")
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
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Schválené?"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Schválené?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $approved;

    public function __construct($data = null) {
        $this->foundedDate = new DateTime();
        $this->approved = false;
        parent::__construct($data);
    }

}

?>