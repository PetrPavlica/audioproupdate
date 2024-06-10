<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Intra\Model\Database\Entity\DpdAddress;
use Kdyby\Doctrine\EntityManager;

class DpdAddressFacade extends BaseFacade
{
    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, DpdAddress::class);
    }

}