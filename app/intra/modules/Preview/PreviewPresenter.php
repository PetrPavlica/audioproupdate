<?php

namespace App\Presenters;

use Nette;
use Intra\Model\Facade\ProductActionFacade;
use Intra\Model\Database\Entity\Product;
use Intra\Model\Database\Entity\Orders;
use Intra\Model\Utils\DPHCounter;

class PreviewPresenter extends BasePresenter
{
    /** @var ProductActionFacade @inject */
    public $actionFacade;
    public function renderDefault()
    {
        $values = [
            'from' => '',
            'to' => '',
            'text' => ''
        ];
        $this->template->values = (object)$values;
        $this->template->actualCurrency = $this->actionFacade->gEMCurrency()->find(1)->toArray();
        $this->template->settings = $this->actionFacade->getAllsettings();
        //$this->template->product = $this->actionFacade->getEm()->getRepository(Product::class)->find(1);
        $this->template->order = $this->actionFacade->getEm()->getRepository(Orders::class)->find(31);
        $this->template->products = $prod = $this->actionFacade->gEMProductInOrder()->findBy(['orders' => 31]);
        $this->template->dc = new DPHCounter();
        $this->template->discount = $this->actionFacade->gEMDiscountInOrder()->findOneBy(['orders' => 31]);
        $this->template->state = $this->actionFacade->gEMOrderState()->find(5);
        $this->template->setFile(__DIR__.'/../../components/MailSender/templates/changeOrderState.latte');
    }
}