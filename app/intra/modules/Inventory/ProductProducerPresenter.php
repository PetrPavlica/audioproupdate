<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\ProductProducerFacade;

class ProductProducerPresenter extends BaseIntraPresenter {

    /** @var ProductProducerFacade @inject */
    public $facade;

    /**
     * ACL name='Správa výrobců produktů'
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
     * ACL name='Tabulka výrobců produtků'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'ProductProducer:edit');
        if ($action)
            $action->setIcon('pencil')
                    ->setTitle('Úprava')
                    ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit výrobce'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit výrobce produtků', 'success'], ['Nepodařilo se uložit výrobce produktů!', 'warning']);
        $form->setRedirect(':ProductProducer:default');
        return $form;
    }

}
