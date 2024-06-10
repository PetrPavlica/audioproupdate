<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\LanguageFacade;

class LanguagePresenter extends BaseIntraPresenter {

    /** @var LanguageFacade @inject */
    public $facade;

    /**
     * ACL name='Správa jazyků - sekce'
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
     * ACL name='Tabulka všech jazyků'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'Language:edit');
        if ($action)
            $action->setIcon('pencil')
                    ->setTitle('Úprava')
                    ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit jazyků'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit jazyk', 'success'], ['Nepodařilo se uložit jazyk!', 'warning']);
        $form->setRedirect(':Language:default');
        return $form;
    }

}
