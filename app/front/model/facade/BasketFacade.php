<?php

namespace Front\Model\Facade;

use Front\Model\Utils\Text\UnitParser;
use Intra\Components\MailSender\MailSender;
use Intra\Model\Database\Entity\Currency;
use Intra\Model\Database\Entity\Customer;
use Intra\Model\Database\Entity\DeliveryMethod;
use Intra\Model\Database\Entity\DiscountInCategory;
use Intra\Model\Database\Entity\DiscountInOrder;
use Intra\Model\Database\Entity\Orders;
use Intra\Model\Database\Entity\OrderState;
use Intra\Model\Database\Entity\PaymentMethod;
use Intra\Model\Database\Entity\Product;
use Intra\Model\Database\Entity\ProductInOrder;
use Intra\Model\Facade\ProductFacade;
use Intra\Model\Utils\DPHCounter;
use Intra\Model\Utils\ProductHelper\ProductHelper;
use Nette\Caching\IStorage;
use Kdyby\Doctrine\EntityManager;
use App\Core\Model\BaseFacade;
use Nette\Utils\DateTime;
use Soukicz\Zbozicz\Client;
use Soukicz\Zbozicz\Order;
use Soukicz\Zbozicz\CartItem;
use Tracy\Debugger;

class BasketFacade extends BaseFacade
{

    /** @var IStorage */
    private $storage;

    /** @var ProductHelper */
    private $productHelper;

    /** @var MailSender */
    private $mailSender;

    /** @var ProductFacade */
    private $productFacade;

