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
class PaymentMethod extends ABaseEntity {

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
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Info poznámka "
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Poznámka'
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $info;

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
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\Vat'
     * GRID entity-alias='vatt'
     * GRID filter=single-entity #['name']
     */
    protected $vat;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Pořadí"
     * FORM attribute-placeholder='Pořadí'
     * FROM required='Toto je povinné pole'
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Pořadí"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $orderState;

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
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Dobírka? - pro změnu textu v emailech pro zákazníka."
     *
     * GRID type='bool'
     * GRID title="Dobírka"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $deliveryCash;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Přesměrovat na ThePay (platební bránu)"
     *
     * GRID type='bool'
     * GRID title="Přesměrovat na ThePay"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $thePay;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Přesměrovat na HomeCredit"
     *
     * GRID type='bool'
     * GRID title="Přesměrovat na HomeCredit"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $homeCredit;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $image;

    /**
     * @ORM\ManyToOne(targetEntity="HeurekaCartPaymentTypes")
     * FORM type='select'
     * FORM title='Heuréka - typ platby'
     * FORM prompt='-- vyberte typ'
     * FORM data-entity-values=Intra\Model\Database\Entity\HeurekaCartPaymentTypes[$name$][][]
     * FORM attribute-class="form-control selectpicker"
     * FORM attribute-data-live-search="true"
     *
     * GRID type='text'
     * GRID title="Heuréka - typ platby"
     * GRID sortable='true'
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\HeurekaCartPaymentTypes'
     * GRID entity-alias='heurekat'
     * GRID filter=single-entity #['name']
     */
    protected $heurekaPayment;

    public function __construct($data = null) {
        parent::__construct($data);
    }

}

?>