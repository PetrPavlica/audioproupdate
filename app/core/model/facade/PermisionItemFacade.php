<?php

namespace App\Core\Model\Facade;

use Nette\Database\Context;
use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use App\Core\Model\Database\Entity\PermisionItem;
use App\Core\Model\Database\Entity\PermisionGroup;

class PermisionItemFacade extends BaseFacade {

    /** @var Context */
    public $db;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, Context $db) {
        parent::__construct($em, PermisionItem::class);
        $this->db = $db;
    }

    /**
     * Get permision items by presenter
     * @param strin $presenter name of presenter
     * @return type
     */
    public function getByPresenter($presenter) {
        $presenter = str_replace('\\', '_', $presenter);
        $presenter = str_replace('App_Presenters_', '', $presenter);
        return $this->get()->createQueryBuilder('i')
                        ->where('i.name LIKE :presenter')
                        ->orWhere('i.type = :menu')
                        ->orWhere('i.type = :global')
                        ->setParameters([
                            'presenter' => $presenter . '%',
                            'menu' => PermisionItem::TYPE_MENU,
                            'global' => PermisionItem::TYPE_GLOBAL_ELEMENT
                        ])
                        ->getQuery()
                        ->getResult();
    }

    /**
     * Return entity Group by Id
     * @param int $id
     * @return PermisionGroup
     */
    public function getGroup($id) {
        return $this->em->getRepository(PermisionGroup::class)->find($id);
    }

}
