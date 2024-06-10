<?php

namespace Intra\Model\Utils;

use Front\Model\Utils\Text\UnitParser;
use Intra\Components\MailSender\MailSender;
use Intra\Components\PDFPrinter\PDFPrinterControl;
use Intra\Model\Database\Entity\OrderRefund;
use Intra\Model\Database\Entity\Orders;
use Intra\Model\Database\Entity\ProductInOrder;
use Intra\Model\Facade\OrderFacade;
use Intra\Model\Facade\ProductFacade;
use Intra\Model\Utils\ProductHelper\ProductHelper;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\LinkGenerator;
use Nette\Database\Context;
use Nette\Utils\DateTime;

class HeurekaCart
{
    /** @var EntityManager */
    protected $em;

    /** @var Context */
    public $db;

    /** @var ProductFacade */
    public $productFac;

    /** @var ProductHelper */
    public $productHelper;

    /** @var LinkGenerator */
    public $linkGenerator;

    /** @var OrderFacade */
    public $orderFac;

    /** @var PDFPrinterControl */
    public $pdfPrinter;

    /** @var array */
    private $currency;

    /** @var string */
    private $apiID = 'validate';

    /** @var string */
    private $apiURL = 'https://api.heureka.cz';

    /** @var boolean */
    public $isProduction;

