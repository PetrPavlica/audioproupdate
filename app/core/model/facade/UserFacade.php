<?php

namespace App\Core\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use App\Core\Model\Database\Entity\User;
use Nette\Security\Passwords;

class UserFacade extends BaseFacade {

    /**
     * Construct
     * @param \Kdyby\Doctrine\EntityManager $em
     */
    public function __construct(\Kdyby\Doctrine\EntityManager $em) {
        parent::__construct($em);
    }

    /**
     * Get em repository
     * @return Repository of User
     */
    public function get() {
        return $this->em->getRepository(User::class);
    }

    /**
     * Return entity class
     * @return string
     */
    public function entity() {
        return User::class;
    }

    /**
     * Hash password
     * @param string $password
     * @return string
     */
    public function hash($password) {
        return Passwords::hash($password);
    }

}
