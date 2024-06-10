<?php

namespace Front\Model\Facade;

use Front\Model\Utils\Text\UnitParser;
use Intra\Model\Database\Entity\HomeCredit;
use Intra\Model\Database\Entity\HomeCreditPayment;
use Intra\Model\Facade\OrderFacade;
use Intra\Model\Utils\DPHCounter;
use Kdyby\GeneratedProxy\__CG__\Intra\Model\Database\Entity\PaymentMethod;
use Kdyby\GeneratedProxy\__CG__\Intra\Model\Database\Entity\Product;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;
use Kdyby\Doctrine\EntityManager;
use App\Core\Model\BaseFacade;
use Intra\Model\Database\Entity\Customer;
use Intra\Model\Database\Entity\Orders;
use Intra\Model\Database\Entity\ProductInOrder;
use Intra\Model\Database\Entity\DiscountInOrder;
use Intra\Model\Database\Entity\ThePayPayment;
use Intra\Model\Database\Entity\ProductOperation;
use Intra\Model\Facade\ProductFacade;
use Heureka\ShopCertification;
use Intra\Model\Utils\ProductHelper\ProductHelper;
use Nette\Utils\DateTime;

class OrderProcessFacade extends BaseFacade
{

    /** @var IStorage */
    private $storage;

    /** @var ProductFacade */
    private $productFacade;

    /** @var Boolean */
    public $isProduction;

    /** @var OrderFacade */
    public $orderFacade;

    /** @var ProductHelper @inject */
    public $productHelper;

    public function setProduction($isProduction)
    {
        $this->isProduction = $isProduction;
    }

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(
        EntityManager $em,
        IStorage $storage,
        ProductFacade $productFacade,
        OrderFacade $orderFacade,
        ProductHelper $productHelper
    ) {
        parent::__construct($em);
        $this->storage = $storage;
        $this->productFacade = $productFacade;
        $this->orderFacade = $orderFacade;
        $this->productHelper = $productHelper;
    }

    public function createThePayPayment($data)
    {
        $payment = new ThePayPayment($data);
        $this->insertNew($payment);
        return $payment;
    }

    public function createHomeCreditPayment($data)
    {
        $created = false;
        $homecredit = $this->gEMHomeCredit()->findOneBy(['orderNumber' => $data['orderNumber']]);

        if (!$homecredit) {
            $homecredit = new HomeCredit();
            $homecredit->setOrderNumber($data['orderNumber']);
            $homecredit->setOrderState($data['orderState']);
            $homecredit->setSendDate(new DateTime($data['sentDate']));
            $homecredit->setNotificationDate(new DateTime($data['notificationDate']));
            $homecredit->setSequenceNumber($data['sequenceNumber']);
            $homecredit->setCheckSum($data['checkSum']);
            $homecredit->setHomecreditId($data['id']);
            $homecredit->setState($data['state']);
            $homecredit->setStateReason($data['stateReason']);
            $this->insertNew($homecredit);
            $created = true;
        }

        return [$created, $homecredit];
    }

    public function setAsPay($payment)
    {
        $payment->setIsPay(true);
        $payment->setDateAccept(new \DateTime());
        $this->save();
    }

    public function getMostExpensiveProduct($order)
    {
        $mostExpensive = $this->gem(ProductInOrder::class)->findOneBy([
            "orders" => $order->id,
            'isPojisteni' => 0,
            'isNastaveni' => 0,
            'isGift' => 0,
        ], ['selingPrice' => 'DESC']);

        $all = $this->gem(ProductInOrder::class)->findBy([
            "orders" => $order->id,
            "product" => $mostExpensive->product->id
        ]);

        $price = 0;
        foreach ($all as $item) {
            $price += $item->selingPrice;
        }

        return ['entity' => $mostExpensive, 'price' => $price];

    }

