<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\ProductDiscussion;

class ProductDiscussionFacade extends BaseFacade {

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        parent::__construct($em, ProductDiscussion::class);
    }

    /**
     * Delete discussion by id
     * @param integer $id of discussion
     * @return boolean
     */
    public function deleteDiscussion($id) {
        $item = $this->get()->find($id);
        if (count($item->reply)) {
            return false;
        }
        $this->remove($item);
        return true;
    }

}
