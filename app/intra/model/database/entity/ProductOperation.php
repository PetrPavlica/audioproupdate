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
class ProductOperation extends ABaseEntity {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     *
     * GRID type='number'
     * GRID title="Id"
     * GRID sortable='true'
     * GRID visible='true'
     * GRID align='left'
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * GRID type='datetime'
     * GRID title="Datum"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $foundedDate;

    /**
     * @ORM\Column(type="float", nullable=true)
     * GRID type='integer'
     * GRID title="Počet"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $count;

    /**
     * @ORM\Column(type="float", nullable=true)
     * GRID type='float'
     * GRID title="Cena/kus"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $price;

    /**
     * @ORM\Column(type="float", nullable=true)
     * GRID type='integer'
     * GRID title="Celkem"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $sumPrice;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="id")
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Orders", inversedBy="id")
     * GRID type='text'
     * GRID title="Faktura"
     * GRID entity-link='codeInvoice'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\Orders'
     * GRID entity-alias='ord'
     * GRID filter=single-entity #['codeInvoice']
     */
    protected $orders;

    /**
     * @ORM\Column(type="text", nullable=true)
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $comment;

    /**
     * @ORM\Column(type="text", nullable=true)
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $type;

    /**
     * @ORM\Column(type="boolean")
     *
     * GRID type='bool'
     * GRID title="Aktivní"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     * GRID inline-type='checkbox'
     */
    protected $isReservation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Core\Model\Database\Entity\User", inversedBy="id")
     * GRID type='text'
     * GRID title="Založil/a"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='App\Core\Model\Database\Entity\User'
     * GRID entity-alias='urs'
     * GRID filter=single-entity #['name']
     */
    protected $originator;

    public function __construct($data = null) {
        $this->foundedDate = new DateTime();
        parent::__construct($data);
    }

}

?>