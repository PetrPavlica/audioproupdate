<?php

namespace App\Presenters;

use Intra\Model\Facade\DpdAddressFacade;
use Nette;
use App\Core\Model\Database\Entity\PermisionItem;

class DpdAddressPresenter extends BaseIntraPresenter {

    /** @var DpdAddressFacade @inject */
    public $facade;

    /**
     * ACL name='Správa svozových adres pro DPD'
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
     * ACL name='Tabulka všech svozových adres pro DPD'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'DpdAddress:edit');
        if ($action)
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit svozové adresy'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);
        $form->setMessages(['Podařilo se uložit svozovou adresu', 'success'],
            ['Nepodařilo se uložit svozovou adresu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'dpdFormSuccess'];
        return $form;
    }

    public function dpdFormSuccess($form, $values)
    {
        $values2 = $this->getRequest()->getPost();
        // ukládám formulář  pomocí automatického save
        $this->formGenerator->processForm($form, $values, true);
        $this->redirect('DpdAddress:default');
    }
}
