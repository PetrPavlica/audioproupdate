<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Front\Model\Facade\CustomerFrontFacade;
use Intra\Components\MailSender\MailSender;
use Intra\Components\PDFPrinter\PDFPrinterControl;

class FavouritePresenter extends BaseFrontPresenter {

    public function renderList($id) {
        if ($id) {
            // Pojistka proti tomu, aby profil si mohl upravovat každý pouze svůj :)
            if (!($this->user->loggedIn) || $id != $this->user->getIdentity()->id) {
                $this->flashMessage('K této sekci nemáte přístup. Prosím přihlašte se.', 'warning');
                $this->redirect(':Front:default');
                exit;
            }
            $this->template->products = $this->facade->gEMFavouriteProduct()->findBy(['customer' => $this->getUser()->id]);
            barDump($this->template->products);
        }
    }

    public function handleRemoveFavourite($customerId, $productId) {
        if ($this->user->loggedIn && $this->user->isInRole('visitor') && $customerId == $this->getUser()->id) {
            $this->facade->removeFavourite($customerId, $productId);
            $this->flashMessage('Produkt byl smazán ze sekce Oblíbené', 'info');
            $this->redirect('this');
        } else {
            $this->flashMessage('K tomuto nemáte přístup!', 'warning');
        }
    }

    protected function createComponentAddToBasketForm()
    {
        return new Nette\Application\UI\Multiplier(function () {
            $form = new Nette\Application\UI\Form();
            $form->setTranslator($this->translator);

            $form->addHidden("productId");
            $form->addHidden('count');
            $form->addSubmit("addToBasket");

            $form->onSuccess[] = [$this, 'addToBasketSucc'];
            return $form;
        });
    }

    public function addToBasketSucc(Nette\Application\UI\Form $form, $values)
    {
        $this->handleAddToCart($values['productId']);
        $this->redirect('this');
    }

}
