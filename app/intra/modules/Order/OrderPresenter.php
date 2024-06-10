<?php

namespace App\Presenters;

use Front\Model\Facade\FrontFacade;
use Intra\Components\PDFPrinter\PDFPrinterControl;
use Intra\Model\Database\Entity\BalikobotPackage;
use Intra\Model\Database\Entity\DpdPackage;
use Intra\Model\Facade\BalikobotShopFacade;
use Intra\Model\Facade\CustomerFacade;
use Intra\Model\Utils\DPHCounter;
use Intra\Model\Utils\HeurekaCart;
use Intra\Model\Utils\MyDpdMessage;
use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\OrderFacade;
use Intra\Model\Facade\OrderStateFacade;
use Intra\Components\PDFPrinter\IPDFPrinterFactory;
use Intra\Model\Facade\OrderRefundFacade;
use Intra\Components\MailSender\MailSender;
use Intra\Model\Facade\ProductFacade;
use Intra\Model\Utils\ProductHelper\ProductHelper;
use Tracy\Debugger;

class OrderPresenter extends BaseIntraPresenter
{

    /** @var OrderFacade @inject */
    public $facade;

    /** @var OrderStateFacade @inject */
    public $orderStateFac;

    /** @var OrderRefundFacade @inject */
    public $refundFac;

    /** @var ProductFacade @inject */
    public $productFac;

    /** @var IPDFPrinterFactory @inject */
    public $IPrintFactory;

    /** @var MailSender @inject */
    public $mailSender;

    /** @var BalikobotShopFacade @inject */
    public $balikobot;

    /** @var ProductHelper @inject */
    public $productHelper;

    /** @var PDFPrinterControl @inject */
    public $pdfPrinter;

    /** @var HeurekaCart @inject */
    public $heurekaCart;

    /* Session */
    protected $sess;

    /** @var array */
    private $dpdPickups = null;

    /** @var array */
    private $zasilkovna;

    /** @var FrontFacade @inject */
    public $frontFac;

    /** @var CustomerFacade @inject */
    public $customerFac;

    protected function createComponentPrint()
    {
        return $this->IPrintFactory->create();
    }

    /**
     * ACL name='Správa objednávek sekce'
     * ACL rejection='Nemáte přístup správě objednvávek'
     * ACL back-url=':Homepage:default'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
        $this->sess = $this->session->getSection('orders');

        if ($this->request->getParameter('action') == 'default') {
            $slug = $this->request->getParameter('slug');
            if (!$slug) {
                $this->flashMessage("Nepodařilo se určit stav objednávek - není zadán stav!", 'warning');
                $this->redirect(':Homepage:default');
            }

            $orderState = $this->orderStateFac->get()->findOneBy(['slug' => $slug])->toArray();

            if (!count($orderState)) {
                $this->flashMessage("Nepodařilo se určit stav objednávek - neexistující stav", 'warning');
                $this->redirect(':Homepage:default');
            }
            $this->sess->orderState = $orderState;
        }
        $this->template->orderState = $this->sess->orderState;
    }

    public function renderDefault($slug)
    {
        // set actual order state
        // set new Query builder for grid - need contition by state
        $qb = $this->doctrineGrid->createQueryBuilder($this->facade->entity(),
            ['orderState' => $this->sess->orderState[ 'id' ]], ['foundedDate', 'DESC']);
        $this[ 'table' ]->getGrid()->setDataSource($qb);

    }

    /**
     * ACL name='Detail objednávky - přehled'
     */
    public function renderView($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

        if ($id) {
            $this->template->order = $order = $this->facade->get()->find($id);
            if (!$order) {
                $this->flashMessage('Objednávka nebyla nalezena!', 'danger');
                $this->redirect('Order:');
            }
            if ($order->errorInPayment) {
                $this->flashMessage('Pozor! Při platbě zákazníka se objevila chyba! Prosím kontaktujte zákazníka a tento problém vyřešte. Text chyby: ' . $order->errorInPayment,
                    'danger');
            }
            $this->template->refunds = $this->facade->gEMOrderRefund()->findBy(['orders' => $id],
                ['foundedDate' => 'ASC']);
            $this->template->orderS = $this->orderStateFac->get()->findBy(['active' => true], ['orderState' => 'ASC']);
            $packageLabel = false;
            if ($order->packages) {
                foreach($order->packages as $p) {
                    if ($p->package_id) {
                        $packageLabel = true;
                        break;
                    }
                }
            }
            $this->template->packageLabel = $packageLabel;
            $packageLabelDpd = false;
            /*if ($order->dpdPackages) {
                foreach($order->dpdPackages as $p) {
                    if ($p->packageId) {
                        $packageLabelDpd = true;
                        break;
                    }
                }
            }*/
            $this->template->packageLabelDpd = $packageLabelDpd;
            $this->template->historyOrders = $this->facade->get()->findBy(['id !=' => $id, 'email' => $order->email], ['foundedDate' => 'DESC'], 10);
            $sumTotal = [];
            $sumCurr = [];
            $orders = $this->facade->get()->findBy(['email' => $order->email, 'orderState' => 5, 'codeCreditNote' => null], ['foundedDate' => 'DESC']);
            if ($orders) {
                foreach ($orders as $o) {
                    if (!isset($sumTotal[$o->currency->id])) {
                        $sumTotal[$o->currency->id] = 0;
                    }
                    if (!isset($sumCurr[$o->currency->id])) {
                        $sumCurr[$o->currency->id] = $o->currency;
                    }
                    $sumTotal[$o->currency->id] += $o->totalPrice;
                }
            }
            $this->template->sumTotal = $sumTotal;
            $this->template->sumCurr = $sumCurr;
            $this->template->customer = $this->facade->gEMCustomer()->findOneBy(['email' => $order->email]);
        } else {
            $this->flashMessage('Nepodařilo se vybrat objednávku!');
            $this->redirect(':Homepage:default');
        }
        $this->template->dc = new DPHCounter();
        $this->template->defaultVat = $this->facade->gEMVat()->findOneBy(['defaultVal' => 1]);
        //$this->template->ulozenkaPickups = $this->balikobot->getUlozenkaPickups();
        $this->template->dpdPickups = $this->facade->getDPDPickups();
        $this->template->zasilkovna = $this->balikobot->getZasilkovna();
    }

