<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\NewsletterFacade;

class NewsletterEmailPresenter extends BaseIntraPresenter {

    /** @var NewsletterFacade @inject */
    public $facade;

    /**
     * ACL name='Správa newsletter emailů'
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
     * ACL name='Tabulka s všech emailů pro newsletter'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $grid->setMessages(['Podařilo se uložit email', 'success'], ['Nepodařilo se uložit email!', 'warning'], $this);
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

}