    /**
     * Save and create Order and Customer from basket form values
     * @param array $values of post from basket
     * @return array [Order, boolean - reset psw]
     */
    public function createOrder($values)
    {
        /** @var Customer $customer */
        $customer = null;
        $resetPwd = false;
        $typPlatby = isset($values[ 'payMethod' ]) ? $values[ 'payMethod' ] : $values[ 'typ_platby' ];
        $typDopravy = isset($values[ 'deliveryMethod' ]) ? $values[ 'deliveryMethod' ] : $values[ 'typ_dopravy' ][ "id" ];

        $payMethod = $this->gEMPaymentMethod()->find($typPlatby);
        $deliMethod = $this->gEMDeliveryMethod()->find($typDopravy);

        $currency = $this->gEMCurrency()->find($values[ 'currency' ]);
        $customer = $this->setCustomerData($values[ 'id' ], $values, $currency, $payMethod, $deliMethod,
            isset($values[ 'id' ]) && is_numeric($values[ 'id' ]),
            isset($values[ 'rememberData' ]) && $customer == null);

        if ($customer && isset($values[ 'rememberData' ]) && $customer == null) {
            $resetPwd = $customer->id;
        }

        //heureka ověření
        $options = [
            // Use \Heureka\ShopCertification::HEUREKA_SK if your e-shop is on heureka.sk
            'service' => \Heureka\ShopCertification::HEUREKA_CZ,
        ];

        $overeno = new \Heureka\ShopCertification('549fbe1c4ee8ceaff5c06dabc01fdef9', $options);

        //heureka ověření
        $optionsSk = [
            'service' => \Heureka\ShopCertification::HEUREKA_SK,
        ];
        $overenoSk = new \Heureka\ShopCertification('cab436a776539fede32d0e85e8442734', $optionsSk);

        $mail = isset($values[ 'email' ]) ? $values[ 'email' ] : $customer->email;
        $overeno->setEmail($mail);
        $overenoSk->setEmail($mail);

        /* Objednávka */
        unset($values[ 'id' ]);

        $order = new Orders($values);

        $order->setOrderState($this->gEMOrderState()->findOneBy(['acceptOrder' => 1]));

        if ($customer) {
            $order->setCustomer($customer);
        }

        if (isset($values[ 'isRapid' ])) { // Pokud se jedná o zrychlenou objednávku, tak předvyplňují ze zákazníka
            $order->data($customer->toArray());
            $order->setName($customer->name . ' ' . $customer->surname);
            $order->setContactPerson($customer->nameDelivery . ' ' . $customer->surnameDelivery);
        } else {
            $order->setName($values[ 'name' ] . ' ' . $values[ 'surname' ]);
            $order->setContactPerson($values[ 'nameDelivery' ] . ' ' . $values[ 'surnameDelivery' ]);
        }
        $order->setCurrency($currency);
        $order->setPaymentMethod($payMethod);
        $order->setDeliveryMethod($deliMethod);
        if ($deliMethod->isDPD && isset($values[ 'dpdPickup' ])) {
            $order->setDeliveryPlace($values[ 'dpdPickup' ]);
        } elseif ($deliMethod->isUlozenka && isset($values[ 'ulozenkaPickup' ])) {
            $order->setDeliveryPlace($values[ 'ulozenkaPickup' ]);
        }

        $deliMethodPrice = round($deliMethod->selingPrice / $currency->exchangeRate, $currency->countDecimal);
        $payMethodPrice = round($payMethod->selingPrice / $currency->exchangeRate, $currency->countDecimal);

        $freeDeli = $this->settingEntity('delivery_free')->value / $currency->exchangeRate;

        if ($deliMethod->selingPrice > 0 && floatval($freeDeli) <= floatval($values[ 'cena_celkem_se_slevou' ])) {
            $deliMethodPrice = $payMethodPrice = 0;
        }

        if (isset($values[ 'euVat' ]) && $values[ 'euVat' ] == true) {
            $order->setEuVat(true);
        }
        $order->setPayDeliveryPrice($deliMethodPrice);
        $order->setPayMethodPrice($payMethodPrice);
        $order->setDueDate(new \DateTime('+' . $this->setting('due_invoice_days') . ' days'));
        if (isset($values[ 'comment' ]) && $values[ 'comment' ] != '') {
            $order->setComment($values[ 'comment' ]);
        }

        $this->insertNew($order);

        /* Obsah objednávky - produkty - přidám do objednávky a udělám rezervace na skladu */
        $price = 0;
        $priceBezDPH = $dph = 0;
        $products = [];
        foreach ($values[ 'count' ] as $productId => $count) {

            $product = $this->gEMProduct()->find($productId);

            $productHelper = new ProductHelper($this->storage, $this->productHelper->getProductFacade(), $this->productHelper->getUser());
            $productHelper->setProduct($product, $currency->toArray());

            $prodInOrder = $this->orderFacade->addNewProduct($order, $product, $currency, $count);

            $sellPrice = $prodInOrder->selingPrice;
            $price += $sellPrice * $count;

            if (!$product->notInFeeds) {
                $overeno->addProductItemId($product->id);
                $overenoSk->addProductItemId($product->id);
            }

            $coeffVat = round($product->vat->value / (100 + $product->vat->value), 4);
            $sellPrice = round($sellPrice / $currency->exchangeRate, $currency->countDecimal);
            $priceBezDPH += round($sellPrice - ($sellPrice * $coeffVat), 2);

            // Provedu rezervaci kusů na skladu
            $this->productFacade->addStockOperation($product, -$count, null, '', null, $order->id, 1);

            // přidám případné pojištění
            $pojisteniPrice = 0;
            if (isset($values[ 'pojisteni' ][ $product->id ])) {
                $pojisteni = $this->addPojisteni($order, $product, $count, $values[ 'pojisteni_one' ][ $product->id ],
                    $currency);
                $price += $pojisteni->selingPrice * $count;
                $pojisteniPrice = $pojisteni->selingPrice;
            }

            // přidám případné nastavení
            $nastaveniPrice = 0;
            if (isset($values[ 'nastaveni' ][ $product->id ])) {
                $nastaveni = $this->addNastaveni($order, $product, $count, $currency);
                $price += $nastaveni->selingPrice * $count;
                $nastaveniPrice = $nastaveni->selingPrice;
            }

            $productArr[ $prodInOrder->product->id ] = [
                'product' => $product,
                'name' => $prodInOrder->name,
                'count' => $prodInOrder->count,
                'price' => $prodInOrder->selingPrice,
                //'helper' => $productHelper,
                'nastaveni' => $nastaveniPrice,
                'pojisteni' => $pojisteniPrice
            ];
        }

        $priceBezDoprav = $price;
        /* Dodatek k objednávce -> VS + total price */
        $price += $deliMethodPrice + $payMethodPrice;

        /* Sleva - pokud je zadaná, tak zkontroluji její kód a pokud je ok, tak ji aplikuji */
        if (isset($values[ 'cena_sleva_code_input' ]) && $values[ 'cena_sleva_code_input' ] != '') {
            $discount = $this->gEMDiscount()->findOneBy([
                'code' => trim($values[ 'cena_sleva_code_input' ]),
                'active' => 1,
                'dateForm <=' => new \DateTime(),
                'dateTo >=' => new \DateTime()
            ]);
            if (count($discount)) {
                $sleva = ($discount->percent / 100) * $price;
                $price = $price - $sleva;
                $price = round($price, 2);

                $sleva2 = ($discount->percent / 100) * $priceBezDPH;
                $priceBezDPH = $priceBezDPH - $sleva2;
                $priceBezDPH = round($priceBezDPH, 2);

                $sleva3 = ($discount->percent / 100) * $priceBezDoprav;
                $priceBezDoprav = $priceBezDoprav - $sleva3;
                $priceBezDoprav = round($priceBezDoprav, 2);

                $dio = new DiscountInOrder();
                $dio->setDiscount($discount);
                $dio->setOrders($order);
                $dio->setPercent($discount->percent);
                $this->insertNew($dio);
            }
        }
        $this->orderFacade->recountPrice($order->id);

        $n = 4 - strlen($order->id);
        $no = substr(date("Y"), 2);
        for ($i = 0; $i < $n; $i++) {
            $no .= "0";
        }
        $order->setVariableSymbol($no . $order->id);
        $this->save();
        $overeno->setOrderId(intval($no . $order->id));
        $overenoSk->setOrderId(intval($no . $order->id));
        if ($this->isProduction) //pouze na produkci posílat na Heureku
        {
            $overeno->logOrder();
            $overenoSk->logOrder();
        }

        return [
            'order' => $order,
            'resetPwd' => $resetPwd,
            'payMethod' => $payMethod,
            'priceBezDPH' => $priceBezDPH,
            'dph' => $priceBezDoprav - $priceBezDPH,
            'delivery' => $price - $priceBezDoprav,
            'products' => $products
        ];
    }

