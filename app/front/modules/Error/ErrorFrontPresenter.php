<?php

namespace App\Presenters;

use Intra\Model\Facade\ProductFacade;
use Nette;
use Intra\Model\Facade\RedirectRuleFacade;

class ErrorFrontPresenter extends BaseFrontPresenter
{
    /** @var RedirectRuleFacade */
    public $redRuleFacade;

    /** @var ProductFacade @inject */
    public $productFac;

    public function __construct(RedirectRuleFacade $redRuleFacade)
    {
        parent::__construct();
        $this->redRuleFacade = $redRuleFacade;
    }

    public function startup()
    {
        parent::startup();
        if (!$this->getRequest()->isMethod(Nette\Application\Request::FORWARD)) {
            $this->error();
        }
    }

    public function renderDefault($exception)
    {
        // Redirector from old url saved in db
        if ($exception->getCode() == '404') {
            $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $url = $this->redRuleFacade->get()->findOneBy(['oldPath' => $actual_link]);
            if (count($url)) {
                $this->redirectUrl($url->newPath, 301);
                exit;
            }
        }

        $errorCodes = [
            403,
            404,
            405,
            410
        ];

        // load template 403.latte or 404.latte or ... 4xx.latte
        /*$file = __DIR__ . "/templates/Error/{$exception->getCode()}.latte";
        $this->template->setFile(is_file($file) ? $file : __DIR__ . '/templates/Error/4xx.latte');*/
        $this->template->setFile(__DIR__ . '/templates/Error/404.latte');
        $this->template->code = in_array($exception->getCode(), $errorCodes) ? $exception->getCode() : '404';
        $qb = $this->productFac->getEm()->createQueryBuilder()
            ->select('p')
            ->from($this->productFac->entity(), 'p')
            ->where('p.active = 1 and p.saleTerminated = 0')
            ->setMaxResults(30)
            ->orderBy('p.id', 'DESC');

        $this->template->onFrontMostSold = $qb->getQuery()->getResult();
    }


}
