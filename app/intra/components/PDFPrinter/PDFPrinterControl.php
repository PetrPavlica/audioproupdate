<?php

namespace Intra\Components\PDFPrinter;

use Intra\Model\Utils\DPHCounter;
use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Control;
use Intra\Model\Facade\OrderFacade;
use Nette\Application\UI\ITemplateFactory;

class PDFPrinterControl extends UI\Control {

    /** @var ITemplateFactory @inject */
    public $templateFactory;

    /** @var OrderFacade */
    public $orderFac;

    public function __construct(ITemplateFactory $templateFactory, OrderFacade $orderFac) {
        parent::__construct();
        $this->templateFactory = $templateFactory;
        $this->orderFac = $orderFac;
    }

    public function renderInvoice($id, $text = "", $isProforma = false, $isCreditNote = false) {
        $template = $this->template;
        $template->id = $id;
        $template->text = $text;
        $template->isProforma = $isProforma;
        $template->isCreditNote = $isCreditNote;
        $template->type = 'invoice';
        $template->setFile(__DIR__ . '/templates/default.latte');
        $template->render();
    }

    public function handleCreateInvoice($invoiceId, $isProforma, $isCreditNote, $output = 'D')
    {
        $invoice = $this->orderFac->get()->find($invoiceId);

        $template = $this->templateFactory->createTemplate();
        $template->invoice = $invoice;
        if ($isCreditNote) {
            $template->productsInCreditNote = $this->orderFac->gEMProductInCreditNote()->findBy(['order' => $invoiceId]);
        } else {
            $template->products = $this->orderFac->gEMProductInOrder()->findBy(['orders' => $invoiceId]);
        }
        $template->discount = $this->orderFac->gEMDiscountInOrder()->findOneBy(['orders' => $invoiceId]);
        $template->isProforma = $isProforma;
        $template->isCreditNote = $isCreditNote;
        $template->settings = $this->orderFac->getAllsettings();
        $template->refund = $this->orderFac->gEMOrderRefund()->findOneBy(['orders' => $invoiceId], ['id' => 'ASC']);

        $template->setFile(__DIR__ . '/templates/invoice.latte');
        $mpdf = new \Mpdf\Mpdf();
        $stylesheet = file_get_contents('intra/css/pdf_print.css'); // external css
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($template, 2);

        if ($isProforma) {
            if ($output == 'D' || $output == 'I') {
                $mpdf->Output('Proforma_invoice.pdf', $output);
                die;
            }

            return $mpdf->Output('Proforma_invoice.pdf', $output);
        } elseif ($isCreditNote) {
            if ($output == 'D' || $output == 'I') {
                $mpdf->Output($invoice->codeCreditNote.'.pdf', $output);
                die;
            }

            return $mpdf->Output($invoice->codeCreditNote.'.pdf', $output);
        } else {
            if ($output == 'D' || $output == 'I') {
                $mpdf->Output($invoice->codeInvoice.'.pdf', $output);
                die;
            }

            return $mpdf->Output($invoice->codeInvoice . '.pdf', $output);
        }
    }

    public function renderStockList($id, $text = "") {
        $template = $this->template;
        $template->id = $id;
        $template->text = $text;
        $template->type = 'stockList';
        $template->setFile(__DIR__ . '/templates/default.latte');
        $template->render();
    }

    public function handleCreateStockList($invoiceId, $isProforma, $output = 'D') {

        $invoice = $this->orderFac->get()->find($invoiceId);

        $template = $this->templateFactory->createTemplate();
        $template->invoice = $invoice;
        $template->products = $this->orderFac->gEMProductInOrder()->findBy(['orders' => $invoiceId]);
        $template->isProforma = $isProforma;
        $template->settings = $this->orderFac->getAllsettings();
        $template->refund = $this->orderFac->gEMOrderRefund()->findOneBy(['orders' => $invoiceId], ['id' => 'ASC']);

        $template->setFile(__DIR__ . '/templates/stockList.latte');
        $mpdf = new \mPDF();
        $stylesheet = file_get_contents('intra/css/pdf_print.css'); // external css
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($template, 2);

        if ($output == 'D' || $output == 'I') {
            $mpdf->Output('Dodaci_list.pdf', $output);
            die;
        }

        return $mpdf->Output('Dodaci_list.pdf', $output);
    }

