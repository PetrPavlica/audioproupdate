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
class ProductInCreditNote extends ABaseEntity
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
     * @ORM\ManyToOne(targetEntity="ProductInOrder", inversedBy="id")
     */
    protected $productInOrder;

    /**
     * @ORM\ManyToOne(targetEntity="Orders", inversedBy="productsInCreditNote")
     */
    protected $order;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='text'
     * FORM title="Počet kusů"
     * FORM attribute-placeholder='Počet kusů'
     * FORM required='Počet kusů je povinné pole!'
     * FORM rule-number="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Prodejní cena"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $count;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}