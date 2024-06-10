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
class ProductSetItems extends ABaseEntity {

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
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="setProducts")
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
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="id")
     * GRID type='text'
     * GRID title="Přiřazený produkt"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\Product'
     * GRID entity-alias='prods'
     * GRID filter=single-entity #['name']
     */
    protected $products;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     *
     * GRID type='bool'
     * GRID title="Zobrazit u přiřazeného produktu"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $showSet;

    public function __construct($data = null) {
        $this->showSet = false;
        parent::__construct($data);
    }

}

?>