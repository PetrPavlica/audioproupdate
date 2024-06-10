<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\CountryFacade;

class CountryPresenter extends BaseIntraPresenter {

    /** @var CountryFacade @inject */
    public $facade;

    /**
     * ACL name='Správa států - sekce'
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
     * ACL name='Tabulka s všech států'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'Country:edit');
        if ($action)
            $action->setIcon('pencil')
                    ->setTitle('Úprava')
                    ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit států'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit stát', 'success'], ['Nepodařilo se uložit stát!', 'warning']);
        $form->setRedirect(':Country:default');
        return $form;
    }

}
