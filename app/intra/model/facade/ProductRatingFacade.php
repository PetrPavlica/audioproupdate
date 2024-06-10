<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\ProductRating;

class ProductRatingFacade extends BaseFacade {

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        parent::__construct($em, ProductRating::class);
    }

    public function deleteRating($idRating) {
        $rating = $this->get()->find($idRating);
        if (count($rating)) {
            $this->remove($rating);
            return $rating;
        }
        return false;
    }

    public function approveRating($idRating) {
        $rating = $this->get()->find($idRating);
        if (count($rating)) {
            $rating->setApproved(TRUE);
            $this->save();
            return $rating;
        }
        return false;
    }

}
