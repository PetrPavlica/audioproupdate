<?php

namespace App\Presenters;

use Intra\Model\Database\Entity\BalikobotOrder;
use Intra\Model\Facade\BalikobotPackageFacade;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\BalikobotShopFacade;

class BalikobotOrderPresenter extends BaseIntraPresenter
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

    public function renderEdit($id)
    {
        if (!$id) {
            $this->flashMessage("Nepodařilo se vybrat objednávku.", 'warning');
            $this->redirect('BalikobotOrder:default');
        }
        $this->template->order = $this->facade->gEMBalikobotOrder()->find($id);
        $qb = $this->doctrineGrid->createQueryBuilder($this->facade->entity(),
            [], []);
        $qb->where('a.balOrders = ' . $id)
            ->addOrderBy('a.id', 'DESC');
        $this[ 'tablePackage' ]->getGrid()->setDataSource($qb);
    }

    /**
     * ACL name='Tabulka balikobot objednávek'
     */
    public function createComponentTable()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation(BalikobotOrder::class, $this->user, get_class(),
            __FUNCTION__);

        $action = $grid->addAction('edit', '', 'BalikobotOrder:edit');
        if ($action) {
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        }
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Tabulka balikobot balíků'
     */
    public function createComponentTablePackage()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(),
            __FUNCTION__);

        $grid->addGroupAction('Tisk štítků pro vybrané')->onSelect[] = [$this, 'createMultiPrintLabels'];

        $action = $grid->addAction('track', '', 'BalikobotPackage:track');
        if ($action) {
            $action->setIcon('road')
                ->setTitle('Track')
                ->setClass('btn btn-xs btn-default');
        }

        $presenter = $this;
        $grid->addActionCallback('label', '')
            ->setIcon('file-pdf-o')
            ->setClass('btn btn-xs btn-default')
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
}
