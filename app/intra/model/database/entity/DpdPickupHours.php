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
class DpdPickupHours extends ABaseEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="DpdPickup", inversedBy="id")
     */
    protected $dpdPickup;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $day;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $dayName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $openMorning;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $closeMorning;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $openAfternoon;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $closeAfternoon;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}