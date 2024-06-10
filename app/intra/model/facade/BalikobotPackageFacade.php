<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Intra\Model\Database\Entity\BalikobotPackage;
use Kdyby\Doctrine\EntityManager;

class BalikobotPackageFacade extends BaseFacade
{

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, BalikobotPackage::class);
    }

}
