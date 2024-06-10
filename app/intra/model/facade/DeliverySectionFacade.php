<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\DeliverySection;

class DeliverySectionFacade extends BaseFacade {

	/**
	 * Construct
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em) {
		parent::__construct($em, DeliverySection::class);
	}

}
