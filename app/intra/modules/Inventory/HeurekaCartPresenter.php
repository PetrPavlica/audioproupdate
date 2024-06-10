<?php

namespace App\Presenters;

use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\HeurekaCartFacade;

class HeurekaCartPresenter extends BaseIntraPresenter
{

    /** @var HeurekaCartFacade @inject */
    public $facade;

    /**
     * ACL name='Správa Heuréka košíku'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $this[ 'form' ]->setDefaults($this->facade->get()->find($id)->toArray());
        }
    }

    /**
     * ACL name='Tabulka heuréka košíku'
     */
    public function createComponentTable()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(),
            __FUNCTION__);
        $action = $grid->addAction('edit', '', 'HeurekaCart:edit');
        if ($action) {
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        }
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit heuréka košíku'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);
        $form->setMessages(['Podařilo se uložit heuréka košík.', 'success'],
            ['Nepodařilo se uložit heuréka košík!', 'warning']);
        $form->setRedirect('HeurekaCart:default');
        return $form;
    }

}
