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
class UlozenkaData extends ABaseEntity {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * FORM type="hidden"
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="PaymentMethod", inversedBy="id")
    */
    protected $orderId;

    /*
     * @ORM\Column(type="string")
    */
    protected $consigmentId;

    /*
     * @ORM\Column(type="string")
    */
    protected $orderNumber;

    public function __construct($data = null) {
        parent::__construct($data);
    }

}

?>