<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Front\Model\Facade\FrontFacade;
use Front\Model\Utils\Text\UnitParser;
use Intra\Model\Database\Entity\DpdManifest;
use Intra\Model\Utils\MyDpd;
use Intra\Model\Utils\MyDpdMessage;
use Intra\Model\Database\Entity\ProductInCreditNote;
use Intra\Model\Utils\ProductHelper\ProductHelper;
use Intra\Model\Database\Entity\Currency;
use Intra\Model\Database\Entity\Customer;
use Intra\Model\Database\Entity\UlozenkaData;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\Orders;
use Intra\Model\Database\Entity\OrderState;
use Intra\Model\Database\Entity\DeliveryMethod;
use Intra\Model\Database\Entity\PaymentMethod;
use Intra\Model\Database\Entity\ProductInOrder;
use Intra\Model\Database\Entity\Product;
use Intra\Model\Database\Entity\DiscountInOrder;
use Intra\Model\Utils\EET\EETAdapter;
use Intra\Model\Utils\DPHCounter;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class OrderFacade extends BaseFacade
{
    /**  @var EETAdapter */
    public $eetAdapter;

    /** @var ProductHelper @inject */
    public $productHelper;

    /** @var MyDpd */
    public $myDpd;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, EETAdapter $eetAdapter, ProductHelper $productHelper, MyDpd $myDpd)
    {
        parent::__construct($em, Orders::class);
        $this->eetAdapter = $eetAdapter;
        $this->productHelper = $productHelper;
        $this->myDpd = $myDpd;
    }

    public function getDeliveryMethod()
    {
        return $this->getEm()->getRepository(DeliveryMethod::class);
    }

    public function getDeliveryMethodEntity()
    {
        return DeliveryMethod::class;
    }

    public function getPaymentMethod()
    {
        return $this->getEm()->getRepository(PaymentMethod::class);
    }

    public function getPaymentMethodEntity()
    {
        return PaymentMethod::class;
    }

    public function getCurrencyEntity()
    {
        return Currency::class;
    }

    public function getProduct()
    {
        return $this->getEm()->getRepository(Product::class);
    }

    public function getProductEntity()
    {
        return Product::class;
    }

    public function getProductInOrder()
    {
        return $this->getEm()->getRepository(ProductInOrder::class);
    }

    public function getProductInOrderEntity()
    {
        return ProductInOrder::class;
    }

    public function getDiscountInOrderEntity()
    {
        return DiscountInOrder::class;
    }

    public function getProductToSelect()
    {
        $arr = [];
        $items = $this->getProduct()->findAll();
        foreach ($items as $item) {
            $arr[ $item->id ] = '[ ' . $item->code . '] ' . $item->name . ', ' . $item->selingPrice . ' Kč' . '/' . $item->unit;
        }
        return $arr;
    }

    public function getDeliveryMethodToSelect()
    {
        $arr = [];
        $items = $this->getDeliveryMethod()->findAll();
        foreach ($items as $item) {
            $arr[ $item->id ] = $item->name . ' - ' . $item->selingPrice . ' Kč';
        }
        return $arr;
    }

    public function getPaymentMethodToSelect()
    {
        $arr = [];
        $items = $this->getPaymentMethod()->findAll();
        foreach ($items as $item) {
            $arr[ $item->id ] = $item->name . ' - ' . $item->selingPrice . ' Kč';
        }
        return $arr;
    }

    public function swapState($orderId, $state)
    {
        $orState = null;
        if (is_numeric($state)) {
            $orState = $this->gEMOrderState()->find($state);
        } else {
            $orState = $this->gEMOrderState()->findOneBy([$state => 1]);
        }
        if (count($orState)) {
            $order = $this->get()->find($orderId);
            $order->setOrderState($orState);
            $order->setLastUpdate(new DateTime());
            $this->save();
        }
        return $orState;
    }

    public function deleteProductInOrder($productOrderId)
    {
        $entity = $this->getProductInOrder()->find($productOrderId);
        $orderId = $entity->orders->id;
        $this->getEm()->remove($entity);
        $this->getEm()->flush();
        $this->recountPrice($orderId);
    }

    public function deleteBasketProductAndServices($order, $product)
    {
        $product = $this->gEMProductInOrder()->findAll(["orders" => $order->id, "product" => $product->id]);
        $this->getEm()->remove($product);
        $this->recountPrice($order->id);
    }


    public function deleteDiscount($id)
    {
        $entity = $this->gEMDiscountInOrder()->find($id);
        $this->getEm()->remove($entity);
        $this->getEm()->flush();
    }

    public function recountPrice($orderId)
    {
        $order = $this->get()->find($orderId);
        $price = 0;
        $defaultVat = $this->gEMVat()->findOneBy(['defaultVal' => 1]);
        $products = $this->gEMProductInOrder()->findBy(['orders' => $orderId]);
        $dc = new DPHCounter();
        $dc->setDisableDPH($order->euVat);
        foreach ($products as $item) {
            $vat = 0;
            if ($item->product) {
                $vat = $item->product->vat->value;
            } else {
                $vat = $defaultVat->value;
            }
            $dc->setPriceWithDPH($item->selingPrice, $vat, $item->count);
            $price += $dc->getTotalPrice();
        }

        $vat = 0;
        if ($order->deliveryMethod) {
            $vat = $order->deliveryMethod->vat->value;
        } else {
            $vat = $defaultVat->value;
        }
        $dc->setPriceWithDPH($order->payDeliveryPrice, $vat, 1);
        $price += $dc->getTotalPrice();

        $vat = 0;
        if ($order->paymentMethod) {
            $vat = $order->paymentMethod->vat->value;
        } else {
            $vat = $defaultVat->value;
        }
        $dc->setPriceWithDPH($order->payMethodPrice, $vat, 1);
        $price += $dc->getTotalPrice();

        /* Sleva - pokud je zadaná, tak zkontroluji její kód a pokud je ok, tak ji aplikuji */
        $discount = $this->gEMDiscountInOrder()->findOneBy(['orders' => $orderId]);
        if ($discount) {
            $sleva = ($discount->percent / 100) * $price;
            $price = $price - $sleva;
            $price = round($price, 2);
        }
        $order->setTotalPrice(round($price, $order->currency->countDecimal));
        $this->save();
        return $price;
    }

    public function checkRefunds($orderId)
    {
        $order = $this->get()->find($orderId);

        $price = 0;
        foreach ($order->refund as $item) {
            $price += $item->value;
        }

        return $order->totalPrice - $price;
    }

    public function generateInvoice($orderId)
    {
        $order = $this->get()->find($orderId);
        // Prepare next invoice number:
        $nEntity = $this->settingEntity('invoice_number');
        $number = explode('{', $nEntity->value);
        $number[ 1 ] = substr($number[ 1 ], 0, -1);
        $number[ 2 ] = strlen($number[ 1 ]);
        $number[ 1 ] = intval($number[ 1 ]) + 1;
        $number[ 2 ] = $number[ 2 ] - strlen($number[ 1 ]);

        $numberString = "";
        for ($i = 0; $i < $number[ 2 ]; $i++) {
            $numberString .= "0";
        }

        // Update invoice number - settings and new invoice
        $nEntity->setValue($number[ 0 ] . "{" . $numberString . $number[ 1 ] . "}");
        $order->setCodeInvoice($number[ 0 ] . $numberString . $number[ 1 ]);

        $this->save();
    }

    public function sendToEET($idRefund, $user)
    {
        if (is_numeric($idRefund)) {
            $item = $this->gEMOrderRefund()->find($idRefund);
        } else {
            $item = $idRefund;
        }

        if (is_numeric($user)) {
            $user = $this->gEMUser()->find($user);
        }

        $prc = $this->recountPrice($item->orders->id);
        $pomer = 1; // poměr kolik částky z faktury půjde do EET - pokud je částka stejná jako částka faktury, tak je $pomer = 1
        if (round($item->value, 2) != round($prc, 2)) {
            $pomer = round($item->value, 2) / round($prc, 2);
        }

        $products = $this->gEMProductInOrder()->findBy(['orders' => $item->orders->id]);

        // Zpočítám si jednotlivé sazby dle poměru kvůli částečným úhradám
        $nepodlehajiciSum = $dan1 = $dan2 = $dan3 = 0;
        foreach ($products as $product) {
            // Přenesená daňová povinnost - nepodléhá dani
            /* if ($product->transferredTax == 1) {
              $nepodlehajiciSum += $product->countPrice * $pomer;
              continue;
              } */

            // Dohledám sazbu DPH k danému produktu
            $vat = null;
            if (isset($product->product->vat)) // přímo na kartě produktu
            {
                $vat = $product->product->vat;
            }

            if (!$vat) { // a nebo jako poslední pokus vezmu defaultní sazbu dph
                $vat = $this->gEMVat()->findOneBy(['defaultVal' => 1]);
            }
            $sumVat = $product->selingPrice * $product->count;

            // nyní rozdělím částky dle poměru do jednotlivých sazeb pro EET
            if ($vat->standartRate == 1) {
                $dan1 += $sumVat * $pomer;
                continue;
            }
            if ($vat->reducedRate == 1) {
                $dan2 += $sumVat * $pomer;
                continue;
            }
            if ($vat->secondReducedRate == 1) {
                $dan3 += $sumVat * $pomer;
                continue;
            }

            throw new \Exception('Pozor! nepodařilo se připravit částky z faktury na EET při zpracování jejích úhrad. Jedná se o objednávku č. ' . $item->orders->variableSymbol);
            return false;
        }

        //Pro platbu
        $vat = isset($item->orders->paymentMethod->vat) ? $item->orders->paymentMethod->vat : null;
        if (!$vat) {
            $vat = $this->gEMVat()->findOneBy(['defaultVal' => 1]);
        }
        $sumVat = $item->orders->payMethodPrice;

        // nyní rozdělím částky dle poměru do jednotlivých sazeb pro EET
        if ($vat->standartRate == 1) {
            $dan1 += $sumVat * $pomer;
        } elseif ($vat->reducedRate == 1) {
            $dan2 += $sumVat * $pomer;
        } elseif ($vat->secondReducedRate == 1) {
            $dan3 += $sumVat * $pomer;
        }

        //Pro dopravu
        $vat = isset($item->orders->deliveryMethod->vat) ? $item->orders->deliveryMethod->vat : null;
        if (!$vat) {
            $vat = $this->gEMVat()->findOneBy(['defaultVal' => 1]);
        }
        $sumVat = $item->orders->payDeliveryPrice;

        // nyní rozdělím částky dle poměru do jednotlivých sazeb pro EET
        if ($vat->standartRate == 1) {
            $dan1 += $sumVat * $pomer;
        } elseif ($vat->reducedRate == 1) {
            $dan2 += $sumVat * $pomer;
        } elseif ($vat->secondReducedRate == 1) {
            $dan3 += $sumVat * $pomer;
        }


        // Nastavím pořadové číslo účtenky pro eet
        if ($item->eetNo == "") {
            // Prepare next number:
            $nEntity = $this->settingEntity('refund_number_eet');
            $number = explode('{', $nEntity->value);
            $number[ 1 ] = substr($number[ 1 ], 0, -1);
            $number[ 2 ] = strlen($number[ 1 ]);
            $number[ 1 ] = intval($number[ 1 ]) + 1;
            $number[ 2 ] = $number[ 2 ] - strlen($number[ 1 ]);
            $numberString = "";
            for ($i = 0; $i < $number[ 2 ]; $i++) {
                $numberString .= "0";
            }

            // Update number - settings and new
            $nEntity->setValue($number[ 0 ] . "{" . $numberString . $number[ 1 ] . "}");
            $item->setEetNo($number[ 0 ] . $numberString . $number[ 1 ]);
            $this->save();
        }

        //Připravím si koeficient pro aplikaci případné slevy z částky
        $coefDis = 1;
        $discount = $this->gEMDiscountInOrder()->findOneBy(['orders' => $item->orders->id]);
        if (count($discount)) {
            $coefDis = 1 - ($discount->percent / 100);
        }

        $item->setEetProvoz($user->eetProvoz);
        $item->setEetPokl($user->eetPokl);
        $this->save();
        $totalSum = $nepodlehajiciSum + $dan1 + $dan2 + $dan3;

        $currencyRate = $item->orders->currency->exchangeRate;
        $dph1 = new DPHCounter();
        $dph1->setDisableDPH($item->orders->euVat);
        $dph1->setPriceWithDPH($dan1 * $currencyRate * $coefDis,
            $this->gEMVat()->findOneBy(['standartRate' => 1])->value);

        $dph2 = new DPHCounter();
        $dph2->setDisableDPH($item->orders->euVat);
        $dph2->setPriceWithDPH($dan2 * $currencyRate * $coefDis,
            $this->gEMVat()->findOneBy(['reducedRate' => 1])->value);

        $dph3 = new DPHCounter();
        $dph3->setDisableDPH($item->orders->euVat);
        $dph3->setPriceWithDPH($dan3 * $currencyRate * $coefDis,
            $this->gEMVat()->findOneBy(['secondReducedRate' => 1])->value);

        if($item->orders->euVat){
            $totalSum = $dph1->getTotalWithoutDPH() + $dph2->getTotalWithoutDPH() + $dph3->getTotalWithoutDPH() + $nepodlehajiciSum;
        }
        $arr = [
            'dic_popl' => $this->setting('company_DIC'),
            'id_provoz' => $user->eetProvoz,
            'id_pokl' => $user->eetPokl,
            'porad_cis' => $item->eetNo,
            'celk_trzba' => round($totalSum * $currencyRate * $coefDis, 2),
            'zakl_nepodl_dph' => round($nepodlehajiciSum * $currencyRate * $coefDis, 2),
            'zakl_dan1' => $dph1->getTotalWithoutDPH(),
            'dan1' => $dph1->getTotalDPH(),
            'zakl_dan2' => $dph2->getTotalWithoutDPH(),
            'dan2' => $dph2->getTotalDPH(),
            'zakl_dan3' => $dph3->getTotalWithoutDPH(),
            'dan3' => $dph3->getTotalDPH(),
            'dat_trzby' => $item->foundedDate
        ];

        $result = $this->eetAdapter->sendBase($arr);
        if ($result === false) {
            $item->setFikEET(0);
            $item->setBkpEET(0);
            $this->save();
            return false;
        }
        $item->setFikEET($result[ 'fik' ]);
        $item->setBkpEET($result[ 'bkp' ]);
        $this->save();
        return true;
    }

    public function checkDueInvoices($state)
    {
        $orders = $this->get()->findBy(['dueDate <' => new \DateTime('-1 days'), 'orderState.checkDueDate' => '1']);
        foreach ($orders as $order) {
            $this->swapState($order, $state->id);
        }
        return $orders;
    }

    public function createCustomOrder()
    {

        $state = $this->gEMOrderState()->findOneBy(["stateForNew" => 1]);
        $currency = $this->gEMCurrency()->findOneBy(["code" => "CZK"]);

        if (!count($state)) {
            return null;
        }

        $order = new Orders();

        $order->setFoundedDate(null);
        $order->setCurrency($currency);
        $order->setOrderState($state);
        $this->insertNew($order);

        return $order;
    }

    public function addNewProduct($order, $product, $currency, $count = 1)
    {

        $orderProduct = $this->gEMProductInOrder()->findOneBy(["product" => $product->id, "orders" => $order->id]);

        $prodArr = $product->toArray();
        $prodArr[ 'unit' ] = UnitParser::parse($product->unit, $count);
        unset($prodArr[ 'id' ]);

        $this->productHelper->setProduct($product, $currency->toArray());

        $sellPrice = round($this->productHelper->getPrice(), $currency->countDecimal);

        if (!$orderProduct) {

            $orderProduct = new ProductInOrder($prodArr);
            $orderProduct->setProduct($product);
            $orderProduct->setSelingPrice($sellPrice);
            $orderProduct->setCount($count);
            $orderProduct->setOrders($order);
            $this->insertNew($orderProduct);

        } else {
            $orderProduct->setCount($count);
            $this->save();
        }

        return $orderProduct;

    }

    public function generateCreditNote($orderId)
    {
        $order = $this->get()->find($orderId);
        // Prepare next invoice number:
        $nEntity = $this->settingEntity('credit_note_number');
        $number = explode('{', $nEntity->value);
        $number[1] = substr($number[1], 0, -1);
        $number[2] = strlen($number[1]);
        $number[1] = intval($number[1]) + 1;
        $number[2] = $number[2] - strlen($number[1]);

        $numberString = "";
        for ($i = 0; $i < $number[2]; $i++) {
            $numberString .= "0";
        }

        // Update invoice number - settings and new invoice
        $nEntity->setValue($number[0] . "{" . $numberString . $number[1] . "}");
        $order->setCodeCreditNote($number[0] . $numberString . $number[1]);

        $this->save();
    }

    public function updateCreditNoteProducts($orderId, $products)
    {
        if ($products) {
            $order = $this->get()->find($orderId);
            foreach ($products as $id => $count) {
                $productInOrder = $this->gEMProductInOrder()->find($id);
                if ($productInOrder) {
                    $product = $this->gEMProductInCreditNote()->findOneBy(['productInOrder' => $id]);
                    if ($product) {
                        if ($count) {
                            $product->setCount($count);
                        } else {
                            $this->em->remove($product);
                        }
                    } else {
                        if ($count) {
                            $product = new ProductInCreditNote();
                            $product->setProductInOrder($productInOrder);
                            $product->setOrder($order);
                            $product->setCount($count);
                            $this->em->persist($product);
                        }
                    }
                }
            }
            $this->save();
        }
    }

    public function dpdShipment($orderId)
    {
        $order = $this->get()->find($orderId);
        if ($order && $order->dpdPackages) {
            $createShipment = [];
            $shipmentExist = false;
            $lastOrderNumber = 1;
            $lastShipment = null;
            foreach ($order->dpdPackages as $p) {
                if ($p->parcelId && $lastShipment != $p->shipmentId) {
                    if ($p->orderNumber == $lastOrderNumber) {
                        $lastOrderNumber++;
                    }
                    $lastShipment = $p->shipmentId;
                }
            }
            foreach ($order->dpdPackages as $p) {
                if ($p->parcelId) {
                    $shipmentExist = true;
                    continue;
                }

                if (!isset($createShipment[$p->dpdAddress->addressId])) {
                    $createShipment[$p->dpdAddress->addressId] = [
                        'packages' => [],
                        'orderNumber' => $lastOrderNumber
                    ];
                    $lastOrderNumber++;
                }
                $createShipment[$p->dpdAddress->addressId]['packages'][] = $p;
            }

            $shipmentsArr = [];
            foreach ($createShipment as $addressId => $shipment) {
                $result = $this->myDpd->createShipment($order, $shipment['packages'], $addressId, $shipment['orderNumber'], $shipmentExist);
                if ($result) {
                    if ($result->error) {
                        throw new MyDpdMessage($result->error->text);
                    }
                    foreach ($shipment['packages'] as $p) {
                        $p->setOrderNumber($shipment['orderNumber']);
                        $p->setShipmentId($result->shipmentReference->id);
                        $shipmentsArr[] = $result->shipmentReference->id;

                        if ($result->parcelResultList) {
                            if (is_array($result->parcelResultList)) {
                                foreach ($result->parcelResultList as $item) {
                                    if ($item->parcelReferenceNumber == $p->id) {
                                        $p->setParcelId($item->parcelId);
                                    }
                                }
                            } else {
                                $p->setParcelId($result->parcelResultList->parcelId);
                            }
                        }
                    }
                    $order->setDeliveryOrdered(true);
                    $this->save();
                }
            }
            if (count($shipmentsArr)) {
                $this->myDpd->getShipmentLabel($shipmentsArr);
                $status = $this->myDpd->getShipmentStatus($shipmentsArr);
                if ($status) {
                    if ($status->statusInfoList) {
                        $infoList = $status->statusInfoList;
                        if (!is_array($status->statusInfoList)) {
                            $infoList = [$status->statusInfoList];
                        }
                        foreach ($infoList as $i) {
                            if ($i->error) {
                                continue;
                            }
                            $packages = $this->gEMDpdPackage()->findBy(['shipmentId' => $i->statusReference->id]);
                            if ($packages) {
                                foreach ($packages as $p) {
                                    $p->setTrackingId($i->statusInfo->parcelNo);
                                    $p->setDpdUrl($i->statusInfo->dpdUrl);
                                }
                                $this->save();
                            }
                        }
                    }
                }
            }
        }
    }

    public function deleteDpdShipment($shipmentId)
    {
        $result = $this->myDpd->deleteShipment($shipmentId);
        if ($result) {
            if ($result->error) {
                throw new MyDpdMessage($result->error->text);
            }
            $packages = $this->gEMDpdPackage()->findBy(['shipmentId' => $shipmentId]);
            if ($packages) {
                foreach ($packages as $p) {
                    $p->setShipmentId(null);
                    $p->setOrderNumber(null);
                    $p->setParcelId(null);
                }
                $this->save();
            }
        }
    }

    public function getShipmentLabelDpd($shipmentId)
    {
        $result = $this->myDpd->getShipmentLabel([$shipmentId]);
        if ($result) {
            if ($result->error) {
                throw new MyDpdMessage($result->error->text);
            }

            return $result->pdfFile;
        }

        return null;
    }

    public function getShipmentLabelDpdOrder($orderId)
    {
        $order = $this->get()->find($orderId);
        if ($order && $order->dpdPackages) {
            $shipments = [];
            foreach ($order->dpdPackages as $p) {
                if (!in_array($p->shipmentId, $shipments)) {
                    $shipments[] = $p->shipmentId;
                }
            }

            if (count($shipments)) {
                $result = $this->myDpd->getShipmentLabel($shipments);
                if ($result) {
                    if ($result->error) {
                        throw new MyDpdMessage($result->error->text);
                    }

                    return $result->pdfFile;
                }
            }
        }

        return null;
    }

    private function getManifest()
    {
        try {
            $manifest = new DpdManifest();
            $manifest = $this->insertNew($manifest);

            return $manifest;
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return null;
    }

    public function closeManifest($ids)
    {
        $packages = $this->gEMDpdPackage()->findBy(['id' => $ids]);
        if ($packages) {
            $dpdAddress = [];
            $shipments = [];
            foreach ($packages as $p) {
                if ($p->manifest) {
                    throw new MyDpdMessage('Nemůžete vytvořit seznam se zásilkou, která už je v seznamu.');
                }
                if (!in_array($p->dpdAddress->id, $dpdAddress)) {
                    $dpdAddress[] = $p->dpdAddress->id;
                }

                $shipments[] = $p->shipmentId;
            }

            if (count($dpdAddress) > 1) {
                throw new MyDpdMessage('Nemůžete vytvořit seznam, kde je více svozových míst.');
            }

            if (count($shipments)) {
                $manifest = $this->getManifest();
                $result = $this->myDpd->closeManifest($shipments, $manifest->id);
                if ($result) {
                    if ($result->error) {
                        throw new MyDpdMessage($result->error->text);
                    }

                    foreach ($packages as $p) {
                        $p->setManifest($manifest);
                    }

                    $manifest->setReferenceNumber($result->manifestId);

                    $this->save();

                    return true;
                }
            }
        }

        return null;
    }

    public function printManifest($manifestNumber)
    {
        $result = $this->myDpd->reprintManifest($manifestNumber);
        if ($result) {
            if ($result->error) {
                throw new MyDpdMessage($result->error->text);
            }

            return $result->pdfManifestFile;
        }

        return null;
    }

    public function printLabel($manifestNumber)
    {
        $result = $this->myDpd->reprintManifest($manifestNumber);
        if ($result) {
            if ($result->error) {
                throw new MyDpdMessage($result->error->text);
            }

            return $result->pdfLabelFile;
        }

        return null;
    }

    public function getDPDPickups()
    {
        $arr = [];

        $dpdPickups = $this->gEMDpdPickup()->findAll();
        if ($dpdPickups) {
            foreach ($dpdPickups as $d) {
                $arr[$d->code] = $d->company.', '.$d->street.' '.$d->houseNumber.', '.$d->city.' ('.$d->postcode.'), '.substr($d->code, 0, 2);
            }
        }

        return $arr;
    }
}
