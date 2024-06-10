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
class DpdOrder extends ABaseEntity
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
     * @ORM\ManyToOne(targetEntity="DpdAddress", inversedBy="id")
     * FORM type='select'
     * FORM title='Adresa vyzvednutí'
     * FORM data-entity=Intra\Model\Database\Entity\DpdAddress[name]
     * FORM attribute-class="form-control"
     * FORM required="Adresa vyzvednutí je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Adresa vyzvednutí"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\DpdAddress'
     * GRID entity-alias='da'
     * GRID filter=select-entity #['name']
     */
    protected $dpdAddress;

    /**
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum vyzvednutí"
     * FORM attribute-class='form-control input-md'
     * FORM required="Datum vyzvednutí je povinné pole!"
     *
     * GRID type='datetime'
     * GRID title="Datum vyzvednutí"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $date;

    /**
     * @ORM\Column(type="time", nullable=true)
     * FORM type='time'
     * FORM title="Čas od"
     * FORM attribute-class='form-control input-md'
     * FORM required="Čas od je povinné pole!"
     *
     * GRID type='datetime'
     * GRID title="Čas od"
     * GRID sortable='true'
     * GRID visible='true'
     * GRID format-time='H:i'
     */
    protected $fromTime;

    /**
     * @ORM\Column(type="time", nullable=true)
     * FORM type='time'
     * FORM title="Čas do"
     * FORM attribute-class='form-control input-md'
     * FORM required="Čas do je povinné pole!"
     *
     * GRID type='datetime'
     * GRID title="Čas do"
     * GRID sortable='true'
     * GRID visible='true'
     * GRID format-time='H:i'
     */
    protected $toTime;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Doplňující informace pro kurýra"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Doplňující informace pro kurýra'
     *
     * GRID type='text'
     * GRID title="Doplňující informace pro kurýra"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $specialInstruction;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='number'
     * FORM title="Počet zásilek"
     * FORM attribute-class='form-control input-md'
     * FORM required="Počet zásilek je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Počet zásilek"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $quantity;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Hmotnost (kg)"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Hmotnost (kg)'
     * FORM rule-float ='Prosím zadávejte desetinné číslo'
     * FORM required="Hmotnost je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Hmotnost (kg)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $weight;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='select'
     * FORM title="Země"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Země'
     * FORM data-own=['CZ' > 'Česká Republika']
     *
     * GRID type='translate-text'
     * GRID title="Země"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     * GRID filter=select #['' > 'Vše'|'CZ' > 'Česká Republika']
     */
    protected $destinationCountryCode;

    /**
     * @ORM\Column(type="string", nullable=true)
     * GRID type='text'
     * GRID title="Referenční číslo v DPD systému"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $referenceNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     * GRID type='text'
     * GRID title="Důvod zrušení svozu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $description;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * GRID type='bool'
     * GRID title="Svoz objednán"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $orderSuccess;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * GRID type='bool'
     * GRID title="Svoz zrušen"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $orderCanceled;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}