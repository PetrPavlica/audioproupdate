<?php

namespace App\Presenters;

use Front\Components\HomeCredit\HomeCredit;
use Front\Model\Facade\FrontFacade;
use Front\Model\Facade\OrderProcessFacade;
use Front\Model\Utils\DPDAdapter;
use Intra\Model\Facade\BalikobotShopFacade;
use Intra\Model\Facade\CronFacade;
use Intra\Model\Facade\WebPushNotificationFacade;
use Intra\Model\Facade\WebPushSubscriptionFacade;
use Intra\Model\Utils\BalikobotAdapter;
use Intra\Model\Utils\Cezar;
use Intra\Model\Utils\HeurekaCart;
use Intra\Model\Utils\ThePay;
use Nette;
use Intra\Model\Facade\ProductActionFacade;
use Intra\Model\Facade\CurrencyFacade;
use Intra\Model\Facade\ProductFacade;
use Intra\Model\Facade\OrderFacade;
use Intra\Components\MailSender\MailSender;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;
use Intra\Components\Exporter\XMLExporter;
use Intra\Model\Utils\CategoryUpdater\CategoryUpdater;
use Nette\Utils\DateTime;
use Tracy\Debugger;
use Ublaboo\ImageStorage\ImageStorage;

class CronPresenter extends BasePresenter
{
    /** @var CronFacade @inject */
    public $cronFacade;

    /** @var ProductActionFacade @inject */
    public $actionFacade;

    /** @var ProductFacade @inject */
    public $productFacade;

    /** @var CurrencyFacade @inject */
    public $currencyFacade;

    /** @var OrderFacade @inject */
    public $orderFacade;

    /** @var MailSender @inject */
    public $mailSender;

    /** @var IStorage @inject */
    public $storage;

    /** @var XMLExporter @inject */
    public $xmlExporter;

    /** @var DPDAdapter @inject */
    public $dphAdapter;

    /** @var CategoryUpdater @inject */
    public $categoryUpdater;

    /** @var ThePay @inject */
    public $thePay;

    /** @var BalikobotShopFacade @inject */
    public $balikobotShopFacade;

    /** @var FrontFacade @inject */
    public $facade;

    /** @var OrderProcessFacade @inject */
    public $orderProcFacade;

    /** @var HeurekaCart @inject */
    public $heurekaCart;

    /** @var BalikobotAdapter @inject */
    public $balikobot;

    /** @var ImageStorage @inject */
    public $imagestorage;

    /** @var HomeCredit @inject */
    public $homeCredit;

    /** @var WebPushSubscriptionFacade @inject */
    public $webPushSubFac;

    /** @var WebPushNotificationFacade @inject */
    public $webPushNotifyFac;

    /** @var Cezar @inject */
    public $cezar;

    // Update currency exchange rates.
    // Call on: .../cron/update-exchange-rates
    // Perioda of call: 1x/day
    public function renderUpdateExchangeRates()
    {
        $messages = $this->currencyFacade->checkActualExchangeRates();
        $this->storage->clean([
            Cache::TAGS => ["currency"],
        ]);
        foreach ($messages as $m) {
            echo $m . "<br>";
        }

        if (!count($messages)) {
            echo "Všechny kurzy byly zaktualizovány.";
        }
        die;
    }

    // Update actions on products
    // Call on: .../cron/update-product-actions
    // Perioda of call: 1x / 10 - 20 minutes
    public function renderUpdateProductActions()
    {
        $res = $this->actionFacade->updateAllActions();
        echo "OK";
        die;
    }

    // Update invoice due and create DPD pickup array
    // Call on: .../cron/check-due-invoices
    // Perioda of call: 1x / 1 day
    public function renderCheckDueInvoices()
    {
        $state = $this->orderFacade->gEMOrderState()->findOneBy(['overDue' => 1]);
        if ($state) {
            $orders = $this->orderFacade->checkDueInvoices($state);
            if ($state->notification) { // Pokud má stav do kterého přepínám notifikace, tak rozešlu emaily
                foreach ($orders as $order) {
                    $this->mailSender->sendChangeOrderState($order, $state);
                }
            }
        }
        // Create DPD pickup array
        $this->dphAdapter->createCache('getDPDPickup');
        echo "OK";
        die;
    }

