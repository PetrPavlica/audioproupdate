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
class DpdPackage extends ABaseEntity
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
     * @ORM\ManyToOne(targetEntity="Orders", inversedBy="dpdPackages")
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
    protected $order;

    /**
     * @ORM\ManyToOne(targetEntity="DpdAddress", inversedBy="id")
     * FORM type='select'
     * FORM title='Vyberte expediční místo'
     * FORM data-entity=Intra\Model\Database\Entity\DpdAddress[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Expediční místo"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\DpdAddress'
     * GRID entity-alias='bbb'
     * GRID filter=select-entity #['name']
     */
    protected $dpdAddress;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Šířka balíku (cm)"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Šířka balíku (cm)'
     * FORM rule-float ='Prosím zadávejte desetinné číslo'
     * FORsM required="Šířka balíku je povinné pole!"
     * FORM required='false'
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
     * FORsM required="Délka balíku je povinné pole!"
     * FORM required='false'
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
     * FORsM required="Výška balíku je povinné pole!"
     * FORM required='false'
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
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Doplňující info na štítku"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Doplňující info na štítku'
     *
     * GRID type='text'
     * GRID title="Doplňující info na štítku"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $additionalInfo;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $shipmentId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $parcelId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $trackingId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $dpdUrl;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='hidden'
     */
    protected $orderNumber;

    /**
     * Původní uložiště ID manifestu z eshopu - v jiném projektu může být odstraněno
     * @ORM\Column(type="string", nullable=true)
     */
    protected $manifestNumber;

    /**
     * @ORM\ManyToOne(targetEntity="DpdManifest", inversedBy="id")
     */
    protected $manifest;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}