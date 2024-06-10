<?php

namespace Intra\Components\MailSender;

use Front\Components\ProductTile\IProductTileControlFactory;
use Front\Components\ProductTile\ProductTileControl;
use Intra\Model\Utils\DPHCounter;
use kcfinder\path;
use Nette;
use Intra\Model\Facade\OrderFacade;
use Intra\Model\Facade\CustomerFacade;
use Nette\Application\UI\ITemplateFactory;
use Nette\Mail\Message;
use Intra\Components\PDFPrinter\PDFPrinterControl;
use Kdyby\Translation\Translator;
use Nette\Application\LinkGenerator;

use Nette\Http;
use Tracy\Debugger;

class MailSender
{

    /** @var ITemplateFactory */
    public $templateFactory;

    /** @var LinkGenerator */
    public $linkGenerator;

    /** @var PDFPrinter */
    public $pdfPrinter;

    /** @var Translator */
    public $trans;

    /** @var string */
    public $dir;

    /** @var OrderFacade */
    public $orderFac;

    /** @var CustomerFacade */
    public $customerFac;

    /** @var boolean */
    public $production;

    public function __construct(
        ITemplateFactory $templateFactory,
        LinkGenerator $linkGenerator,
        PDFPrinterControl $pdfPrinter,
        Translator $trans,
        OrderFacade $orderFac,
        CustomerFacade $customerFac
    ) {
        $this->templateFactory = $templateFactory;
        $this->linkGenerator = $linkGenerator;
        $this->pdfPrinter = $pdfPrinter;
        $this->trans = $trans;
        $this->orderFac = $orderFac;
        $this->customerFac = $customerFac;
    }

    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    public function setProduction($production)
    {
        $this->production = $production;
    }