    // Delete old orders without customer
    // Call on: .../cron/delete-old-orders
    // Perioda of call: 1x / 1 hour
    public function actionDeleteOldOrders()
    {
        try {
            $orders = $this->orderFacade->get()->findBy(['customer' => NULL, 'orderState' => NULL, 'foundedDate < ' => new \DateTime('-30 days')], [], 250);
            if ($orders) {
                foreach ($orders as $o) {
                    $products = $this->orderFacade->gEMProductInOrder()->findBy(['orders' => $o, 'parentProduct' => null]);
                    foreach ($products as $p) {
                        if ($p->childProduct) {
                            $this->orderFacade->remove($p->childProduct);
                        }
                        $this->orderFacade->remove($p);
                    }
                    $discounts = $this->orderFacade->gEMDiscountInOrder()->findBy(['orders' => $o]);
                    if ($discounts) {
                        foreach ($discounts as $d) {
                            $this->orderFacade->remove($d);
                        }
                    }
                    $operations = $this->orderFacade->gEMProductOperation()->findBy(['orders' => $o]);
                    if ($operations) {
                        foreach ($operations as $op) {
                            $this->orderFacade->remove($op);
                        }
                    }
                    $this->orderFacade->remove($o);
                }
                $this->orderFacade->save();
            }
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }
        try {
            $orders = $this->orderFacade->get()->findBy(['orderState' => 9, 'foundedDate < ' => new \DateTime('-30 days')], [], 250);
            if ($orders) {
                foreach ($orders as $o) {
                    if (count($o->products) == 0) {
                        $discounts = $this->orderFacade->gEMDiscountInOrder()->findBy(['orders' => $o]);
                        if ($discounts) {
                            foreach ($discounts as $d) {
                                $this->orderFacade->remove($d);
                            }
                            $this->orderFacade->save();
                        }
                        $operations = $this->orderFacade->gEMProductOperation()->findBy(['orders' => $o]);
                        if ($operations) {
                            foreach ($operations as $op) {
                                $this->orderFacade->remove($op);
                            }
                        }
                        $this->orderFacade->remove($o);
                    }
                }
                $this->orderFacade->save();
            }
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        echo 'OK';
        die;
    }

    // Perioda - nespouštět! - přegenerování thumb všech obrázků
    public function renderRecreateImages()
    {
        $res = $this->productFacade->recreateImages();
        echo "OK";
        die;
    }

    // Perioda - nespouštět - nastavení hlavních obrázků na produktech - pokud je nemají
    public function renderProductMainImages()
    {
        $res = $this->productFacade->setProductsMainImages();
        echo "OK";
        die;
    }

    // Update XML for Heureka
    // Call on: .../cron/update-xml-heureka
    // Perioda of call: 1x / 2 hours
    public function actionUpdateXMLHeureka($code = 'CZK')
    {
        try {
            $this->xmlExporter->createHeurekaXML($code);
            echo "OK";
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        die;
    }

    // Update XML for Zbozi
    // Call on: .../cron/update-xml-zbozi-cz
    // Perioda of call: 1x / 2 hours
    public function actionUpdateXMLZboziCz()
    {
        $this->xmlExporter->createZboziCzXML();
        echo "OK";
        die;
    }

    // Update XML for Google Merchants
    // Call on: .../cron/update-xml-google-merchants
    // Perioda of call: 1x / 2 hours
    public function actionUpdateXMLGoogleMerchants()
    {
        $this->xmlExporter->createGoogleMerchantsXML();
        echo "OK";
        die;
    }

    // Update XML for Mall.cz
    // Call on: .../cron/update-xml-mall
    // Period of call: 1x / 24 hours
    public function actionUpdateXMLMall($code = 'CZK')
    {
        try {
            $this->xmlExporter->createMallXML($code);
            echo "OK";
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        die;
    }

    // Update availbility XML for Mall.cz
    // Call on: .../cron/update-xml-availbility-mall
    // Period of call: 1x / 2 hours
    public function actionUpdateXMLAvailbilityMall()
    {
        try {
            $this->xmlExporter->createAvailabilityMallXML();
            echo "OK";
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        die;
    }

    // Update list of product's category for Google Merchants
    // Call on: .../cron/update-list-category-google-merchants
    // Perioda of call: 1x / week
    public function actionUpdateListCategoryGoogleMerchants()
    {
        $this->categoryUpdater->checkGoogleMerchantCategory();
        echo "OK";
        die;
    }

    // Update list of product's category for Heureka
    // Call on: .../cron/update-list-category-heureka
    // Perioda of call: 1x / week
    public function actionUpdateListCategoryHeureka()
    {
        $this->categoryUpdater->checkHeurekaCategory();
        echo "OK";
        die;
    }

    // Update list of product's category for Zbozi cz
    // Call on: .../cron/update-list-category-zbozi-cz
    // Perioda of call: 1x / week
    public function actionUpdateListCategoryZboziCz()
    {
        $this->categoryUpdater->checkZboziCZCategory();
        echo "OK";
        die;
    }

    // Update list of delivery types from Balikobot
    // Call on: .../cron/update-balikobot-delivery-types
    // Perioda of call: 1x / day
    public function actionUpdateBalikobotDeliveryTypes()
    {
        $this->balikobotShopFacade->updateDeliveryTypes($this->mailSender);
        echo "OK";
        die;
    }

    public function actionNotifyThePay()
    {
        $params = $this->request->getParameters();
        $this->thePay->checkPayment($params);
        die;
    }

    // Update prices from Partner list
    // Call on: .../cron/update-prices-partner
    // Period of call: 1x / 24 hour - about 18:25
    public function actionUpdatePricesPartner()
    {
        $this->stockKB->updatePrices();
        $this->stockAvIntegra->updatePrices();
        $this->stockHorizontTrade->updatePrices(); // added 15.12.2020
        $this->stockPanter->updatePrices(); // added 15.12.2020
        $this->stockAsbis->updatePrices(); // added 18.5.2021
        $this->stockWdq->updatePrices(); // added 14.6.2021
        $this->stockAcoustiqueQuality->updatePrices(); // added 14.6.2021
        $this->mailSender->sendPricesChange();
        echo "OK";
        die;
    }

    /*public function actionUpdatePrices()
    {
        $this->stockKB->updatePricesReverse();
        echo "OK";
        die;
    }*/

    public function actionUpdateCategoriesToNewTable() {
        $this->facade->getEm()->getConnection()->query('truncate product_in_category');
        $products = $this->facade->gEMProduct()->findAll();
        if ($products) {
            foreach($products as $p) {
                $category = new \Intra\Model\Database\Entity\ProductInCategory();
                $category->setCategory($p->category);
                $category->setProduct($p);
                $this->facade->getEm()->persist($category);
            }
            $this->facade->save();
        }
        echo 'OK';
        die;
    }

    private function findNewGroupFilter($groupFilters, $search)
    {
        if ($groupFilters) {
            foreach($groupFilters as $g) {
                if ($g['name'] == $search) {
                    return $g['id'];
                }
            }
        }

        return false;
    }

    public function actionUpdateFilters()
    {
        $groupFilters = $this->facade->getEm()->getConnection()->query('SELECT * FROM group_product_filter GROUP BY name ORDER BY id')->fetchAll();
        $productFilters = $this->facade->gEMProductFilter()->findAll();
        if ($productFilters) {
            foreach($productFilters as $p) {
                $find = $this->findNewGroupFilter($groupFilters, $p->filterGroup->name);
                if ($find) {
                    $p->setFilterGroup($this->facade->gEMGroupProductFilter()->find($find));
                }
            }
            $this->facade->save();
        }
        $id = [];
        foreach($groupFilters as $g) {
            $id[] = $g['id'];
        }
        $this->facade->getEm()->getConnection()->query('DELETE FROM group_product_filter WHERE id not in ('.implode(',', $id).')');
        echo 'OK';
        die;
    }

    private function findNewProductFilter($productFilters, $search, $filterId)
    {
        if ($productFilters) {
            foreach($productFilters as $p) {
                if (mb_strtolower($p['name']) == mb_strtolower($search) && $filterId == $p['filter_group_id']) {
                    return $p['id'];
                }
            }
        }

        return false;
    }

    public function actionUpdateProductFilters()
    {
        $productFilters = $this->facade->getEm()->getConnection()->query('SELECT * FROM product_filter GROUP BY filter_group_id, name ORDER BY id')->fetchAll();
        $productInFilters = $this->facade->gEMProductInFilter()->findAll();
        if ($productInFilters) {
            foreach($productInFilters as $p) {
                $find = $this->findNewProductFilter($productFilters, $p->filter->name, $p->filter->filterGroup->id);
                if ($find) {
                    $p->setFilter($this->facade->gEMProductFilter()->find($find));
                }
            }
            $this->facade->save();
        }
        $id = [];
        foreach($productFilters as $p) {
            $id[] = $p['id'];
        }
        $this->facade->getEm()->getConnection()->query('DELETE FROM product_filter WHERE id not in ('.implode(',', $id).')');
        echo 'OK';
        die;
    }

    public function actionUpdateStockOnkyo()
    {
        $products = $this->facade->gEMProduct()->findBy(['productMark' => 47]);
        if ($products) {
            foreach($products as $p) {
                $p->count = 0;
            }
            $this->facade->save();
        }
        die;
    }

    public function actionUpdateStockKlipsch()
    {
        $products = $this->facade->gEMProduct()->findBy(['productMark' => 45]);
        if ($products) {
            foreach($products as $p) {
                $p->count = 0;
            }
            $this->facade->save();
        }
        die;
    }

    public function actionUpdateCategoryUrl()
    {
        $categories = $this->facade->gEMProductCategory()->findAll();
        if ($categories) {
            foreach($categories as $c) {
                $c->url = Nette\Utils\Strings::webalize($c->name);
            }
            $this->facade->save();
        }
        die;
    }

    public function actionUpdateCustomerData()
    {
        $orders = $this->facade->getEm()->getConnection()->query('SELECT * FROM orders GROUP BY customer_id ORDER BY founded_date ASC')->fetchAll();
        if ($orders) {
            foreach($orders as $o) {
                if ($o['customer_id']) {
                    $customer = $this->facade->gEMCustomer()->find($o['customer_id']);
                    if ($customer) {
                        $customer->setFoundedDate(new DateTime($o['founded_date']));
                    }
                }
            }
            $this->facade->save();
        }
        die;
    }

    public function actionUpdateStockYamaha()
    {
        $products = $this->facade->gEMProduct()->findBy(['productMark' => 29]);
        if ($products) {
            foreach($products as $p) {
                $p->count = 0;
            }
            $this->facade->save();
        }
        die;
    }

    public function actionUpdateProductsColor()
    {
        $params = $this->facade->gEMProductParameter()->findBy(['name' => 'Barva']);
        if ($params) {
            foreach($params as $p) {
                $p->product->setColorText($p->value);
            }
            $this->facade->save();
        }
        die;
    }

    //Period of call: every 1 hour
    public function actionGetPaymentStatus()
    {
        $this->setHeurekaCartCurrency();
        $this->heurekaCart->getPaymentStatus();
        die;
    }

    //Period of call: every 35 minutes
    public function actionGetShopStatus()
    {
        $this->setHeurekaCartCurrency();
        $this->heurekaCart->getShopStatus();
        die;
    }

    private function setHeurekaCartCurrency()
    {
        if ($this->locale == 'sk') {
            $currency = $this->facade->gEMCurrency()->findOneBy(['code' => 'EUR'])->toArray();
        } // EUR
        else {
            $currency = $this->facade->gEMCurrency()->findOneBy(['code' => 'CZK'])->toArray();
        } // CZK
        $this->heurekaCart->setCurrency($currency);
    }

    public function actionSendUnfinishedOrders()
    {
        $days = $this->facade->setting('unfinished_email_days');
        $orders = $this->facade->gEMOrders()->findBy(['orderState' => 9, 'unfinishedDate' => null, 'lastUpdate <=' => new DateTime('-'.$days.' days')], ['foundedDate' => 'DESC'], 50);
        if ($orders) {
            foreach($orders as $o) {
                if (count($o->products)) {
                    if ($this->mailSender->sendUnfinishedOrder($o->id)) {
                        $o->setUnfinishedDate(new DateTime());
                    }
                    if (!$o->webPushNotify) {
                        $subscriptions = $this->webPushSubFac->get()->findBy(['customer' => $o->customer->id]);
                        if ($subscriptions) {
                            foreach ($subscriptions as $s) {
                                $this->webPushNotifyFac->sendCustomerNotification($o, $s);
                            }
                            $o->setWebPushNotify(true);
                        }
                    }
                    $this->facade->save();
                }
            }
        }
        die;
    }

    public function actionSendRatingOrders()
    {
        $days = $this->facade->setting('rating_email_days');
        $orders = $this->facade->gEMOrders()->findBy(['orderState' => 5, 'ratingDate' => null, 'lastUpdate <=' => new DateTime('-'.$days.' days'), 'email !=' => ''], ['foundedDate' => 'DESC'], 50);
        if ($orders) {
            foreach($orders as $o) {
                if ($this->mailSender->sendRatingOrder($o->id)) {
                    $o->setRatingDate(new DateTime());
                    $this->facade->save();
                }
            }
        }
        die;
    }

    /*public function actionDeletePackageFromBalikobot($id = null, $shipper = null, $packageId = null)
    {
        $shop = $this->facade->gEMBalikobotShop()->find($id);
        if ($shop) {
            $this->balikobot->init($shop);
            try {
                $this->balikobot->get()->dropPackage($shipper, $packageId);
            } catch (\UnexpectedValueException $e) {
                die($e->getMessage());
            }
        }
        die;
    }

    public function actionOverviewBalikobot($id = null, $shipper = null)
    {
        $shop = $this->facade->gEMBalikobotShop()->find($id);
        if ($shop) {
            $this->balikobot->init($shop);
            try {
                $data = $this->balikobot->get()->overview($shipper);
                dump($data);
            } catch (\UnexpectedValueException $e) {
                die($e->getMessage());
            }
        }
        die;
    }*/

    public function actionDeleteImgCache()
    {
        /*$imgs = $this->facade->gEMProductImage()->findAll();
        if ($imgs) {
            foreach($imgs as $i) {
                if (file_exists($i->path)) {
                    $this->imagestorage->deleteCache($i->path, false);
                    $this->imagestorage->deleteCache($i->pathThumb, true);
                }
            }
        }*/

        $this->deleteImages(glob('productImages/*.{jpg,png,gif,JPG,PNG,GIF}', GLOB_BRACE));
        $dirs = glob('productImages/*', GLOB_ONLYDIR);
        if ($dirs) {
            foreach($dirs as $d) {
                $this->deleteImages(glob($d.'/*.{jpg,png,gif,JPG,PNG,GIF}', GLOB_BRACE));
            }
        }

        die;
    }

    private function deleteImages($images)
    {
        if ($images) {
            foreach($images as $i) {
                if (stripos($i, 'shrink_only') !== false) {
                    echo $i . "<br>";
                    @unlink($i);
                }
            }
        }
    }

    public function actionNotificationHomecredit($id)
    {
        $postdata = file_get_contents("php://input");
        \Tracy\Debugger::log($postdata, 'HomeCreditData');
        $order = $this->orderFacade->get()->findOneBy(['variableSymbol' => $id]);
        if ($order) {
            $notifArr = json_decode($postdata, true);
            if ($this->homeCredit->checkChecksum($notifArr['orderNumber'], $notifArr['stateReason'], $notifArr['checkSum'])) {
                list($created, $homeCr) = $this->orderProcFacade->createHomeCreditPayment($notifArr);
                if ($homeCr) {
                    if (($created && $notifArr['state'] == 'READY') || (!$created && $notifArr['state'] == 'READY' && $notifArr['state'] != $homeCr->state)) {
                        $refund = $this->facade->addPayment($order, [], "HomeCredit");

                        if ($refund) {
                            $this->orderFacade->generateInvoice($order->id);
                            $this->orderFacade->swapState($order->id, 'paySuccess');
                            \Tracy\Debugger::log('Úspěšná platba: '.$notifArr['orderNumber'], 'HomeCredit');
                        } else {
                            $this->facade->writeErrorInPayment($order, "Chyba s připsáním platby k Objednávce.",
                                "HomeCredit");
                            $this->orderFacade->swapState($order->id, 'afterErrorPay');
                            \Tracy\Debugger::log('Nastala chyba s připsáním platby: ' . $notifArr['orderNumber'], 'HomeCreditErrors');
                        }
                    } else {
                        if ($notifArr['state'] == 'REJECTED') {
                            $this->facade->writeErrorInPayment($order, "Úvěr okamžitě zamítnut", "HomeCredit");
                            \Tracy\Debugger::log('Úvěr byl okamžitě zamítnut: ' . $notifArr['orderNumber'], 'HomeCreditErrors');
                            $this->orderFacade->swapState($order->id, 'afterErrorPay');
                        } elseif ($notifArr['state'] == 'PROCESSING') {
                            $this->facade->writeErrorInPayment($order, "Odložená autorizace (posouzení)", "HomeCredit");
                            \Tracy\Debugger::log('Odložená autorizace (posouzení): '.$notifArr['orderNumber'], 'HomeCreditErrors');
                            $this->orderFacade->swapState($order->id, 'afterErrorPay');
                        } else {
                            \Tracy\Debugger::log('Úvěr byl stornován: '.$notifArr['orderNumber'], 'HomeCreditErrors');
                        }
                    }
                    $homeCr->setState($notifArr['state']);
                    $this->facade->save();
                }
            }
        }
        die;
    }

    /*public function actionTransferPackages()
    {
        $this->cronFacade->transferPackages();
        die;
    }*/

    /*public function actionFixCreditNotes()
    {
        $orders = $this->facade->gEMOrders()->findBy(['codeCreditNote !=' => null]);
        if ($orders) {
            foreach ($orders as $o) {
                $o->setCreditNoteWithDelivery(true);
                if ($o->products) {
                    foreach ($o->products as $p) {
                        $productInCredit = new ProductInCreditNote();
                        $productInCredit->setOrder($o);
                        $productInCredit->setProductInOrder($p);
                        $productInCredit->setCount($p->count);
                        $this->facade->getEm()->persist($productInCredit);
                    }
                }
            }
            $this->facade->save();
        }

        die;
    }*/

    public function actionImportDpdPickup()
    {
        $this->cronFacade->importDpdPickup();
        die;
    }

    public function actionSendNotifications()
    {
        $this->webPushNotifyFac->sendNotificationCron();
        die;
    }

    // Update list of product's category for Mall
    // Call on: .../cron/update-list-category-mall
    // Perioda of call: 1x / week
    public function actionUpdateListCategoryMall()
    {
        $this->categoryUpdater->checkMallCategory();
        echo "OK";
        die;
    }

    public function actionCezarData() {
        $path = 'import-export/';
        $state = $this->readCezarState();
        //$updateDay = $this->readCezarUpdateDay();
        if ($state == 0) {
            $files = glob($path.'*', GLOB_BRACE);
            if ($files) {
                foreach ($files as $f) {
                    @rename($f, mb_strtolower($f));
                }
            }
            /*if ($updateDay == date("Y-m-d")) {
                die;
            }*/
            if (file_exists($path.'audiopro.csv')) {
                if ($this->cezar->products()) {
                    $this->setCezarState(1);
                }
            }
        } elseif($state == 1) {
            if (!is_dir($path.'backup')) {
                mkdir($path.'backup', 0755, true);
            }
            if (file_exists($path.'audiopro.csv')) {
                copy($path.'audiopro.csv', $path.'backup/audiopro.csv');
                unlink($path.'audiopro.csv');
            }
            $this->setCezarState(0);
            $this->setCezarUpdateDay();
        }
        die;
    }

    private function setCezarState($state) {
        file_put_contents('cezar.txt', $state);
    }

    private function readCezarState() {
        if (file_exists('cezar.txt')) {
            return intval(file_get_contents('cezar.txt'));
        } else {
            return 0;
        }
    }

    private function setCezarUpdateDay() {
        file_put_contents('cezar-update.txt', date("Y-m-d H:i:s"));
    }

    private function readCezarUpdateDay() {
        if (file_exists('cezar-update.txt')) {
            return date("Y-m-d", strtotime((file_get_contents('cezar-update.txt'))));
        } else {
            return date("Y-m-d", strtotime("-1 DAY"));
        }
    }
}
