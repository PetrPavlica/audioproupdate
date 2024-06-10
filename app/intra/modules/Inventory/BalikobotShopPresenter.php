<?php

namespace App\Presenters;

use Intra\Model\Facade\BalikobotShopFacade;
use App\Core\Model\Database\Entity\PermisionItem;

class BalikobotShopPresenter extends BaseIntraPresenter
{

    /** @var BalikobotShopFacade @inject */
    public $facade;

    /**
     * ACL name='Správa Balikobot e-shopu'
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
     * ACL name='Tabulka balikobot e-shopu'
     */
    public function createComponentTable()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(),
            __FUNCTION__);
        $action = $grid->addAction('edit', '', 'BalikobotShop:edit');
        if ($action) {
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        }
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit balikobot e-shopu'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);
        $form->setMessages(['Podařilo se uložit shop balíkobotu', 'success'],
            ['Nepodařilo se uložit shop balíkobotu!', 'warning']);
        $form->setRedirect(':BalikobotShop:default');
        return $form;
    }

}
