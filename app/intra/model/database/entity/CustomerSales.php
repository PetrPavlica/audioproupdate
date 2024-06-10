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
class CustomerSales extends ABaseEntity {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="sales")
     */
    protected $customer;

    /**
     * @ORM\ManyToOne(targetEntity="ProductMark", inversedBy="id")
     */
    protected $mark;

    /**
     * @ORM\Column(type="integer")
     */
    protected $value;

    public function __construct($data = null) {
        parent::__construct($data);
    }

}

?>