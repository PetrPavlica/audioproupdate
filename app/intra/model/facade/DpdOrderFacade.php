<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Intra\Model\Database\Entity\DpdOrder;
use Intra\Model\Utils\MyDpd;
use Intra\Model\Utils\MyDpdMessage;
use Kdyby\Doctrine\EntityManager;
use Nette\Utils\DateTime;

class DpdOrderFacade extends BaseFacade
{
    /** @var MyDpd */
    public $myDpd;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, MyDpd $myDpd)
    {
        parent::__construct($em, DpdOrder::class);
        $this->myDpd = $myDpd;
    }

    public function getOrderDate()
    {
        $date = new DateTime();

        while (true)
        {
            if ($date->format('N') >= 1 && $date->format('N') <= 5 && $date->format('H') <= 18) {
                return $date;
            } else {
                $date->modify('+1 day');
            }
        }

        return $date;
    }

    public function createPickupOrder(DpdOrder $order)
    {
        $result = $this->myDpd->createPickupOrder($order);

        if ($result) {
            if ($result->error) {
                throw new MyDpdMessage($result->error->text);
            }

            $order->setReferenceNumber($result->pickupOrderReference->id);
            $order->setOrderSuccess(true);
            $this->save();

            return $result->pickupOrderReference->id;
        }

        return null;
    }

    public function deletePickupOrder(DpdOrder $order)
    {
        $result = $this->myDpd->deletePickupOrder($order);

        if ($result) {
            if ($result->error) {
                throw new MyDpdMessage($result->error->text);
            }

            $order->setOrderCanceled(true);
            $this->save();

            return $result->pickupOrderReference->id;
        }

        return null;
    }
}