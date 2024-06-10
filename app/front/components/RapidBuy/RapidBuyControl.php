<?php

namespace Front\Components\RapidBuy;

use Front\Components\FreeTransportRemains\IFreeTransportRemainsFactory;
use Intra\Model\Utils\DPHCounter;
use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\ITemplateFactory;
use Front\Model\Facade\FrontFacade;
use Kdyby\Translation\Translator;
use Intra\Components\MailSender\MailSender;
use Front\Model\Facade\OrderProcessFacade;
use Intra\Model\Facade\OrderFacade;
use Intra\Model\Facade\BalikobotShopFacade;
use Intra\Model\Utils\ProductHelper\ProductHelper;

class RapidBuyControl extends UI\Control {

    /** @var Translator */
    public $translator;

    /** @var ITemplateFactory */
    public $templateFactory;

    /** @var LinkGenerator */
    public $linkGenerator;

    /** @var FrontFacade */
    public $facade;

    /** @var OrderProcessFacade */
    public $orderProcFacade;

    /** @var OrderFacade */
    public $orderFacade;

    /** @var MailSender */
    public $mailSender;

    /** @var IFreeTransportRemainsFactory */
    public $freeTransportFactory;

    /** @var BalikobotShopFacade @inject */
    public $balikobotFacade;

    /** @var ProductHelper @inject */
    public $productHelper;

    public function __construct(Translator $translator, ITemplateFactory $templateFactory, LinkGenerator $linkGenerator,
        FrontFacade $facade, OrderProcessFacade $orderProcFacade, OrderFacade $orderFacade, MailSender $mailSender,
        IFreeTransportRemainsFactory $freeTransportFactory, BalikobotShopFacade $balikobotShopFacade,
        ProductHelper $productHelper) {
        $this->templateFactory = $templateFactory;
        $this->linkGenerator = $linkGenerator;
        $this->facade = $facade;
        $this->translator = $translator;
        $this->orderFacade = $orderFacade;
        $this->orderProcFacade = $orderProcFacade;
        $this->mailSender = $mailSender;
        $this->freeTransportFactory = $freeTransportFactory;
        $this->balikobotFacade = $balikobotShopFacade;
        $this->productHelper = $productHelper;
        parent::__construct();
    }

    public function createComponentFreeTransport() {
        return $this->freeTransportFactory->create();
    }

    public function render() {

        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/layout.latte');

        $template->actualCurrency = $this->parent->getSess()->actualCurrency;
        $template->freeDelivery = $this->facade->getAllCashSettings()[ "delivery_free" ];

        $template->currency = $this->parent->getSess()->actualCurrency;

        $template->settings = $this->facade->getAllCashSettings();
        $this->parent->getSess()->basketSum;

        $template->productHelper = $this->productHelper;

        // render template
        $template->render();
    }

    public function handleBuy($productId, $customerId) {
        if ($this->parent->getUser()->loggedIn && $this->parent->getUser()->roles[ 0 ] == 'visitor' && $this->parent->getUser()->id == $customerId) {

            $this->template->rapidCustomer = $customer = $this->facade->gEMCustomer()->find($customerId);

            $productToBuy = $this->facade->gEMProduct()->find($productId);

            $actualCurrency = $this->parent->getSess()->actualCurrency;

            $this->productHelper->setProduct($productToBuy, $actualCurrency);

            $sellingPrice = $this->productHelper->getPrice();

            $settings = $this->facade->getAllCashSettings();
            $freeDelivery = false;

            if (($settings[ "delivery_free" ] / $actualCurrency['exchangeRate'] - $sellingPrice) <= 0) {
                $freeDelivery = true;
            }

            $delMethods = $this->facade->getDeliveryMethodDropSource($actualCurrency, $freeDelivery);
            $payMethods = $this->facade->getPayMethodDropSource($actualCurrency, $freeDelivery);

            $this[ 'rapidBuyForm' ]->components[ 'payMethod' ]->setItems($payMethods[ 'data' ]);
            $this[ 'rapidBuyForm' ]->components[ 'deliveryMethod' ]->setItems($delMethods[ 'data' ]);

            $this->template->payMethods = $payMethods[ 'price' ];
            $this->template->delMethods = $delMethods[ 'price' ];

            $this->template->productToBuy = $productToBuy;
            $this->template->delMethodDPD = $this->facade->gEMDeliveryMethod()->findOneBy(['isDPD' => 1]);
            $this->template->delMethodUlozenka = $this->facade->gEMDeliveryMethod()->findOneBy(['isUlozenka' => 1]);

            /*if ($this->template->delMethodUlozenka) {
                $this->template->ulozenkaPickups = $this->balikobotFacade->getUlozenkaPickups();
            }*/
            $this->template->dpdPickups = $this->balikobotFacade->getDPDPickups();

            $this[ 'rapidBuyForm' ]->setDefaults([
                'product' => $productId,
                'id' => $customerId,
                'currency' => $actualCurrency[ 'id' ],
            ]);

            $this->template->customer = $customer;

            if ($customer->paymentMethod) {
                $this[ 'rapidBuyForm' ]->setDefaults(['payMethod' => $customer->paymentMethod->id]);
            }
            if ($customer->deliveryMethod) {
                $this[ 'rapidBuyForm' ]->setDefaults(['deliveryMethod' => $customer->deliveryMethod->id]);
            }

            $this->redrawControl('handle-snipp');
            $this->redrawControl('rapid-buy-snipp');

            $this->template->rapidBuy = true;
            if (!$this->parent->isAjax()) {
                $this->parent->redirect('this');
            }
        } else {
            if ($this->parent->getUser()->loggedIn && $this->getUser()->roles[ 0 ] != 'visitor')
                $this->parent->flashMessage('Administrátor nemůže provést akci koupit zrychleně', 'info');
            else
                $this->parent->flashMessage('Operace se nezdařila', 'warning');
        }

        $this->parent->payload->completed = 1;
    }

