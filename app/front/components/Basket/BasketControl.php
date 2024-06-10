<?php

namespace Front\Components\Basket;

use Front\Model\Facade\OrderProcessFacade;
use Intra\Model\Database\Entity\Currency;
use Nette\Application\UI;
use Front\Model\Facade\FrontFacade;

class BasketControl extends UI\Control
{

    /** @var FrontFacade */
    protected $facade;

    /** @var OrderProcessFacade */
    protected $orderFacade;

    /** @var array */
    protected $currency;

    public function __construct(FrontFacade $facade, OrderProcessFacade $orderProcessFacade)
    {
        $this->facade = $facade;
        $this->orderFacade = $orderProcessFacade;
        parent::__construct();
    }

    public function init($currency)
    {
        $this->currency = $currency;

    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/a.latte');

        // render template
        $template->render();
    }

    public function handleAddToCart($productId, $pojisteni = 0.0, $nastaveni = 0.0, $enableAction = true)
    {
        if (!is_numeric($productId)) {
            return;
        }
        if (!isset($this->sess->basket)) {
            $this->sess->basket = [];
        }

        // If user is logged, create not completed order
        if ($this->isLoginCustomer()) {
            if (!isset($this->sess->orderId)) {
                $orderNotFinish = $this->orderFacade->createNotFinishedOrder(
                    $this->user->getId(),
                    $this->sess->actualCurrency[ "code" ]);
                $this->sess->orderId = $orderNotFinish->id;
            } else {
                $orderNotFinish = $this->facade->gEMOrders()->find($this->sess->orderId);
            }
        }

        $product = $this->facade->gEMProduct()->find($productId);
        //$currency = $this->facade->gEMCurrency()->findOneBy(['code' => $this->sess->actualCurrency[ "code" ]]);

        if (count($product)) {

            if (isset($this->sess->basket[ $product->id ])) {

                $count = $this->sess->basket[ $product->id ][ 'countItems' ] + 1;
                $this->sess->basket[ $product->id ][ 'countItems' ] = $count;

                /* if ($this->isLoginCustomer()) {
                     $this->orderFacade->addNewProduct($orderNotFinish, $product, $currency, $count);
                 }*/
            } else {

                $this->sess->basket[ $product->id ] = $product->toArray();
                $this->productHelper->setProduct($product, $this->sess->actualCurrency);
                $this->sess->basket[ $product->id ][ 'selingPrice' ] = $this->productHelper->getPrice();

                $count = 1;
                $this->sess->basket[ $product->id ][ 'countItems' ] = $count;

                /* if ($this->isLoginCustomer()) {
                     $this->orderFacade->addNewProduct($orderNotFinish, $product, $currency, 1);
                 }*/
            }

            $this->sess->basket[ $product->id ][ 'pojisteni' ] = $pojisteni;
            $this->sess->basket[ $product->id ][ 'nastaveni' ] = $nastaveni;

            /*if ($pojisteni > 0) {
                $this->orderProcFacade->addPojisteni($orderNotFinish, $product, $count);
            }

            if ($nastaveni > 0) {
                $this->orderProcFacade->addPojisteni($orderNotFinish, $product, $count);
            }*/

            if ($enableAction) {
                $this->flashMessage('Produkt byl přidán do košíku!', 'success');
            }

        } else {
            if ($enableAction) {
                $this->flashMessage('Produkt se nepodařilo přidat do košíku!', 'warning');
                $this->redirect('this');
            } else {
                return false;
            }
        }

// Recalculate basket sums
        //     $this->recalculateBasket();

        /* if ($this->isLoginCustomer()) {
             $this->orderFacade->synchronizeNonFinishedOrder($this->user->getId(), $this->sess);
         }*/

        if ($enableAction) {
            $template = $this->templateFactory->createTemplate();
            $template->product = $product;
            $template->count = $this->sess->basket[ $product->id ][ 'countItems' ];
            $template->enforceFreeDelivery = isset($this->sess->enforceFreeDelivery) ? $this->sess->enforceFreeDelivery : false;

            $template->freeDelivery = $this->freeTransportFactory->create();

            $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
            $template->locale = $this->locale;
            $template->productHelper = $this->productHelper;
            $template->setFile(__DIR__ . '/templates/insertedIntoBasket.latte');
            $template->basePath = $this->getBasePath();

            $template->actualCurrency = $this->sess->actualCurrency;
            $template->settings = $this->facade->getAllCashSettings();
            $template->basketSum = $this->sess->basketSum;

            $this->payload->completed = 1;
            $this->payload->data = $template . '';

            if ($this->isAjax()) {
                $this->redrawControl('basket-snipp');
            } else {
                $this->redirect('this');
            }
        }
        return true;
    }
}
