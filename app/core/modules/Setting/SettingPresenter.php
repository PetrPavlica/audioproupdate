<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use App\Core\Model\Facade\SettingFacade;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;

class SettingPresenter extends BaseIntraPresenter {

    /** @var SettingFacade @inject */
    public $facade;

    /** @var IStorage @inject */
    public $storage;

    /**
     * ACL name='Správa uživatelských nastavení'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id) {
        if ($id) {
            $this[ 'form' ]->setDefaults($this->facade->get()->find($id)->toArray());
        }
    }

    /**
     * ACL name='Tabulka všech uživatelských nastavení'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'Setting:edit');
        if ($action)
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit uživatelských nastavení'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit uživatelské nastavení', 'success'], ['Nepodařilo se uložit uživatelské nastavení!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'settingsFormSuccess'];
        return $form;
    }

    public function settingsFormSuccess($form, $values) {
        // Save form by automatic save
        $this->formGenerator->processForm($form, $values, true);

        // Clean cache
        $this->storage->clean([
            Cache::TAGS => ["settings"],
            Cache::PRIORITY => 100,
        ]);
        $this->redirect('Setting:default');
    }

}
