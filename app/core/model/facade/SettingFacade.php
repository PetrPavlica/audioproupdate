<?php

namespace App\Core\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use App\Core\Model\Database\Entity\Setting;

class SettingFacade extends BaseFacade {

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        parent::__construct($em, Setting::class);
    }

}
