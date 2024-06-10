<?php

namespace App\Core\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use App\Core\Model\Database\Entity\PermisionGroup;

class PermisionGroupFacade extends BaseFacade {

    /**
     * Construct
     * @param \Kdyby\Doctrine\EntityManager $em
     */
    public function __construct(\Kdyby\Doctrine\EntityManager $em) {
        parent::__construct($em);
    }

    /**
     * Get em repository
     * @return Repository of PermisionGroup
     */
    public function get() {
        return $this->em->getRepository(PermisionGroup::class);
    }

    /**
     * Return entity class
     * @return string
     */
    public function entity() {
        return PermisionGroup::class;
    }

}
