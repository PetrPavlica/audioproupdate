<?php

namespace App\Presenters;

use Front\Components\HomeCredit\HomeCredit;
use Front\Model\Utils\Text\UnitParser;
use Intra\Model\Database\Entity\Discount;
use Intra\Model\Database\Entity\Orders;
use Intra\Model\Database\Entity\Product;
use Intra\Model\Database\Entity\ProductInOrder;
use Intra\Model\Facade\BalikobotShopFacade;
use Nette\Application\UI\Form;
use Intra\Components\MailSender\MailSender;
use Front\Model\Facade\OrderProcessFacade;
use Intra\Model\Facade\OrderFacade;
use Intra\Model\Facade\ProductFacade;
use Intra\Model\Utils\ThePay;
use Intra\Model\Utils\ProductHelper\ProductHelper;
use Tracy\Debugger;

class BasketPresenter extends BaseFrontPresenter
{
    /** @var OrderProcessFacade @inject */
    public $orderProcFacade;

    /** @var OrderFacade @inject */
    public $orderFacade;

    /** @var ProductFacade @inject */
    public $prodFac;

    /** @var BalikobotShopFacade @inject */
    public $balikobotFacade;

    /** @var MailSender @inject */
    public $mailSender;

    /** @var ThePay @inject */
    public $thePay;

    /** @var HomeCredit @inject */
    public $homeCredit;

    /** @var ProductHelper @inject */
    public $productHelper;

    public function startup()
    {
        parent::startup();
        if ($this->disableEshop) {
            $this->redirect('Front:');
        }
        $this->basketFacade->changeCurrencyOnOrder($this->sess, $this->sess->actualCurrency);
    }

    public function beforeRender()
    {
        parent::beforeRender();
        /** @var array GARemarketing - kódy pro remarketing Google */
        $this->template->GARemarketing = [
            'page' => 'cart',
        ];
        $this->template->unitParser = new UnitParser();
        $this->template->basket = true;
        $this->template->helper = $this->productHelper;
        $this->template->specialView = $this->sess->specialView;
    }

    public function createComponentOrderForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addSubmit('send', 'K rekapitulaci');
        $form->addHidden('currency');
        $form->addHidden('expeditionToday');
        $form->addHidden('id');
        $form->addHidden('freeDelivery');
        $form->addText('name')
            ->setRequired('Prosím zadejte své jméno')
            ->setAttribute('placeholder', 'Jméno');
        $form->addText('surname')
            ->setRequired('Prosím zadejte své příjmení')
            ->setAttribute('placeholder', 'Příjmení');
        $form->addText('street')
            ->setAttribute('placeholder', 'Ulice a č. p.')
            ->setRequired('Prosím vyplňte svou ulici.');
        $form->addText('city')
            ->setAttribute('placeholder', 'Město')
            ->setRequired('Prosím vyplňte své město.');
        $form->addText('zip')
            ->setAttribute('placeholder', 'PSČ')
            ->setRequired('Prosím vyplňte své PSČ.');
        $form->addSelect('country', 'Stát', ['CZ' => 'Česká republika', 'SK' => 'Slovensko'])
            ->setRequired('Toto pole je povinné.')
            ->setDefaultValue($this->locale == 'cs' ? 'CZ' : 'SK');

