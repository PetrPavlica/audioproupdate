<?php

namespace App\Presenters;

use Nette;
use Nette\Utils\DateTime;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Components\MailSender\MailSender;
use Intra\Model\Facade\ProductDiscussionFacade;

class ProductDiscussionPresenter extends BaseIntraPresenter {

    /** @var ProductDiscussionFacade @inject */
    public $facade;

    /** @var MailSender @inject */
    public $mailSender;

    /**
     * ACL name='Správa diskuze produktů'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id, $idProduct, $idReplayed, $returnTo) {
        if ($idReplayed && $idProduct) { // Odpovídá na otázku
            $product = $this->facade->gEMProduct()->find($idProduct);
            $parent = $this->facade->get()->find($idReplayed);
            $this[ 'form' ]->setDefaults([
                'parent' => $idReplayed,
                'product' => $idProduct,
                'user' => $this->user->id,
                'foundedDate' => date_format(new DateTime(), "d. m. Y"),
                'returnTo' => $returnTo
            ]);
            $this->template->parent = $parent;
            $this->template->product = $product;
        } else if ($id && $idProduct) { // Upravuje se
            $dis = $this->facade->get()->find($id);
            if ($dis->parent) {
                $this->template->parent = $dis->parent;
            }
            $this[ 'form' ]->setDefaults($dis->toArray());
            $this[ 'form' ]->setDefaults([
                'user' => $this->user->id,
                'returnTo' => $returnTo
            ]);
            $this->template->product = $dis->product;
        } else {
            $this->flashMessage('Nepodařilo se zvolit produkt!', 'warning');
            $this->redirect(':Product:default');
        }
    }

    public function renderDefault($id, $onlyNonReply = false) {
        if ($id) {
            $this->template->product = $this->facade->gEMProduct()->find($id);
            $this->template->discussions = $this->facade->get()->findBy(['product' => $id, 'parent' => NULL], ['foundedDate' => 'ASC']);
            $this->template->onlyNonReply = $onlyNonReply;
        } else {
            $this->flashMessage('Nepodařilo se zvolit produkt!', 'warning');
            $this->redirect(':Product:default');
        }
    }

    public function renderAll() {
        $this->template->discussions = $this->facade->get()->findBy(['parent' => NULL], ['foundedDate' => 'DESC']);
    }

    /**
     * ACL name='Tabulka diskuze produtku'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'ProductDiscussion:edit');
        if ($action)
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit diskuze produktu'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this, __FUNCTION__);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formSuccess'];
        $form->addHidden('returnTo');
        return $form;
    }

    public function formSuccess($form, $values) {
        $emailSend = true;

        if ($values->id) {
            $emailSend = false;
        }

        // ukládám formulář  pomocí automatického save
        $discussion = $this->formGenerator->processForm($form, $values, true);
        $this->flashMessage('Podařilo se uložit diskuzi produtku', 'success');

        if ($emailSend) { // Jedná se o odpověď na dotaz - informuji zákazníka
            $this->mailSender->sendAnswerBlog($discussion);
            $this->flashMessage('Zákazník byl informován o odpovědi emailem.', 'success');
        }

        if ($values[ 'returnTo' ])
            $this->redirect(':ProductDiscussion:' . $values[ 'returnTo' ]);
        else
            $this->redirect(':ProductDiscussion:default', ['id' => $discussion->product->id]);
    }

    public function handleDelete($id) {
        $res = $this->facade->deleteDiscussion($id);
        if ($res) {
            $this->flashMessage('Pořadilo se položku smazat');
        } else {
            $this->flashMessage('Nepodařilo se položku smazat!', 'warning');
        }
        $this->redirect('this');
    }

}
