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
class WebResources extends ABaseEntity {

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
     * @ORM\Column(type="integer", nullable=true)
     * GRID type='integer'
     * GRID title="Stránka id"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $pageId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Div ID"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Název'
     *
     * GRID type='text'
     * GRID title="Div ID"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $divId;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Text"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Text'
     *
     * GRID type='text'
     * GRID title="Text"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     *
     */
    protected $text;

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
        $this->created = new DateTime();
        $this->updated = new DateTime();
        parent::__construct($data);
    }

}

?>