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
class BalikobotOrder extends ABaseEntity
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
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='date'
     * FORM title="Datum vytvoření"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='datetime'
     * GRID title="Datum vytvoření"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $foundedDate;

    /**
     * @ORM\ManyToOne(targetEntity="BalikobotTypeDelivery", inversedBy="id")
     *
     * GRID type='text'
     * GRID title="Typ dopravy"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\BalikobotTypeDelivery'
     * GRID entity-alias='bbb'
     * GRID filter=single-entity #['name']
     */
    protected $balikobotTypeDelivery;

    /**
     * @ORM\ManyToOne(targetEntity="BalikobotShop", inversedBy="id")
     * FORM type='select'
     * FORM title='Vyberte expediční místo (Balikobot e-shop)'
     * FORM data-entity=Intra\Model\Database\Entity\BalikobotShop[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Balikobot e-shop"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\BalikobotShop'
     * GRID entity-alias='bbb'
     * GRID filter=single-entity #['name']
     */
    protected $balikobotShop;


    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * GRID type='text'
     * GRID title="Počet balíků"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $count;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $handover_url;

    /**
     * @ORM\OneToMany(targetEntity="BalikobotPackage", mappedBy="balOrders")
     */
    protected $packages;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $order_id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $labels_url;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $file_url;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;


    public function __construct($data = null)
    {
        $this->foundedDate = new DateTime();

        parent::__construct($data);
    }

}

?>