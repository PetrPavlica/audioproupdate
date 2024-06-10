<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\CurrencyFacade;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;

class CurrencyPresenter extends BaseIntraPresenter {

    /** @var CurrencyFacade @inject */
    public $facade;

    /** @var IStorage @inject */
    public $storage;

    /**
     * ACL name='Správa měny - sekce'
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
     * ACL name='Tabulka s všech měn'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'Currency:edit');
        if ($action)
            $action->setIcon('pencil')
                    ->setTitle('Úprava')
                    ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit měn'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit měnu', 'success'], ['Nepodařilo se uložit měnu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'currencyFormSuccess'];
        return $form;
    }

    public function currencyFormSuccess($form, $values) {
        // ukládám formulář  pomocí automatického save
        $setting = $this->formGenerator->processForm($form, $values, true);
        // clean cache
        $this->storage->clean([
            Cache::TAGS => ["currency"],
        ]);
        $this->redirect(':Currency:default');
    }

    /**
     * ACL name='Aktualizace kurzů dle ČNB akce'
     */
    public function handleActualizeExchangeRates() {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

        $messages = $this->facade->checkActualExchangeRates();
        if (count($messages)) {
            foreach ($messages as $m) {
                $this->flashMessage($m, 'warning');
            }
        } else
            $this->flashMessage('Všechny měny byly úspěšně aktualizovany dle ČNB', 'success');

        $this->storage->clean([
            Cache::TAGS => ["currency"],
        ]);
        $this->redirect('this');
    }

}
