<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 */
class WebPushNotificationFront extends ABaseEntity
{
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
     * @ORM\ManyToOne(targetEntity="WebPushNotification", inversedBy="id")
     */
    protected $notification;

    /**
     * @ORM\ManyToOne(targetEntity="WebPushSubscription", inversedBy="id")
     */
    protected $subscription;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}