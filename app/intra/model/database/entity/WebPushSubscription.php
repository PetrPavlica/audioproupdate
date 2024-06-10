<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 */
class WebPushSubscription extends ABaseEntity
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
     * @ORM\Column(type="string", unique=true)
     * GRID type='text'
     * GRID title="Endpoint"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $endpoint;

    /**
     * @ORM\Column(type="string", name="sub_key")
     * GRID type='text'
     * GRID title="Klíč"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $key;

    /**
     * @ORM\Column(type="string")
     * GRID type='text'
     * GRID title="Token"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $token;

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
     * @ORM\Column(type="datetimetz", nullable=true)
     *
     * GRID type='datetime'
     * GRID title="Datum založení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $created;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     *
     * GRID type='datetime'
     * GRID title="Datum aktualizace"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $updated;

    public function __construct($data = null)
    {
        $this->created = new DateTime();
        $this->updated = new DateTime();
        parent::__construct($data);
    }
}