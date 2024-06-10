<?php

namespace App\Presenters;

use Front\Components\Basket\BasketControl;
use Front\Components\FreeTransportRemains\IFreeTransportRemainsFactory;
use Front\Components\StockExpedition\IStockExpeditionFactory;
use Front\Model\Facade\BasketFacade;
use Front\Model\Facade\CustomerFrontFacade;
use Front\Model\Facade\OrderProcessFacade;
use Intra\Components\MailSender\MailSender;
use Intra\Model\Database\Entity\Product;
use Intra\Model\Facade\OrderFacade;
use Intra\Model\Utils\ProductHelper\ProductHelper;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Front\Model\Facade\FrontFacade;
use Front\Model\Facade\SearchFacade;
use Front\Components\Menu\IMenuControlFactory;
use Front\Components\ProductTile\IProductTileControlFactory;
use Front\Components\InlineElem\IInlineControlFactory;
use Front\Components\RapidBuy\IRapidBuyControlFactory;
use Nette\Application\UI\ITemplateFactory;
use Nette\Application\LinkGenerator;
use Intra\Model\Utils\DPHCounter;
use Nette\Utils;
use Ublaboo\ImageStorage\ImageStorage;
use Minetro\Forms\reCAPTCHA\ReCaptchaField;
use Minetro\Forms\reCAPTCHA\ReCaptchaHolder;

/**
 * Base presenter for all intra Presenter
 */
abstract class BaseFrontPresenter extends BasePresenter
{

    /** @var FrontFacade @inject */
    public $facade;

    /** @var SearchFacade @inject */
    public $searchFac;

    /* Session */
    protected $sess;

    /* @var Nette\Http\SessionSection */
    protected $frontBanner;

    /** @var IMenuControlFactory @inject */
    public $menu;

    /** @var CustomerFrontFacade @inject */
    public $customerFac;

    /** @var IInlineControlFactory @inject */
    public $inlineElemFac;

    /** @var IProductTileControlFactory @inject */
    public $productTileFac;

    /** @var IRapidBuyControlFactory @inject */
    public $rapidBuyFac;

    /** @var MailSender @inject */
    public $mailSender2;

    /** @var ITemplateFactory @inject */
    public $templateFactory;

    /** @var IFreeTransportRemainsFactory @inject */
    public $freeTransportFactory;

    /** @var IStockExpeditionFactory @inject */
    public $stockExpedition;

    /** @var LinkGenerator @inject */
    public $linkGenerator;

    /** @var OrderFacade @inject */
    public $orderFacade;

    /** @var OrderProcessFacade @inject */
    public $orderProcFacade;

    /** @var BasketControl @inject */
    public $basketControl;

    /** @var ProductHelper @inject */
    public $productHelper;

    /** @var BasketFacade @inject */
    public $basketFacade;

    /** @var ImageStorage @inject */
    public $imageStorage;

    /** @var boolean */
    public $isProduction;

    /** @var bool */
    public $isMobile;

    /** @var bool */
    public $disableEshop;

    protected function createComponentMenu()
    {
        return $this->menu->create();
    }

    protected function createComponentFreeTransport()
    {
        return $this->freeTransportFactory->create();
    }

    protected function createComponentStockExpedition()
    {
        return $this->stockExpedition->create();
    }

    protected function createComponentInline()
    {
        return $this->inlineElemFac->create();
    }

    protected function createComponentProductTile()
    {
        return $this->productTileFac->create();
    }

    protected function createComponentRapidBuy()
    {
        return $this->rapidBuyFac->create();
    }

    protected function createComponentBasket()
    {
        return $this->basketControl;
    }

    protected function startup()
    {
        parent::startup();
        $this->isProduction = $this->getContext()->parameters[ 'isProduction' ];
        $this->sess = $this->session->getSection('front');
        $this->frontBanner = $this->session->getSection('frontBanner');
        $this->frontBanner->setExpiration('14 days');

        if (!isset($this->sess->basketRecalculated)) {
            $this->sess->basketRecalculated = 0;
        }

        if (!$this->sess->basketRecalculated) {
            $this->recalculateBasket();
        }

        if (!isset($this->sess->actualCurrency) || ($this->sess->actualCurrency['code'] == 'EUR' && $this->locale == 'cs') || ($this->sess->actualCurrency['code'] == 'CZK' && $this->locale == 'sk')) { // pokud není stanovena měna, tak to určím dle lokalizace
            if ($this->locale == 'sk') {
                $this->sess->actualCurrency = $this->facade->gEMCurrency()->findOneBy(['code' => 'EUR'])->toArray();
            } // EUR
            else {
                $this->sess->actualCurrency = $this->facade->gEMCurrency()->findOneBy(['code' => 'CZK'])->toArray();
            } // CZK
            $this->basketFacade->changeCurrencyOnOrder($this->sess, $this->sess->actualCurrency);
        }

        $this->basketFacade->checkOrder($this->sess);

        $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        $this->isMobile = false;
        if ($useragent) {
            if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
                $this->isMobile = true;
            }
        }
        $this->template->isMobile = $this->isMobile;

