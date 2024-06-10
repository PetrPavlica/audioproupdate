<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Core\Model\Database\Utils\ABaseEntity;

/**
 * @ORM\Entity
 */
class RedirectRule extends ABaseEntity {

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
     * GRID inline-type='id'
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Stará url"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Stará url'
     * FORM required='Toto pole je povinné'
     *
     * GRID type='text'
     * GRID title="Stará url"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID inline-type='text'
     */
    protected $oldPath;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přesměrovat na"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Přesměrovat na'
     * FORM required='Toto pole je povinné'
     *
     * GRID type='text'
     * GRID title="Přesměrovat na"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID inline-type='text'
     */
    protected $newPath;

    public function __construct($data = null) {
        parent::__construct($data);
    }

}

?>