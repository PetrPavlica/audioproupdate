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
class PermisionRule extends ABaseEntity {

    const ACTION_WRITE = 'write';
    const ACTION_READ = 'read';
    const ACTION_SHOW = 'show';
    const ACTION_ALL = 'all';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="PermisionGroup", inversedBy="rule")
     */
    protected $group;

    /**
     * @ORM\Column(type="string")
     */
    protected $item;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('write', 'read', 'show', 'all')")
     */
    protected $action;

    public function __construct($data = null) {
        parent::__construct($data);
    }

}

?>
