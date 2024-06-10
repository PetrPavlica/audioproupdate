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
class BalikobotPackage extends ABaseEntity
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
     * @ORM\Column(type="boolean")
     *
     * GRID type='bool'
     * GRID title="Objednáno?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $isOrdered;

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
     * @ORM\ManyToOne(targetEntity="Orders", inversedBy="packages")
     * FORM type="hidden"
     * FORM data-entity=Intra\Model\Database\Entity\Orders[id]
     *
     * GRID type='text'
     * GRID title="Z objednávky"
     * GRID entity-link='variableSymbol'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\Orders'
     * GRID entity-alias='ccc'
     * GRID filter=single-entity #['variableSymbol']
     */
    protected $orders;

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
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Šířka balíku (cm)"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Šířka balíku (cm)'
     * FORM rule-float ='Prosím zadávejte desetinné číslo'
     * FORM required="Šířka balíku je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Šířka balíku (cm)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $width;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Délka balíku (cm)"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Délka balíku (cm)'
     * FORM rule-float ='Prosím zadávejte desetinné číslo'
     * FORM required="Délka balíku je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Délka balíku (cm)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $length;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Výška balíku (cm)"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Výška balíku (cm)'
     * FORM rule-float ='Prosím zadávejte desetinné číslo'
     * FORM required="Výška balíku je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Výška balíku (cm)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $height;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Hmotnost balíku (kg)"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Hmotnost balíku (kg)'
     * FORM rule-float ='Prosím zadávejte desetinné číslo'
     * FORM required="Hmotnost balíku je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Hmotnost balíku (kg)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $weight;

    /**
     * @ORM\ManyToOne(targetEntity="BalikobotOrder", inversedBy="packages")
     */
    protected $balOrders;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $carrier_id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $package_id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $label_url;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $file_url;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $order_id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='hidden'
     */
    protected $orderNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='hidden'
     */
    protected $eid;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $trackUrl;

    public function __construct($data = null)
    {
        $this->isOrdered = false;
        parent::__construct($data);
    }

}

?>