    public function seDeliveryPlace($customer, $deliMethod, $value)
    {

        if ($deliMethod->isDPD && isset($value[ 'typ_dopravy' ][ 'place' ])) {
            $customer->setDeliveryPlace($value[ 'typ_dopravy' ][ 'place' ]);
        } elseif ($deliMethod->isUlozenka && isset($value[ 'typ_dopravy' ][ 'place' ])) {
            $customer->setDeliveryPlace($value[ 'typ_dopravy' ][ 'place' ]);
        }

        return $customer;
    }

    public function setCustomerData(
        $custId,
        $values,
        $currency,
        $payMethod,
        $deliMethod,
        $filled = false,
        $remember = false
    ) {

        $customer = null;

        if ($filled) {
            $customer = $this->gEMCustomer()->find($custId);
            $customer->data($values);
            $customer->setCurrency($currency);
            $customer->setPaymentMethod($payMethod);
            $customer->setDeliveryMethod($deliMethod);

            if ($customer->email == "") {
                $customer->setEmail($values[ "email" ]);
            }

            $customer = $this->seDeliveryPlace($customer, $deliMethod, $values);
            $this->save();
        }

        if ($remember) {

            $customer = $this->gEMCustomer()->findOneBy(['email' => $values[ 'email' ]]);
            $isnew = false;
            if (!count($customer)) {// Zkontroluji, zda email nemám zadán, pokud ano, tak jej aktualizuji a resetuji heslo
                $customer = new Customer($values);
                $isnew = true;
            } else {
                unset($values[ 'id' ]);
                $customer->data($values);
            }

            if ($customer->email == "") {
                $customer->setEmail($values[ "email" ]);
            }

            $customer->setCurrency($currency);
            $customer->setPaymentMethod($payMethod);
            $customer->setDeliveryMethod($deliMethod);

            $customer = $this->seDeliveryPlace($customer, $deliMethod, $values);

            if ($isnew) {
                $this->insertNew($customer);
            } else {
                $this->save();
            }

        }

        return $customer;

    }

