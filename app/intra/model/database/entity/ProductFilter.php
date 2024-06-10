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
class ProductFilter extends ABaseEntity {

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
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Název'
     * FORM required="Název je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Pořadí"
     * FORM attribute-placeholder='Pořadí'
     * FROM required='Toto je povinné pole'
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Pořadí"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $orderState;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Minimální hodnota (pro picker)"
     * FORM attribute-placeholder='Minimální hodnota (pro picker)'
     * FORM required='0'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Minimální hodnota (pro picker)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $minValue;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Krok pickeru (posun)"
     * FORM attribute-placeholder='Krok pickeru (posun)'
     * FORM required='0'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Krok pickeru (posun)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $step;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Maximální hodnota (pro picker)"
     * FORM attribute-placeholder='Maximální hodnota (pro picker)'
     * FORM required='0'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Maximální hodnota (pro picker)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $maxValue;

    /**
     * @ORM\ManyToOne(targetEntity="GroupProductFilter", inversedBy="filters")
     * FORM type='select'
     * FORM title='Vyberte skupinu filtrů'
     * FORM prompt='-- vyberte skupinu'
     * FORM required='Skupina filtrů je povinné pole'
     * FORM data-entity=Intra\Model\Database\Entity\GroupProductFilter[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Skupina filtrů"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\GroupProductFilter'
     * GRID entity-alias='gpf'
     * GRID filter=single-entity #['name']
     */
    protected $filterGroup;

    /**
     * @ORM\OneToMany(targetEntity="ProductInFilter", mappedBy="filter")
     */
    protected $product;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Posuvníkový výběr"
     *
     * GRID type='bool'
     * GRID title="Posuvníkový výběr"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $slider;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Interval?"
     *
     * GRID type='bool'
     * GRID title="Interval?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $intervalV;

    public function __construct($data = null) {
        parent::__construct($data);
    }

}

?>