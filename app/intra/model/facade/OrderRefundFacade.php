<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\OrderRefund;

class OrderRefundFacade extends BaseFacade {

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        parent::__construct($em, OrderRefund::class);
    }

    public function deleteRefund($id) {
        $entity = $this->get()->find($id);
        $this->getEm()->remove($entity);
        $this->save();
    }

}