    /**
     * ACL name='Tabulka přehled objednávek'
     */
    public function createComponentTable()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(),
            __FUNCTION__);
        $action = $grid->addAction('view', '', 'Order:view');
        if ($action) {
            $action->setIcon('eye')
                ->setTitle('Zobrazit')
                ->setClass('btn btn-xs btn-default');
        }
        return $this->tblFactory->create($grid);
    }

    public function formSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        if (isset($values->customer) && is_numeric($values->customer)) {
            $values->customer = $this->customerFac->get()->find($values->customer);
        } elseif (isset($values->customer)) {
            unset($values->customer);
        }

        if (!isset($values->customer) && isset($values->billingInfo)) {
            if (!empty($values->email)) {
                $customer = $this->customerFac->get()->findOneBy(['email' => $values->email]);
                if (!$customer) {
                    $customer = $this->customerFac->createFromOrder($values);
                }
                $values->customer = $customer;
            }
        }

        $order = $this->formGenerator->processForm($form, $values, true);

        if (isset($values->customer) && is_object($values->customer)) {
            if ($order->deliveryToOther && !$order->streetDelivery) {
                $order->setStreetDelivery($values->customer->streetDelivery);
                $order->setCityDelivery($values->customer->cityDelivery);
                $order->setZipDelivery($values->customer->zipDelivery);
                $order->setCountryDelivery($values->customer->countryDelivery);
                $order->setEmailDelivery($values->customer->emailDelivery);
                $order->setContactPerson($values->customer->nameDelivery . ' ' . $values->customer->surnameDelivery);
            }
            if (!$order->email) {
                if ($values->customer->phoneDelivery) {
                    $order->setPhone($values->customer->phoneDelivery);
                } else {
                    $order->setPhone($values->customer->phone);
                }
                $order->setEmail($values->customer->email);
                $order->setEuVat($values->customer->euVat);
            }
            $this->facade->save();
        }

        if (isset($values->creditNoteDate)) {
            $this->facade->updateCreditNoteProducts($order->id, isset($values2['product']) ? $values2['product'] : null);
            if (!$order->codeCreditNote) {
                $this->facade->getEm()->refresh($order);
                $price = 0;
                if ($order->creditNoteWithDelivery) {
                    $price += $order->payMethodPrice + $order->payDeliveryPrice;
                }
                if ($order->productsInCreditNote) {
                    foreach ($order->productsInCreditNote as $p) {
                        $price += $p->count * $p->productInOrder->selingPrice;
                    }
                }
                $discount = $this->facade->gEMDiscountInOrder()->findOneBy(['orders' => $order->id]);
                if ($discount) {
                    $price = (100 - $discount->percent) / 100 * $price;
                }
                $this->frontFac->addPayment($order, ['online' => false, 'price' => $price], 'Dobropis');
                $this->facade->generateCreditNote($order->id);
                $this->productFac->addStockOperationsForCreditNote($order->id);
                $this->flashMessage('Dobropis byl vytvořen.', 'success');
            } else {
                $this->flashMessage('Dobropis byl již vytvořen.', 'warning');
            }
            if ($order->heurekaId) {
                $invoice = $this->pdfPrinter->handleCreateInvoice($order->id, false, true, 'S');
                $this->heurekaCart->setCurrency($order->currency->toArray());
                $this->heurekaCart->sendInvoice($order->id, $invoice);
            }
        }

        $this->facade->recountPrice($order->id);
        $this->redirect(':Order:view', ['id' => $order->id]);
    }

    /**
     * ACL name='Edit fakturačních údajů'
     */
    public function renderEditBilingInfo($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

        if ($id) {
            $order = $this->facade->get()->find($id);
            $this->template->order = $order;
            $this[ 'formEditBilingInfo' ]->setDefaults($order->toArray());
            if ($order->customer) {
                $this['formEditBilingInfo']->setAutocmp('customer', ($order->customer->company ? $order->customer->company.', ' : '').$order->customer->name.' '.$order->customer->surname);
            }
        }
    }

    /**
     * ACL name='Formulář pro edit fakturačních údajů'
     */
    public function createComponentFormEditBilingInfo()
    {
        $form = $this->formGenerator->generateFormWithoutAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);

        $form->addHidden('id');

        $form->addHidden('customer', '')
            ->setAttribute('data-preload', "false")
            ->setAttribute('data-suggest', "true")
            ->setAttribute('data-minlen', "3")
            ->setAttribute('class', "form-control autocomplete-input")
            ->setAttribute('data-toggle', 'completer')
            ->setAttribute('autocomplete', 'true')
            ->setAttribute('title', 'Zákazník');

        $form->addTextAcl('company', 'Firma ')
            ->setAttribute('placeholder', 'Název firmy')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('name', 'Jméno a příjmení')
            ->setAttribute('placeholder', 'Jméno a příjmení')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('idNo', 'IČ')
            ->setAttribute('placeholder', 'IČ')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('vatNo', 'DIČ')
            ->setAttribute('placeholder', 'DIČ')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('vatNo2', 'IČ DPH')
            ->setAttribute('placeholder', 'IČO pro plátce DPH')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('phone', 'Telefon')
            ->setAttribute('placeholder', 'Telefon')
            ->setAttribute('class', 'form-control input-md');

        $form->addEmailAcl('email', 'E-mail')
            ->setAttribute('placeholder', 'E-mail')
            ->setAttribute('class', 'form-control input-md');

        $form->addCheckboxAcl('payVat', 'Plátce DPH?');

        $form->addTextAcl('street', 'Ulice a číslo popisné')
            ->setAttribute('placeholder', 'Ulice a číslo popisné')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('zip', 'PSČ')
            ->setAttribute('placeholder', 'PSČ')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('city', 'Město')
            ->setAttribute('placeholder', 'Město')
            ->setAttribute('class', 'form-control input-md');

        $form->addSelectAcl('country', 'Země', ['CZ' => 'Česká republika', 'SK' => 'Slovenská republika'])
            ->setAttribute('placeholder', 'Země')
            ->setAttribute('class', 'form-control input-md');

        $form->addCheckboxAcl('deliveryToOther', 'Doručit na jinou adresu?');

        $form->addHiddenAcl('billingInfo', '1');

        $form->addSubmitAcl('send', 'Uložit');

        $form->setMessages(['Podařilo se upravit fakturační údaje.', 'success'],
            ['Nepodařilo se upravit fakturační údaje!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formSuccess'];

        return $form;
    }

    /**
     * ACL name='Edit dobropisu'
     */
    public function renderEditCreditNote($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

        if ($id) {
            $order = $this->facade->get()->find($id);
            $this->template->order = $order;
            $orderArr = $order->toArray();
            if (!$orderArr['creditNoteDate']) {
                $nowDate = new Nette\Utils\DateTime();
                $orderArr['creditNoteDate'] = $nowDate->format('j. n. Y');
            }
            $this[ 'formEditCreditNote' ]->setDefaults($orderArr);
            $productInCreditNote = [];
            if ($order && $order->productsInCreditNote) {
                foreach ($order->productsInCreditNote as $p) {
                    $productInCreditNote[$p->productInOrder->id] = $p->count;
                }
            }
            $this->template->productInCreditNote = $productInCreditNote;
        }
    }

    /**
     * ACL name='Formulář pro edit dobropisu'
     */
    public function createComponentFormEditCreditNote()
    {
        $form = $this->formGenerator->generateFormWithoutAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);

        $form->addHidden('id');

        $form->addTextAcl('creditNoteDate', 'Datum vystavení dobropisu')
            ->setAttribute('data-provide', 'datepicker')
            ->setAttribute('data-date-orientation', 'bottom')
            ->setAttribute('data-date-format', 'd. m. yyyy')
            ->setAttribute('data-date-today-highlight', 'true')
            ->setAttribute('data-date-autoclose', 'true')
            ->setAttribute('autocomplete', 'off')
            ->setRequired(false)
            ->addRule(Nette\Application\UI\Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011',
                '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})')
            ->setAttribute('class', 'form-control input-md');

        $form->addCheckboxAcl('creditNoteWithDelivery', 'Včetně dopravy');

        $form->addSubmitAcl('send', 'Uložit');

        $form->setMessages(['Podařilo se upravit dobropis.', 'success'],
            ['Nepodařilo se upravit dobropis!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formSuccess'];

        return $form;
    }

    /**
     * ACL name='Edit dodacích údajů'
     */
    public function renderEditDeliveryInfo($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

        if ($id) {
            $order = $this->facade->get()->find($id);
            $this->template->order = $order;
            $this[ 'formEditDeliveryInfo' ]->setDefaults($order->toArray());
        }
    }

    /**
     * ACL name='Formulář pro edit dodacích údajů'
     */
    public function createComponentFormEditDeliveryInfo()
    {
        $form = $this->formGenerator->generateFormWithoutAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);

        $form->addHidden('id');
        $form->addTextAcl('contactPerson', 'Jméno')
            ->setAttribute('placeholder', 'Jméno')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('streetDelivery', 'Ulice a číslo popisné')
            ->setAttribute('placeholder', 'Ulice a číslo popisné')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('zipDelivery', 'PSČ')
            ->setAttribute('placeholder', 'IČ')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('cityDelivery', 'Město')
            ->setAttribute('placeholder', 'Město')
            ->setAttribute('class', 'form-control input-md');

        $form->addSelectAcl('countryDelivery', 'Stát', ['CZ' => 'Česká republika', 'SK' => 'Slovenská republika'])
            ->setAttribute('placeholder', 'Stát')
            ->setAttribute('class', 'form-control input-md');

        $form->addSubmitAcl('send', 'Uložit');

        $form->setMessages(['Podařilo se upravit dodací údaje.', 'success'],
            ['Nepodařilo se upravit fakturační údaje!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formSuccess'];

        return $form;
    }

    /**
     * ACL name='Edit dodacích a platebních podmínek'
     */
    public function renderEditPayDelInfo($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

        if ($id) {
            $order = $this->facade->get()->find($id);
            $this->template->order = $order;
            $orderArr = $order->toArray();
            if ($this->dpdPickups == null) {
                $this->dpdPickups = $this->facade->getDPDPickups();
            }
            if (!empty($orderArr['deliveryPlace']) && !isset($this->dpdPickups[$orderArr['deliveryPlace']]) && !isset($this->zasilkovna[$orderArr['deliveryPlace']])) {
                $this->flashMessage('Místo dodání '.$orderArr['deliveryPlace'].' v balíkobotu již neexistuje.', 'warning');
                $orderArr['deliveryPlace'] = '';
            }
            $this[ 'formEditPayDelInfo' ]->setDefaults($orderArr);
        }
    }

    /**
     * ACL name='Formulář pro edit dodacích a platebních údajů'
     */
    public function createComponentFormEditPayDelInfo()
    {
        $form = $this->formGenerator->generateFormWithoutAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);
        $form->arrayForeignEntity[ 'deliveryMethod' ] = $this->facade->getDeliveryMethodEntity();
        $form->arrayForeignEntity[ 'paymentMethod' ] = $this->facade->getPaymentMethodEntity();

        $form->addSelectAcl('deliveryMethod', 'Způsob dodání', $this->facade->getDeliveryMethodToSelect())
            ->setAttribute('placeholder', '-- vyberte záznam')
            ->setAttribute('class', 'form-control input-md');

        /*$ulozenkaPickups = $this->balikobot->getUlozenkaPickups();
        foreach($ulozenkaPickups as $k => $u) {
            $ulozenkaPickups[$k] = "Uloženka - ".$u;
        }*/

        $zasilkovna = $this->zasilkovna = $this->balikobot->getZasilkovna();
        foreach($zasilkovna as $k => $u) {
            $zasilkovna[$k] = "Zásilkovna - ".$u;
        }

        $dpdPickups = $this->dpdPickups = $this->facade->getDPDPickups();
        foreach($dpdPickups as $k => $u) {
            $dpdPickups[$k] = "DPD Pickup - ".$u;
        }

        $items = ['' => '-- vyberte místo dodání'];
        $items = /*$ulozenkaPickups + */ $items + $zasilkovna + $dpdPickups;

        $form->addSelectAcl('deliveryPlace', 'Místo dodání (např. DPD Pickup)', $items, count($items))
            ->setAttribute('placeholder', 'Místo dodání')
            ->setAttribute('class', 'form-control input-md selectpicker')
            ->setAttribute('data-live-search', 'true');

        /*$form->addTextAcl('deliveryPlace', 'Místo dodání (např. DPD Pickup)')
            ->setAttribute('placeholder', 'Místo dodání')
            ->setAttribute('class', 'form-control input-md');*/

        $form->addTextAcl('payDeliveryPrice', 'Vlastní cena za dodání')
            ->setRequired(false)
            ->addRule(Nette\Application\UI\Form::FLOAT)
            ->setAttribute('class', 'form-control input-md');

        $form->addSelectAcl('paymentMethod', 'Způsob placení', $this->facade->getPaymentMethodToSelect())
            ->setAttribute('prompt', '-- vyberte záznam')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('payMethodPrice', 'Vlastní cena za způsob placení')
            ->setRequired(false)
            ->addRule(Nette\Application\UI\Form::FLOAT)
            ->setAttribute('class', 'form-control input-md');

        $form->addHidden('id');

        $form->addSubmitAcl('send', 'Uložit');

        $form->setMessages(['Podařilo se upravit platební a dodací údaje.', 'success'],
            ['Nepodařilo se upravit platební a dodací údaje!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formSuccess'];

        return $form;
    }

    /**
     * ACL name='Edit/Add položky v objednávce'
     */
    public function renderEditBasketItem($productOrderId, $orderId)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

        if ($productOrderId && $productOrderId != 0) {
            $product = $this->facade->getProductInOrder()->find($productOrderId);
            $this->template->orders = $product->orders;
            $this[ 'formEditBasketInfo' ]->setDefaults($product->toArray());
        } elseif ($orderId && $productOrderId == 0) {
            $order = $this->facade->get()->find($orderId);
            $this->template->orders = $order;
            $this[ 'formEditBasketInfo' ]->setDefaults(['orders' => $order->id]);
        }
    }

    /**
     * ACL name='Formulář pro edit dodacích a platebních údajů'
     */
    public function createComponentFormEditBasketInfo()
    {
        $form = $this->formGenerator->generateFormWithoutAnnotation($this->facade->getProductInOrderEntity(),
            $this->user, $this, __FUNCTION__);
        $form->arrayForeignEntity[ 'orders' ] = $this->facade->entity();
        $form->arrayForeignEntity[ 'product' ] = $this->facade->getProductEntity();

        $form->addSelectAcl('product', 'Produkt', $this->facade->getProductToSelect())
            ->setPrompt('-- vyberte záznam')
            ->setAttribute('class', 'form-control selectpicker')
            ->setAttribute('data-live-search', 'true');

        $form->addTextAcl('selingPrice', 'Vlastní cena/kus')
            ->setRequired(false)
            ->addRule(Nette\Application\UI\Form::FLOAT)
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('count', 'Počet kusů')
            ->setRequired(false)
            ->addRule(Nette\Application\UI\Form::INTEGER)
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('name', 'Vlastní název')
            ->setAttribute('placeholder', 'Vlastní název')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('unit', 'Vlastní název jednotek')
            ->setAttribute('placeholder', 'Vlastní název jednotek')
            ->setAttribute('class', 'form-control input-md');

        $form->addHidden('orders');
        $form->addHidden('id');

        $form->addSubmitAcl('send', 'Uložit');

        $form->setMessages(['Podařilo se uložit položku objednávky.', 'success'],
            ['Nepodařilo se uložit položku objednávky!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formEditBasketInfoSuccess'];

        return $form;
    }

    public function formEditBasketInfoSuccess($form, $values)
    {
        $product = $this->productFac->get()->find(["id" => $values[ "product" ]]);

        if (!is_numeric($values[ "selingPrice" ]) && $values[ "selingPrice" ] == "") {
            if ($product->activeAction) {
                $values[ "selingPrice" ] = $product->activeAction->selingPrice;
            } else {
                $values[ "selingPrice" ] = $product->selingPrice;
            }

        }

        if ($values[ "count" ] == "") {
            $values[ "count" ] = 1;
        }

        if ($values[ "name" ] == "") {
            $values[ "name" ] = $product->name;
        }

        if ($values[ "unit" ] == "") {
            $values[ "unit" ] = $product->unit;
        }

        $product = $this->formGenerator->processForm($form, $values, true);
        $this->facade->recountPrice($product->orders->id);
        $this->redirect(':Order:view', ['id' => $product->orders->id]);
    }

    /**
     * ACL name='Edit informací o zákazníkovi'
     */
    public function renderEditCustomerInfo($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

        if ($id) {
            $order = $this->facade->get()->find($id);
            $this->template->order = $order;

            //dump($order->toArray());

            $this[ 'formEditCustomerInfo' ]->setDefaults($order->toArray());
        }
    }

    /**
     * ACL name='Formulář pro edit informací o fakturaci'
     */
    public function createComponentFormEditCustomerInfo()
    {

        $form = $this->formGenerator->generateFormWithoutAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);
        $form->addHidden('id');

        $form->addTextAcl('foundedDate', 'Datum objednání')
            ->setAttribute('data-provide', 'datepicker')
            ->setAttribute('data-date-orientation', 'bottom')
            ->setAttribute('data-date-format', 'd. m. yyyy')
            ->setAttribute('data-date-today-highlight', 'true')
            ->setAttribute('data-date-autoclose', 'true')
            ->setRequired(false)
            ->addRule(Nette\Application\UI\Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011',
                '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('phone', 'Telefon zákazníka')
            ->setAttribute('placeholder', 'Telefon zákazníka')
            ->setAttribute('class', 'form-control input-md');

        $form->addEmailAcl('email', 'Email zákazníka')
            ->setAttribute('placeholder', 'Email zákazníka')
            ->setAttribute('class', 'form-control input-md');

        $form->addCheckboxAcl('payVat', 'Plátce DPH');

        $form->addTextAreaAcl('comment', 'Doplňující komentář od zákazníka: ')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAreaAcl('commentInternal', 'Poznámka')
            ->setAttribute('class', 'form-control input-md');

        $form->addSubmitAcl('send', 'Uložit');

        $form->setMessages(['Podařilo se upravit údaje zákazníka.', 'success'],
            ['Nepodařilo se upravit údaje zákazníka!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formSuccess'];

        return $form;
    }


    /**
     * ACL name='Edit informací o fakturaci'
     */
    public function renderEditInvoiceInfo($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

        if ($id) {
            $order = $this->facade->get()->find($id);
            $this->template->order = $order;
            $this[ 'formEditInvoiceInfo' ]->setDefaults($order->toArray());
        }
    }

    /**
     * ACL name='Formulář pro edit informací o fakturaci'
     */
    public function createComponentFormEditInvoiceInfo()
    {
        $form = $this->formGenerator->generateFormWithoutAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);

        $form->arrayForeignEntity[ 'currency' ] = $this->facade->getCurrencyEntity();

        $form->addHidden('id');

        $form->addTextAcl('dueDate', 'Datum splatnosti')
            ->setAttribute('data-provide', 'datepicker')
            ->setAttribute('data-date-orientation', 'bottom')
            ->setAttribute('data-date-format', 'd. m. yyyy')
            ->setAttribute('data-date-today-highlight', 'true')
            ->setAttribute('data-date-autoclose', 'true')
            ->setRequired(false)
            ->addRule(Nette\Application\UI\Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011',
                '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('paymentDate', 'Datum úhrady')
            ->setAttribute('data-provide', 'datepicker')
            ->setAttribute('data-date-orientation', 'bottom')
            ->setAttribute('data-date-format', 'd. m. yyyy')
            ->setAttribute('data-date-today-highlight', 'true')
            ->setAttribute('data-date-autoclose', 'true')
            ->setRequired(false)
            ->addRule(Nette\Application\UI\Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011',
                '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('variableSymbol', 'Variabilní symbol')
            ->setAttribute('placeholder', 'Variabilní symbol:')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('constantSymbol', 'Konstatní symbol')
            ->setAttribute('placeholder', 'Konstatní symbol')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('specificSymbol', 'Specifický symbol')
            ->setAttribute('placeholder', 'Specifický symbol')
            ->setAttribute('class', 'form-control input-md');

        $form->addTextAcl('typeOfPayment', 'Typ úhrady')
            ->setAttribute('placeholder', 'Typ úhrady')
            ->setAttribute('class', 'form-control input-md');

        $cArr = [];
        $currency = $this->facade->gEMCurrency()->findBy(['active' => true]);
        if ($currency) {
            foreach($currency as $c) {
                $cArr[$c->id] = $c->code;
            }
        }

        $form->addSelectAcl('currency', 'Měna', $cArr, count($cArr))
            ->setAttribute('placeholder', 'Měna')
            ->setAttribute('size', '1')
            ->setAttribute('class', 'form-control input-md');

        $form->addCheckboxAcl('euVat', 'Evropský odběratel (nulové DPH)');

        $form->addSubmitAcl('send', 'Uložit');

        $form->setMessages(['Podařilo se upravit informace o fakturaci.', 'success'],
            ['Nepodařilo se upravit fakturační údaje!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formSuccess'];

        return $form;
    }

    /**
     * ACL name='Edit úhrad faktury/objednávky'
     */
    public function renderEditRefund($id, $orderId)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);
        if (!$orderId && !is_numeric($orderId)) {
            $this->flashMessage('Nepodařilo se vybrat objednávku!', 'warning');
            $this->redirect('Homepage:default');
            return;
        }
        $this->template->orderId = $orderId;
        $order = $this->facade->get()->find($orderId);
        if ($id) {
            $refund = $this->refundFac->get()->find($id);
            $this->template->refund = $refund;
            $this[ 'formRefund' ]->setDefaults($refund->toArray());
        } else {
            $this[ 'formRefund' ]->setDefaults([
                'orders' => $orderId,
                'value' => $order->totalPrice,
                'originator' => $this->user->id
            ]);
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit úhrad objednávek'
     */
    public function createComponentFormRefund()
    {
        $form = $this->formGenerator->generateFormByAnnotation($this->refundFac->entity(), $this->user, $this,
            __FUNCTION__);
        $form->setMessages(['Podařilo se uložit úhradu', 'success'], ['Nepodařilo se uložit úhradu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formEditRefundSuccess'];
        return $form;
    }

    public function formEditRefundSuccess($form, $values)
    {
        $id = $values->id;
        $refund = $this->formGenerator->processForm($form, $values, true);
        if ($refund->online && $id == "") {
            try {
                $res = $this->facade->sendToEET($refund, $this->user->id);
                if ($res) {
                    $this->flashMessage("Platba byla poslána na EET.", 'info');
                } else {
                    $this->flashMessage("Nepodařilo se odeslat platbu na EET! Prosím zkuste to znovu a pokud se to i tak nepovede, tak prosím kontaktujte technickou podporu.",
                        'danger');
                }
            } catch (\Exception $ex) {
                $this->flashMessage($ex->getMessage(), 'danger');
                Debugger::log($ex);
                $this->flashMessage('Při odesílání platby na EET došlo k chybě. Kontaktujte prosím podporu.', 'danger');
            }
        }
        $this->facade->recountPrice($refund->orders->id);
        $isRefunded = $this->facade->checkRefunds($refund->orders->id);
        if ($refund->orders->heurekaId) {
            $this->heurekaCart->setCurrency($refund->orders->currency->toArray());
            $this->heurekaCart->sendPaymentStatus($refund->orders, $isRefunded);
        }
        if ($isRefunded == 0) {
            $this->switchStatePayAccept($refund);
            $this->flashMessage('Faktura byla kompletně uhrazena. Bylo by potřeba zaslat email s fakturou zákazníkovi.',
                'success');
        } elseif ($isRefunded > 0) {
            $this->flashMessage($this->translator->trans('Úhrada byla přijata. Zbývá ještě doplatit: ') . $refund->orders->currency->markBefore . ' ' . round($isRefunded,
                    2) . ' ' . $refund->orders->currency->markBehind, 'success');
        } else {
            $this->switchStatePayAccept($refund);
            $this->flashMessage($this->translator->trans('Úhrada byla přijata. Faktura již byla přeplacena o: ') . $refund->orders->currency->markBefore . ' ' . round(-$isRefunded,
                    2) . ' ' . $refund->orders->currency->markBehind, 'success');
        }
        $this->redirect(':Order:view', ['id' => $refund->orders->id]);
    }

    public function switchStatePayAccept($refund)
    {
        $this->facade->swapState($refund->orders->id, 'paySuccess');
        if (!$refund->orders->codeInvoice) {
            $this->facade->generateInvoice($refund->orders->id);
            $this->productFac->addStockOperationsForInvoice($refund->orders->id);
        }
        if ($refund->orders->heurekaId) {
            $invoice = $this->pdfPrinter->handleCreateInvoice($refund->orders->id, false, false, 'S');
            $this->heurekaCart->setCurrency($refund->orders->currency->toArray());
            $this->heurekaCart->sendOrderState($refund->orders->id);
            $this->heurekaCart->sendInvoice($refund->orders->id, $invoice);
        }
    }

    /**
     * ACL name='Edit/Add slevy v objednávce'
     */
    public function renderEditDiscount($discountId, $orderId)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

        if ($discountId && $discountId != 0) {
            $discount = $this->facade->gEMDiscountInOrder()->find($discountId);
            $this->template->orders = $discount->orders;
            $this[ 'formEditDiscount' ]->setDefaults($discount->toArray());
        } elseif ($orderId && $discountId == 0) {
            $order = $this->facade->get()->find($orderId);
            $this->template->orders = $order;
            $this[ 'formEditDiscount' ]->setDefaults(['orders' => $order->id]);
        }
    }

    /**
     * ACL name='Formulář pro edit slev v objednávce'
     */
    public function createComponentFormEditDiscount()
    {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->getDiscountInOrderEntity(), $this->user,
            $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit slevu.', 'success'], ['Nepodařilo se uložit slevu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formEditDiscountSucc'];
        return $form;
    }

    public function formEditDiscountSucc($form, $values)
    {
        $discount = $this->formGenerator->processForm($form, $values, true);
        $this->facade->recountPrice($discount->orders->id);
        $this->redirect(':Order:view', ['id' => $discount->orders->id]);
    }

    /**
     * ACL name='Objednání dopravy k objednávce - Balíkobot'
     */
    public function renderEditPackageBalikobot($id, $orderId)
    {
        if (!$orderId && !is_numeric($orderId)) {
            $this->flashMessage('Nepodařilo se vybrat objednávku!', 'warning');
            $this->redirect('Homepage:default');
            return;
        }
        $this->template->order = $order = $this->facade->get()->find($orderId);
        $shops = $this->facade->gEMBalikobotShop()->findAll();
        if ($id) {
            $package = $this->facade->gEMBalikobotPackage()->find($id);
            if ($package->isOrdered) {
                $this->flashMessage('Tento balík již nejde upravit. Již pro něj byl objednán svoz!', 'warning');
                $this->redirect(':Order:view', ['id' => $order->id]);
            }
            $this[ 'formPackageBalikobot' ]->setDefaults($package->toArray());
        } else {
            $this[ 'formPackageBalikobot' ]->setDefaults([
                'orders' => $orderId,
                'orderNumber' => count($order->packages) + 1,
                'eid' => isset($order->packages[0]->eid) ? $order->packages[0]->eid : null
            ]);
            $shopsArr = [];
            if ($shops) {
                foreach($shops as $s) {
                    if (isset($order->packages[0]) && $order->packages[0]->balikobotShop->id != $s->id) {
                        $shopsArr[] = $s->id;
                    }
                }
            }
            if ($shopsArr) {
                $this['formPackageBalikobot']->getComponents()['balikobotShop']->setDisabled($shopsArr);
            }
        }
    }

    /**
     * ACL name='Formulář pro přidání balíku pro Balikobot'
     */
    public function createComponentFormPackageBalikobot()
    {
        $form = $this->formGenerator->generateFormByAnnotation(BalikobotPackage::class, $this->user,
            $this,
            __FUNCTION__);

        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formPackageBalikobotSuccess'];
        return $form;
    }

    private $errorCodes = [
        'cod_price' => [413 => 'Nepovolená dobírka.'],
        'cod_currency' => [413 => 'Nepovolený ISO kód měny.'],
        'price' => [413 => 'Nepovolená částka udané ceny.'],
        'branch_id' => [413 => 'Neznámé ID pobočky.'],
        'rec_email' => [413 => 'Špatný formát emailu příjemce.'],
        'order_number' => [413 => 'Sdružená zásilka není povolena.'],
        'rec_country' => [413 => 'Nepovolený ISO kód země příjemce'],
        'rec_zip' => [413 => 'Nepovolené PSČ příjemce.'],
        'weight' => [413 => 'Neplatný formát váhy/váha překračuje maximální povolenou hodnotu'],
        'swap' => [413 => 'Výměnná zásilka není pro vybranou službu povolena.'],
        'rec_phone' => [413 => 'Špatný formát telefonního čísla.'],
        'credit_card' => [413 => 'Platba kartou není pro tuto službu/pobočku povolena.'],
        'service_range' => [413 => 'Balíček nelze přidat, protože číselná řada v klientské zóně je již přečerpaná.'],
        'b2c_service' => [413 => 'Službu B2C service není možné použít. Zkontrolujte, zda ji máte povolenou na straně přepravce.'],
        'del_insurance' => [413 => 'Zásilku není možno připojistit.'],
        'del_exworks' => [413 => 'Služba exworks není možná. Zkontrolujte, zda ji máte povolenou na straně přepravce.'],
        'mu_type' => [413 => 'Nepovolený kód manipulační jednotky.'],
        'pieces_count' => [413 => 'Počet nákladových kusů musí být alespoň 1'],
        'sms_notification' => [413 => 'Služba SMS avízo není povolená'],
        'phone_notification' => [413 => 'Služba telefonické avízo není povolená.'],
        'delivery_date' => [413 => 'Datum má špatný formát nebo není povoleno.'],
        'cod_price + swap' => [413 => 'Nepovolená kombinace služeb dobírky a výměnné zásilky.']
    ];

    public function formPackageBalikobotSuccess($form, $values)
    {
        /*if ($values->id != "") { // pokud se jedná o úpravu, tak zkontroluji, zda jsem ho již k nim neposlal a pokud ano, tak jej dropnu a vytvořím znovu.
            $packageOld = $this->facade->gEMBalikobotPackage()->find($values->id);
            if ($packageOld->label_url) {
                $this->balikobot->drop($packageOld);
            }
        }*/
        $package = $this->formGenerator->processForm($form, $values, true);
        $balikobotDeli = $this->facade->gEMBalikobotTypeDelivery()->find($package->orders->deliveryMethod->balikobotDelivery->id);
        $package->setBalikobotTypeDelivery($balikobotDeli);
        $this->facade->save();

        $this->flashMessage('Podařilo se uložit balík.', 'success');
        $this->redirect(':Order:view', ['id' => $package->orders->id]);
    }

    /**
     * ACL name='Objednání dopravy k objednávce - DPD'
     */
    public function renderEditPackageDpd($id, $orderId)
    {
        if (!$orderId && !is_numeric($orderId)) {
            $this->flashMessage('Nepodařilo se vybrat objednávku!', 'warning');
            $this->redirect('Homepage:default');
            return;
        }
        $this->template->order = $order = $this->facade->get()->find($orderId);
        if ($id) {
            $package = $this->facade->gEMDpdPackage()->find($id);
            if ($package->shipmentId) {
                $this->flashMessage('Tento balík již nejde upravit!', 'warning');
                $this->redirect('Order:view', ['id' => $order->id]);
            }
            $this[ 'formPackageDpd' ]->setDefaults($package->toArray());
        } else {
            $this[ 'formPackageDpd' ]->setDefaults([
                'order' => $orderId
            ]);
        }
    }

    /**
     * ACL name='Formulář pro přidání balíku pro Balikobot'
     */
    public function createComponentFormPackageDpd()
    {
        $form = $this->formGenerator->generateFormByAnnotation(DpdPackage::class, $this->user, $this,__FUNCTION__);

        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formPackageDpdSuccess'];
        return $form;
    }

    public function formPackageDpdSuccess($form, $values)
    {
        $package = $this->formGenerator->processForm($form, $values, true);

        $this->flashMessage('Podařilo se uložit balík.', 'success');
        $this->redirect('Order:view', ['id' => $package->order->id]);
    }

    public function handleChangeState($orderId, $stateId)
    {
        if ($orderId && $stateId) {
            $state = $this->facade->swapState($orderId, $stateId);
            $order = $this->facade->get()->find($orderId);
            if ($order && $order->heurekaId) {
                $this->heurekaCart->setCurrency($order->currency->toArray());
                $this->heurekaCart->sendOrderState($order->id);
            }
            if ($state->notification && !$order->codeCreditNote) { // Pokud má na sobě stav přidělený email, tak jej zašlu
                $this->mailSender->sendChangeOrderState($orderId, $state);
            }
            $this->flashMessage('Stav objednávky byl změněn', 'success');
            $this->redirect('this');
        }
    }

    public function handleDeleteProductOrder($productOrderId)
    {
        if ($productOrderId) {
            $this->facade->deleteProductInOrder($productOrderId);
            $this->flashMessage('Položka objednávky byla úspěšně smazána.', 'info');
            $this->redirect('this');
        }
    }

    public function handleDeleteRefund($refundId)
    {
        if ($refundId) {
            $this->refundFac->deleteRefund($refundId);
            $this->flashMessage('Úhrada faktury byla úspěšně smazána.', 'info');
            $this->redirect('this');
        }
    }

    public function handleSendProformaEmail($orderId)
    {
        if ($orderId) {
            $this->mailSender->sendCreationOrder($orderId);
            $this->flashMessage('Email s proforma fakturou byl znovu odeslán zákazníkovi.', 'info');
            $this->redirect('this');
        }
    }

    public function handleSendInvoiceEmail($orderId)
    {
        if ($orderId) {
            $this->mailSender->sendAcceptPayments($orderId);
            $this->flashMessage('Email s fakturou (daňovým dokladem) byl znovu odeslán zákazníkovi.', 'info');
            $this->redirect('this');
        }
    }

    public function handleDeleteDiscount($discountId)
    {
        if ($discountId) {
            $this->facade->deleteDiscount($discountId);
            $this->flashMessage('Sleva byla úspěšně smazána.', 'info');
            $this->redirect('this');
        }
    }

    public function handleDeleteError($orderId)
    {
        if ($orderId) {
            $order = $this->facade->gEMOrders()->find($orderId);
            $order->setErrorInPayment(null);
            $this->facade->save();
            $this->flashMessage('Chyba byla smazána.', 'info');
            $this->redirect('this');
        }
    }

    public function handleDeleteDpd($dpdId)
    {
        if ($dpdId) {
            $dpdOrder = $this->facade->gEMDpdOrder()->find($dpdId);
            $this->facade->remove($dpdOrder);
            $this->flashMessage('Záznam byl smazán.', 'info');
            $this->redirect('this');
        }
    }


    public function handleDoDPD($orderId)
    {
        $this->dpdOrderFacade->orderDPD($orderId);
        $this->flashMessage('Doprava byla objednána', 'success');
        $this->redirect('this');
    }

    public function handleSendAgainEET($idRefund)
    {
        $refund = $this->facade->gEMOrderRefund()->find($idRefund);
        if ($refund->online && ($refund->fikEET === "0" || $refund->fikEET == "")) {
            // Send to EET
            try {
                $res = $this->facade->sendToEET($refund, $this->user->id);
                if ($res == true) {
                    $this->flashMessage('Příjem byl úspěšně zaevidován na EET', 'success');
                } else {
                    $this->flashMessage('Pozor! Nepodařilo se zaregistrovat platbu pro EET! Prosím zkuste ji zaslat znovu. Pokud problém přetrvá, tak prosím kontaktujte technické oddělení.',
                        'danger');
                }
            } catch (\Exception $ex) {
                $this->flashMessage($ex->getMessage(), 'danger');
                Debugger::log($ex);
                $this->flashMessage('Při odesílání platby na EET došlo k chybě. Kontaktujte prosím podporu.', 'danger');
            }
        }
        $this->redirect('this');
    }


    public function handleCreateCustomOrder()
    {

        $order = $this->facade->createCustomOrder();

        if ($order == null) {
            $this->flashMessage('Pozor! Objednávka nelze vytvořit, protože není nastavený defualtní stav pro ručně vytvořené objednávky',
                'danger');
        }

        $this->flashMessage('Ruční vytvoření objednávky proběhlo úspěšně', 'success');
        $this->redirect(':Order:view', ['id' => $order->id]);

    }

    public function handleGetProductData($product_id, $order_id)
    {
        $product = $this->productFac->get()->find(["id" => $product_id]);
        $order = $this->productFac->gEMOrders()->findOneBy(['id' => $order_id]);

        $this->productHelper->setProduct($product, $order->currency->toArray());

        /*if ($product->activeAction) {
            $sellPrice = $product->activeAction->selingPrice;
        } else {
            $sellPrice = $product->selingPrice;
        }*/

        $this->payload->completed = 1;
        $this->payload->data = json_encode([
            "name" => $product->name,
            "price" => $this->productHelper->getPrice(),
            "count" => 1,
            "unit" => $product->unit
        ]);

        $this->sendPayload();

    }

    public function handleDeletePackage($packageId)
    {
        $package = $this->facade->gEMBalikobotPackage()->find($packageId);
        if (count($package) && $package->isOrdered == false) {
            try {
                if ($package->label_url) {
                    $this->balikobot->drop($package);
                }
                $this->facade->remove($package);
                $this->flashMessage('Záznam byl smazán.', 'info');
            } catch(\UnexpectedValueException $e) {
                if ($e->getMessage() == 'The package was not added as the last one.') {
                    $this->flashMessage('Balík nelze smazat, protože nebyl zadán jako poslední.', 'info');
                }
            }
            $this->redirect('this');
        } else {
            $this->flashMessage('Balík již nelze smazat. Již na něj zřejmě byla objednána doprava!', 'warning');
        }
    }

    public function handleAddPackagesToBalikobot($orderId)
    {
        $packages = $this->facade->gEMBalikobotPackage()->findBy(['orders' => $orderId], ['orderNumber' => 'ASC']);
        $return = $this->balikobot->add($packages);
        if ($return === true) {
            $this->flashMessage('Štítky byly úspěšně vytvořeny.', 'success');
        } else {
            $this->flashMessage($return, 'warning');
        }
        $this->redirect('this');
    }

    public function handleRemovePackagesFromBalikobot($orderId)
    {
        $packages = $this->facade->gEMBalikobotPackage()->findBy(['orders' => $orderId], ['orderNumber' => 'DESC']);
        $res = $this->balikobot->drop($packages);
        if ($res === true) {
            $this->flashMessage('Štítky byly úspěšně odebrány.', 'success');
        } else {
            $this->flashMessage($res, 'warning');
        }
        $this->redirect('this');
    }

    public function handleSendCreditNoteEmail($orderId)
    {
        if ($orderId) {
            $this->mailSender->sendCreditNote($orderId);
            $this->flashMessage('Email s dobropisem byl znovu odeslán zákazníkovi.', 'info');
            $this->redirect('this');
        }
    }

    /**
     * Get customers for autocomplete
     * @param string $term
     */
    public function handleGetCustomers($term)
    {
        $result = $this->customerFac->getAutocompleteData($term);
        $arr = [];
        foreach ($result as $item) {
            $arr[$item->id] = [
                ($item->company ? $item->company.', ' : '').$item->name.' '.$item->surname,
                [
                    $item->company,
                    $item->name.' '.$item->surname,
                    $item->idNo,
                    $item->vatNo,
                    $item->vatPay,
                    $item->street,
                    $item->zip,
                    $item->city,
                    $item->country,
                    $item->deliveryToOther,
                    $item->phone,
                    $item->email
                ],
                $item->id
            ];
        }
        $this->payload->autoComplete = json_encode($arr);
        $this->sendPayload();
    }

    public function handleDeletePackageDpd($packageId)
    {
        $package = $this->facade->gEMDpdPackage()->find($packageId);
        if ($package && !$package->shipmentId) {
            $order = $package->order;
            try {
                $this->facade->remove($package);
                if (count($order->dpdPackages) == 0) {
                    $order->setDeliveryOrdered(false);
                    $this->facade->save();
                }
                $this->flashMessage('Balík byl smazán.', 'info');
            } catch(\Exception $e) {
                $this->flashMessage('Balík nelze smazat.', 'info');
            }
            $this->redirect('this');
        } else {
            $this->flashMessage('Balík již nelze smazat. Již na něj zřejmě byla objednána doprava!', 'warning');
        }
    }

    public function handleAddPackagesToDpd($orderId)
    {
        try {
            $this->facade->dpdShipment($orderId);
            $this->flashMessage('Zásilky se podařilo vytvořit.', 'success');
        } catch (MyDpdMessage $ex) {
            $this->flashMessage($ex->getMessage(), 'danger');
        } catch (\Exception $ex) {
            Debugger::log($ex);
            $this->flashMessage('Při vytváření zásilek došlo k chybě. Kontaktujte podporu.', 'danger');
        }
        /*$packages = $this->facade->gEMDpdPackage()->findBy(['order' => $orderId]);
        $return = $this->balikobot->add($packages);
        if ($return === true) {
            $this->flashMessage('Štítky byly úspěšně vytvořeny.', 'success');
        } else {
            $this->flashMessage($return, 'warning');
        }*/
        $this->redirect('this');
    }

    public function handleRemovePackagesFromDpd($orderId)
    {
        $packages = $this->facade->gEMBalikobotPackage()->findBy(['order' => $orderId]);
        /*$res = $this->balikobot->drop($packages);
        if ($res === true) {
            $this->flashMessage('Štítky byly úspěšně odebrány.', 'success');
        } else {
            $this->flashMessage($res, 'warning');
        }*/
        $this->redirect('this');
    }

    public function handleDeleteShipmentDpd($shipmentId)
    {
        try {
            $this->facade->deleteDpdShipment($shipmentId);
            $this->flashMessage('Zásilku se podařilo úspěšně smazat.', 'success');
        } catch (MyDpdMessage $ex) {
            $this->flashMessage($ex->getMessage(), 'danger');
        } catch (\Exception $ex) {
            Debugger::log($ex);
            $this->flashMessage('Při mazání zásilky došlo k chybě. Kontaktujte podporu.', 'danger');
        }
        $this->redirect('this');
    }

    public function handleGetShipmentLabelDpd($shipmentId)
    {
        try {
            $pdf = $this->facade->getShipmentLabelDpd($shipmentId);
            header("Content-type: application/pdf");
            header("Content-Disposition: inline; filename=stitky.pdf");
            echo $pdf;
            die;
        } catch (MyDpdMessage $ex) {
            $this->flashMessage($ex->getMessage(), 'danger');
        } catch (\Exception $ex) {
            Debugger::log($ex);
            $this->flashMessage('Při tisku štítků došlo k chybě. Kontaktujte podporu.', 'danger');
        }
    }

    public function handleGetShipmentLabelOrderDpd($orderId)
    {
        try {
            $pdf = $this->facade->getShipmentLabelDpdOrder($orderId);
            header("Content-type: application/pdf");
            header("Content-Disposition: inline; filename=stitky.pdf");
            echo $pdf;
            die;
        } catch (MyDpdMessage $ex) {
            $this->flashMessage($ex->getMessage(), 'danger');
        } catch (\Exception $ex) {
            Debugger::log($ex);
            $this->flashMessage('Při tisku štítků došlo k chybě. Kontaktujte podporu.', 'danger');
        }
    }
}
