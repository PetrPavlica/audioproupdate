<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\DeliveryMethod;

class DeliveryMethodFacade extends BaseFacade {

	/**
	 * Construct
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em) {
		parent::__construct($em, DeliveryMethod::class);
	}

	public function saveImage($path, $method) {
		$method->setImage($path);
		$this->save();
	}

	public function deleteImage($methodId) {
		$method = $this->get()->find($methodId);
		if (count($method)) {
			if (file_exists($method->image)) {
				unlink($method->image);
			}
			$method->setImage('');
			$this->save();
			return true;
		}
		return false;
	}

}
