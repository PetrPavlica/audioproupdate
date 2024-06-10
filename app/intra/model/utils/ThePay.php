<?php

namespace Intra\Model\Utils;

use Front\Model\Facade\FrontFacade;
use Front\Model\Facade\OrderProcessFacade;
use Intra\Components\MailSender\MailSender;
use Intra\Model\Database\Entity\Orders;
use Intra\Model\Facade\OrderFacade;
use Intra\Model\Facade\ProductFacade;
use Nette\Application\LinkGenerator;
use ThePay\ApiClient\Model\CreatePaymentCustomer;
use ThePay\ApiClient\Model\CreatePaymentParams;
use ThePay\ApiClient\TheClient;
use ThePay\ApiClient\TheConfig;
use Tracy\Debugger;

class ThePay
{
    /** @var string */
    private $merchantId;

    /** @var string */
    private $apiPassword;

    /** @var string */
    private $demoApiUrl = 'https://demo.api.thepay.cz/';

    /** @var string */
    private $demoGateUrl = 'https://demo.gate.thepay.cz/';

    /** @var string */
    private $apiUrl = 'https://api.thepay.cz/';

    /** @var string */
    private $gateUrl = 'https://gate.thepay.cz/';

    /** @var string */
    private $projectId;

    /** @var string */
    private $language = 'cs';

    /** @var bool */
    private $production;

    /** @var OrderProcessFacade */
    public $orderProcFac;

    /** @var OrderFacade */
    public $orderFac;

    /** @var FrontFacade */
    public $frontFac;

    /** @var ProductFacade */
    public $prodFac;

    /** @var LinkGenerator */
    public $linkGen;

    /** @var MailSender */
    public $mailSender;

    public function __construct(OrderProcessFacade $orderProcFac, OrderFacade $orderFac, FrontFacade $frontFac, ProductFacade $prodFac, LinkGenerator $linkGen, MailSender $mailSender)
    {
        $this->orderProcFac = $orderProcFac;
        $this->orderFac = $orderFac;
        $this->frontFac = $frontFac;
        $this->prodFac = $prodFac;
        $this->linkGen = $linkGen;
        $this->mailSender = $mailSender;
    }

    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
    }

    public function setApiPassword($apiPassword)
    {
        $this->apiPassword = $apiPassword;
    }

    public function setProduction($production)
    {
        $this->production = $production;
    }

    private function getApiUrl()
    {
        return $this->production ? $this->apiUrl : $this->demoApiUrl;
    }

    private function getGateUrl()
    {
        return $this->production ? $this->gateUrl : $this->demoGateUrl;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getConfig()
    {
        $config = new TheConfig(
            $this->merchantId,
            $this->projectId,
            $this->apiPassword,
            $this->getApiUrl(),
            $this->getGateUrl()
        );

        $config->setLanguage($this->language);

        return $config;
    }

    public function createPayment(Orders $order)
    {
        $payment = $this->orderProcFac->createThePayPayment([
            'action' => 'recapFormSuccess|BasketPresenter',
            'value' => $order->totalPrice,
            'currency' => $order->currency->code,
            'merchantData' => $order->variableSymbol,
            'isPay' => '0',
            'isPrepared' => '1'
        ]);
        $thePay = new TheClient($this->getConfig());
        $paymentParams = new CreatePaymentParams((int)($order->totalPrice * 100), $order->currency->code, $payment->id);
        $createPaymentCustomer = new CreatePaymentCustomer($order->customer->name, $order->customer->surname, $order->email, $order->phone);
        $paymentParams->setCustomer($createPaymentCustomer);
        $paymentParams->setDescriptionForCustomer('Nákup zboží v eshopu Audio pro');
        $paymentParams->setOrderId($order->variableSymbol);
        $paymentParams->setReturnUrl($this->linkGen->link('Basket:returnFromThePay'));
        $paymentParams->setNotifUrl($this->linkGen->link('Cron:notifyThePay'));
        $payment = $thePay->createPayment($paymentParams);
        return $payment->getPayUrl();
    }

    public function checkPayment($params)
    {
        if (!isset($params['payment_uid']) || !isset($params['project_id'])) {
            return null;
        }
        $filename = $params['payment_uid'].'-'.$params['project_id'].'.txt';
        if (file_exists($filename)) {
            return null;
        }
        file_put_contents($filename, $params['payment_uid']);
        $thePayPayment = $this->orderProcFac->gEMThePayPayment()->find($params['payment_uid']);
        if (!$thePayPayment) {
            @unlink($filename);
            return false;
        }
        $this->setProjectId($params['project_id']);

        $thePay = new TheClient($this->getConfig());
        try {
            $payment = $thePay->getPayment($params['payment_uid']);
            $order = $this->orderProcFac->gEMOrders()->findOneBy(['variableSymbol' => $thePayPayment->merchantData]);
            $this->orderFac->getEm()->refresh($thePayPayment);
            if (!$thePayPayment->isPay && $payment->getState() === 'paid') {
                $this->orderProcFac->setAsPay($thePayPayment);
                $paymentRefund = $this->frontFac->gEMOrderRefund()->findOneBy(['orders' => $order, 'typePayment' => 'ThePay úhrada']);
                if (!$paymentRefund) {
                    $refund = $this->frontFac->addPayment($order, ['value' => $payment->getAmount() / 100, 'paymentId' => $payment->getUid()], "ThePay");
                    if ($refund) {
                        $isRefunded = $this->orderFac->checkRefunds($order->id);
                        $this->orderFac->swapState($order->id, 'paySuccess');
                        $this->orderFac->generateInvoice($order->id);
                        $this->prodFac->addStockOperationsForInvoice($order->id);
                        $this->mailSender->sendAcceptPayments($order->id);
                        \Tracy\Debugger::log('Úspěšná platba: ' . json_encode($params), 'ThePay');
                    } else {
                        $this->frontFac->writeErrorInPayment($order, "Chyba s připsáním platby.", "ThePay");
                        $this->orderFac->swapState($order->id, 'afterErrorPay');
                        \Tracy\Debugger::log('Nastala chyba s připsáním platby, která byla OK: ' . json_encode($params), 'ThePayErrors');
                    }
                }
            }
            @unlink($filename);
            return [$order, $payment->getState()];
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        @unlink($filename);

        return null;
    }
}