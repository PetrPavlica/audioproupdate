<?php

namespace App\Presenters;

use Intra\Model\Facade\BalikobotPackageFacade;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\BalikobotShopFacade;
use Nette\Application\Responses\FileResponse;

class BalikobotPackagePresenter extends BaseIntraPresenter
{

    /** @var BalikobotPackageFacade @inject */
    public $facade;

    /** @var BalikobotShopFacade @inject */
    public $balikobotShopFacade;

    /**
     * ACL name='Správa Balikobot package'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderDefault()
    {
        /*$qb = $this->doctrineGrid->createQueryBuilder($this->facade->entity(),
            [], []);
        $qb->where('a.isOrdered = 0')
            ->addOrderBy('a.id', 'DESC');
        $this[ 'table' ]->getGrid()->setDataSource($qb);*/
    }

    public function renderTrack($id)
    {
        if (!$id) {
            $this->flashMessage("Nepodařilo se vybrat objednávku.", 'warning');
            $this->redirect('Homepage:default');
        }
        $this->template->package = $package = $this->facade->gEMBalikobotPackage()->find($id);
        $this->template->track = $this->balikobotShopFacade->track($package);
    }

    public function renderOverview($id)
    {
        if (!$id) {
            $this->flashMessage("Nepodařilo se vybrat objednávku.", 'warning');
            $this->redirect('Homepage:default');
        }
        $this->template->package = $package = $this->facade->gEMBalikobotPackage()->find($id);
        $this->template->overview = $this->balikobotShopFacade->packageInfo($package);
    }

    /**
     * ACL name='Tabulka balikobot balíků'
     */
    public function createComponentTable()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(),
            __FUNCTION__, ['isOrdered' => 0, 'package_id !' => null], ['id', 'DESC']);

        $grid->addGroupAction('Objednat vybraným svoz')->onSelect[] = [$this, 'createOrderPicking'];
        $grid->addGroupAction('Tisk štítků pro vybrané')->onSelect[] = [$this, 'createMultiPrintLabels'];


        $presenter = $this;
        $grid->addActionCallback('label', '')
            ->setIcon('file-pdf-o')
            ->setClass('btn btn-xs btn-default')
            ->addAttributes(['target' => '_blank'])
            ->onClick[] = function ($id) use ($presenter) {
            $package = $presenter->facade->gEMBalikobotPackage()->find($id);
            $presenter->redirectUrl($package->label_url);
        };

        $grid->addActionCallback('down', '')
            ->setIcon('pencil')
            ->setClass('btn btn-xs btn-default')
            ->onClick[] = function ($id) use ($presenter) {
            $package = $presenter->facade->gEMBalikobotPackage()->find($id);
            $presenter->redirect('Order:view', ['id' => $package->orders->id]);
        };

        return $this->tblFactory->create($grid);
    }

    public function createMultiPrintLabels($ids)
    {
        if (!count($ids)) {
            return;
        }
        $res = $this->balikobotShopFacade->getLabels($ids);
        if (isset($res[ 'error' ])) {
            $this->flashMessage($res[ 'error' ], 'warning');
            $this->redirect('this');
        }
        $this->redirectUrl($res);
    }

    public function createOrderPicking($ids)
    {
        $res = $this->balikobotShopFacade->createOrderPicking($ids);
        if (is_string($res)) {
            $this->flashMessage($res, 'warning');
            $this->redirect('this');
        }
        $this->flashMessage('Objednávka byla úspěšně vytvořena!');
        $this->redirect('BalikobotOrder:edit', ['id' => $res->id]);
    }


}
