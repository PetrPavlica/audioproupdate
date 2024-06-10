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
class BalikobotTypeDelivery extends ABaseEntity
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
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $shipper;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $service;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $serviceCode;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $active;

    public function __construct($data = null)
    {
        $this->active = true;
        parent::__construct($data);
    }

}

?>