    /** @var Boolean */
    public $isProduction;

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
        ProductHelper $productHelper,
        MailSender $mailSender,
        ProductFacade $productFacade
    ) {
        parent::__construct($em);
        $this->storage = $storage;
        $this->productHelper = $productHelper;
        $this->mailSender = $mailSender;
        $this->productFacade = $productFacade;
    }

    /**
     * Insert/manage product in basket
     * @param $sess
     * @param Product $product
     * @param $count
     * @param $pojisteni
     * @param $nastaveni
     * @param $gift
     * @param $user
     * @param $increment
     * @param $order
     * @return array
     */
    public function insertToBasket(
        $sess,
        Product $product,
        $count,
        $pojisteni,
        $nastaveni,
        $gift,
        $user,
        $increment = true,
        $order = null,
        $packageItem = null
    ) {
        if ($order == null) {
            /** @var Orders $order */
            $order = $this->createOrReturnOrder($sess, $user);
        }

        if (!$order) {
            return null;
        }

        /** @var ProductInOrder $orderProduct */
        $orderProduct = $this->manageProductInOrder($order, $product, $count, $sess, $increment, $packageItem);

        if ($product && $product->productMark && $product->productMark->extendedWarrantyFree) {
            $pojisteni = 1;
        }

        $this->managePojisteniInOrder($order, $product, $orderProduct, $pojisteni, $sess->actualCurrency);
        $this->manageNastaveniInOrder($order, $product, $orderProduct, $nastaveni, $sess->actualCurrency);
        $this->manageGiftInOrder($order, $product, $orderProduct, $gift);
        $this->save();

        return [$order, $orderProduct];
    }

    /**
     * Get basket preview (prices, counts)
     * @param $order
     * @return array
     */
    public function getBasketPreview($order)
    {
        if (is_numeric($order)) {
            $order = $this->gEMOrders()->find($order);
        }

        $productsInOrder = null;

        if ($order) {
            $productsInOrder = $this->gEMProductInOrder()->findBy([
                "orders" => $order->id
            ], ['isPojisteni' => 'ASC', 'isNastaveni' => 'ASC']);
        }
        //TODO cache

        $overView = [
            'totalPrice' => 0,
            'totalCount' => 0,
            'freeDelivery' => false,
            'products' => []
        ];
        if ($productsInOrder) {
            foreach ($productsInOrder as $productInOrder) { // nastaveni / pojisteni
                if ($productInOrder->isPojisteni || $productInOrder->isNastaveni) {
                    if (!$productInOrder->parentProduct) {
                        continue;
                    }
                    if ($productInOrder->product && isset($overView['products'][$productInOrder->parentProduct->id])) { // Add price from pojisteni to overView
                        $overView['products'][$productInOrder->parentProduct->id]['price'] += $productInOrder->selingPrice;
                    }
                } else { // Product
                    if ($productInOrder->isGift) {
                        continue;
                    }
                    if ($productInOrder->product) {
                        $overView['products'][$productInOrder->id] = [
                            'id' => $productInOrder->product->id,
                            'img' => $productInOrder->product->mainImage,
                            'name' => $productInOrder->name,
                            'count' => $productInOrder->count,
                            'price' => $productInOrder->selingPrice
                        ];
                    }
                    $overView['totalCount'] += $productInOrder->count;
                }
                $overView['totalPrice'] += ($productInOrder->selingPrice * $productInOrder->count);
            }
        }
        
        if ($overView[ 'freeDelivery' ] == false && $order && $overView[ 'totalPrice' ] >= (int)($this->setting('delivery_free') / $order->currency->exchangeRate)) { // nastavení dopravy zdarma
            $overView[ 'freeDelivery' ] = true;
        }
        return $overView;
    }

    /**
     * Get and sort products in order for basket preview
     * @param $order
     * @param $sess
     * @param $currency
     * @return array
     */
    public function getProductsInOrder($order, $sess, $currency)
    {
        if (is_numeric($order)) {
            $order = $this->gEMOrders()->find($order);
        }

        if (!$order) {
            return ['totalPrices' => 0, 'products' => []];
        }

        $sale = $this->gem(DiscountInOrder::class)->findOneBy(['orders' => $order->id]);
        $productsInOrder = $this->gEMProductInOrder()->findBy([
            "orders" => $order->id
        ], ['isPojisteni' => 'ASC', 'isNastaveni' => 'ASC']);

        $productArr = [];
        $prices = [
            'totalWithoutDPH' => 0,
            'totalDPH' => 0,
            'totalPrice' => 0,
            'totalWithSale' => 0,
            'totalWithoutDPHWithSale' => 0,
            'totalSale' => 0,
            'totalWithDeliPay' => 0,
            'totalWithoutDPHWithDeliPay' => 0,
            'totalSaleWithDeliPay' => 0,
            'totalWithoutDPHWithSaleWithDeliPay' => 0,
            'totalWithSaleWithDeliPay' => 0,
            'freeDelivery' => false
        ];
        foreach ($productsInOrder as $productInOrder) {
            $dphCounter = null;

            $isProduct = false;
            if ($productInOrder->isPojisteni) {
                if (!$productInOrder->parentProduct) {
                    continue;
                }
                $productArr[$productInOrder->parentProduct->id][ 'pojisteni-active' ] = true;
                /** @var DPHCounter $dphCounter */
                $dphCounter = $productArr[$productInOrder->parentProduct->id][ 'pojisteni' ];
            } elseif ($productInOrder->isNastaveni) {
                if (!$productInOrder->parentProduct) {
                    continue;
                }
                $productArr[$productInOrder->parentProduct->id]['nastaveni-active'] = true;
                /** @var DPHCounter $dphCounter */
                $dphCounter = $productArr[$productInOrder->parentProduct->id]['nastaveni'];
            } else if ($productInOrder->isGift) {
                if (!$productInOrder->parentProduct) {
                    continue;
                }
                $productArr[$productInOrder->parentProduct->id]['gift'] = $productInOrder->gift;
            } else { // Product
                $isProduct = true;
                $product = $productInOrder->product;
                $productHelper = new ProductHelper($this->storage, $this->productHelper->getProductFacade(), $this->productHelper->getUser());
                $productHelper->setProduct($product, $currency, false, null, $order->customer);
                if ($productInOrder->packageItem) {
                    $productHelper->setProduct($product, $currency, false, $currency['code'] == 'CZK' ? $productInOrder->packageItem->discountCZK : $productInOrder->packageItem->discountEUR, $order->customer);
                }

                $dphNastaveniCounter = new DPHCounter();
                $price = round($product->priceInstallSettingAdd / $currency[ 'exchangeRate' ],
                    $currency[ 'countDecimal' ]);
                $dphNastaveniCounter->setPriceWithDPH($price, $product->vat->value);
                if (isset($order->customer->euVat)) {
                    $dphNastaveniCounter->setDisableDPH($order->customer->euVat);
                }

                $dphPojisteniCounter = new DPHCounter();
                $price = $productHelper->getPrice() * 0.1;
                if ($product && $product->productMark && $product->productMark->extendedWarranty) {
                    $price = $price * $product->productMark->extendedWarranty;
                }
                if ($product && $product->productMark && $product->productMark->extendedWarrantyFree) {
                    $price = 0;
                }
                $dphPojisteniCounter->setPriceWithDPH($price, $product->vat->value);
                if (isset($order->customer->euVat)) {
                    $dphPojisteniCounter->setDisableDPH($order->customer->euVat);
                }

                $salePercent = 0;
                if ($sale && $sale->discount &&
                    (
                        (
                            $sale->discount->categories && $product
                            && $this->productHasCategory($product, $sale->discount->categories)
                        )
                        ||
                        (
                            $sale->discount->productMarks && $product
                            && $this->productHasMark($product, $sale->discount->productMarks)
                        )
                        ||
                        (
                            !$sale->discount->categories && !$sale->discount->productMarks
                        )
                    )
                ) {
                    $salePercent = max(0, min($sale->percent, 100));
                }

                $productArr[$productInOrder->id] = [
                    'id' => $productInOrder->id,
                    'product' => $product,
                    'name' => $productInOrder->name,
                    'count' => $productInOrder->count,
                    'price' => $productInOrder->selingPrice,
                    //'helper' => $productHelper,
                    'nastaveni' => $dphNastaveniCounter,
                    'pojisteni' => $dphPojisteniCounter,
                    'packageItem' => $productInOrder->packageItem,
                    'salePercent' => $salePercent
                ];
                if ($productHelper->specialExists() && isset($sess->specialView[$product->id])) {
                    $dphCounter = $productHelper->getSpecialDPHCounter();
                } else {
                    $dphCounter = $productHelper->getDPHCounter();
                }
            }
            if ($dphCounter) {
                $index = $productInOrder->id;
                if (!$isProduct) {
                    if ($productInOrder->parentProduct) {
                        $index = $productInOrder->parentProduct->id;
                    } else {
                        continue;
                    }
                }
                if (isset($order->customer->euVat)) {
                    $dphCounter->setDisableDPH($order->customer->euVat);
                }
                $coef = 1;
                $price = $dphCounter->getTotalPrice() * $productArr[$index]['count'];
                $without = $dphCounter->getTotalWithoutDPH() * $productArr[$index]['count'];

                $salePercent = isset($productArr[$index]['salePercent']) ? $productArr[$index]['salePercent'] : null;

                if ($salePercent) {
                    $coef = 1 - $salePercent / 100;
                }

                $prices['totalWithoutDPH'] += $without;
                $prices['totalPrice'] += $price;
                $prices['totalDPH'] += $price - $without;
                $prices['totalWithSale'] += $price * $coef;
                $prices['totalWithoutDPHWithSale'] += $without * $coef;
            }
        }
        $prices[ 'totalWithDeliPay' ] = $prices[ 'totalPrice' ];
        $prices[ 'totalWithoutDPHWithDeliPay' ] = $prices[ 'totalWithoutDPH' ];
        $prices['totalSale'] = $prices['totalWithSale'] - $prices['totalPrice'];
        $prices['totalWithSaleWithDeliPay'] = $prices['totalWithSale'];

        if ($prices[ 'freeDelivery' ] == false && $prices[ 'totalPrice' ] >= ($this->setting('delivery_free') / $currency[ 'exchangeRate' ])) { // nastavení dopravy zdarma
            $prices[ 'freeDelivery' ] = true;
        }
        $order->setFreeDelivery($prices[ 'freeDelivery' ]);

        if ($order->deliveryMethod && !$prices[ 'freeDelivery' ]) {
            $dphCounter = new DPHCounter();
            $dphCounter->setPriceWithDPH($order->deliveryMethod->selingPrice / $currency[ 'exchangeRate' ],
                $order->deliveryMethod->vat->value);
            if (isset($order->customer->euVat)) {
                $dphCounter->setDisableDPH($order->customer->euVat);
            }
            $prices[ 'totalWithoutDPHWithDeliPay' ] += $dphCounter->getTotalWithoutDPH();
            $prices[ 'totalWithDeliPay' ] += $dphCounter->getTotalPrice();
            $prices['totalWithSaleWithDeliPay'] += $dphCounter->getTotalPrice();
            $prices['totalWithoutDPHWithSaleWithDeliPay'] += $dphCounter->getTotalWithoutDPH();
        }

        if ($order->paymentMethod && !$prices[ 'freeDelivery' ]) {
            $dphCounter = new DPHCounter();
            $dphCounter->setPriceWithDPH($order->paymentMethod->selingPrice / $currency[ 'exchangeRate' ],
                $order->paymentMethod->vat->value);
            if (isset($order->customer->euVat)) {
                $dphCounter->setDisableDPH($order->customer->euVat);
            }
            $prices[ 'totalWithoutDPHWithDeliPay' ] += $dphCounter->getTotalWithoutDPH();
            $prices[ 'totalWithDeliPay' ] += $dphCounter->getTotalPrice();
            $prices['totalWithSaleWithDeliPay'] += $dphCounter->getTotalPrice();
            $prices['totalWithoutDPHWithSaleWithDeliPay'] += $dphCounter->getTotalWithoutDPH();
        }

        $prices[ 'totalSaleWithDeliPay' ] = $prices[ 'totalWithSaleWithDeliPay' ] - $prices[ 'totalWithDeliPay' ];

        // Aplication sale
        if ($sale) {
            $prices[ 'sale' ] = $sale;
        }

        $order->setTotalPriceWithoutDeliPay($prices[ 'totalWithSale' ]);
        $order->setTotalPrice($prices[ 'totalWithSaleWithDeliPay' ]);
        $this->save();
        return ['totalPrices' => $prices, 'products' => $productArr];
    }


    /**
     * Handler for change currency on order
     * @param $sess
     * @param $currency
     */
    public function changeCurrencyOnOrder($sess, $currency)
    {
        if (isset($sess->basketOrder)) {
            $order = $this->gEMOrders()->find($sess->basketOrder);
            if (!$order) {
                return;
            }
        } else {
            return;
        }

        $productsInOrder = $this->gEMProductInOrder()->findBy([
            "orders" => $order->id
        ], ['isPojisteni' => 'ASC', 'isNastaveni' => 'ASC', 'isGift' => 'ASC']);

        $productArr = [];
        foreach ($productsInOrder as $productInOrder) { // nastaveni / pojisteni
            if ($productInOrder->isPojisteni) {
                if (!$productInOrder->parentProduct) {
                    continue;
                }
                $this->managePojisteniInOrder($order, $productInOrder->product,
                    $productArr[ $productInOrder->parentProduct->id ], 1, $currency);
            } elseif ($productInOrder->isNastaveni) {
                if (!$productInOrder->parentProduct) {
                    continue;
                }
                $this->manageNastaveniInOrder($order, $productInOrder->product,
                    $productArr[$productInOrder->parentProduct->id], 1, $currency);
            } elseif ($productInOrder->isGift) {
                if (!$productInOrder->parentProduct) {
                    continue;
                }
                $this->manageGiftInOrder($order, $productInOrder->product,
                    $productArr[ $productInOrder->parentProduct->id ], $productInOrder->gift);
            } else { // Product
                $productArr[ $productInOrder->id ] =
                    $this->manageProductInOrder($order, $productInOrder->product, 0, $sess, true, $productInOrder->packageItem);
            }
        }
        $order->setCurrency($this->gEMCurrency()->find($currency[ 'id' ]));
        $this->save();
    }

    /**
     * Manage product in order (add, edit, delete)
     * @param Orders $order
     * @param Product $product
     * @param $count
     * @param $pojisteni
     * @param $nastaveni
     * @param $sess
     * @param $increment
     * @param $gift
     * @return ProductInOrder|null
     */
    public function manageProductInOrder(Orders $order, Product $product, $count, $sess, $increment = true, $packageItem = null)
    {
        $isNew = false;
        /** @var ProductInOrder $orderProduct */
        $orderProduct = $this->gEMProductInOrder()->findOneBy(["product" => $product->id, "orders" => $order->id, 'packageItem' => $packageItem]);
        if (!$orderProduct) {
            $orderProduct = new ProductInOrder();
            $isNew = true;
        }

        if ($packageItem) {
            $this->productHelper->setProduct($product, $sess->actualCurrency, false, $sess->actualCurrency['code'] == 'CZK' ? $packageItem->discountCZK : $packageItem->discountEUR, $order->customer);
        } else {
            $this->productHelper->setProduct($product, $sess->actualCurrency, false, null, $order->customer);
        }

        $discountOrder = isset($order->discount[0]->discount) ? $order->discount[0] : null;

        $saleCoef = 1;
        if ($discountOrder && $discountOrder->discount &&
            (
                (
                    $discountOrder->discount->categories && $product
                    && $this->productHasCategory($product, $discountOrder->discount->categories)
                )
                ||
                (
                    $discountOrder->discount->productMarks && $product
                    && $this->productHasMark($product, $discountOrder->discount->productMarks)
                )
                ||
                (
                    !$discountOrder->discount->categories && !$discountOrder->discount->productMarks
                )
            )
        ) {
            $saleCoef = 1 - max(0, min($discountOrder->percent, 100)) / 100;
        }

        $orderProduct->setProduct($product);
        $orderProduct->setName($product->name);
        if (isset($sess->specialView[$product->id])) {
            $orderProduct->setBasePrice($this->productHelper->getSpecialPrice());
            $orderProduct->setSelingPrice($this->productHelper->getSpecialPrice() * $saleCoef);
        } else {
            $orderProduct->setBasePrice($this->productHelper->getPrice());
            $orderProduct->setSelingPrice($this->productHelper->getPrice() * $saleCoef);
        }
        if ($increment) {
            $orderProduct->setCount($orderProduct->count + $count);
        } else {
            $orderProduct->setCount($count);
        }
        if (!$product->active || $product->saleTerminated) {
            $orderProduct->setCount(0);
        }
        $orderProduct->setUnit(UnitParser::parse($product->unit, $orderProduct->count));
        $orderProduct->setOrders($order);
        if ($packageItem) {
            $orderProduct->setPackageItem($packageItem);
        }

        $order->setLastUpdate(new \DateTime());

        if ($isNew) {
            $this->insertNew($orderProduct);
        }

        if ($orderProduct->count <= 0) { // If count <= 0 - delete product from order
            $childProducts = $this->gEMProductInOrder()->findBy(['parentProduct' => $orderProduct]);
            if ($childProducts) {
                foreach ($childProducts as $p) {
                    $this->em->remove($p);
                }
            }
            $this->em->remove($orderProduct);
            return null;
        }

        return $orderProduct;
    }

    /**
     * Manage pojisteni in order (add, edit, delete)
     * @param Orders $order
     * @param Product $product
     * @param ProductInOrder $orderProduct
     * @param $pojisteni
     * @param $currency
     * @return ProductInOrder|null
     */
    public function managePojisteniInOrder(
        Orders $order,
        Product $product,
        $orderProduct,
        $pojisteni,
        $currency
    ) {
        $isNew = false;
        /** @var ProductInOrder $prodOrderP */
        $prodOrderP = $this->gEMProductInOrder()->findOneBy([
            "orders" => $order->id,
            "product" => $product->id,
            "isPojisteni" => 1,
            'parentProduct' => $orderProduct
        ]);

        if ($orderProduct == null || $pojisteni == 0) { // If don't exist ProductInOrder - delete from order
            if ($prodOrderP) {
                $this->em->remove($prodOrderP);
            }
            return null;
        }

        if ($pojisteni == -1 && !$prodOrderP) {
            return null;
        }

        if (!$prodOrderP) {
            $prodOrderP = new ProductInOrder();
            $isNew = true;
        }

        if ($orderProduct->packageItem) {
            $this->productHelper->setProduct($product, $currency, false, $currency['code'] == 'CZK' ? $orderProduct->packageItem->discountCZK : $orderProduct->packageItem->discountEUR, $order->customer);
        } else {
            $this->productHelper->setProduct($product, $currency, false, null, $order->customer);
        }

        $discountOrder = isset($order->discount[0]->discount) ? $order->discount[0] : null;

        $saleCoef = 1;
        if ($discountOrder && $discountOrder->discount &&
            (
                (
                    $discountOrder->discount->categories && $product
                    && $this->productHasCategory($product, $discountOrder->discount->categories)
                )
                ||
                (
                    $discountOrder->discount->productMarks && $product
                    && $this->productHasMark($product, $discountOrder->discount->productMarks)
                )
                ||
                (
                    !$discountOrder->discount->categories && !$discountOrder->discount->productMarks
                )
            )
        ) {
            $saleCoef = 1 - max(0, min($discountOrder->percent, 100)) / 100;
        }

        $prodOrderP->setProduct($product);
        $price = $this->productHelper->getPrice() * 0.1;
        $monthCount = 36;
        if ($product && $product->productMark && $product->productMark->extendedWarranty) {
            $price = $price * $product->productMark->extendedWarranty;
            $monthCount = 12 * $product->productMark->extendedWarranty;
        }
        if ($product && $product->productMark && $product->productMark->extendedWarrantyFree) {
            $price = 0;
        }
        $prodOrderP->setBasePrice($price);
        $prodOrderP->setSelingPrice($price * $saleCoef);
        $prodOrderP->setCount($orderProduct->count);
        $prodOrderP->setName('Prodloužení záruky na '.$monthCount.' měsíců k produktu: ' . $product->name);
        $prodOrderP->setIsPojisteni(true);
        $prodOrderP->setOrders($order);
        $prodOrderP->setParentProduct($orderProduct);

        if ($isNew) {
            $this->insertNew($prodOrderP);
        }

        return $prodOrderP;
    }

    /**
     * Manage nastaveni in order (add, edit, delete)
     * @param Orders $order
     * @param Product $product
     * @param ProductInOrder $orderProduct
     * @param $nastaveni
     * @param $currency
     * @return ProductInOrder|null
     */
    public function manageNastaveniInOrder(
        Orders $order,
        Product $product,
        $orderProduct,
        $nastaveni,
        $currency
    ) {
        $isNew = false;

        /** @var ProductInOrder $prodOrderP */
        $prodOrderP = $this->gEMProductInOrder()->findOneBy([
            "orders" => $order->id,
            "product" => $product->id,
            "isNastaveni" => 1,
            'parentProduct' => $orderProduct
        ]);

        if ($orderProduct == null || $nastaveni == 0) { // If don't exist ProductInOrder - delete from order
            if ($prodOrderP) {
                $this->em->remove($prodOrderP);
            }
            return null;
        }

        if ($nastaveni == -1 && !$prodOrderP) {
            return null;
        }

        if (!$prodOrderP) {
            $prodOrderP = new ProductInOrder();
            $isNew = true;
        }
        $price = round($product->priceInstallSettingAdd / $currency[ 'exchangeRate' ], $currency[ 'countDecimal' ]);

        $discountOrder = isset($order->discount[0]->discount) ? $order->discount[0] : null;

        $saleCoef = 1;
        if ($discountOrder && $discountOrder->discount &&
            (
                (
                    $discountOrder->discount->categories && $product
                    && $this->productHasCategory($product, $discountOrder->discount->categories)
                )
                ||
                (
                    $discountOrder->discount->productMarks && $product
                    && $this->productHasMark($product, $discountOrder->discount->productMarks)
                )
                ||
                (
                    !$discountOrder->discount->categories && !$discountOrder->discount->productMarks
                )
            )
        ) {
            $saleCoef = 1 - max(0, min($discountOrder->percent, 100)) / 100;
        }

        $prodOrderP->setProduct($product);
        $prodOrderP->setBasePrice($price);
        $prodOrderP->setSelingPrice($price * $saleCoef);
        $prodOrderP->setCount($orderProduct->count);
        $prodOrderP->setName('Instalace a nastavení produktu: ' . $product->name);
        $prodOrderP->setIsNastaveni(true);
        $prodOrderP->setOrders($order);
        $prodOrderP->setParentProduct($orderProduct);

        if ($isNew) {
            $this->insertNew($prodOrderP);
        }

        return $prodOrderP;
    }

    /**
     * If order dont exist -> create. If exist return it.
     * @param $sess
     * @param $user
     * @return Orders
     */
    public function createOrReturnOrder($sess, $user)
    {
        $order = null;
        if (!isset($sess->basketOrder) || !is_numeric($sess->basketOrder)) {
            $order = new Orders();
            $this->insertNew($order);
            $order->setCurrency($this->gem(Currency::class)->find($sess->actualCurrency[ 'id' ]));
            $order->setDeliveryMethod($this->gem(DeliveryMethod::class)->findOneBy(['active' => '1']));
            $order->setPaymentMethod($this->gem(PaymentMethod::class)->findOneBy(['active' => '1'],
                ['orderState' => 'ASC']));
            $sess->basketOrder = $order->id;
        } else {
            $order = $this->gem(Orders::class)->find($sess->basketOrder);
            if (!$order) {
                unset($sess->basketOrder);
                return $this->createOrReturnOrder($sess, $user);
            }
        }
        $this->addCustomerToOrder($user, $sess);
        return $order;
    }

    /**
     * Add information about order and customer from basket
     * @param $sess
     * @param $values
     */
    public function addOrderInformation($sess, $user, $values)
    {
        if ($sess->basketOrder) {
            $order = $this->gem(Orders::class)->find($sess->basketOrder);
        } else {
            return null;
        }
        unset($values[ 'id' ]);
        $typPlatby = $values[ 'typ_platby' ];
        $typDopravy = $values[ 'typ_dopravy' ][ "id" ];

        $payMethod = $this->gem(PaymentMethod::class)->find($typPlatby);
        $deliMethod = $this->gem(DeliveryMethod::class)->find($typDopravy);

        $currency = $this->gem(Currency::class)->find($values[ 'currency' ]);
        list($sess->needResetPassword, $customer) = $this->setCustomerData($values, $currency, $payMethod, $deliMethod, $user);

        $this->addCustomerToOrder($user, $sess, $customer);

        $order->setCurrency($currency);
        $order->setPaymentMethod($payMethod);
        $order->setDeliveryMethod($deliMethod);
        $order->setExpeditionToday($values['expeditionToday']);

        $order->setDeliveryPlace(null);
        if ($deliMethod->isDPD) {
            $order->setDeliveryPlace($values[ 'dpdPickup' ]);
        } elseif ($deliMethod->isUlozenka) {
            $order->setDeliveryPlace($values[ 'ulozenkaPickups' ]);
        } elseif ($deliMethod->isZasilkovna) {
            $order->setDeliveryPlace($values[ 'zasilkovna' ]);
        }
        $deliMethodPrice = round($deliMethod->selingPrice / $currency->exchangeRate, $currency->countDecimal);
        $payMethodPrice = round($payMethod->selingPrice / $currency->exchangeRate, $currency->countDecimal);

        if ($order->freeDelivery) {
            $deliMethodPrice = $payMethodPrice = 0;
        }

        if ($customer && $customer->euVat) {
            $order->setEuVat(true);
        }

        $order->setPayDeliveryPrice($deliMethodPrice);
        $order->setPayMethodPrice($payMethodPrice);


        if (isset($values[ 'comment' ]) && $values[ 'comment' ] != '') {
            $order->setComment($values[ 'comment' ]);
        }

        $state = $this->gem(OrderState::class)->findOneBy(['stateNotFinished' => 1]);
        if ($state) {
            $order->setOrderState($state);
        }

        $this->save();

        /* Obsah objednávky - produkty - přidám do objednávky */
        foreach ($values[ 'count' ] as $productInOrderId => $count) {
            $productInOrder = $this->gem(ProductInOrder::class)->find($productInOrderId);

            $pojisteni = 0; // přidám případné pojištění
            if (isset($values[ 'pojisteni' ][ $productInOrder->id ])) {
                $pojisteni = 1;
            }
            $nastaveni = 0; // přidám případné nastavení
            if (isset($values[ 'nastaveni' ][ $productInOrder->id ])) {
                $nastaveni = 1;
            }
            $gift = null;

            $this->insertToBasket($sess, $productInOrder->product, $count, $pojisteni, $nastaveni, $gift, $user, false, $order, $productInOrder->packageItem);
        }
        $this->save();
    }

    /**
     * Set Customer data from basket order
     * @param $values
     * @param $currency
     * @param $payMethod
     * @param $deliMethod
     * @param $user
     * @param bool $remember
     * @return array
     * @throws \Exception
     */
    public function setCustomerData(
        $values,
        $currency,
        $payMethod,
        $deliMethod,
        $user
    ) {
        $customer = null;
        $resPass = false;

        if (isset($values['vatNo2']) && $values['vatNo2']) {
            $values[ 'vatPay' ] = true;
        }
        if ($this->isCustomerLogin($user)) {
            $customer = $this->gem(Customer::class)->find($user->id);
            $customer->data($values);
            $customer->setCurrency($currency);
            $customer->setPaymentMethod($payMethod);
            $customer->setDeliveryMethod($deliMethod);
            if (!$customer->foundedDate) {
                $customer->setFoundedDate(new DateTime());
            }
            if (!$customer->ip) {
                $customer->setIp($_SERVER['REMOTE_ADDR']);
            }
            $this->save();
        } else {
            $customer = $this->gem(Customer::class)->findOneBy(['email' => $values[ 'email' ]]);
            $isnew = false;
            if (!$customer) { // Zkontroluji, zda email nemám zadán, pokud ano, tak jej aktualizuji a resetuji heslo
                $customer = new Customer($values);
                $isnew = true;
            } else {
                $customer->data($values);
            }

            $customer->setCurrency($currency);
            $customer->setPaymentMethod($payMethod);
            $customer->setDeliveryMethod($deliMethod);
            if (!$customer->foundedDate) {
                $customer->setFoundedDate(new DateTime());
            }
            if (!$customer->ip) {
                $customer->setIp($_SERVER['REMOTE_ADDR']);
            }

            if ($isnew) {
                $this->insertNew($customer);
            }
            $resPass = true;
        }

        $customer->setEuVat(false);
        // Check if customer is not EU vat pay:
        if ($customer->isCompany) { // Pokud se jedná o firmu
            if (isset($values[ 'vatNo2' ]) && $values[ 'vatNo2' ] != "") { // Pokud má evropské DIČ, které je v EU.
                $euState = [
                    'BE',
                    'BG',
                    'DK',
                    'DE',
                    'EE',
                    'IE',
                    'EL',
                    'ES',
                    'FR',
                    'HR',
                    'IT',
                    'CY',
                    'LV',
                    'LT',
                    'LU',
                    'HU',
                    'MT',
                    'NL',
                    'AT',
                    'PL',
                    'PT',
                    'RO',
                    'SI',
                    'SK',
                    'FI',
                    'SE'
                ];
                if (in_array(substr($values[ 'vatNo2' ], 0, 2), $euState)) {
                    $customer->setEuVat(true); // Tak nastavím nulové DPHčko.
                }
            }
        }
        $this->save();
        return [$resPass, $customer];
    }

    /**
     * If user is logged in, add him to order and change state order to non-finished
     * @param $user
     * @param $order
     */
    public function addCustomerToOrder($user, $sess, $customer = null)
    {
        if ($this->isCustomerLogin($user) || $customer !== null) { // pokud je přihlášený customer, tak ho přidám k objednávce
            if ($customer == null) {
                $customer = $this->gem(Customer::class)->find($user->id);
            }
            if (isset($sess->basketOrder)) {
                $order = $this->gem(Orders::class)->find($sess->basketOrder);
                if (!$order) {
                    return;
                }
            } else { // pokud má nějakou nedokončenou z minula, tak ji pokusím natáhnout :)
                $order = $this->gem(Orders::class)->findOneBy([
                    'customer' => $user->id,
                    'orderState.stateNotFinished' => 1
                ], ['id' => 'DESC']);
                if (!$order) {
                    return;
                }
                $sess->basketOrder = $order->id;
            }
            $data = $customer->toArray();
            $data[ 'payVat' ] = $customer->vatPay;
            $data[ 'name' ] = $customer->name . ' ' . $customer->surname;
            $data[ 'contactPerson' ] = $customer->nameDelivery . ' ' . $customer->surnameDelivery;
            unset($data[ 'id' ]);

            unset($data[ 'currency' ]);
            if ($data[ 'deliveryMethod' ]) {
                $data[ 'deliveryMethod' ] = $this->gem(DeliveryMethod::class)->find($data[ 'deliveryMethod' ]);
            } else {
                unset($data[ 'deliveryMethod' ]);
            }
            if ($data[ 'paymentMethod' ]) {
                $data[ 'paymentMethod' ] = $this->gem(PaymentMethod::class)->find($data[ 'paymentMethod' ]);
            } else {
                unset($data[ 'paymentMethod' ]);
            }
            unset($data['foundedDate']);
            $order->data($data);
            $order->setCustomer($customer);
            $state = $this->gem(OrderState::class)->findOneBy(['stateNotFinished' => 1]);
            if ($state) {
                $order->setOrderState($state);
            }
            if (isset($sess->subscription->id)) {
                $sub = $this->gEMWebPushSubscription()->find($sess->subscription->id);
                if ($sub) {
                    $sub->setCustomer($customer);
                }
            }
            $this->save();
        }
    }

    /**
     * Check if customer is login
     * @param $user
     * @return bool
     */
    public function isCustomerLogin($user)
    {
        return ($user->loggedIn && isset($user->roles[ 0 ]) && $user->roles[ 0 ] === 'visitor');
    }

    /**
     * Add discout to order
     * @param $sess
     * @param string $code
     */
    public function addDiscount($sess, $code)
    {
        $discount = $this->gEMDiscount()->findOneBy([
            'code' => trim($code),
            'active' => 1,
            'dateForm <=' => new \DateTime(),
            'dateTo >=' => new \DateTime()
        ]);
        if (!$discount) {
            return false;
        }

        if (!isset($sess->basketOrder)) {
            return;
        }
        $order = $this->gem(Orders::class)->find($sess->basketOrder);

        $old = $this->gem(DiscountInOrder::class)->findOneBy(['orders' => $order->id]);
        if ($old) {
            $this->remove($old);
        }

        $discountInOrder = new DiscountInOrder();
        $discountInOrder->setOrders($order);
        $discountInOrder->setDiscount($discount);
        $discountInOrder->setPercent($discount->percent);
        $this->insertNew($discountInOrder);

        $order->setLastUpdate(new \DateTime());
        $this->save();
        return true;
    }

    /**
     * Change delivery/payment method on order
     * @param $sess
     * @param $method
     * @param $type3
     */
    public function changeMethod($sess, $method, $type, $dpdPickup)
    {
        if ($type === 'payment') {
            $this->changePaymentMethod($sess, $method);
        } elseif ($type === 'delivery') {
            $this->changeDeliveryMethod($sess, $method, $dpdPickup);
        }
    }

    /**
     * @param $sess
     * @param $method
     */
    public function changePaymentMethod($sess, $method)
    {
        $order = $this->gem(Orders::class)->find($sess->basketOrder);
        $method = $this->gem(PaymentMethod::class)->find($method);
        if ($method && $order) {
            $order->setPaymentMethod($method);
            $order->setPayMethodPrice($method->selingPrice);
            if ($order->freeDelivery) {
                $order->setPayMethodPrice(0);
            }
            $order->setLastUpdate(new \DateTime());
            $this->save();
        }
    }

    /**
     * @param $sess
     * @param $method
     */
    public function changeDeliveryMethod($sess, $method, $dpdPickup)
    {
        $order = $this->gem(Orders::class)->find($sess->basketOrder);
        $method = $this->gem(DeliveryMethod::class)->find($method);
        if ($method && $order) {
            $order->setDeliveryMethod($method);
            $order->setPayDeliveryPrice($method->selingPrice);
            if ($order->freeDelivery) {
                $order->setPayMethodPrice(0);
            }
            if ($method->id == 2) {
                $order->setDeliveryPlace($dpdPickup);
            } else {
                $order->setDeliveryPlace(null);
            }
            $order->setLastUpdate(new \DateTime());
            $this->save();
            if ($method->id == 5 && $sess['actualCurrency']['code'] != 'CZK' && $order->paymentMethod->id == 1) {
                $this->changePaymentMethod($sess, 3);
            }
        }
    }

    /**
     * Finish order
     * @param $sess
     */
    public function finishOrder($sess)
    {
        /** @var Orders $order */
        $order = null;
        if ($sess->basketOrder) {
            $order = $this->gem(Orders::class)->find($sess->basketOrder);
        } else {
            return null;
        }

        $n = 4 - strlen($order->id);
        $no = substr(date("Y"), 2);
        for ($i = 0; $i < $n; $i++) {
            $no .= "0";
        }
        $order->setVariableSymbol($no . $order->id);

        //heureka ověření
        /*$options = [
            // Use \Heureka\ShopCertification::HEUREKA_SK if your e-shop is on heureka.sk
            'service' => \Heureka\ShopCertification::HEUREKA_CZ,
        ];

        $overeno = new \Heureka\ShopCertification('549fbe1c4ee8ceaff5c06dabc01fdef9', $options);
        $clientZbozi = new Client(126721, "9sw8YlQzIVn9Mo/5r9GtHCh7AkwfTCqK", false);

        //heureka ověření
        $optionsSk = [
            'service' => \Heureka\ShopCertification::HEUREKA_SK,
        ];
        $overenoSk = new \Heureka\ShopCertification('cab436a776539fede32d0e85e8442734', $optionsSk);

        $overeno->setEmail($order->email);
        $overenoSk->setEmail($order->email);*/

        $zboziOrder = new Order($no . $order->id);
        $zboziOrder->setEmail($order->email);

        $order->setOrderState($this->gem(OrderState::class)->findOneBy(['acceptOrder' => 1]));
        $order->setDueDate(new \DateTime('+' . $this->setting('due_invoice_days') . ' days'));

        /* Obsah objednávky - udělám rezervace na skladu */
        $productsInOrder = $this->gEMProductInOrder()->findBy([
            "orders" => $order->id,
            'isPojisteni' => 0,
            'isNastaveni' => 0,
            'isGift' => 0
        ]);

        foreach ($productsInOrder as $productInOrder) {
            $product = $productInOrder->product;
            /*if (!$product->notInFeeds) {
                try {
                    $overeno->addProductItemId($product->id);
                } catch(\Exception $ex) {}
                try {
                    $overenoSk->addProductItemId($product->id);
                } catch (\Exception $ex) {}
                try {
                    $zboziOrder->addCartItem((new CartItem)
                        ->setId($product->id)
                        ->setUnitPrice($productInOrder->selingPrice)
                        ->setQuantity($productInOrder->count)
                    );
                } catch (\Exception $ex) {}
            }*/

            // Provedu rezervaci kusů na skladu
            if ($product && !$productInOrder->isPojisteni && !$productInOrder->isNastaveni && !$productInOrder->parentProduct) // jenom pokud se jedná o produkt s kartou
            {
                if ($product->isSet) {
                    foreach ($product->setProducts as $p) {
                        $this->productFacade->addStockOperation($p->products, -$productInOrder->count, null, '', null, $order, true);
                    }
                } else {
                    $this->productFacade->addStockOperation($product, -$productInOrder->count, null, '', null, $order, true);
                }
            }
        }

        $this->save();
        /*$overeno->setOrderId(intval($no . $order->id));
        $overenoSk->setOrderId(intval($no . $order->id));
        if ($this->isProduction) //pouze na produkci posílat na Heureku
        {
            try {
                $overeno->logOrder();
                $overenoSk->logOrder();
                $clientZbozi->sendOrder($zboziOrder);
            } catch (\Exception $e) {
                Debugger::log($e);
            }
        }*/

        $this->deleteUnfinishedOrders($order->email);

        return $order;
    }

    public function deleteUnfinishedOrders($email)
    {
        $orders = $this->gEMOrders()->findBy(['orderState' => 9, 'email' => $email]);
        if ($orders) {
            foreach($orders as $o) {
                foreach($o->products as $p) {
                    $this->em->remove($p);
                }
                $discounts = $this->gEMDiscountInOrder()->findBy(['orders' => $o]);
                if ($discounts) {
                    foreach($discounts as $d) {
                        $this->em->remove($d);
                    }
                }
                if ($o->packages) {
                    foreach($o->packages as $p) {
                        $this->em->remove($p);
                    }
                }
                $this->em->remove($o);
            }
            $this->save();
        }
    }

    /**
     * @param Orders $order
     * @param Product $product
     * @param ProductInOrder $orderProduct
     * @param $gift
     * @return ProductInOrder|null
     */
    public function manageGiftInOrder(
        Orders $order,
        Product $product,
        $orderProduct,
        $gift
    ) {
        $isNew = false;

        /** @var ProductInOrder $prodOrderP */
        $prodOrderP = $this->gEMProductInOrder()->findOneBy([
            "orders" => $order->id,
            "product" => $product->id,
            "isGift" => 1
        ]);

        if ($orderProduct == null || $gift === 0) { // If don't exist ProductInOrder - delete from order
            if ($prodOrderP) {
                $this->em->remove($prodOrderP);
            }
            return null;
        }

        if ($gift == null && !$prodOrderP) {
            return null;
        }

        $giftProduct = null;

        if ($gift) {
            $giftEnt = $this->gEMProductGifts()->find($gift);
            if ($giftEnt && $giftEnt->product == $product && $giftEnt->gift) {
                $giftProduct = $giftEnt->gift;
            }
        }

        if (!$giftProduct) {
            return null;
        }

        if (!$prodOrderP) {
            $prodOrderP = new ProductInOrder();
            $isNew = true;
        }
        $prodOrderP->setProduct($product);
        $prodOrderP->setSelingPrice(0);
        $prodOrderP->setCount($orderProduct->count);
        $prodOrderP->setName('Dárek zdarma: ' . $giftProduct->name);
        $prodOrderP->setIsGift(true);
        $prodOrderP->setOrders($order);
        $prodOrderP->setGift($giftProduct);
        $prodOrderP->setParentProduct($orderProduct);

        if ($isNew) {
            $this->insertNew($prodOrderP);
        }

        return $prodOrderP;
    }

    public function checkOrder($sess)
    {
        if (isset($sess->basketOrder)) {
            if (is_numeric($sess->basketOrder)) {
                $order = $this->gEMOrders()->find($sess->basketOrder);
                if (!$order || ($order->orderState && !$order->orderState->stateNotFinished)) {
                    unset($sess->basketOrder);
                }
            } else {
                unset($sess->basketOrder);
            }
        }
    }

    /**
     * @param $product Product
     * @param $categories DiscountInCategory
     * @return bool
     */
    public function productHasCategory($product, $categories)
    {
        if ($product && $categories && $product->categories) {
            foreach ($product->categories as $c) {
                if (!$c->category) {
                    continue;
                }

                $temp = $c->category;
                while($temp) {
                    foreach ($categories as $cc) {
                        if (!$cc->category) {
                            continue;
                        }

                        if ($cc->category->id == $temp->id) {
                            return true;
                        }
                    }
                    $temp = $temp->parentCategory;
                }
            }
        }
        return false;
    }

    /**
     * @param $product Product
     * @param $marks
     * @return bool
     */
    public function productHasMark($product, $marks)
    {
        if ($product && $marks && ($product->productMark)) {
            foreach ($marks as $m) {
                if (!$m->productMark) {
                    continue;
                }

                if (
                    ($product->productMark && $product->productMark->id == $m->productMark->id)
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
