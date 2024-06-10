<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\RedirectRuleFacade;

class RedirectRulePresenter extends BaseIntraPresenter {

    /** @var RedirectRuleFacade @inject */
    public $facade;

    /**
     * ACL name='Správa přesměrování url'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id) {
        if ($id) {
            $this['form']->setDefaults($this->facade->get()->find($id)->toArray());
        }
    }

    /**
     * ACL name='Tabulka přesměrování url'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $grid->setMessages(['Podařilo se uložit url', 'success'], ['Nepodařilo se uložit url!', 'warning'], $this);
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

}
