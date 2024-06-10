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
class OrderRefund extends ABaseEntity {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * FORM type="hidden"
     */
    protected $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Částka"
     * FORM attribute-placeholder='Čáska'
     * FORM required='Toto pole je povinné!'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Cena platební metody"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $value;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='date'
     * FORM title="Datum úhrady"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Datum úhrady'
     *
     * GRID type='datetime'
     * GRID title="Datum úhrady"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $foundedDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Forma úhrady"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Forma úhrady'
     *
     * GRID type='text'
     * GRID title="Forma úhrady"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $typePayment;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Poznámka"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Poznámka'
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $text;

    /**
     * @ORM\ManyToOne(targetEntity="Orders", inversedBy="refund")
     * FORM type='hidden'
     * FORM data-entity=Intra\Model\Database\Entity\Orders[id]
     *
     * GRID type='text'
     * GRID title="Objednávka"
     * GRID entity-link='id'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\Orders'
     * GRID entity-alias='pcc'
     * GRID filter=single-entity #['id']
     */
    protected $orders;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * GRID type='text'
     * GRID title="FIK EET"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $fikEET;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * GRID type='text'
     * GRID title="BKP EET"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $bkpEET;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * GRID type='text'
     * GRID title="Pořadové číslo účtenky pro EET"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $eetNo;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * GRID type='text'
     * GRID title="Označení provozovny eet"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $eetProvoz;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * GRID type='text'
     * GRID title="Označení poklady pro eet"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $eetPokl;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Hotovostní platba? (pro EET)"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Hotovostní platba?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $online;

    /**
     * @ORM\ManyToOne(targetEntity="App\Core\Model\Database\Entity\User", inversedBy="id")
     * FORM type='hidden'
     * FORM data-entity=App\Core\Model\Database\Entity\User[name]
     *
     * GRID type='text'
     * GRID title="Vytvořil"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='App\Core\Model\Database\Entity\User'
     * GRID entity-alias='asf'
     * GRID filter=single-entity #['name']
     */
    protected $originator;

    public function __construct($data = null) {
        parent::__construct($data);
    }

}

?>