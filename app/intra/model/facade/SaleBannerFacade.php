<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Intra\Model\Database\Entity\SaleBanner;
use Kdyby\Doctrine\EntityManager;

class SaleBannerFacade extends BaseFacade {

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        parent::__construct($em, SaleBanner::class);
    }

    public function saveImage($path, $banner)
    {
        $banner->setImage($path);
        $this->save();
    }
}
