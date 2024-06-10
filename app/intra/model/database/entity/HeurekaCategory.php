<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Core\Model\Database\Utils\ABaseEntity;

/**
 * @ORM\Entity
 */
class HeurekaCategory extends ABaseEntity {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
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
     * FORM attribute-placeholder='Název'
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

    public function __construct($data = null) {
        parent::__construct($data);
    }

}

?>