    public function sendCreationOrder($orderId)
    {
        $invoice = $this->pdfPrinter->handleCreateInvoice($orderId, true, false, 'S');

        $template = $this->templateFactory->createTemplate();
        $template->order = $order = $this->orderFac->get()->find($orderId);
        $template->products = $prod = $this->orderFac->gEMProductInOrder()->findBy(['orders' => $orderId]);
        $template->settings = $this->orderFac->getAllsettings();
        $template->discount = $this->orderFac->gEMDiscountInOrder()->findOneBy(['orders' => $orderId]);
        $template->dc = new DPHCounter();
        $template->pageTerms = $this->orderFac->gEMWebMenu()->findOneBy(['visible' => '1', 'forTerms' => 1]);
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/creationOrder.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setFrom($this->orderFac->setting('email_sender_mask'));

        try {
            $mail->addTo(trim($order->email));
        } catch (Nette\Utils\AssertionException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        $admins = $this->customerFac->gEMUser()->findBy(['active' => 1, 'noticeNewOrder' => 1]);
        foreach ($admins as $adm) {
            if ($adm->email) {
                $mail->addBcc($adm->email);
            }
        }
        $mail->setSubject($this->trans->translate('Nová objednávka z AudioPro'));
        $invCap = $this->trans->translate('Proforma faktura');
        $mail->addAttachment($invCap . '.pdf', $invoice);
        $conditionsCap = $this->trans->translate('Obchodní podmínky');
        /*$mail->addAttachment($conditionsCap . '.pdf',
            file_get_contents($this->dir . $this->orderFac->setting('terms_and_condition_path')));*/

        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (Nette\Mail\SendException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        } catch (Nette\Mail\SmtpException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        return true;
    }

    public function sendAcceptPayments($orderId)
    {
        $invoice = $this->pdfPrinter->handleCreateInvoice($orderId, false, false, 'S');

        $template = $this->templateFactory->createTemplate();
        $template->order = $order = $this->orderFac->get()->find($orderId);
        $template->products = $prod = $this->orderFac->gEMProductInOrder()->findBy(['orders' => $orderId]);
        $template->settings = $this->orderFac->getAllsettings();
        $template->discount = $this->orderFac->gEMDiscountInOrder()->findOneBy(['orders' => $orderId]);
        $template->dc = new DPHCounter();
        $template->pageTerms = $this->orderFac->gEMWebMenu()->findOneBy(['visible' => '1', 'forTerms' => 1]);
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        if ($order->paymentMethod && $order->paymentMethod->deliveryCash) {
            $template->setFile(__DIR__ . '/templates/acceptPaymentOnDeliveryCash.latte');
        } else {
            $template->setFile(__DIR__ . '/templates/acceptPayment.latte');
        }

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setFrom($this->orderFac->setting('email_sender_mask'));

        try {
            $mail->addTo(trim($order->email));
        } catch (Nette\Utils\AssertionException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        $admins = $this->customerFac->gEMUser()->findBy(['active' => 1, 'noticeAcceptPay' => 1]);
        foreach ($admins as $adm) {
            if ($adm->email) {
                $mail->addBcc($adm->email);
            }
        }
        $mail->setSubject($this->trans->translate('Potvrzení o přijetí platby z AudioPro'));
        $invCap = $this->trans->translate('Faktura č.');
        $mail->addAttachment($invCap . ' ' . $order->codeInvoice . '.pdf', $invoice);

        // Zjištění, zda přikládat i účtenku/y - zda existuje
        $refundsEntity = $this->orderFac->gEMOrderRefund()->findBy(['orders' => $orderId], ['foundedDate' => 'ASC']);
        foreach ($refundsEntity as $r) {
            $refund = $this->pdfPrinter->handlePrintReceipt($r->id, 'S');
            $cap = $this->trans->translate('Příjmový doklad č.');
            $mail->addAttachment($cap . ' ' . $refund[ 0 ], $refund[ 1 ]);
        }
        /*$conditionsCap = $this->trans->translate('Obchodní podmínky');
        $mail->addAttachment($conditionsCap . '.pdf',
            file_get_contents($this->dir . $this->orderFac->setting('terms_and_condition_path')));*/
        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (Nette\Mail\SendException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        } catch (Nette\Mail\SmtpException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        return true;
    }

    public function sendChangeOrderState($orderId, $state)
    {
        $invoice = $this->pdfPrinter->handleCreateInvoice($orderId, false, false,'S');
        if (is_numeric($state)) { //
            $state = $this->orderFac->gEMOrderState()->find($state);
        }
        $template = $this->templateFactory->createTemplate();
        $template->order = $order = $this->orderFac->get()->find($orderId);
        $template->products = $prod = $this->orderFac->gEMProductInOrder()->findBy(['orders' => $orderId]);
        $template->settings = $this->orderFac->getAllsettings();
        $template->discount = $this->orderFac->gEMDiscountInOrder()->findOneBy(['orders' => $orderId]);
        $dpdPackages = $this->orderFac->gEMDpdPackage()->findBy(['order' => $orderId, 'trackingId !=' => null]);
        $existShipments = [];
        if ($dpdPackages) {
            foreach ($dpdPackages as $k => $d) {
                if (in_array($d->shipmentId, $existShipments)) {
                    unset($dpdPackages[$k]);
                    continue;
                }
                $existShipments[] = $d->shipmentId;
            }
        }
        $template->dpdPackages = $dpdPackages;
        $template->state = $state;
        $template->dc = new DPHCounter();
        $template->pageTerms = $this->orderFac->gEMWebMenu()->findOneBy(['visible' => '1', 'forTerms' => 1]);
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/changeOrderState.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setFrom($this->orderFac->setting('email_sender_mask'));

        try {
            $mail->addTo(trim($order->email));
        } catch (Nette\Utils\AssertionException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        $admins = $this->customerFac->gEMUser()->findBy(['active' => 1, 'noticeAcceptPay' => 1]);
        foreach ($admins as $adm) {
            if ($adm->email) {
                $mail->addBcc($adm->email);
            }
        }

        if (in_array($order->deliveryMethod->id, [3, 4])) {
            $subject = $state->subjectEmailPersonal != '' ? str_replace("#ORDER_NUMBER#", $order->variableSymbol, $state->subjectEmailPersonal) : $state->name;
        } else {
            $subject = $state->subjectEmail != '' ? str_replace("#ORDER_NUMBER#", $order->variableSymbol, $state->subjectEmail) : $state->name;
        }
        $mail->setSubject($this->trans->translate($subject));
        $invCap = $this->trans->translate('Faktura č.');
        $mail->addAttachment($invCap . ' ' . $order->codeInvoice . '.pdf', $invoice);

        // Zjištění, zda přikládat i účtenku/y - zda existuje
        $refundsEntity = $this->orderFac->gEMOrderRefund()->findBy(['orders' => $orderId], ['foundedDate' => 'ASC']);
        foreach ($refundsEntity as $r) {
            $refund = $this->pdfPrinter->handlePrintReceipt($r->id, 'S');
            $cap = $this->trans->translate('Příjmový doklad č.');
            $mail->addAttachment($cap . ' ' . $refund[ 0 ], $refund[ 1 ]);
        }
        /*$conditionsCap = $this->trans->translate('Obchodní podmínky');
        $mail->addAttachment($conditionsCap . '.pdf',
            file_get_contents($this->dir . $this->orderFac->setting('terms_and_condition_path')));*/

        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (Nette\Mail\SendException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        } catch (Nette\Mail\SmtpException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        return true;
    }

    public function sendCustomerCredential($user, $pwd = null)
    {
        if (is_numeric($user)) { // If $user is id of enitty Customer, find entity by id
            $user = $this->orderFac->gEMCustomer()->find($user);
        }

        if ($pwd == null) { // If password is null, generate new random
            $pwd = $this->customerFac->createNewCredencial($user);
        }

        $template = $this->templateFactory->createTemplate();
        $template->user = $user;
        $template->password = $pwd;
        $template->settings = $this->orderFac->getAllsettings();
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/customerCredentials.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setFrom($this->orderFac->setting('email_sender_mask'));

        try {
            $mail->addTo(trim($user->email));
        } catch (Nette\Utils\AssertionException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        $mail->setSubject($this->trans->translate('Nové přístupové údaje'));
        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (Nette\Mail\SendException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        } catch (Nette\Mail\SmtpException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        return true;
    }

    public function sendCustomerCredentialOrder($user, $pwd = null)
    {
        if (is_numeric($user)) { // If $user is id of enitty Customer, find entity by id
            $user = $this->orderFac->gEMCustomer()->find($user);
        }

        if ($pwd == null) { // If password is null, generate new random
            $pwd = $this->customerFac->createNewCredencial($user);
        }

        $template = $this->templateFactory->createTemplate();
        $template->user = $user;
        $template->password = $pwd;
        $template->settings = $this->orderFac->getAllsettings();
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/customerCredentialsOrder.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setFrom($this->orderFac->setting('email_sender_mask'));

        try {
            $mail->addTo(trim($user->email));
        } catch (Nette\Utils\AssertionException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        $mail->setSubject($this->trans->translate('Nové přístupové údaje'));
        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (Nette\Mail\SendException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        } catch (Nette\Mail\SmtpException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        return true;
    }

    public function sendAnswerBlog($productDiscussion)
    {
        if (!isset($productDiscussion->parent->customer)) {
            return false;
        }
        $customer = $productDiscussion->parent->customer;
        $template = $this->templateFactory->createTemplate();
        $template->customer = $customer;
        $template->question = $productDiscussion->parent;
        $template->product = $productDiscussion->product;
        $template->answer = $productDiscussion;
        $template->settings = $this->orderFac->getAllsettings();
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/blogAnswer.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setFrom($this->orderFac->setting('email_sender_mask'));

        try {
            $mail->addTo(trim($productDiscussion->parent->customer->email));
        } catch (Nette\Utils\AssertionException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        $mail->setSubject($this->trans->translate('Odpověď na Vaši otázku'));
        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (Nette\Mail\SendException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        } catch (Nette\Mail\SmtpException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        return true;
    }

    public function sendHelpContact($data)
    {
        $template = $this->templateFactory->createTemplate();
        $template->data = $data;

        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/helpCustomer.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setFrom($this->orderFac->setting('email_sender_mask'));

        try {
            $mail->addTo(trim($this->orderFac->setting('email_for_help')));
        } catch (Nette\Utils\AssertionException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        $mail->setSubject($this->trans->translate('Zákazník potřebuje pomoc s formulářem'));
        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (Nette\Mail\SendException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        } catch (Nette\Mail\SmtpException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        return true;
    }

    public function sendBackOutOfContract($data, $currency, $attachment)
    {

        $template = $this->templateFactory->createTemplate();

        $template->data = $data;
        $template->settings = $this->orderFac->getAllsettings();
        $template->actualCurrency = $currency;

        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/backOutOfContract.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setFrom($this->orderFac->setting('email_sender_mask'));

        try {
            $mail->addTo(trim($this->orderFac->setting('email_for_help')));
            $mail->addTo(trim($data[ "email" ]));
        } catch (Nette\Utils\AssertionException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        $mail->setSubject($this->trans->translate('Odstoupení od kupní smlouvy'));

        $mail->addAttachment("kupni_doklad.pdf", file_get_contents($attachment));

        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (Nette\Mail\SendException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        } catch (Nette\Mail\SmtpException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        return true;
    }

    public function sendReclamation($data, $currency)
    {

        $template = $this->templateFactory->createTemplate();

        $template->data = $data;
        $template->settings = $this->orderFac->getAllsettings();
        $template->actualCurrency = $currency;

        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/reclamation.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setFrom($this->orderFac->setting('email_sender_mask'));

        try {
            $mail->addTo(trim($this->orderFac->setting('email_for_help')));
            $mail->addTo(trim($data[ "email" ]));
        } catch (Nette\Utils\AssertionException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        $mail->setSubject($this->trans->translate('Reklamace zboží'));

        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (Nette\Mail\SendException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        } catch (Nette\Mail\SmtpException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        return true;
    }

    public function sendProduct($values, $currency)
    {
        $product = $this->customerFac->gEMProduct()->find($values->id);

        if ($product) {

            $template = $this->templateFactory->createTemplate();
            $settings = $this->orderFac->getAllsettings();

            $template->product = $product;
            $template->settings = $settings;
            $template->values = $values;
            $template->actualCurrency = $currency;

            $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
            $template->setFile(__DIR__ . '/templates/shareProduct.latte');

            $mail = new Message();
            $mail->setHtmlBody($template);
            $mail->setFrom($this->orderFac->setting('email_sender_mask'));

            try {
                $mail->addTo(trim($values->to));
            } catch (Nette\Utils\AssertionException $e) {
                Debugger::log($e, 'MailSender');
                return false;
            }

            $mail->setSubject($this->trans->translate('Doporučení produktu z ' . $settings[ "company_name_email" ] . ''));
            $mailer = $this->createMailer();
            try {
                $mailer->send($mail);
            } catch (Nette\Mail\SendException $e) {
                Debugger::log($e, 'MailSender');
                return false;
            } catch (Nette\Mail\SmtpException $e) {
                Debugger::log($e, 'MailSender');
                return false;
            }

            return true;

        }

        return false;
    }

    public function sendProblemEmailToAdmin($text)
    {
        $template = $this->templateFactory->createTemplate();
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/problemEmail.latte');
        $template->text = $text;

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setFrom($this->orderFac->setting('email_sender_mask'));

        $admins = $this->customerFac->gEMUser()->findBy(['active' => 1]);
        foreach ($admins as $adm) {
            if ($adm->email) {
                $mail->addTo($adm->email);
            }
        }
        $mail->setSubject('Nastal problém ve Vašem eshopu.');
        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (Nette\Mail\SendException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        } catch (Nette\Mail\SmtpException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        return true;
    }

    public function sendUnfinishedOrder($orderId)
    {
        $template = $this->templateFactory->createTemplate();
        $template->order = $order = $this->orderFac->get()->find($orderId);
        $template->products = $prod = $this->orderFac->gEMProductInOrder()->findBy(['orders' => $orderId]);
        $template->settings = $this->orderFac->getAllsettings();
        $template->dc = new DPHCounter();
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/unfinishedOrder.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setSubject($this->trans->translate('Nedokončená objednávka z AudioPro'));
        $mail->setFrom($this->orderFac->setting('email_sender_mask'));

        try {
            $mail->addTo(trim($order->email));
        } catch (Nette\Utils\AssertionException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (Nette\Mail\SendException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        } catch (Nette\Mail\SmtpException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        return true;
    }

    public function sendRatingOrder($orderId)
    {
        $template = $this->templateFactory->createTemplate();
        $template->order = $order = $this->orderFac->get()->find($orderId);
        $template->products = $prod = $this->orderFac->gEMProductInOrder()->findBy(['orders' => $orderId]);
        $template->settings = $this->orderFac->getAllsettings();
        $template->dc = new DPHCounter();
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/ratingOrder.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setSubject($this->trans->translate('Hodnocení produktů objednávky z AudioPro'));
        $mail->setFrom($this->orderFac->setting('email_sender_mask'));

        try {
            $mail->addTo(trim($order->email));
        } catch (Nette\Utils\AssertionException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (Nette\Mail\SendException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        } catch (Nette\Mail\SmtpException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        return true;
    }

    public function sendCreditNote($orderId)
    {
        $invoice = $this->pdfPrinter->handleCreateInvoice($orderId, false, true, 'S');

        $template = $this->templateFactory->createTemplate();
        $template->order = $order = $this->orderFac->get()->find($orderId);
        $template->products = $prod = $this->orderFac->gEMProductInOrder()->findBy(['orders' => $orderId]);
        $template->settings = $this->orderFac->getAllsettings();
        $template->discount = $this->orderFac->gEMDiscountInOrder()->findOneBy(['orders' => $orderId]);
        $template->dc = new DPHCounter();
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/creditNote.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setFrom($this->orderFac->setting('email_sender_mask'));

        try {
            $mail->addTo(trim($order->email));
        } catch (Nette\Utils\AssertionException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        $admins = $this->customerFac->gEMUser()->findBy(['active' => 1, 'noticeAcceptPay' => 1]);
        foreach ($admins as $adm) {
            if ($adm->email) {
                $mail->addBcc($adm->email);
            }
        }
        $mail->setSubject($this->trans->translate('Dobropis č.').' '.$order->codeCreditNote);
        $invCap = $this->trans->translate('Dobropis č.');
        $mail->addAttachment($invCap . ' ' . $order->codeCreditNote . '.pdf', $invoice);

        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (Nette\Mail\SendException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        } catch (Nette\Mail\SmtpException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        return true;
    }

    public function sendInquiry($values, $user = false)
    {
        $settings = $this->orderFac->getAllsettings();
        $template = $this->templateFactory->createTemplate();

        $template->values = $values;
        $template->settings = $settings;

        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/inquiry.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        if (!$user) {
            if ($this->production) {
                $mail->setFrom($values->email)
                    ->addTo($settings['email_sender_mask']);
            } else {
                $mail->setFrom($settings['email_sender_mask'])
                    ->addTo($values->email);
            }
        } else {
            $mail->setFrom($settings['email_sender_mask'])
                ->addTo($values->email);
        }
        $mail->setSubject($this->trans->translate($settings['company_name_email'] . ' - Poptávkový formulář'));

        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (Nette\Mail\SendException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        } catch (Nette\Mail\SmtpException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        return true;
    }

    private $mailer;
    private function createMailer()
    {
        return new Nette\Mail\SendmailMailer();
        if ($_SERVER['SERVER_NAME'] == 'localhost') {
            $settings = $this->orderFac->getAllsettings();
            if ($settings['host_email_for_sender'] && $settings['email_for_sender'] && $settings['email_for_sender_pass']) {
                $this->mailer = new Nette\Mail\SmtpMailer([
                    'host' => $settings['host_email_for_sender'],
                    'username' => $settings['email_for_sender'],
                    'password' => $settings['email_for_sender_pass'],
                    //'secure' => 'tls',
                ]);
            }
        } else {
            $mailer = new Nette\Mail\SendmailMailer();
        }

        return $mailer;
    }

}
