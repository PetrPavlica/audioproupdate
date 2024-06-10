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
class ProductInOrder extends ABaseEntity {

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
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="id")
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Orders", inversedBy="products")
     */
    protected $orders;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $basePrice;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Prodejní cena"
     * FORM attribute-placeholder='Prodejní cena'
     * FORM required='Prodejní cena je povinné pole!'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Prodejní cena"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $selingPrice;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-placeholder='Název produktu'
     * FORM required='Název je povinné pole!'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Jednotka"
     * FORM attribute-placeholder='(ks, metry, role...)'
     * FORM required='Jednotka je povinné pole!'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Jednotka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $unit;

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

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isPojisteni;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isNastaveni;

    /**
     * @ORM\ManyToOne(targetEntity="ProductPackageItems")
     */
    protected $packageItem;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isGift = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="id")
     */
    protected $gift;

    /**
     * @ORM\ManyToOne(targetEntity="ProductInOrder", inversedBy="id")
     */
    protected $parentProduct;

    /**
     * @ORM\OneToOne(targetEntity="ProductInOrder", mappedBy="parentProduct")
     */
    protected $childProduct;

    public function __construct($data = null) {
        $this->isPojisteni = 0;
        $this->isNastaveni = 0;
        $this->isGift = 0;
        parent::__construct($data);
    }

}