        $form->addText('company')
            ->setAttribute('placeholder', 'Název firmy');
        $form->addText('idNo')
            ->setAttribute('placeholder', 'IČO');
        $form->addText('vatNo2')
            ->setAttribute('placeholder', 'IČO pro plátce DPH')
            ->setRequired(false)
            ->addRule(Form::PATTERN, 'IČO pro plátce DPH musí být ve formátu "CZ9999999999"', '([a-zA-Z]{2}\d{4}\d*)');
        $form->addText('vatNo')
            ->setAttribute('placeholder', 'DIČ')
            ->setRequired(false)
            ->addConditionOn($form['country'], Form::EQUAL, 'CZ')
                ->addRule(Form::PATTERN, 'DIČ musí být ve formátu "CZ9999999999"', '([a-zA-Z]{2}\d{4}\d*)')
            ->elseCondition()
                ->addRule(Form::PATTERN, 'DIČ musí být ve formátu "9999999999"', '(\d{4}\d*)')
            ->endCondition();
        $form->addText('phone')
            ->setRequired('Toto pole je povinné.')
            ->setAttribute('placeholder', 'Telefon');
        $form->addText('nameDelivery')
            ->setAttribute('placeholder', 'Jméno');
        $form->addText('surnameDelivery')
            ->setAttribute('placeholder', 'Příjmení');
        $form->addText('streetDelivery')
            ->setAttribute('placeholder', 'Ulice a č. p.');
        $form->addText('cityDelivery')
            ->setAttribute('placeholder', 'Město');
        $form->addText('zipDelivery')
            ->setAttribute('placeholder', 'PSČ');
        $form->addSelect('countryDelivery', 'Stát', ['CZ' => 'Česká republika', 'SK' => 'Slovensko'])
            ->setRequired('Toto pole je povinné.');
        $form->addText('email')
            ->setRequired('Prosím zadejte svůj email.')
            ->addRule(Form::EMAIL, 'Prosím zadejte platný formát emailu.')
            ->setAttribute('placeholder', 'Email');
        $form->addCheckbox('newsletter')
            ->setAttribute('class', 'icheck icheck_blue');
        $form->addTextArea('comment');
        $form->onSuccess[] = [$this, 'orderFormSuccess'];
        return $form;
    }

    public function orderFormSuccess($form, $values)
    {
        $values2 = $this->getRequest()->getPost();

        $values2['idNo'] = trim($values2['idNo']);
        $values2['vatNo'] = trim($values2['vatNo']);
        $values2['vatNo2'] = trim($values2['vatNo2']);

        $order = $this->basketFacade->addOrderInformation($this->sess, $this->getUser(), $values2);
        if (isset($values2[ "newsletter" ])) // pokud je newsletter, tak přidám email do db newsletteru
        {
            $this->facade->addNewsletterEmail(['email' => $values2[ "email" ]]);
        }
        $this->redirect('Basket:recap');
    }

    public function createComponentRecapForm()
    {
        $form = new Form;
        $form->onSuccess[] = [$this, 'recapFormSuccess'];
        return $form;
    }

    public function recapFormSuccess($form, $values)
    {
        $order = $this->basketFacade->finishOrder($this->sess);
        if (!$order) {
            $this->redirect('Basket:one');
        }
        if (isset($this->sess->needResetPassword) && $this->sess->needResetPassword) { // Generate new password for user
            $this->mailSender->sendCustomerCredentialOrder($order->customer);
            unset($this->sess->needResetPassword);
        }

        $productsInOrder = $this->basketFacade->getProductsInOrder($order, $this->sess, $order->currency->toArray());
        $prices = $productsInOrder['totalPrices'];
        $this->sess->orderFinish = [ // Pro konverzní kódy
            'price' => $prices[ 'totalWithoutDPHWithSale' ],
            'variableSymbol' => $order->variableSymbol,
            'dph' => $prices[ 'totalWithSale' ] - $prices[ 'totalWithoutDPHWithSale' ],
            'delivery' => $prices[ 'totalWithSaleWithDeliPay' ] - $prices[ 'totalWithSale' ],
            'products' => $productsInOrder['products'],
            'order' => $order,
        ];

        $url = null;

        if ($order) {
            $this->mailSender->sendCreationOrder($order->id);

            if ($order->paymentMethod) {

                if ($order->paymentMethod->thePay) {
                    try {
                        $url = $this->thePay->createPayment($order);
                    } catch (\Exception $ex) {
                        Debugger::log($ex);
                        $this->flashMessage('Při vytváření platby na platební bráně došlo k chybě.', 'error');
                    }

                } elseif ($order->paymentMethod->homeCredit) {
                    $this->sess->homecreditOrder = $order->id;
                    $url = $this->homeCredit->iShop($order, $productsInOrder);
                    if (!$url) {
                        $this->flashMessage('Během zpracování objednávky na HomeCredit došlo k chybě. Kontaktujte prodejce.', 'error');
                    }
                } elseif($order->paymentMethod->id == 2) {
                    $this->orderFacade->swapState($order, 1);
                } else {
                    $this->orderFacade->swapState($order, 'noWaitPay');
                }
            } else {
                $this->orderFacade->swapState($order, 'noWaitPay');
            }
            unset($this->sess->basketOrder);

            if ($url) {
                $this->redirect('Basket:wait', ['toUrl' => $url]);
            } else {
                $text = 'Objednávka byla úspěšně přijatá.';
                $this->flashMessage('Objednávka byla úspěšně přijatá.',
                    'success');
                $this->redirect('Basket:end', ['id' => $order->id, 'text' => $text]);
            }
        } else {
            $this->flashMessage('Něco se pokazilo. Prosím zkuste založit objednávku znovu.', 'warning');
        }
    }

    public function renderOne()
    {
        if ($this->isAjax()) {  // Oprava chyby v nette 2.4 se snippetem includovaným v části formu
            $template = $this->getTemplate();
            $template->getLatte()->addProvider('formsStack', [$this[ 'orderForm' ]]);
        }
        $t = $this->template;
        if ($this->sess->basketOrder) {
            $order = $this->facade->gem(Orders::class)->find($this->sess->basketOrder);
            if (!$order) {
                unset($this->sess->basketOrder);
                return;
            }
        } else {
            return;
        }
        $t->order = $order;
        $data = $this->basketFacade->getProductsInOrder($order, $this->sess, $this->sess->actualCurrency);
        $t->prices = $data[ 'totalPrices' ];
        $t->products = $products = $data[ 'products' ];
        if (!count($t->products)) {
            unset($t->basket);
        }

        $t->deliverySections = $sections = $this->facade->gEMDeliverySection()->findBy(['active' => '1'],
            ['orderState' => 'ASC']);
        $methods = [];
        foreach ($sections as $s) {
            $methods[ $s->id ] = $this->facade->gEMDeliveryMethod()->findBy(['active' => '1', 'section' => $s->id]);
        }
        $payMethods = $this->facade->gEMPaymentMethod()->findBy(['active' => '1'],
            ['orderState' => 'ASC']);
        if (($this->locale == 'cs' && $data[ 'totalPrices' ]['totalPrice'] < 1000)
            || ($this->locale == 'sk' && $data[ 'totalPrices' ]['totalPrice'] < 40)
            || $this->locale == 'sk') { // Blokace výpisu homecreditu na sk verzi - není implementováno
            foreach($payMethods as $k => $p) {
                if ($p->homeCredit) {
                    unset($payMethods[$k]);
                }
            }
        }
        $t->payMethods = $payMethods;
        $t->deliveryMethods = $methods;

        $data = $order->toArray();
        if ($order->customer) {
            $data[ 'name' ] = $order->customer->name;
            $data[ 'surname' ] = $order->customer->surname;
            $data[ 'nameDelivery' ] = $order->customer->nameDelivery;
            $data[ 'surnameDelivery' ] = $order->customer->surnameDelivery;
        }
        if (!in_array($data[ 'country' ], ['CZ', 'SK'])) {
            $data[ 'country' ] = 'CZ';
        }
        if (!in_array($data[ 'countryDelivery' ], ['CZ', 'SK'])) {
            $data[ 'countryDelivery' ] = 'CZ';
        }
        $this[ 'orderForm' ]->setDefaults($data);
        $t->data = $data;

        if (isset($t->deliveryMethods[5])) {
            $t->ulozenkaPickups = $this->balikobotFacade->getUlozenkaPickups();
        }
        $t->dpdPickups = array_merge(['' => '-- vyberte výdejní místo'], $this->orderFacade->getDPDPickups());
        //$t->zasilkovna = array_merge(['' => '-- vyberte výdejní místo'], $this->balikobotFacade->getZasilkovna());
        $expeditionToday = true;

        if ($order->products) {
            foreach ($order->products as $p) {
                if ($p->count > $p->product->count) {
                    $expeditionToday = false;
                }
            }
        }

        $t->expeditionToday = $expeditionToday;
        $productUnavailable = false;
        if ($products) {
            foreach ($products as $productInOrder) {
                $product = $productInOrder['product'];
                if ($product->count <= 0) {
                    $productUnavailable = true;
                    break;
                }
            }
        }
        $t->productUnavailable = $productUnavailable;
    }

    public function renderRecap()
    {
        $t = $this->template;
        $order = null;
        if ($this->sess->basketOrder) {
            $order = $this->facade->gem(Orders::class)->find($this->sess->basketOrder);
        }

        if ($order === null) { // If order is null or order dont have state return to first step in basket
            $this->redirect('Basket:one');
        }

        $t->order = $order;
        $data = $this->basketFacade->getProductsInOrder($order, $this->sess, $this->sess->actualCurrency);
        $t->prices = $data[ 'totalPrices' ];
        $t->products = $products = $data[ 'products' ];

        $t->deliverySections = $sections = $this->facade->gEMDeliverySection()->findBy(['active' => '1'],
            ['orderState' => 'ASC']);
        $methods = [];
        foreach ($sections as $s) {
            $methods[ $s->id ] = $this->facade->gEMDeliveryMethod()->findBy(['active' => '1', 'section' => $s->id]);
        }
        $t->payMethods = $this->facade->gEMPaymentMethod()->findBy(['active' => '1'],
            ['orderState' => 'ASC']);
        $t->deliveryMethods = $methods;

        // Delivery terms page
        $t->pageTerms = $this->facade->gEMWebMenu()->findOneBy(['visible' => '1', 'forTerms' => 1]);
        if (isset($t->deliveryMethods[5])) {
            $t->ulozenkaPickups = $this->balikobotFacade->getUlozenkaPickups();
        }
        $t->dpdPickups = $this->orderFacade->getDPDPickups();
        //$t->zasilkovna = $this->balikobotFacade->getZasilkovna();
        $productUnavailable = false;
        if ($products) {
            foreach ($products as $productInOrder) {
                $product = $productInOrder['product'];
                if ($product->count <= 0) {
                    $productUnavailable = true;
                    break;
                }
            }
        }
        $t->productUnavailable = $productUnavailable;
    }

    public function renderEnd($id, $text)
    {
        unset($this->sess->homecreditOrder);
        $order = null;
        if ($id) {
            $order = $this->facade->gem(Orders::class)->find($id);
        }
        if ($order === null) { // If order is null
            $this->redirect('Front:default');
        }
        $t = $this->template;
        $t->order = $order;
        $t->text = $text;
        unset($t->basket);
        //unset($this->sess->basketOrder);
    }

    public function renderWait($toUrl)
    {
        //barDump($this->sess->orderFinish);
        $this->template->toUrl = $toUrl;

        /** @var array GARemarketing - kódy pro remarketing Google */
        $this->template->GARemarketing = [
            'page' => 'purchase',
        ];
        unset($this->template->basket);
    }

    public function handleChangeCountProduct()
    {
        $values = $this->getRequest()->getPost();
        if (isset($values[ 'product' ]) && isset($values[ 'count' ])) {
            $order = $this->basketFacade->createOrReturnOrder($this->sess, $this->getUser());
            $productInOrder = $this->basketFacade->gEMProductInOrder()->findOneBy(['id' => $values[ 'product' ], 'orders' => $order]);

            if ($productInOrder) {
                $this->basketFacade->insertToBasket($this->sess, $productInOrder->product, $values['count'], -1, -1, null, $this->getUser(),
                    false, $order, $productInOrder->packageItem);

                if ($productInOrder->packageItem) {
                    if ($productInOrder->packageItem->package && $productInOrder->packageItem->package->products) {
                        foreach ($productInOrder->packageItem->package->products as $p) {
                            if ($p->id != $productInOrder->packageItem->id) {
                                $this->basketFacade->insertToBasket($this->sess, $p->product, $values['count'], -1, -1, null, $this->getUser(),
                                    false, $order, $p);
                            }
                        }
                    }
                }
            }

            if ($this->isAjax()) {
                $this->redrawControl('basket-snipp');
                $this->redrawControl('basket-products');
                if ($this->isMobile) {
                    $this->redrawControl('basket-prices-m-top');
                    $this->redrawControl('basket-prices-m-1');
                } else {
                    $this->redrawControl('basket-prices-1');
                    $this->redrawControl('basket-prices-2');
                }
                $this->redrawControl('basket-transport');
                $this->redrawControl('basket-delivery');
                $this->redrawControl('basket-payment');
            } else {
                $this->redirect('this');
            }
        }
    }

    public function handleChangePojisteniProduct()
    {
        $values = $this->getRequest()->getPost();
        if (isset($values[ 'product' ]) && isset($values[ 'on' ]) && isset($values[ 'type' ])) {
            $order = $this->basketFacade->createOrReturnOrder($this->sess, $this->getUser());
            $productInOrder = $this->basketFacade->gEMProductInOrder()->findOneBy(['id' => $values[ 'product' ], 'orders' => $order]);
            $pojisteni = -1;
            $nastaveni = -1;
            if ($values[ 'type' ] == 'pojisteni') {
                $pojisteni = 0;
                if ($values[ 'on' ] == 1) {
                    $pojisteni = 1;
                }
            }
            if ($values[ 'type' ] == 'nastaveni') {
                $nastaveni = 0;
                if ($values[ 'on' ] == 1) {
                    $nastaveni = 1;
                }
            }
            if ($productInOrder) {
                $this->basketFacade->insertToBasket($this->sess, $productInOrder->product, 0, $pojisteni, $nastaveni, null, $this->getUser(),
                    true, $order, $productInOrder->packageItem);
            }
            if ($this->isAjax()) {
                $this->redrawControl('basket-snipp');
                $this->redrawControl('basket-prices-1');
                $this->redrawControl('basket-prices-2');
                $this->redrawControl('basket-transport');
                $this->redrawControl('basket-delivery');
                $this->redrawControl('basket-payment');
            } else {
                $this->redirect('this');
            }
        }
    }

    public function handleChangeMethod()
    {
        $values = $this->getRequest()->getPost();
        $this->template->openDelivery = isset($values['openDelivery']) && $values['openDelivery'] == 'true';
        $this->template->openPayment = isset($values['openPayment']) && $values['openPayment'] == 'true';
        $dpdPickup = isset($values['dpdPickup']) ? $values['dpdPickup'] : null;
        if (isset($values[ 'method' ]) && isset($values[ 'on' ]) && isset($values[ 'type' ])) {
            $this->basketFacade->changeMethod($this->sess, $values[ 'method' ], $values[ 'type' ], $dpdPickup);

            if ($this->isAjax()) {
                $this->redrawControl('basket-prices-2');
                $this->redrawControl('basket-transport');
                $this->redrawControl('basket-delivery');
                $this->redrawControl('basket-payment');
            } else {
                $this->redirect('this');
            }
        }
    }

    public function handleAddDiscountSucc($code)
    {
        $res = $this->basketFacade->addDiscount($this->sess, $code);
        if ($res) {
            $this->flashMessage('Slevu se pořadilo uplatnit.', 'success');
            $this->redirect('this');
        } else {
            $this->flashMessage('Neplatný kód slevy.', 'warning');
            $this->redirect('this');
        }
    }

    /**
     * Get customers for autocomplete
     * @param string $term
     */
    public function handleGetCustomersAres($term)
    {
        $result = Register::getBySubject($term);
        $arr = [];
        barDump($result);
        foreach ($result as $item) {
            $arr[] = [
                ($item[ 'nazev' ] . ' (IČ: ' . $item[ 'ico' ] . '), <br /> ' . $item[ 'adresa' ] . '<br />' . $item[ 'role' ]),
                [
                    $item[ 'nazev' ],
                    $item[ 'ico' ],
                    $item[ 'forma' ],
                    $item[ 'adresa' ],
                    $item[ 'role' ],
                ],
                '1'
            ];
        }
        $this->payload->autoComplete = json_encode($arr);
        $this->sendPayload();
    }

    public function actionReturnFromThePay()
    {
        $params = $this->request->getParameters();
        $res = $this->thePay->checkPayment($params);
        if ($res && is_array($res)) {
            $order = $res[0];
            $state = $res[1];
            if ($state === 'paid') {
                $text = 'Úspěšně jsme přijali Vaší platbu v plné výši objednávky! O následujícím průběhu Vás budeme informovat.';
            } elseif ($state === 'expired') {
                $text = 'Platba vyexpirovala.';
            } elseif ($state === 'partially_refunded') {
                $text = 'Platba byla částečně vrácena zákazníkovi.';
            } elseif ($state === 'refunded') {
                $text = 'Platba byla vrácena zákazníkovi.';
            } elseif ($state === 'preauthorized') {
                $text = 'Platba byla autorizována na platební bráně.';
            } elseif ($state === 'preauth_cancelled') {
                $text = 'Autorizace platby byla zrušena.';
            } elseif ($state === 'preauth_expired') {
                $text = 'Autorizace platby expirovala.';
            } elseif ($state === 'waiting_for_payment') {
                $text = 'Čeká se na zaplacení platby.';
            } elseif ($state === 'waiting_for_confirmation') {
                $text = 'Čeká se na potvrzení platby.';
            } else {
                $text = 'Neznámý stav platby...';
            }
            $this->redirect('Basket:end', ['id' => $order->id, 'text' => $text]);
        } else {
            $this->redirect('Front:');
        }
    }

    public function actionReturnFromHomecreditApproved()
    {
        if (isset($this->sess->homecreditOrder)) {
            $this->redirect('Basket:end', ['id' => $this->sess->homecreditOrder, 'text' => 'Úvěr byl schválen.']);
        }

        $this->redirect('Front:default');
    }

    public function actionReturnFromHomecreditRejected()
    {
        if (isset($this->sess->homecreditOrder)) {
            $this->redirect('Basket:end', ['id' => $this->sess->homecreditOrder, 'text' => 'Úvěr byl zamítnut.']);
        }

        $this->redirect('Front:default');
    }

    /*public function renderReturnFromHomeCredit()
    {
        $values = $this->request->getParameters();
        $text = "";
        $order = null;
        if (!empty($values)) {

            if ($this->homeCredit->checkIShopReturnedData($values)) {

                $order = $this->facade->gem(Orders::class)->findOneBy(['variableSymbol' => $values[ 'hc_o_code' ]]);

                if (!count($order)) {
                    $text = 'Nastala nečekaná chyba. Prosím kontaktujte nás.';
                    $this->flashMessage($text, 'error');
                    $this->facade->writeErrorInPayment($order, $text, "HomeCredit");
                    $this->redirect('Basket:end', ['id' => $order->id, 'text' => $text]);
                }

                $homeCr = $this->orderProcFacade->createHomeCreditPayment($values);

                if ($homeCr !== "exits") {

                    if ($values[ "hc_ret" ] == "Y") {

                        $refund = $this->facade->addPayment($order, $values, "HomeCredit");

                        if ($refund) {
                            $this->orderFacade->generateInvoice($order->id);
                            $this->orderFacade->swapState($order->id, 'paySuccess');
                            $text = 'Úspěšně jsme přijali Vaší platbu v plné výši objednávky!';
                            $this->flashMessage($text, 'info');
                            \Tracy\Debugger::log('Úspěšná platba: ' . implode('|', $values), 'HomeCredit');
                        } else {
                            $text = 'Nastala nečekaná chyba. Prosím kontaktujte nás.';
                            $this->facade->writeErrorInPayment($order, "Chyba s připsáním platby k Objednávce.",
                                "HomeCredit");
                            $this->orderFacade->swapState($order->id, 'afterErrorPay');
                            $this->flashMessage($text, 'error');
                            \Tracy\Debugger::log('Nastala chyba s připsáním platby' . implode('|',
                                    $values), 'HomeCreditErrors');
                        }

                    } else {

                        if ($values[ "hc_ret" ] == "N") {
                            $this->facade->writeErrorInPayment($order, "Úvěr okamžitě zamítnut", "HomeCredit");
                            $text = 'Váš úvěr byl okamžitě zamítnut';
                            $this->flashMessage($text, 'error');
                            \Tracy\Debugger::log('Úvěr byl okamžitě zamítnut: ' . implode('|', $values),
                                'HomeCreditErrors');
                        } else {
                            $text = 'U vašeho úvěru byla odložená autorizace (posouzení)';
                            $this->facade->writeErrorInPayment($order, "Odložená autorizace (posouzení)", "HomeCredit");
                            $this->flashMessage($text, 'error');
                            \Tracy\Debugger::log('Odložená autorizace (posouzení): ' . implode('|', $values),
                                'HomeCreditErrors');
                        }

                        $this->orderFacade->swapState($order->id, 'afterErrorPay');
                    }

                } else {
                    $this->flashMessage('Pokus o poznamenání již zaevidované platby přes Home Credit', 'info');
                    \Tracy\Debugger::log('Pokus o poznamenání již zaevidované platby přes Home Credit: ' . implode('|',
                            $values), 'HomeCreditErrors');
                }

            } else {
                $this->flashMessage('Návratová adresa z Home Credit není validní.', 'error');
                \Tracy\Debugger::log('Návratová adresa z Home Credit není validní (možnost podvržení): ' . implode('|',
                        $values), 'HomeCreditErrors');
            }

        } else {
            $this->flashMessage('Nebyla přijata žádná data z Home Credit.', 'error');
            \Tracy\Debugger::log('Nebyla přijata žádná data z Home Credit.: ' . implode('|', $values),
                'HomeCreditErrors');
        }

        if ($order) {
            $this->redirect('Basket:end', ['id' => $order->id, 'text' => $text]);
        }
        $this->redirect('Front:default');

    }*/

    /**
     * @param $prodId
     */
    public function handleDeleteBasketProduct($prodId)
    {
        $order = $this->basketFacade->createOrReturnOrder($this->sess, $this->getUser());
        $productInOrder = $this->basketFacade->gEMProductInOrder()->findOneBy(['id' => $prodId, 'orders' => $order]);
        if ($productInOrder) {
            $this->basketFacade->insertToBasket($this->sess, $productInOrder->product, 0, 0, 0, null, $this->getUser(), false, $order, $productInOrder->packageItem);

            if ($productInOrder->packageItem) {
                if ($productInOrder->packageItem->package && $productInOrder->packageItem->package->products) {
                    foreach ($productInOrder->packageItem->package->products as $p) {
                        if ($p->id != $productInOrder->packageItem->id) {
                            $this->basketFacade->insertToBasket($this->sess, $p->product, 0, 0, 0, null, $this->getUser(),
                                false, $order, $p);
                        }
                    }
                }
            }

            $this->flashMessage('Produkt byl odebrán z košíku.', 'info');
        } else {
            $this->flashMessage('Produkt se nepodařilo odebrat z košíku.', 'warning');
        }
        $this->redirect('Basket:one');
    }
}
