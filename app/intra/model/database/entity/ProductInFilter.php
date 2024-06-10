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
class ProductInFilter extends ABaseEntity {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
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
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="filter")
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="ProductFilter", inversedBy="product")
     */
    protected $filter;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $value;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $valueMax;

    public function __construct($data = null) {
        parent::__construct($data);
    }

}

?>