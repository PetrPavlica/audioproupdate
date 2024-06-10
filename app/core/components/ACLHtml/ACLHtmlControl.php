<?php

namespace App\Core\Components\ACLHtml;

use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Control;
use App\Core\Model\ACLMapper;
use App\Core\Model\Database\Entity\PermisionRule;

//@TODO - udělat jako makro

class ACLHtmlControl extends UI\Control {

    /** @var ACLMapper */
    protected $mapper;
    protected $startName = 'App_Presenters_';

    public function __construct(ACLMapper $mapper) {
        parent::__construct();
        $this->mapper = $mapper;
    }

    public function render($html, $caption, $mapName, $presenterName = NULL, $type = NULL) {
        if ($type == 'global-element') {
            $presenterName = 'global';
        } else if ($presenterName == NULL)
            $presenterName = get_class($this->parent);
        else
            $presenterName = $presenterName . 'Presenter';

        $permision = $this->mapper->mapHtmlControl($this->parent->user, $presenterName, $mapName, $caption, $type);
        // if item have permisionRule
        if ($permision == PermisionRule::ACTION_SHOW) {
            $template = $this->template;
            $template->setFile(__DIR__ . '/templates/element.latte');

            $template->html = $html;

            // render template
            $template->render();
        }
    }

}
