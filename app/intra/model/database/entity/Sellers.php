<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 */
class Sellers extends ABaseEntity {

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
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Text"
     * FORM attribute-class='ckeditor'
     * FORM attribute-placeholder='Text'
     *
     * GRID type='text'
     * GRID title="Text"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $text;

    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Pořadí"
     * FORM attribute-placeholder='Pořadí'
     * FORM required="Toto je je povinné pole!"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Pořadí"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $orderSellers;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Odkaz"
     * FORM attribute-placeholder='Odkaz'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Odkaz"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $link;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Zobrazit"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Zobrazit"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $active;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     *
     * GRID type='datetime'
     * GRID title="Datum založení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $created;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     *
     * GRID type='datetime'
     * GRID title="Datum založení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $updated;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $image;

    public function __construct($data = null) {
        $this->active = true;
        $this->created = new DateTime();
        $this->updated = new DateTime();
        parent::__construct($data);
    }

}

?>