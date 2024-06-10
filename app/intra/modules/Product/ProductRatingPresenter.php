<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\ProductRatingFacade;
use Intra\Model\Facade\ProductFacade;

class ProductRatingPresenter extends BaseIntraPresenter {

    /** @var ProductRatingFacade @inject */
    public $facade;

    /** @var ProductFacade @inject */
    public $productFac;

    /**
     * ACL name='Správa hodnocení produktů'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id, $idProduct, $returnTo) {
        if ($idProduct) {
            $this['form']->setDefaults(['product' => $idProduct]);
            $this->template->product = $this->facade->gEMProduct()->find($idProduct);
        } else if ($id) {
            $rating = $this->facade->get()->find($id);
            $this['form']->setDefaults($rating->toArray());
            $this['form']->setDefaults(['returnTo' => $returnTo]);
            $this->template->returnTo = $returnTo;
            $this->template->product = $rating->product;
        } else {
            $this->flashMessage('Nepodařilo se zvolit produkt!', 'warning');
            $this->redirect(':Product:default');
        }
    }

    public function renderAll() {
        $this->template->ratings = $this->facade->get()->findBy(['approved' => false], ['foundedDate' => 'ASC']);
    }

    public function renderDefault($id) {
        if ($id) {
            $this->template->product = $this->facade->gEMProduct()->find($id);
            $qb = $this->doctrineGrid->createQueryBuilder($this->facade->entity(), ['product' => $id]);
            $this['table']->getGrid()->setDataSource($qb);
        } else {
            $this->flashMessage('Nepodařilo se zvolit produkt!', 'warning');
            $this->redirect(':Product:default');
        }
    }

    /**
     * ACL name='Tabulka hodnocení produtku'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'ProductRating:edit');
        if ($action)
            $action->setIcon('pencil')
                    ->setTitle('Úprava')
                    ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit hodnocení produktu'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this, __FUNCTION__);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formSuccess'];
        $form->addHidden('returnTo');
        return $form;
    }

    public function formSuccess($form, $values) {
        // ukládám formulář  pomocí automatického save
        $rating = $this->formGenerator->processForm($form, $values, true);
        $this->productFac->recountRating($rating->product->id);
        $this->flashMessage('Podařilo se uložit hodnocení produtků', 'success');

        if ($values['returnTo'])
            $this->redirect(':ProductRating:' . $values['returnTo']);
        else
            $this->redirect(':ProductRating:default', ['id' => $rating->product->id]);
    }

    public function handleApprove($idRating) {
        $rating = $this->facade->approveRating($idRating);
        $this->productFac->recountRating($rating->product->id);
        if (count($rating)) {
            $this->flashMessage('Hodnocení se podařilo schválit.', 'success');
        } else {
            $this->flashMessage('Hodnocení se nepodařilo schválit!', 'warning');
        }
        $this->redirect('this');
    }

    public function handleDelete($idRating) {
        $rating = $this->facade->deleteRating($idRating);
        $this->productFac->recountRating($rating->product->id);
        if ($rating) {
            $this->flashMessage('Pořadilo se položku smazat');
        } else {
            $this->flashMessage('Nepodařilo se položku smazat!', 'warning');
        }
        $this->redirect('this');
    }

}
