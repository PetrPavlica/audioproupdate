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
class WebTemplate extends ABaseEntity {

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
     * @ORM\Column(type="string")
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Název'
     * FORM required="Toto je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * FORM type='text'
     * FORM title="Název latte šablony"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Název'
     * FORM required="Toto je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Název latte šablony"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $path;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Inline edit?"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Inline edit?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $attributable;

    /**
     * @ORM\OneToMany(targetEntity="WebMenu", mappedBy="template")
     */
    protected $menu;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     * GRID type='datetime'
     * GRID title="Datum založení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $created;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     * GRID type='datetime'
     * GRID title="Datum založení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $updated;

    public function __construct($data = null) {
        $this->attributable = FALSE;
        $this->created = new DateTime();
        $this->updated = new DateTime();
        parent::__construct($data);
    }

}

?>