    public function createComponentRapidBuyForm() {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addSelect('payMethod', 'Způsob placení')
            ->setAttribute('class', 'nice_select full_size borderlight high arrow0');
        $form->addSelect('deliveryMethod', 'Metoda dodání')
            ->setAttribute('class', 'nice_select full_size borderlight high arrow0');

        $form->addSelect('dpdPickup', 'Metoda dodání')
            ->setAttribute('class', 'nice_select full_size borderlight high arrow0');

        $form->addSelect('ulozenkaPickup', 'Metoda dodání')
            ->setAttribute('class', 'nice_select full_size borderlight high arrow0');

        $form->addSubmit('send', 'Odeslat objednávku');
        $form->addHidden('id');
        $form->addHidden('currency');
        $form->addHidden('product');
        $form->addHidden('isRapid')->setDefaultValue(1);
        $form->onSuccess[] = [$this, 'rapidBuyFormSuccess'];
        return $form;
    }

    public function rapidBuyFormSuccess($form, $values)
    {
        $actualCurrency = $this->parent->getSess()->actualCurrency;
        $values2 = $this->parent->request->getPost();
        if ($values2['deliveryMethod'] == 5 && $values2['payMethod'] == 1 && $actualCurrency['code'] != 'CZK') {
            $this->parent->flashMessage('Nelze pokračovat s vybranou dodací metodou a platební metodou v této měně!', 'warning');
        } else {
            $customer = $this->facade->gEMCustomer()->find($values['id']);

            $product = $this->facade->gEMProduct()->find($values['product']);
            $this->productHelper->setProduct($product, $actualCurrency);

            $price = $this->productHelper->getPrice();

            if (isset($values2["pojisteni"])) {
                $values2["pojisteni_all"][$values['product']] = ($price * 0.1) * $values2["count"][$values['product']];
                $values2["pojisteni_one"][$values['product']] = $price * 0.1;
            }

            if (isset($values2["nastaveni"])) {
                $values2["nastaveni_all"][$values['product']] = ($product->priceInstallSettingAdd / $actualCurrency['exchangeRate']) * $values2["count"][$values['product']];
                $values2["nastaveni_one"][$values['product']] = $product->priceInstallSettingAdd / $actualCurrency['exchangeRate'];
            }

            $dphCounter = new DPHCounter();
            $dphCounter->setPriceWithDPH($price, $product->vat->value, $values2["count"][$values['product']]);

            $values2["cena_celkem_bez_dph"] = $dphCounter->getTotalWithoutDPH();
            $values2["cena_zbozi_s_dph"] = $dphCounter->getTotalPrice();
            $values2["cena_celkem_se_slevou"] = $dphCounter->getTotalPrice();

            $res = $this->orderProcFacade->createOrder($values2);

            $this->parent->getSess()->orderFinish = [
                'price' => $res['priceBezDPH'],
                'variableSymbol' => $res['order']->variableSymbol,
                'dph' => $res['dph'],
                'delivery' => $res['delivery'],
                'products' => $res['products'],
                'order' => $res['order']
            ];

            if ($res['resetPwd']) { // Generate new password for user
                $this->mailSender->sendCustomerCredential($res['resetPwd']);
            }
            $url = null;
            if (count($res['order'])) {
                $this->mailSender->sendCreationOrder($res['order']);
                if($res['order']->paymentMethod->id == 2) {
                    $this->orderFacade->swapState($res['order'], 1);
                } else {
                    $this->orderFacade->swapState($res['order'], 'noWaitPay');
                }

                $this->parent->flashMessage('Objednávka byla úspěšně přijatá. O jejím stavu Vás budeme informovat na uvedeném emailu.',
                    'success');
                if ($url) {
                    $this->parent->redirectUrl($url);
                } else {
                    $this->parent->redirect(":Front:default");
                }
            } else {
                $this->parent->flashMessage('Něco se pokazilo. Prosím zkuste založit objednávku znovu.', 'warning');
            }
        }
    }

}