    public function renderReceipt($id, $text = "", $i = "file-pdf-o") {
        $template = $this->template;
        $template->id = $id;
        $template->text = $text;
        $template->i = $i;
        $template->type = 'refund';
        $template->setFile(__DIR__ . '/templates/default.latte');
        $template->render();
    }

    public function handlePrintReceipt($refundId, $output = 'D') {
        $refund = $this->orderFac->gEMOrderRefund()->find($refundId);
        $template = $this->templateFactory->createTemplate();
        $template->refund = $refund;
        $template->settings = $this->orderFac->getAllsettings();
        $template->setFile(__DIR__ . '/templates/receipt.latte');
        $mpdf = new \mPDF();
        $stylesheet = file_get_contents('intra/css/pdf_print.css'); // external css
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($template, 2);

        if ($output == 'S') {
            return [$refund->eetNo . '.pdf', $mpdf->Output($refund->eetNo . '.pdf', $output)];
        } elseif ($output == 'D' || $output == 'I') {
            $mpdf->Output($refund->eetNo . '.pdf', $output);
            die;
        }

        return $mpdf->Output($refund->eetNo . '.pdf', $output);
    }


    public function renderOffer($id, $text = "") {
        $template = $this->template;
        $template->id = $id;
        $template->text = $text;
        $template->type = 'offer';
        $template->setFile(__DIR__ . '/templates/default.latte');
        $template->render();
    }

    public function renderOfferHTML($id, $text = "") {
        $template = $this->template;

        $invoice = $this->orderFac->get()->find($id);

        $template = $this->templateFactory->createTemplate();
        $template->invoice = $invoice;
        $template->products = $this->orderFac->gEMProductInOrder()->findBy(['orders' => $id]);
        $template->discount = $this->orderFac->gEMDiscountInOrder()->findOneBy(['orders' => $id]);

        $template->settings = $this->orderFac->getAllsettings();
        $template->refund = $this->orderFac->gEMOrderRefund()->findOneBy(['orders' => $id], ['id' => 'ASC']);

        $template->dphCounter = new DPHCounter();

        $template->setFile(__DIR__ . '/templates/offer.latte');

        $template->render();
    }

    public function handleCreateOffer($invoiceId, $output = 'D') {

        $invoice = $this->orderFac->get()->find($invoiceId);

        $template = $this->templateFactory->createTemplate();
        $template->invoice = $invoice;
        $template->products = $this->orderFac->gEMProductInOrder()->findBy(['orders' => $invoiceId]);
        $template->discount = $this->orderFac->gEMDiscountInOrder()->findOneBy(['orders' => $invoiceId]);

        $template->settings = $this->orderFac->getAllsettings();
        $template->refund = $this->orderFac->gEMOrderRefund()->findOneBy(['orders' => $invoiceId], ['id' => 'ASC']);

        $template->setFile(__DIR__ . '/templates/offer.latte');

        $template->dphCounter = new DPHCounter();

        $stylesheet = file_get_contents('intra/css/pdf_print.css'); // external css
        $stylesheet2 = file_get_contents('front/css/flex.css'); // external css
        $stylesheet3 = file_get_contents('intra/css/pdf_products.css'); // external css

        $mpdf = new \mPDF();
        $mpdf->WriteHTML($stylesheet3, 1);
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($template . '', 2);

        //$mpdf->debug = true;

        if ($output == 'D' || $output == 'I') {
            $mpdf->Output($invoice->codeInvoice.'aa.pdf', $output);
            die;
        }

        return $mpdf->Output($invoice->codeInvoice . 'aa.pdf', $output);
    }

}
