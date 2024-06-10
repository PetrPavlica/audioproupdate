<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Intra\Model\Database\Entity\HeurekaCart;
use Kdyby\Doctrine\EntityManager;

class HeurekaCartFacade extends BaseFacade
{
    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, HeurekaCart::class);
    }
}