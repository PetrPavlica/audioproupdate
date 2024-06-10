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
class DiscountInCategory extends ABaseEntity
{

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
     * @ORM\ManyToOne(targetEntity="Discount", inversedBy="categories")
     */
    protected $discount;

    /**
     * @ORM\ManyToOne(targetEntity="ProductCategory")
     */
    protected $category;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}