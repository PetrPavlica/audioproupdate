<?php

namespace App\Presenters;

use Intra\Model\Facade\ProductCategoryFacade;
use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\DiscountFacade;

class DiscountPresenter extends BaseIntraPresenter {

    /** @var DiscountFacade @inject */
    public $facade;

    /** @var ProductCategoryFacade @inject */
    public $productCatFac;

    /**
     * ACL name='Správa slev'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id) {
        if ($id) {
            $entity = $this->facade->get()->find($id);
            if (!$entity) {
                $this->flashMessage('Záznam nebyl nalezen.', 'warning');
                $this->redirect('Discount:');
            }
            $arr = $entity->toArray();
            $arr['categories'] = [];
            if ($entity->categories) {
                foreach ($entity->categories as $c) {
                    if (!$c->category) {
                        continue;
                    }
                    $arr['categories'][$c->category->id] = $c->category->id;
                }
            }
            $arr['productMarks'] = [];
            if ($entity->productMarks) {
                foreach ($entity->productMarks as $p) {
                    if (!$p->productMark) {
                        continue;
                    }
                    $arr['productMarks'][$p->productMark->id] = $p->productMark->id;
                }
            }
            $this['form']->setDefaults($arr);
        }
    }

    /**
     * ACL name='Tabulka s všech slev'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'Discount:edit');
        if ($action)
            $action->setIcon('pencil')
                    ->setTitle('Úprava')
                    ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit slev'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit slevu.', 'success'], ['Nepodařilo se uložit slevu!', 'warning']);
        $form->setRedirect('Discount:default');

        $form->getComponent('categories')->setItems($this->productCatFac->getSelectBoxCategoryAll());

        return $form;
    }

}