        $this->disableEshop = false; //true for web | false for eshop
        $this->template->disableEshop = $this->disableEshop;
    }

    protected function beforeRender()
    {
        parent::beforeRender();

        $this->template->isProduction = $this->isProduction;
        $this->template->ver = $this->getContext()->parameters[ 'version' ];

        $this->template->allowEdit = false;
        if ($this->isLoginCustomer()) {
            $this->template->loginCustomer = $this->getUser()->getIdentity();
            $this->template->customerObj = $this->facade->gEMCustomer()->find($this->user->id);
        }
        if ($this->user->loggedIn && !$this->getUser()->isInRole('visitor')) {
            $this->template->allowEdit = true;
        }
        // set default layout
        $path = str_replace(basename(__DIR__), '', dirname(__FILE__));
        $this->setLayout($path . '@layout.latte');

        $settings = $this->facade->getAllCashSettings();
        $settings[ "holidays" ] .= "," . date("d.m.Y", easter_date(2012));
        $this->template->settings = $settings;
        $this->template->imageStorage = $this->imageStorage;

        if ($this->locale == 'sk' && $this->sess->actualCurrency[ 'code' ] != 'EUR') {
            $this->sess->actualCurrency = $this->facade->gEMCurrency()->findOneBy(['code' => 'EUR'])->toArray(); // EUR
            $this->basketFacade->changeCurrencyOnOrder($this->sess, $this->sess->actualCurrency);
        }

        if ($this->locale == 'cs' && $this->sess->actualCurrency[ 'code' ] != 'CZK') {
            $this->sess->actualCurrency = $this->facade->gEMCurrency()->findOneBy(['code' => 'CZK'])->toArray(); // CZK
            $this->basketFacade->changeCurrencyOnOrder($this->sess, $this->sess->actualCurrency);
        }

        $this->template->actualCurrency = $this->sess->actualCurrency;

        $this[ 'currencyForm' ]->setDefaults(['currency' => $this->sess->actualCurrency[ 'id' ]]);

        $this->template->resourceG = $this->facade->gEMWebResources()->findAssoc(['pageId' => '0'], 'divId');
        $this->template->webMenu = $webMenu = $this->facade->gEMWebMenu()->findBy(['visible' => 1, 'showInMenu' => 1, 'parentMenu' => null],
            ['orderPage' => 'ASC']);
        $webSubMenu = [];
        foreach ($webMenu as $w) {
            $webSubMenu[$w->id] = $this->facade->gEMWebMenu()->findBy(['visible' => 1, 'showInMenu' => 1, 'parentMenu' => $w->id], ['orderPage' => 'ASC']);
        }
        $this->template->webSubMenu = $webSubMenu;
        $this->template->webSubMenuHome = $this->facade->gEMWebMenu()->findBy(['visible' => 1, 'showInMenu' => 1, 'parentMenu' => 5], ['orderPage' => 'ASC']);
        $this->template->webSubMenuBusiness = $this->facade->gEMWebMenu()->findBy(['visible' => 1, 'showInMenu' => 1, 'parentMenu' => 6], ['orderPage' => 'ASC']);
        $this->template->webMenuCookies = $this->facade->gEMWebMenu()->findOneBy(['visible' => 1, 'forCookies' => 1]);
        $this->template->footerMenu = $this->facade->gEMWebMenu()->findBy(['visible' => 1, 'inMenu' => 'footer'],
            ['orderPage' => 'ASC']);

        $this->template->sess = $this->sess;
        $this->template->frontBanner = $this->frontBanner;
        $this->template->bannerOnFront = $bannerOnFront = $this->facade->gEMBannerProduct()->findOneBy(['onFront' => true, 'active' => true, 'languages.language.code' => $this->locale], ['orderBanner' => 'ASC']);

        if ($bannerOnFront) {
            if (!isset($this->frontBanner->bannerId) || $bannerOnFront->id != $this->frontBanner->bannerId) {
                $this->frontBanner->remove();
            }
            $this->frontBanner->bannerId = $bannerOnFront->id;
        }

        /** @var array GARemarketing - kódy pro remarketing Google */
        $this->template->GARemarketing = [
            'page' => 'other',
        ];

        // notice about of content in customer basket
        $this->template->basketNoticeDialog = false;

        $this->template->ajax = $this->isAjax();

        if (isset($this->sess->basketSum) && $this->sess->basketSum[ "countItems" ] > 0) {

            $date = new Utils\DateTime();

            $clone = clone $date;
            $clone->add(new \DateInterval('P' . $settings[ "basket_notice" ] . 'D'));

            if (!isset($this->sess->basketNotice)) {
                $this->sess->basketNotice = $clone;
            }

            if ($this->sess->basketNotice < $date) {
                $this->template->basketNoticeDialog = true;
            }

            $this->sess->basketNotice = $clone;

        }
        $this->template->productHelper = $this->productHelper;

        if (isset($this->sess->basketOrder)) {
            $this->template->basketOverView = $this->basketFacade->getBasketPreview($this->sess->basketOrder);
        }
        $this->template->locale = $this->locale;
        $this->template->homepage = false;
    }

    public function getBasePath()
    {
        return rtrim($this->getHttpRequest()->url->basePath, '/');
    }

    public function createComponentAddNewsletterEmailForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('email')
            ->setRequired('Pole s emailem nemůže být prázdné.')
            ->addRule(Form::EMAIL, 'Prosím zadejte správný tvar emailu.')
            ->setAttribute('placeholder', 'zadejte Váš email');
        $form->addSubmit('send', '');
        $form->onSuccess[] = [$this, 'addNewsletterEmailSuccess'];
        return $form;
    }

    public function addNewsletterEmailSuccess($form, $values)
    {
        $res = $this->facade->addNewsletterEmail($values);
        if ($res) {
            $this->flashMessage('Email byl přidán do naší newsletterové databáze.', 'success');
        } else {
            $this->flashMessage('Tento email je už v naší newsletterové databázi', 'warning');
        }
        $this->redirect('this');
    }

    public function createComponentSearchProductForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('searchTerm')
            ->setAttribute('placeholder', 'Zadejte hledané slovo...');
        $form->addSubmit('send', '');
        $form->onSuccess[] = [$this, 'searchProductSuccess'];
        return $form;
    }

    public function searchProductSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        if (isset($values2['textsearch_atcmplt'])) {
            $this->redirect(':ProductList:list', ['searchTerm' => $values2['textsearch_atcmplt']]);
        } else {
            $this->redirect('Front:default');
        }
    }

    public function handlePreloadAddCompareTerms($term)
    {
        $this->handlePreloadSearchTerms($term, 'addCompare');
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

    public function handlePreloadSearchTerms($term, $type = 'search')
    {
        $products = $this->searchFac->findProducts($term, $this->locale);

        $template = $this->templateFactory->createTemplate();

        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->locale = $this->locale;
        $template->setFile(__DIR__ . '/templates/searchedProducts.latte');
        $template->productHelper = $this->productHelper;
        $template->basePath = $this->getBasePath();
        $template->type = $type;
        $template->imageStorage = $this->imageStorage;

        $template->actualCurrency = $this->sess->actualCurrency;

        $values = [];

        foreach ($products as $product) {
            $template->product = $product;
            $template->link = $this->link('addProductToCompare!', ['id' => $product->id, 'locale' => $this->locale]);
            array_push($values, [0 => $template . '', 1 => $product->name]);
        }

        $this->payload->autoComplete = json_encode($values);

        $this->sendPayload();
    }

    public function createComponentCurrencyForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $data = $this->facade->getAllCashCurrency();
        foreach($data as $k => $v) {
            if ($v == 'CZK') {
                $data[$k] = '<img src="'.$this->getBasePath().'/front/design/cz.jpg" alt="Česko">&nbsp;Česko';
            } elseif ($v == 'EUR') {
                $data[$k] = '<img src="'.$this->getBasePath().'/front/design/sk.jpg" alt="Slovensko">&nbsp;Slovensko';
            }
        }
        $form->addSelect('currency', '', $data)->setAttribute('style', 'display: none;');
        $form->addSubmit('send', '')
            ->setAttribute('style', 'display:none');
        $form->onSuccess[] = [$this, 'currencyFormSuccess'];
        return $form;
    }

    public function currencyFormSuccess($form, $values)
    {
        if (is_numeric($values->currency)) {
            $this->sess->actualCurrency = $this->facade->gEMCurrency()->find($values->currency)->toArray();
            $this->basketFacade->changeCurrencyOnOrder($this->sess, $this->sess->actualCurrency);
            $this->flashMessage('Měna byla změněna.', 'success');
        }
        if ($this->sess->actualCurrency[ 'code' ] == 'EUR') {
            $this->locale = 'sk';
        } else {
            $this->locale = 'cs';
        }
        $this->redirect('this');
    }

    protected function recalculateBasket()
    {
        $basketSum = [
            "countItems" => 0,
            "priceSumDPH" => 0,
            "priceSumNoDPH" => 0,
            "priceSumWithServicesDPH" => 0,
            "priceSumWithServicesNoDPH" => 0
        ];

        $dphCounter = new DPHCounter();

        if (isset($this->sess->basket)) {
            foreach ($this->sess->basket as $item) {
                $product = $this->facade->gEMProduct()->findOneBy(['id' => $item[ "id" ]]);
                $vat = $product->vat->value;

                $dphCounter->setPriceWithDPH($item[ "selingPrice" ], $vat, $item[ "countItems" ]);

                $basketSum[ "countItems" ] += $item[ "countItems" ];

                $basketSum[ "priceSumDPH" ] += $dphCounter->getTotalPrice();
                $basketSum[ "priceSumNoDPH" ] += $dphCounter->getTotalWithoutDPH();

                $basketSum[ "priceSumWithServicesDPH" ] += $dphCounter->getTotalPrice();
                $basketSum[ "priceSumWithServicesNoDPH" ] += $dphCounter->getTotalWithoutDPH();

                if (isset($item[ "pojisteni" ])) {

                    $dphCounter->setPriceWithDPH($item[ "pojisteni" ], $vat, $item[ 'countItems' ]);

                    $basketSum[ "priceSumWithServicesDPH" ] += $dphCounter->getTotalPrice();
                    $basketSum[ "priceSumWithServicesNoDPH" ] += $dphCounter->getTotalWithoutDPH();
                }

                if (isset($item[ "nastaveni" ])) {

                    $dphCounter->setPriceWithDPH($item[ "nastaveni" ], $vat, $item[ 'countItems' ]);

                    $basketSum[ "priceSumWithServicesDPH" ] += $dphCounter->getTotalPrice();
                    $basketSum[ "priceSumWithServicesNoDPH" ] += $dphCounter->getTotalWithoutDPH();
                }

            }
        }

        $this->sess->basketSum = $basketSum;
        $this->sess->basketRecalculated = 1;
    }

    public function handleAddToBasket()
    {
        $this->redirect(301, 'Front:default');
    }

    public function handleAddToCart($productId, $pojisteni = "-1", $nastaveni = -1, $enableAction = true)
    {
        $parameters = $this->getParameters();
        if (!is_numeric($productId)) {
            return false;
        }

        if (!isset($this->sess->basket)) {
            $this->sess->basket = [];
        }

        $product = $this->facade->gEMProduct()->find($productId);
        if (!$product || !$product->active || $product->saleTerminated) {
            $this->redirect('Front:default');
        }

        /*$order = $this->basketFacade->createOrReturnOrder($this->sess, $this->getUser());
        $productInOrder = $this->basketFacade->gEMProductInOrder()->findOneBy(['product' => $product, 'orders' => $order]);
        if ($productInOrder && $productInOrder->packageItem) {
            $this->flashMessage('Produkt nelze přidat do košíku, protože je již přidaný v balíčku.', 'info');
            return false;
        }*/

        list($order, $productInOrder) = $this->basketFacade->insertToBasket($this->sess, $product, 1, $pojisteni, $nastaveni,
            isset($parameters['gift']) ? $parameters['gift'] : null,
            $this->getUser());

        $overView = $this->basketFacade->getBasketPreview($order);

        if ($enableAction) {
            //$this->flashMessage('Produkt byl přidán do košíku!', 'success');

            $template = $this->templateFactory->createTemplate();
            $template->productInOrder = $productInOrder;
            $template->imageStorage = $this->imageStorage;
            $template->productsS = $product->accessoriesProducts;
            $template->overView = $overView;
            $template->specialView = isset($this->sess->specialView) ? $this->sess->specialView : [];

            $template->freeDelivery = $this->freeTransportFactory->create();
            $template->productTile = $this->productTileFac->create();

            $template->getLatte()->addProvider('uiControl', $this);
            $template->locale = $this->locale;
            $template->gift = $productInOrder->childProduct && $productInOrder->childProduct->isGift ? $productInOrder->childProduct : null;
            $template->productHelper = $this->productHelper;
            $template->setFile(__DIR__ . '/templates/insertedIntoBasket.latte');
            $template->basePath = $this->getBasePath();

            $template->actualCurrency = $this->sess->actualCurrency;
            $template->settings = $this->facade->getAllCashSettings();

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

    public function handleAddToBasketPackage()
    {
        $this->redirect(301, 'Front:default');
    }

    public function handleAddToCartPackage($packageId)
    {
        $package = $this->facade->gEMProductPackage()->find($packageId);
        if (!$package) {
            $this->flashMessage("Nepodařilo se přidat balíček do košíku!", 'error');
            $this->redirect('this');
        }

        $order = null;
        $productsInOrder = [];
        $packageProducts = [];
        if ($package->products) {
            foreach ($package->products as $k => $item) {
                list($order, $productInOrder) = $this->basketFacade->insertToBasket($this->sess, $item->product, 1, 0, 0, null, $this->getUser(), true, null, $item);
                $productsInOrder[] = $productInOrder;
                $packageProducts[$productInOrder->id] = $item;
            }
        }

        $overView = $this->basketFacade->getBasketPreview($order);

        $template = $this->templateFactory->createTemplate();
        $template->productsI = $packageProducts;
        $template->productsInOrder = $productsInOrder;
        $template->overView = $overView;
        $template->imageStorage = $this->imageStorage;

        $template->freeDelivery = $this->freeTransportFactory->create();

        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->locale = $this->locale;
        $template->productHelper = $this->productHelper;
        $template->setFile(__DIR__ . '/templates/insertedIntoBasket.latte');
        $template->basePath = $this->getBasePath();

        $template->actualCurrency = $this->sess->actualCurrency;
        $template->settings = $this->facade->getAllCashSettings();

        $this->payload->completed = 1;
        $this->payload->data = $template . '';

        if ($this->isAjax()) {
            $this->redrawControl('basket-snipp');
        } else {
            $this->redirect('this');
        }
    }

    public function handleBasketContentInfo()
    {
        $template = $this->templateFactory->createTemplate();

        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->locale = $this->locale;
        $template->setFile(__DIR__ . '/templates/basketContentInfo.latte');

        $template->currency = $this->sess->actualCurrency;
        $template->settings = $this->facade->getAllCashSettings();
        $template->basket = $this->sess->basket;

        $template->freeDelivery = $this->freeTransportFactory->create();

        $this->payload->completed = 1;
        $this->payload->data = $template . '';

        if ($this->isAjax()) {
            $this->redrawControl('basket-snipp');
        } else {
            $this->redirect('this');
        }

    }

    protected function createComponentSignInForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('username', 'Email:')
            ->setRequired('Toto pole je povinné')
            ->setAttribute('placeholder', 'Email');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Toto pole je povinné')
            ->setAttribute('placeholder', 'Heslo');

        $form->addSubmit('send', 'Přihlásit se');

        $form->onSuccess[] = array($this, 'signInddFormSucceededd');
        return $form;
    }

    public function signInddFormSucceededd($form, $values)
    {
        try {
            $this->getUser()->login([$values->username, true], $values->password);
            $this->getUser()->setExpiration('14 days', false);
            $this->basketFacade->addCustomerToOrder($this->getUser(), $this->sess);
            $this->basketFacade->changeCurrencyOnOrder($this->sess, $this->sess->actualCurrency);
            $this->flashMessage('Přihlášení bylo úspěšné!', 'success');

            if ($this->sess->backUrl != null) {
                $this->redirect($this->sess->backUrl);
            } else {
                $this->redirect('this');
            }
        } catch (AuthenticationException $e) {
            $this->flashMessage('show_modal_login');
            $this->flashMessage('Špatné přihlašovací údaje', 'warning');
        }
    }

    protected function createComponentContactForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('phone', 'Telefon')
            ->setAttribute('placeholder', 'Telefon');

        $form->addText('email', 'Email')
            ->setAttribute('placeholder', 'Email');

        $form->addInvisibleReCaptcha('captcha', true, 'Nejste robot?');

        $form->addSubmit('send', 'Odeslat')
            ->setAttribute('class', 'ajax');

        $form->onSuccess[] = array($this, 'contactFormSucc');
        $form->onError[] = function(Form $form) {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'warning');
                }
            }
        };
        return $form;
    }

    public function contactFormSucc($form, $values)
    {
        $this->mailSender2->sendHelpContact($values);
        $this->flashMessage('Váš kontakt jsme si zapsali a budeme Vás neprodleně kontaktovat.', 'success');
    }

    public function handleSaveInline($content, $content_id, $page_id)
    {
        if ($this->isAjax() && $this->user->loggedIn && !$this->getUser()->isInRole('visitor')) {
            $res = $this->facade->addUpdateResources($content, $content_id, $page_id);
            if ($res == true) {
                $this->presenter->flashMessage('Text pole byl úspěšně uložen!', 'success');
            } else {
                $this->presenter->flashMessage('Text se nepodařilo uložit!', 'error');
            }
        }
    }

    public function handleAddFavourite($productId, $customerId)
    {
        if ($this->isLoginCustomer() && $this->user->id == $customerId) {
            $res = $this->facade->addToFavourites($productId, $customerId);
            if ($res) {
                $this->flashMessage('Produkt byl úspěšně přidán do oblíbených!', 'success');
            } else {
                $this->flashMessage('Tento produkt v oblíbených již je zařazen.', 'info');
            }
            if (!$this->isAjax()) {
                $this->redirect('this');
            }
        } else {
            if ($this->user->loggedIn && !$this->getUser()->isInRole('visitor')) {
                $this->flashMessage('Administrátor nemůže přidat produkt do sekce oblíbených!', 'info');
            } else {
                $this->flashMessage('Operace se nezdařila', 'warning');
            }
        }
        $this->redrawControl('favourites-snipp-preview');
    }

    public function handleAddCompare($productId)
    {
        if (!isset($this->sess->compareProduct)) {
            $this->sess->compareProduct = [];
        }
        $this->sess->compareProduct[ $productId ] = $productId;
        $this->payload->completed = 1;
        $this->redrawControl('compare-snipp');
        $this->redrawControl("compare-snipp-preview");
    }

    public function handleLogout()
    {
        $this->getUser()->logout(true);
        unset($this->sess->basketOrder);
        $this->flashMessage('Odhlášení bylo úspěšné!', 'info');
        $this->redirect('this');
    }

    public function getSess()
    {
        return $this->sess;
    }

    public function isLoginCustomer()
    {
        return ($this->user->loggedIn && $this->getUser()->isInRole('visitor')) ? true : false;
    }

    public function handleChangeType($type)
    {
        $types = Product::TYPES;
        $this->sess->productsType = isset($types[$type]) ? $types[$type] : Product::HOME;

        if ($this->isAjax()) {
            $this->redrawControl('products');
            $this->redrawControl('products-filter');
        } else {
            $this->redirect('Front:');
        }
    }

    public function createComponentInquiryForm()
    {
        $form = new Form();
        $form->setTranslator($this->translator);
        $form->addText('name', 'Jméno příjmení')->setAttribute('placeholder', 'Jméno příjmení')
            ->setRequired('Toto pole je povinné.');
        $form->addText('company', 'Firma')->setAttribute('placeholder', 'Firma');
        $form->addEmail('email', 'E-mail')->setAttribute('placeholder', 'E-mail')
            ->setRequired('Toto pole je povinné.');
        $form->addText('phone', 'Telefon')->setAttribute('placeholder', 'Telefon');
        $form->addText('adress', 'Adresa')->setAttribute('placeholder', 'Adresa');
        $form->addTextArea('inquiry', 'Text')->setAttribute('placeholder', 'Text')
            ->setAttribute('style', 'resize: vertical')->setAttribute('rows', '10')
            ->setRequired('Toto pole je povinné.');
        $form->addCheckbox('gdpr', 'Souhlasím se zpracováním osobních údajů v souladu se zákonem o ochranně osobních údajů (tzv. GDPR).')
            ->setRequired('Toto pole je povinné.');
        $form->addSubmit('send', 'Odeslat');

        $form->onSuccess[] = [$this, 'successInquiry'];

        return $form;
    }

    public function successInquiry(Form $form, $values)
    {
        $this->mailSender2->sendInquiry($values, false);
        $this->mailSender2->sendInquiry($values, true);
        $this->flashMessage('Děkujeme za váš zájem.<br>Na Váš dotaz odpovíme co nejdříve.', 'info');
        $this->redirect('Front:');
    }
}
