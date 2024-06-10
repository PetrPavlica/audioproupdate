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
class PermisionGroup extends ABaseEntity {

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
     * FORM title="Název skupiny"
     * FORM attribute-placeholder='Název'
     * FORM attribute-class='form-control input-md'
     * FORM required="Název skupiny je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="PermisionRule", mappedBy="group")
     */
    protected $rule;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="group")
     */
    protected $user;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isHidden;

    public function __construct($data = null) {
        $this->isHidden = FALSE;
        parent::__construct($data);
    }

}

?>
