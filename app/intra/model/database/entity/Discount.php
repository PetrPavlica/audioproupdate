<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity
 */
class Discount extends ABaseEntity
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
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Kód slevy"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Kód slevy'
     * FORM required="Kód slevy je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Kód slevy"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $code;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='text'
     * FORM title=Procentuální sleva"
     * FORM required='Toto pole je povinné'
     * FORM rule-number='Hodnota musí být celé číslo!'
     * FORM rule-range='Číslo může být v rozpětí pouze 0 - 100 #[0, 100],
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Procentuální sleva'
     *
     * GRID type='text'
     * GRID title="Procentuální sleva"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $percent;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='datetime'
     * FORM title="Datum a čas od"
     * FORM attribute-class='form-control input-md'
     * FORM required='Toto pole je povinné'
     * FORM attribute-placeholder='Datum a čas od'
     *
     * GRID type='datetime'
     * GRID title="Datum od"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateForm;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='datetime'
     * FORM title="Datum a čas do"
     * FORM attribute-class='form-control input-md'
     * FORM required='Toto pole je povinné'
     * FORM attribute-placeholder='Datum a čas do'
     *
     * GRID type='datetime'
     * GRID title="Datum do"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateTo;

    /**
     * @ORM\OneToMany(targetEntity="DiscountInCategory", mappedBy="discount")
     * FORM type='multiselect'
     * FORM title='Kategorie'
     * FORM attribute-size='30'
     * FORM attribute-multiselect='true'
     * FORM data-entity=Intra\Model\Database\Entity\ProductCategory[name]
     * FORM multiselect-entity=Intra\Model\Database\Entity\DiscountInCategory[discount][category]
     * FORM attribute-class="form-control selectpicker"
     */
    protected $categories;

    /**
     * @ORM\OneToMany(targetEntity="DiscountInProductMark", mappedBy="discount")
     * FORM type='multiselect'
     * FORM title='Značky'
     * FORM attribute-size='30'
     * FORM attribute-multiselect='true'
     * FORM data-entity=Intra\Model\Database\Entity\ProductMark[publicName]
     * FORM data-entity-values=Intra\Model\Database\Entity\ProductMark[$publicName$][]['publicName' > 'ASC']
     * FORM multiselect-entity=Intra\Model\Database\Entity\DiscountInProductMark[discount][productMark]
     * FORM attribute-class="form-control selectpicker"
     * FORM attribute-data-live-search="true"
     */
    protected $productMarks;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title=" Aktivní"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Aktivní"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $active;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}