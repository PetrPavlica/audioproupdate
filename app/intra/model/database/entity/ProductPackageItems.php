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
class ProductPackageItems extends ABaseEntity
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
     * @ORM\ManyToOne(targetEntity="ProductPackage", inversedBy="products")
     * GRID type='text'
     * GRID title="Hlavní produkt"
     * GRID entity-link='product.name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\ProductPackage'
     * GRID entity-alias='prodpackage'
     * GRID filter=single-entity #['product.name']
     */
    protected $package;

    /**
     * @ORM\ManyToOne(targetEntity="Product")
     * GRID type='text'
     * GRID title="Přiřazený produkt"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\Product'
     * GRID entity-alias='prods'
     * GRID filter=single-entity #['name']
     */
    protected $product;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * GRID type='number'
     * GRID title="Sleva v % (Kč)"
     * GRID sortable='true'
     * GRID visible='true'
     * GRID align='left'
     * GRID filter='single'
     */
    protected $discountCZK;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * GRID type='number'
     * GRID title="Sleva v % (Kč)"
     * GRID sortable='true'
     * GRID visible='true'
     * GRID align='left'
     * GRID filter='single'
     */
    protected $discountEUR;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}