<?php

namespace Front\Components\StockExpedition;

use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Control;
use Nette\Application\UI\ITemplateFactory;
use Front\Model\Facade\FrontFacade;

use Front\Model\Utils\Text\UnitParser;

class StockExpeditionControl extends UI\Control
{

    /** @var ITemplateFactory */
    public $templateFactory;

    /** @var FrontFacade */
    public $facade;

    public function __construct(ITemplateFactory $templateFactory)
    {
        parent::__construct();
        $this->templateFactory = $templateFactory;
    }

    public function render($product, $expedition = true)
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/layout.latte');

        if($product->isSet == true) {
            //set
            $setCount = -1;
            foreach ($product->setProducts as $p){
                //produkty v setu
                if($p->products->count > 0){
                    //skladem
                    if($setCount == -1){
                        $setCount = $p->products->count;
                    }elseif($p->products->count < $setCount){
                        $setCount = $p->products->count;
                    }
                }else{
                    //není skladem
                    $setCount = -1;
                    break;
                }
            }
            if($setCount > 0) {
                $template->count = $setCount;
            }else{
                //není skladem
                $template->count = 0;
            }
        }else {
            //produkt
            $template->count = $product->count;
        }
        $template->unit = $product->unit;
        $template->unitParser = UnitParser::class;
        $template->stockText = $product->stockText;
        $template->expedition = $expedition;
        $template->producer = $product->productMark && $product->productMark->producer ? $product->productMark->producer->id : null;

        $time = new Nette\Utils\DateTime();
        $template->hour = $time->format("H");
        $template->day = $time->format("N");

        // render template
        $template->render();
    }

    public function renderTime($today, $daysPlus = 0, $onlyText = false)
    {
        $template = $this->template;
        if ($onlyText) {
            $template->setFile(__DIR__ . '/templates/textOnly.latte');
        } else {
            $template->setFile(__DIR__ . '/templates/time.latte');
        }
        $days = 0;
        if (!$onlyText) {
            $days++;
        }
        if (!$today) {
            $days += 5;
        } else {
            if (date('H:i') >= '12:00') {
                $days++;
            }
        }
        $days += $daysPlus;

        $time = new Nette\Utils\DateTime('+' . $days . ' days');
        if ($this->isWeekend($time)) {
            $time = $time->modify('+1 day');
        }
        if ($this->isWeekend($time)) {
            $time = $time->modify('+1 day');
        }
        $template->time = $time;

        // render template
        $template->render();
    }

    function isWeekend($date)
    {
        $weekDay = date('w', strtotime($date));
        return ($weekDay == 0 || $weekDay == 6);
    }

}
