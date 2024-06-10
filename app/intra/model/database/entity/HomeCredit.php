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
class HomeCredit extends ABaseEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * FORM type="hidden"
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $orderNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $orderState;

    /**
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    protected $sendDate;

    /**
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    protected $notificationDate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $sequenceNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $checkSum;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $homecreditId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $state;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $stateReason;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}