    /**
     * HeurekaCart constructor.
     * @param EntityManager $em
     * @param Context $context
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(EntityManager $em, Context $context, ProductFacade $productFacade, ProductHelper $productHelper,
    LinkGenerator $linkGenerator, OrderFacade $orderFac, PDFPrinterControl $pdfPrinter)
    {
        $this->em = $em;
        $this->db = $context;
        $this->productFac = $productFacade;
        $this->productHelper = $productHelper;
        $this->linkGenerator = $linkGenerator;
        $this->orderFac = $orderFac;
        $this->pdfPrinter = $pdfPrinter;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        if ($currency['code'] == 'CZK') {
            $this->apiURL = 'https://api.heureka.cz';
            if ($this->isProduction) {
                $apiHeureka = $this->productFac->gEMHeurekaCart()->findOneBy(['country' => 'CZ']);
                if ($apiHeureka) {
                    $this->apiID = $apiHeureka->api;
                }
            }
        } elseif ($currency['code'] == 'EUR') {
            $this->apiURL = 'https://api.heureka.sk';
            if ($this->isProduction) {
                $apiHeureka = $this->productFac->gEMHeurekaCart()->findOneBy(['country' => 'SK']);
                if ($apiHeureka) {
                    $this->apiID = $apiHeureka->api;
                }
            }
        }
    }

    public function setProduction($isProduction)
    {
        $this->isProduction = $isProduction;
    }

    public function getProductsAvailability(array $products)
    {
        $priceTotal = 0;
        if ($products) {
            foreach($products as $k => $p) {
                $products[$k]['count'] = intval($p['count']);
                $product = $this->productFac->get()->find($p['id']);
                if ($product && $product->active && !$product->saleTerminated) {
                    $this->productHelper->setProduct($product, $this->currency);
                    $products[$k]['name'] = $product->name;
                    $products[$k]['price'] = $this->productHelper->getPrice();
                    $products[$k]['priceTotal'] = $this->productHelper->getPrice() * $p['count'];
                    $products[$k]['available'] = true;
                    if (empty(trim($product->stockText))) {
                        if ($product->count > 0) {
                            $products[$k]['delivery'] = 0;
                        } else {
                            $products[$k]['delivery'] = 5;
                        }
                    } else {
                        $products[$k]['delivery'] = -1;
                    }
                    $priceTotal += $this->productHelper->getPrice() * $p['count'];
                } else {
                    $products[$k]['count'] = 0;
                    $products[$k]['available'] = false;
                    $products[$k]['delivery'] = -1;
                }
            }
        }

        return ['products' => $products, 'priceSum' => $priceTotal];
    }

    public function getPaymentDelivery(array $products)
    {
        $priceTotal = 0;
        $deliveryFree = $this->productFac->setting('delivery_free');
        if (!$deliveryFree) {
            $deliveryFree = 0;
        }
        $deliveryFree /= $this->currency['exchangeRate'];
        if ($products) {
            foreach($products as $k => $p) {
                $product = $this->productFac->get()->find($p['id']);
                if ($product && $product->active && !$product->saleTerminated) {
                    $this->productHelper->setProduct($product, $this->currency);
                    $priceTotal += $this->productHelper->getPrice() * $p['count'];
                }
            }
        }
        $freeDeli = false;
        if ($deliveryFree <= $priceTotal) {
            $freeDeli = true;
        }
        $transport = [];
        $delivery = $this->productFac->gEMDeliveryMethod()->findBy(['active' => true, 'heurekaDelivery !=' => null], ['id' => 'ASC']);
        if ($delivery) {
            foreach($delivery as $d) {
                $transport[] = [
                    'id' => $d->id,
                    'type' => $d->heurekaDelivery->id,
                    'name' => $d->name,
                    'price' => $freeDeli ? 0 : $d->selingPrice / $this->currency['exchangeRate'],
                    'description' => '',
                ];
                if (in_array($d->heurekaDelivery->id, [1, 9]) && $d->heurekaStore) {
                    $transport[count($transport) - 1]['store'] = [
                        'type' => $d->heurekaDelivery->id == 1 ? 1 : 3,
                        'id' => $d->heurekaStore,
                    ];
                }
            }
        }
        $payment = [];
        $payments = $this->productFac->gEMPaymentMethod()->findBy(['active' => true, 'heurekaPayment !=' => null], ['orderState' => 'ASC']);
        if ($payments) {
            foreach($payments as $p) {
                $payment[] = [
                    'id' => $p->id,
                    'type' => $p->heurekaPayment->id,
                    'name' => $p->name,
                    'price' => $freeDeli ? 0 : $p->selingPrice / $this->currency['exchangeRate'],
                ];
            }
        }
        $binding = [];
        $bindingId = 1;
        if ($delivery && $payments) {
            foreach($delivery as $d) {
                foreach($payments as $p) {
                    $binding[] = [
                        'id' => $bindingId,
                        'transportId' => $d->id,
                        'paymentId' => $p->id,
                    ];
                    $bindingId++;
                }
            }
        }

        return ['transport' => $transport, 'payment' => $payment, 'binding' => $binding];
    }

    public function getOrderStatus($orderId)
    {
        //$order = $this->productFac->gEMOrders()->findOneBy(['heurekaId' => $orderId]);
        $order = $this->productFac->gEMOrders()->find($orderId);
        if ($order) {
            $status = $order->orderState->heurekaState ? $order->orderState->heurekaState->id - 1 : 1;
        } else {
            $status = 1;
        }

        return ['order_id' => $orderId, 'status' => $status];
    }

    public function createOrder($values)
    {
        $customer = $this->productFac->gEMCustomer()->findOneBy(['email' => $values['customer']['email']]);
        $order = $this->productFac->gEMOrders()->findOneBy(['heurekaId' => $values['heureka_id']]);
        if (!$order) {
            $dataForOrder = [
                'heurekaId' => $values['heureka_id'],
                'payDeliveryPrice' => $values['deliveryPrice'],
                'payMethodPrice' => $values['paymentPrice'],
                'comment' => $values['note'],
                'totalPriceWithoutDeliPay' => $values['productsTotalPrice'],
                'totalPrice' => $values['productsTotalPrice'] + $values['deliveryPrice'] + $values['paymentPrice'],
                'name' => $values['customer']['firstname'].' '.$values['customer']['lastname'],
                'email' => $values['customer']['email'],
                'phone' => $values['customer']['phone'],
                'street' => $values['customer']['street'],
                'city' => $values['customer']['city'],
                'zip' => $values['customer']['postCode'],
                'country' => $values['customer']['state'] == 'Česká republika' ? 'CZ' : 'SK',
                'company' => $values['customer']['company'],
                'idNo' => isset($values['customer']['ic']) ? $values['customer']['ic'] : null,
                'vatNo' => $values['customer']['dic'],
                'contactPerson' => $values['deliveryAddress']['firstname'].' '.$values['deliveryAddress']['lastname'],
                'streetDelivery' => $values['deliveryAddress']['street'],
                'cityDelivery' => $values['deliveryAddress']['city'],
                'zipDelivery' => $values['deliveryAddress']['postCode'],
                'countryDelivery' => $values['deliveryAddress']['state'] == 'Česká republika' ? 'CZ' : 'SK',
                'emailDelivery' => $values['customer']['email'],
                'deliveryToOther' => true,
            ];
            /*if (!empty($values['deliveryAddress']['company'])) {
                $dataForOrder['company'] = $values['deliveryAddress']['company'];
                $dataForOrder['isCompany'] = true;
            }*/
            if (!empty($values['customer']['company'])) {
                $dataForOrder['isCompany'] = true;
            }
            if (isset($values['paymentOnlineType'])) {
                $dataForOrder['heurekaPaymentId'] = $values['paymentOnlineType']['id'];
                $dataForOrder['heurekaPaymentTitle'] = $values['paymentOnlineType']['title'];
            }
            $delivery = $this->productFac->gEMDeliveryMethod()->find($values['deliveryId']);
            if (isset($values['deliveryAddress']['depotId'])) {
                $depotId = $values['deliveryAddress']['depotId'];

                if ($delivery->isDPD && strlen($depotId) > 2 && substr($depotId, 0, 2) == 15) {
                    $depotId = 'CZ'.substr($depotId, 2);
                }
                $dataForOrder['deliveryPlace'] = $depotId;
            }
            $order = new Orders($dataForOrder);
            $this->em->persist($order);
            $paymentMethod = $this->productFac->gEMPaymentMethod()->find($values['paymentId']);
            $order->setCurrency($this->productFac->gEMCurrency()->find($this->currency['id']));
            $order->setDeliveryMethod($delivery);
            $order->setPaymentMethod($paymentMethod);
            if ($paymentMethod && $paymentMethod->id == 2) {
                $order->setOrderState($this->productFac->gEMOrderState()->findOneBy(['slug' => 'waiting']));
            } else {
                $order->setOrderState($this->productFac->gEMOrderState()->findOneBy(['slug' => 'in-duty']));
            }
            $order->setLastUpdate(new DateTime());
            if ($customer) {
                $order->setCustomer($customer);
            }
            $this->em->flush();
            $n = 4 - strlen($order->id);
            $no = substr(date("Y"), 2);
            for ($i = 0; $i < $n; $i++) {
                $no .= "0";
            }
            $order->setVariableSymbol($no . $order->id);
            $this->em->flush();

