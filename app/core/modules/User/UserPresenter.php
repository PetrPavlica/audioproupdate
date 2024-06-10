<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use App\Core\Model\Facade\UserFacade;

class UserPresenter extends BaseIntraPresenter {

    /** @var UserFacade @inject */
    public $userFac;

    /**
     * ACL name='Správa uživatelů'
     * ACL rejection='Nemáte přístup k správě uživatelů.'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidání nového uživatele'
     */
    public function renderEdit($id) {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);
        if ($id) {
            $entity = $this->userFac->get()->find($id);
            $arr = $entity->toArray();
            $this['form']->setDefaults($arr);
        }
    }

    /**
     * ACL name='Tabulka s přehledem uživatelů'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->userFac->entity(), $this->user, get_class(), __FUNCTION__, ['isHidden' => '0']);
        $action = $grid->addAction('edit', '', ':User:edit');
        if ($action)
            $action->setIcon('pencil')
                    ->setTitle('Úprava')
                    ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/editaci uživatelů'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation($this->userFac->entity(), $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit uživatee', 'success'], ['Nepodařilo se uživatele uložit!', 'warning']);
        $form->setRedirect(':User:default');

        $form->onSuccess[] = [$this, 'processFormUser'];
        return $form;
    }

    public function processFormUser($form, $values) {
        if (isset($values['password'])) {
            if ($values['password'] != '')
                $values['password'] = $this->userFac->hash($values['password']);
            else
                unset($values['password']);
        }
        $this->formGenerator->processForm($form, $values, true);
    }

}
