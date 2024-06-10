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
class Vat extends ABaseEntity {

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
     * FORM title="Název sazby"
     * FORM attribute-placeholder='Název sazby'
     * FROM required='Toto je povinné pole'
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
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Sazba v %"
     * FORM attribute-placeholder='sazba v %'
     * FROM required='Toto je povinné pole'
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Sazba (%)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $value;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Výchozí"
     * FORM default-value='false'
     *
     * GRID type='bool'
     * GRID title="Výchozí"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $defaultVal;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Základní sazba dph"
     * FORM default-value='false'
     *
     * GRID type='bool'
     * GRID title="Základní sazba dph"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $standartRate;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Snížená sazba dph"
     * FORM default-value='false'
     *
     * GRID type='bool'
     * GRID title="Snížená sazba dph"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $reducedRate;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Druhá snížená sazba dph (nižší než snížená)"
     * FORM default-value='false'
     *
     * GRID type='bool'
     * GRID title="Druhá snížená sazba dph (nižší než snížená)"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $secondReducedRate;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Aktivní"
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

    public function __construct($data = null) {
        parent::__construct($data);
    }

}

?>