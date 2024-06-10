<?php
namespace App\Presenters;

use Intra\Components\MailSender\MailSender;
use Intra\Model\Facade\ProductFacade;
use Intra\Model\Utils\HeurekaCart;

class ApiPresenter extends BasePresenter
{
    /** @var HeurekaCart @inject */
    public $heurekaCart;

    /** @var ProductFacade @inject */
    public $facade;

    /** @var MailSender @inject */
    public $mailSender;

    public function startup()
    {
        parent::startup();
        if ($this->locale == 'sk') {
            $currency = $this->facade->gEMCurrency()->findOneBy(['code' => 'EUR'])->toArray();
        } // EUR
        else {
            $currency = $this->facade->gEMCurrency()->findOneBy(['code' => 'CZK'])->toArray();
        } // CZK
        $this->heurekaCart->setCurrency($currency);
    }

    public function actionProductsAvailability(array $products)
    {
        $this->sendJson($this->heurekaCart->getProductsAvailability($products));
    }

    public function actionPaymentDelivery(array $products)
    {
        $this->sendJson($this->heurekaCart->getPaymentDelivery($products));
    }

    public function actionOrderStatus($order_id)
    {
        $this->sendJson($this->heurekaCart->getOrderStatus(intval($order_id)));
    }

    public function actionOrderSend()
    {
        if ($this->request->isMethod('POST')) {
            $values = $this->request->getPost();
            $order = $this->heurekaCart->createOrder($values);
            $this->mailSender->sendCreationOrder($order['order_id']);
            $this->sendJson($order);
        }
        $this->terminate();
    }

    public function actionOrderCancel()
    {
        if ($this->request->isMethod('POST')) {
            $values = $this->request->getPost();
        } elseif ($this->request->isMethod('PUT')) {
            parse_str($this->getHttpRequest()->getRawBody(), $values);
        }
        $this->sendJson($this->heurekaCart->cancelOrder($values));
    }

    public function actionPaymentStatus()
    {
        if ($this->request->isMethod('POST')) {
            $values = $this->request->getPost();
        } elseif ($this->request->isMethod('PUT')) {
            parse_str($this->getHttpRequest()->getRawBody(), $values);
        }
        $this->sendJson($this->heurekaCart->paymentStatus($values));
    }
}