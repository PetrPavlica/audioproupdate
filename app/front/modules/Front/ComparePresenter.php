<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Front\Model\Facade\CustomerFrontFacade;
use Intra\Components\MailSender\MailSender;
use Intra\Components\PDFPrinter\PDFPrinterControl;

class ComparePresenter extends BaseFrontPresenter
{

    protected function startup()
    {
        parent::startup();
        // $this->sess = $this->session->getSection('front');
    }

    public function renderList()
    {
        // unset($this->sess->compareProduct);
        if (isset($this->sess->compareProduct)) {
            $this->template->products = $products = $this->facade->gEMProduct()->findBy(['id' => $this->sess->compareProduct]);
            $category = [];
            foreach ($products as $product) {
                if (isset($product->categories[0]->category->mainCategory->id)) {
                    $category[$product->categories[0]->category->mainCategory->id] = $product->categories[0]->category->mainCategory;
                }
            }

            $data = $this->searchFac->getDataForCompare($category, $products, $this->locale);
            $this->template->categories = $category;
            $this->template->cData = $data[ 'data' ];
            $this->template->filters = $data[ 'filters' ];
        }
    }

    public function handleRemoveProduct($productId)
    {
        if (isset($this->sess->compareProduct[ $productId ])) {
            unset($this->sess->compareProduct[ $productId ]);
        }
        $this->flashMessage('Produkt byl odebrán ze srovnávání', 'info');
        $this->redirect('this');
    }

    public function createComponentAddProductForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('searchTerm')
            ->setAttribute('placeholder', 'Zadejte hledané slovo...');
        $form->addSubmit('send', '');
        $form->onSuccess[] = [$this, 'addProductSuccess'];
        return $form;
    }

    public function handleAddProductToCompare($id)
    {
        if (!isset($this->sess->compareProduct)) {
            $this->sess->compareProduct = [];
        }
        $this->sess->compareProduct[ $id ] = $id;

        $this->flashMessage('Produkt byl přidán do srovnání', 'info');
        $this->redirect('this');
        // $this->redirect(':ProductList:list', ['searchTerm' => $values2[ 'textsearch_atcmplt' ]]);
    }

    public function addProductSuccess($form, $values)
    {
        $values2 = $this->request->getPost();

        if (!isset($this->sess->compareProduct)) {
            $this->sess->compareProduct = [];
        }
        $this->sess->compareProduct[ $values2[ 'ico-autocomplete-val' ] ] = $values2[ 'ico-autocomplete-val' ];

        $this->flashMessage('Produkt byl přidán do srovnání', 'info');
        $this->redirect('this');
        // $this->redirect(':ProductList:list', ['searchTerm' => $values2[ 'textsearch_atcmplt' ]]);
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
