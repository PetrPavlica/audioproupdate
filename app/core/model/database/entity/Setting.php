<?php

namespace App\Core\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity
 */
class Setting extends ABaseEntity {

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
     * FORM attribute-placeholder='Kód'
     * FORM attribute-class='form-control input-md'
     * FORM disabled="true"
     *
     * GRID type='text'
     * GRID title="Kód"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $codeSetting;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='text'
     * FORM title="Hodnota"
     * FORM attribute-placeholder='Hodnota'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Hodnota"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $value;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Poznámka"
     * FORM attribute-placeholder='volitelná poznámka'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $description;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * GRID type='datetime'
     * GRID title="Datum zapsání"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateInsert;

    public function __construct($data = null) {
        parent::__construct($data);
        $this->dateInsert = new DateTime();
    }

}

?>