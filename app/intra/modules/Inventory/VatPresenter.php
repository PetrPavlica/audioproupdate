<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\VatFacade;

class VatPresenter extends BaseIntraPresenter {

    /** @var VatFacade @inject */
    public $facade;

    /**
     * ACL name='Správa sazeb DPH'
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
     * ACL name='Tabulka s všech sazeb DPH'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'Vat:edit');
        if ($action)
            $action->setIcon('pencil')
                    ->setTitle('Úprava')
                    ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit sazeb DPH'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit sazbu DPH', 'success'], ['Nepodařilo se uložit sazbu DPH!', 'warning']);
        $form->setRedirect(':Vat:default');
        return $form;
    }

}
