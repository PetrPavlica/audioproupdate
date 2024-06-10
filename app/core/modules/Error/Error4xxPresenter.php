<?php

namespace App\Presenters;

use Nette;
use Intra\Model\Facade\RedirectRuleFacade;

class Error4xxPresenter extends Nette\Application\UI\Presenter
{

    /** @var RedirectRuleFacade */
    public $facade;

    public function __construct(RedirectRuleFacade $facade)
    {
        parent::__construct();
        $this->facade = $facade;
    }

    public function startup()
    {
        parent::startup();
        if (!$this->getRequest()->isMethod(Nette\Application\Request::FORWARD)) {
            $this->error();
        }
    }

    public function renderDefault(Nette\Application\BadRequestException $exception)
    {
        // Redirector from old url saved in db
        if ($exception->getCode() == '404') {
            $actual_link = (isset($_SERVER[ 'HTTPS' ]) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $url = $this->facade->get()->findOneBy(['oldPath' => $actual_link]);
            if (count($url)) {
                $this->redirectUrl($url->newPath, 301);
                exit;
            }
        }

        // load template 403.latte or 404.latte or ... 4xx.latte
        $file = __DIR__ . "/templates/Error/{$exception->getCode()}.latte";
        $this->template->setFile(is_file($file) ? $file : __DIR__ . '/templates/Error/4xx.latte');
    }

}
