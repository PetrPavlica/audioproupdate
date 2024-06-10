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
class ProductGifts extends ABaseEntity
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
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="gifts")
     * GRID type='text'
     * GRID title="Hlavní produkt"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\Product'
     * GRID entity-alias='prod'
     * GRID filter=single-entity #['name']
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Product")
     * GRID type='text'
     * GRID title="Dárek"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\Product'
     * GRID entity-alias='prodg'
     * GRID filter=single-entity #['name']
     */
    protected $gift;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * GRID type='number'
     * GRID title="Pořadí"
     * GRID sortable='true'
     * GRID filter=single
     * GRID visible='false'
     * GRID align='left'
     */
    protected $rank;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}