    public function addPojisteni($order, $product, $count = 1, $price, $currency)
    {
        $counter = new DPHCounter();
        $price = round($price / $currency->exchangeRate, $currency->countDecimal);
        $counter->setPriceWithDPH($price, $product->vat->value, $count);

        $prodOrderP = $this->gEMProductInOrder()->findOneBy([
            "orders" => $order->id,
            "product" => $product->id,
            "isPojisteni" => 1
        ]);

        if ($prodOrderP) {
            $prodOrderP->setSelingPrice($counter->getOnePrice());
            $prodOrderP->setCount($count);
            $this->save();
        } else {
            $prodOrderP = new ProductInOrder();
            $prodOrderP->setProduct($product);
            $prodOrderP->setSelingPrice($counter->getOnePrice());
            $prodOrderP->setCount($count);
            $prodOrderP->setOrders($order);
            $prodOrderP->setName('Prodloužení záruky na 36 měsíců k produktu: ' . $product->name);
            $prodOrderP->setIsPojisteni(true);
            $this->insertNew($prodOrderP);
        }

        return $prodOrderP;
    }

    public function addNastaveni($order, $product, $count = 1, $currency)
    {
        $counter = new DPHCounter();
        $price = round($product->priceInstallSettingAdd / $currency->exchangeRate, $currency->countDecimal);
        $counter->setPriceWithDPH($price, $product->vat->value, $count);

        $prodOrderP = $this->gEMProductInOrder()->findOneBy([
            "orders" => $order->id,
            "product" => $product->id,
            "isNastaveni" => 1
        ]);

        if ($prodOrderP) {
            $prodOrderP->setSelingPrice($counter->getOnePrice());
            $prodOrderP->setCount($count);
            $this->save();
        } else {
            $prodOrderP = new ProductInOrder();
            $prodOrderP->setProduct($product);
            $prodOrderP->setSelingPrice($counter->getOnePrice());
            $prodOrderP->setCount($count);
            $prodOrderP->setOrders($order);
            $prodOrderP->setName('Instalace a nastavení produktu: ' . $product->name);
            $prodOrderP->setIsNastaveni(true);
            $this->insertNew($prodOrderP);
        }

        return $prodOrderP;
    }

}
