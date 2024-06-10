<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\BannerProduct;

class BannerProductFacade extends BaseFacade {

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        parent::__construct($em, BannerProduct::class);
    }

    public function saveImage($path, $banner)
    {
        $banner->setImage($path);
        $this->save();
    }
}
