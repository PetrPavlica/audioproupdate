<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity
 */
class DiscountInOrder extends ABaseEntity {

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
     * @ORM\ManyToOne(targetEntity="Discount", inversedBy="id")
     * FORM type='select'
     * FORM title='Sleva'
     * FORM prompt='-- vyberte slevu'
     * FORM data-entity=Intra\Model\Database\Entity\Discount[code]
     * FORM attribute-class="form-control selectpicker"
     * FORM attribute-data-live-search="true"
     *
     * GRID type='text'
     * GRID title="Sleva"
     * GRID entity-link='code'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\Discount'
     * GRID entity-alias='prod'
     * GRID filter=single-entity #['code', 'name']
     */
    protected $discount;

    /**
     * @ORM\ManyToOne(targetEntity="Orders", inversedBy="discount")
     * FORM type='hidden'
     * FORM data-entity=Intra\Model\Database\Entity\Orders[id]
     */
    protected $orders;

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

    public function __construct($data = null) {
        parent::__construct($data);
    }

}

?>