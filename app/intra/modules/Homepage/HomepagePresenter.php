<?php

namespace App\Presenters;

use Intra\Model\Database\Entity\KbLog;
use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\ProductFacade;

class HomepagePresenter extends BaseIntraPresenter {

    /** @var ProductFacade @inject */
    public $prodFacade;

    /**
     * ACL name='Domovská stránka'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction(NULL, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení homepage - default'
     */
    public function renderDefault() {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);
        $this->template->productAlerts = $this->prodFacade->getProductStockAlerts();
    }

}
