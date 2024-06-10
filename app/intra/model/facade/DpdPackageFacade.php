<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Intra\Model\Database\Entity\DpdPackage;
use Intra\Model\Utils\MyDpd;
use Intra\Model\Utils\MyDpdMessage;
use Kdyby\Doctrine\EntityManager;
use Tracy\Debugger;

class DpdPackageFacade extends BaseFacade
{
    /** @var MyDpd */
    public $myDpd;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, MyDpd $myDpd)
    {
        parent::__construct($em, DpdPackage::class);
        $this->myDpd = $myDpd;
    }

    public function deletePackage($packageId)
    {
        $package = $this->get()->find($packageId);
        if ($package) {
            $result = $this->myDpd->deleteShipment($package->shipmentId);
            if ($result) {
                if ($result->error) {
                    throw new MyDpdMessage($result->error->text);
                }
                $packages = $this->gEMDpdPackage()->findBy(['shipmentId' => $package->shipmentId]);
                if ($packages) {
                    foreach ($packages as $p) {
                        $p->setShipmentId(null);
                        $p->setOrderNumber(null);
                        $p->setParcelId(null);
                        $p->setIsOrdered(false);
                    }
                    $this->save();
                }

                return true;
            }
        }

        return null;
    }

    public function getShipmentStatus($shipments)
    {
        $result = $this->myDpd->getShipmentStatus($shipments);
        if ($result) {
            if (isset($result->statusInfoList->error) && $result->statusInfoList->error) {
                throw new MyDpdMessage($result->statusInfoList->error->text);
            }

            return $result->statusInfoList;
        }

        return null;
    }

    public function getParcelStatus($shipments)
    {
        $result = $this->myDpd->getParcelStatus($shipments);
        bdump($result);
        if ($result) {
            if (isset($result->statusInfoList->error) && $result->statusInfoList->error) {
                throw new MyDpdMessage($result->statusInfoList->error->text);
            }

            return $result->statusInfoList;
        }

        return null;
    }

    public function getShipments($ids)
    {
        $shipments = [];
        try {
            $packages = $this->get()->findBy(['id' => $ids]);
            if ($packages) {
                foreach ($packages as $p) {
                    if ($p->shipmentId) {
                        $shipments[] = $p->shipmentId;
                    }
                }
            }
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return $shipments;
    }
}