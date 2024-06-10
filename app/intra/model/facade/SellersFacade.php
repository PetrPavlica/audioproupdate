<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\Sellers;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;
use Nette\Database\Context;

class SellersFacade extends BaseFacade {

    /** @var IStorage */
    private $storage;

    /** @var Context */
    public $db;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, Context $db) {
        parent::__construct($em, Sellers::class);
        $this->db = $db;
    }

    public function saveImage($path, $sellers)
    {
        $sellers->setImage($path);
        $this->save();
    }

    public function deleteImage($sellersId)
    {
        $sellers = $this->get()->find($sellersId);
        if (count($sellers)) {
            if (file_exists($sellers->image)) {
                unlink($sellers->image);
            }
            $sellers->setImage('');
            $this->save();
            return true;
        }
        return false;
    }

}
