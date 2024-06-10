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
class Currency extends ABaseEntity {

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
     * FORM title="Kód"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Kód'
     * FORM required="Kód je povinné pole!"
     * FORM rule-length="Délka kódu musí být %d znaky" #[3]
     *
     * GRID type='text'
     * GRID title="Kód"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $code;

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
     * FORM title="Pořadí (priorita)"
     * FORM attribute-placeholder='Pořadí'
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Pořadí (priorita)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $orderCurrency;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Měnový kurz oproti CZK"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Kurz'
     * FORM rule-float ='Prosím zadávejte desetinné číslo'
     * FORM required="Měnový kurz je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Měnový kurz"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $exchangeRate;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Počet desetinných míst bez zaokrouhlení"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Počet desetinných míst'
     * FORM rule-number ='Prosím zadávejte desetinné číslo'
     * FORM required="Počet desetinných míst je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Počet desetinných míst"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $countDecimal;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Označení před"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Označení před'
     *
     * GRID type='text'
     * GRID title="Označení před"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $markBefore;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Označení za"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Označení za'
     *
     * GRID type='text'
     * GRID title="Označení za"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $markBehind;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Aktivní (zobrazena v eshopu)"
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