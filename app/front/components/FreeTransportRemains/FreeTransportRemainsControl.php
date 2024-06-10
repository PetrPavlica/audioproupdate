<?php

namespace Front\Components\FreeTransportRemains;

use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Control;
use Nette\Application\UI\ITemplateFactory;
use Front\Model\Facade\FrontFacade;

class FreeTransportRemainsControl extends UI\Control
{

    /** @var ITemplateFactory */
    public $templateFactory;

    /** @var FrontFacade */
    public $facade;

    public function __construct(ITemplateFactory $templateFactory, FrontFacade $facade)
    {
        parent::__construct();
        $this->templateFactory = $templateFactory;
        $this->facade = $facade;
    }

    private function setVariables(&$template, $requiredPrice, $actualPrice, $currency, $progress, $enforceFree = false)
    {
        if (is_numeric($requiredPrice) && is_numeric($actualPrice)) {
            $requiredPrice = $requiredPrice / $currency['exchangeRate'];

            $percentage = (100 * $actualPrice) / $requiredPrice;

            if ($percentage > 100) {
                $percentage = 100;
            }

            $template->remains = (($requiredPrice - $actualPrice) >= 0 ? $requiredPrice - $actualPrice : 0);
            if ($enforceFree) {
                $template->remains = 0;
                $percentage = 100;
            }
            $template->percentage = $percentage;
            $template->empty = false;
            $template->currency = $currency;
            $template->progress = $progress;

        } else {
            $template->empty = true;

        }

    }

    public function render(
        $requiredPrice = 0,
        $actualPrice = 0,
        $currency = "Kč",
        $progress = true,
        $enforceFree = false
    ) {

        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/layout.latte');

        $this->setVariables($template, $requiredPrice, $actualPrice, $currency, $progress, $enforceFree);

        // render template
        $template->render();

    }

    public function renderToString($requiredPrice, $actualPrice, $currency, $progress = true, $enforceFree = false)
    {

        $template = $this->templateFactory->createTemplate();
        $template->setFile(__DIR__ . '/templates/layout.latte');

        $this->setVariables($template, $requiredPrice, $actualPrice, $currency, $progress, $enforceFree);

        return $template;

    }

}
