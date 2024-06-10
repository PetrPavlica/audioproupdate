<?php

namespace App\Presenters;

use Intra\Model\Facade\WebPushSubscriptionFacade;

class WebPushPresenter extends BaseFrontPresenter
{
    /** @var WebPushSubscriptionFacade @inject */
    public $webPushFacade;

    public function actionSubscription()
    {
        $rawData = file_get_contents("php://input");
        $values = json_decode($rawData, true);
        if ($this->getRequest()->isMethod('POST') || $this->getRequest()->isMethod('PUT')) {
            $this->sess->subscription = $this->webPushFacade->updateSubscription($values, $this->getUser());
        } else if ($this->getRequest()->isMethod('DELETE')) {
            $this->webPushFacade->deleteSubscription($values);
        }
        $this->sendJson([]);
    }
}