            if ($values['products']) {
                foreach($values['products'] as $p) {
                    if (intval($p['count']) > 0) {
                        $product = $this->productFac->get()->find($p['id']);
                        if ($product) {
                            $prEnt = new ProductInOrder();
                            $prEnt->setProduct($product);
                            $prEnt->setOrders($order);
                            $prEnt->setSelingPrice($p['price']);
                            $prEnt->setName($product->name);
                            $prEnt->setCount($p['count']);
                            $prEnt->setUnit(UnitParser::parse($product->unit, $p['count']));
                            $this->em->persist($prEnt);

                            $this->productFac->addStockOperation($product, -$prEnt->count, null, '', null, $order->id, 1);
                        }
                    }
                }
                $this->em->flush();
            }
        }
        $heurekaDir = 'heureka-orders/';
        if (!is_dir($heurekaDir)) {
            mkdir($heurekaDir, 0777);
        }
        file_put_contents($heurekaDir.$order->id.".json", json_encode($values));
        return ['order_id' => $order->id, 'internal_id' => $order->variableSymbol, 'variableSymbol' => intval($order->variableSymbol)];
    }

    public function cancelOrder($values)
    {
        $status = false;
        $order = $this->productFac->gEMOrders()->find($values['order_id']);
        if ($order) {
            $order->setOrderState($this->productFac->gEMOrderState()->findOneBy(['slug' => 'canceled']));
            $this->em->flush();
            $status = true;
        }
        return ['status' => $status];
    }

    public function paymentStatus($values)
    {
        $status = false;

        $order = $this->productFac->gEMOrders()->find($values['order_id']);
        if ($order) {
            $order->setHeurekaPaymentStatus($values['status']);
            $order->setHeurekaPaymentDate(new DateTime($values['date']));
            if (!$order->refund && $values['status'] == 1) {
                $refund = new OrderRefund();
                $refund->setValue($order->totalPrice);
                $refund->setFoundedDate(new DateTime());
                $refund->setTypePayment('Heuréka - Online');
                $refund->setOrders($order);
                $refund->setOnline(false);
                $refund->setOriginator($this->productFac->gEMUser()->find(1));
                $this->em->persist($refund);
                $this->em->flush();
                $this->orderFac->swapState($refund->orders->id, 2);
                $this->sendOrderState($refund->orders->id);
                if (!$refund->orders->codeInvoice) {
                    $this->orderFac->generateInvoice($refund->orders->id);
                    $this->productFac->addStockOperationsForInvoice($refund->orders->id);
                    $invoice = $this->pdfPrinter->handleCreateInvoice($refund->orders->id, false, false, 'S');
                    $this->sendInvoice($refund->orders->id, $invoice);
                }
            }
            $this->em->flush();
            $status = true;
        }

        return ['status' => $status];
    }

    private function sendRequest($url, $data = null, $dataType = null, $headers = null)
    {
        $r = curl_init();
        curl_setopt($r, CURLOPT_URL, $url);
        curl_setopt($r, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($r, CURLOPT_HEADER, false);
        curl_setopt($r, CURLINFO_HEADER_OUT, true);
        curl_setopt($r, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($r, CURLOPT_TIMEOUT, 120);
        if ($headers) {
            curl_setopt($r, CURLOPT_HTTPHEADER, $headers);
        }
        if ($data && $dataType == 'POST') {
            curl_setopt($r, CURLOPT_POST, true);
            curl_setopt($r, CURLOPT_POSTFIELDS, $data);
        } elseif($data && $dataType == 'PUT') {
            curl_setopt($r, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($r, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        $response = curl_exec($r);
        //$info = curl_getinfo($r);
        //$status = $info['http_code'];
        curl_close($r);

        /*switch($status) {
            case 400:
                throw new \Exception("Bad request", $status);
                break;
            case 401:
                throw new \Exception("Unauthorized", $status);
                break;
            case 403:
                throw new \Exception("Forbidden", $status);
                break;
            case 404:
                throw new \Exception("Not found", $status);
                break;
            case 405:
                throw new \Exception("Method Not Allowed", $status);
                break;
            case 408:
                throw new \Exception("Request Timeout", $status);
                break;
            case 500:
                throw new \Exception("Internal server error", $status);
                break;
            case 502:
                throw new \Exception("Bad Gateway", $status);
                break;
            case 503:
                throw new \Exception("Service unavailable", $status);
                break;
            case 504:
                throw new \Exception("Gateway Timeout", $status);
                break;
            case 505:
                throw new \Exception("HTTP Version Not Supported", $status);
                break;
        }*/

        return json_decode($response, true);
    }

    public function getPaymentStatus()
    {
        $orders = $this->productFac->gEMOrders()->findBy(['heurekaPaymentId !=' => null, 'heurekaPaymentDate' => null], [], 30);
        if ($orders) {
            foreach($orders as $o) {
                try {
                    $payment = $this->sendRequest($this->apiURL.'/api/cart/'.$this->apiID.'/1/payment/status?order_id='.$o->id);
                    if ($payment['status'] == 1) {
                        $o->setHeurekaPaymentDate(new DateTime($payment['date']));
                        $o->setHeurekaPaymentStatus($payment['status']);
                        if ($o->orderState->slug != 'canceled') {
                            $o->setOrderState($this->productFac->gEMOrderState()->findOneBy(['slug' => 'in-duty']));
                        }
                        if (!$o->refund && $payment['status'] == 1) {
                            $refund = new OrderRefund();
                            $refund->setValue($o->totalPrice);
                            $refund->setFoundedDate(new DateTime());
                            $refund->setTypePayment('Heuréka - Online');
                            $refund->setOrders($o);
                            $refund->setOnline(false);
                            $refund->setOriginator($this->productFac->gEMUser()->find(1));
                            $this->em->persist($refund);
                            $this->em->flush();
                            $this->orderFac->swapState($refund->orders->id, 2);
                            $this->sendOrderState($refund->orders->id);
                            if (!$refund->orders->codeInvoice) {
                                $this->orderFac->generateInvoice($refund->orders->id);
                                $this->productFac->addStockOperationsForInvoice($refund->orders->id);
                                $invoice = $this->pdfPrinter->handleCreateInvoice($refund->orders->id, false, false, 'S');
                                $this->sendInvoice($refund->orders->id, $invoice);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Tracy\Debugger::log($e->getMessage(), 'HeurekaCartError');
                    continue;
                }
            }
            $this->em->flush();
        }
    }

    public function getShopStatus()
    {
        try {
            $status = $this->sendRequest($this->apiURL.'/api/cart/'.$this->apiID.'/1/shop/status/');
            if (!$status['status']) {
                \Tracy\Debugger::log($status['error']['message'].' - '.$status['error']['created'], 'HeurekaCart');
            }
        } catch (\Exception $e) {
            \Tracy\Debugger::log($e->getMessage(), 'HeurekaCartError');
        }
    }

    public function sendOrderState($order)
    {
        if (is_numeric($order)) {
            $order = $this->productFac->gEMOrders()->find($order);
        }
        if ($order) {
            $data = [
                'order_id' => $order->id,
                'status' => $order->orderState->heurekaState->id - 1,
            ];
            if ($order->deliveryOrdered && $order->packages) {
                $data['transport'] = [
                    'tracking_url' => $this->linkGenerator->link('Front:tracking', ['hash' => base64_encode('order_id: '.$order->id)]),
                ];
            }
            try {
                $response = $this->sendRequest($this->apiURL . '/api/cart/' . $this->apiID . '/1/order/status/', $data, 'PUT');
                return isset($response['status']) ? $response['status'] : null;
            } catch (\Exception $e) {
                \Tracy\Debugger::log($e->getMessage(), 'HeurekaCartError');
                return false;
            }
        }

        return false;
    }

    public function sendPaymentStatus($order, $currentPrice)
    {
        if (is_numeric($order)) {
            $order = $this->productFac->gEMOrders()->find($order);
        }
        if ($order) {
            $data = [
                'order_id' => $order->id,
                'status' => $currentPrice > 0 ? -1 : 1,
                'date' => $order->refund ? $order->refund[0]->foundedDate->format('Y-m-d') : date('Y-m-d'),
            ];
            try {
                $response = $this->sendRequest($this->apiURL . '/api/cart/' . $this->apiID . '/1/payment/status/', $data, 'PUT');
                if (isset($response['status'])) {
                    return $response['status'];
                }

                return false;
            } catch (\Exception $e) {
                \Tracy\Debugger::log($e->getMessage(), 'HeurekaCartError');
                return false;
            }
        }

        return false;
    }

    public function sendInvoice($order, $invoice)
    {
        if (is_numeric($order)) {
            $order = $this->productFac->gEMOrders()->find($order);
        }
        if ($order) {
            if (!is_dir('tempInvoice')) {
                mkdir('tempInvoice', 0755);
            }
            $invoicePath = 'tempInvoice/'.$order->id.'.pdf';
            file_put_contents($invoicePath, $invoice);
            $data = [
                'order_id' => $order->id,
            ];
            if (function_exists('curl_file_create')) { // For PHP 5.5+
                $data['invoice'] = curl_file_create($invoicePath);
            } else {
                $data['invoice'] = '@' . realpath($invoicePath);
            }
            try {
                $response = $this->sendRequest($this->apiURL . '/api/cart/' . $this->apiID . '/1/order/invoice/', $data, 'POST', ["Content-Type" => "multipart/form-data"]);
                return $response['status'];
            } catch (\Exception $e) {
                \Tracy\Debugger::log($e->getMessage(), 'HeurekaCartError');
                return false;
            }
            @unlink($invoicePath);
        }

        return false;
    }
}