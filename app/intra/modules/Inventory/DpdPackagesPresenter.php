<?php

namespace App\Presenters;

use App\Core\Components\UblabooTable\Model\ACLGrid;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Database\Entity\Orders;
use Intra\Model\Facade\DpdPackageFacade;
use Intra\Model\Facade\OrderFacade;
use Intra\Model\Utils\MyDpdMessage;
use Tracy\Debugger;

class DpdPackagesPresenter extends BaseIntraPresenter
{
    /** @var DpdPackageFacade @inject */
    public $facade;

    /** @var OrderFacade @inject */
    public $orderFacade;

    /**
     * ACL name='Správa zásilek pro DPD'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderShipmentStatus($shipmentId = null, array $shipments = null)
    {
        if ($shipmentId || $shipments) {
            $shipmentsArr = [];
            try {
                if ($shipmentId) {
                    $shipmentsArr[] = $this->facade->getShipmentStatus([$shipmentId]);
                } else {
                    $shipmentsArr = $this->facade->getShipmentStatus($shipments);
                }
            } catch (MyDpdMessage $ex) {
                $this->flashMessage($ex->getMessage(), 'danger');
            } catch (\Exception $ex) {
                Debugger::log($ex);
                $this->flashMessage('Při získání stavu zásilky došlo k chybě. Kontaktujte podporu.', 'danger');
            }

            $this->template->shipmentsArr = $shipmentsArr;
        } else {
            $this->redirect('DpdPackages:');
        }
    }

    /**
     * ACL name='Tabulka všech zásilek pro DPD'
     */
    public function createComponentTable() {
        //$grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $grid = new ACLGrid($this->user, get_class(), __FUNCTION__, $this->acl);
        $grid->setTranslator($this->translator);
        $this->doctrineGrid->setGrid($grid);
        $grid = $this->doctrineGrid->setScope($grid);

        $datasource = $this->facade->get()->createQueryBuilder();
        $datasource->select('a')->from($this->facade->entity(), 'a');
        $datasource->leftJoin(Orders::class, 'o', 'WITH', 'a.order = o');
        $datasource->where('a.shipmentId is not null')->groupBy('a.shipmentId');

        $grid->setDataSource($datasource);

        $dpdAddresses = ['' => 'vše'];
        $addresses = $this->facade->gEMDpdAddress()->findAll();
        if ($addresses) {
            foreach ($addresses as $a) {
                $dpdAddresses[$a->id] = $a->name;
            }
        }

        $grid->addColumnText('id', 'id')->setSortable();
        $grid->addColumnText('shipmentId', 'Zásilka č.')->setSortable();
        $grid->addColumnText('order', 'Z objednávky', 'order.variableSymbol')->setSortable()
            ->setFilterText('o.variableSymbol');
        $grid->addColumnText('dpdAddress', 'Svozové místo', 'dpdAddress.name')->setSortable()
            ->setFilterSelect($dpdAddresses, 'a.dpdAddress');

        $grid->addGroupAction('Vytvořit seznam zásilek')->onSelect[] = [$this, 'closeManifest'];
        $grid->addGroupAction('Zjistit stav zásilek')->onSelect[] = [$this, 'redirectShipmentStatus'];

        /*$grid->allowRowsGroupAction(function($item) {
            return $item->manifest == null;
        });*/

        $grid->allowRowsAction('delete', function($item) {
            return $item->manifest === null;
        });

        $grid->allowRowsAction('label', function($item) {
            return $item->shipmentId !== null;
        });

        $grid->allowRowsAction('status', function($item) {
            return $item->shipmentId !== null;
        });

        $grid->setDefaultSort(['id' => 'DESC']);

        $action = $grid->addAction('status', '', 'shipmentStatus', ['shipmentId' => 'shipmentId']);
        if ($action) {
            $action->setTitle('Zjistit stav zásilky')->setClass('btn btn-xs btn-default')->setIcon('road')
                ->addAttributes(['target' => '_blank']);
        }

        $action = $grid->addAction('label', '', 'printLabel!', ['shipmentId' => 'shipmentId']);
        if ($action) {
            $action->setTitle('Vytisknout štítky')->setClass('btn btn-xs btn-default')->setIcon('file')
                ->addAttributes(['target' => '_blank']);
        }

        $this->doctrineGrid->addButonDelete();
        return $grid;
    }

    /**
     * ACL name='Tabulka všech balíků pro DPD'
     */
    public function createComponentPackagesTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);

        $grid->allowRowsAction('delete', function($item) {
            return $item->manifest === null;
        });

        $grid->allowRowsAction('label', function($item) {
            return $item->shipmentId !== null;
        });

        $grid->setDefaultSort(['id' => 'DESC']);

        $action = $grid->addAction('label', '', 'printLabel!', ['shipmentId' => 'shipmentId']);
        if ($action) {
            $action->setTitle('Vytisknout štítky')->setClass('btn btn-xs btn-default')->setIcon('file')
                ->addAttributes(['target' => '_blank']);
        }

        $this->doctrineGrid->addButonDelete();
        return $grid;
    }

    public function closeManifest($ids)
    {
        try {
            $this->orderFacade->closeManifest($ids);
        } catch (MyDpdMessage $ex) {
            $this->flashMessage($ex->getMessage(), 'danger');
        } catch (\Exception $ex) {
            Debugger::log($ex);
            $this->flashMessage('Při tisku seznamu zásilek došlo k chybě. Kontaktujte podporu.', 'danger');
        }
        if ($this->isAjax()) {
            $this['table']->reload();
        } else {
            $this->redirect('this');
        }
    }

    public function redirectShipmentStatus($ids)
    {
        $shipments = $this->facade->getShipments($ids);
        $this->redirect('DpdPackages:shipmentStatus', ['shipments' => $shipments]);
    }

    public function handleDelete($id)
    {
        try {
            $this->facade->deletePackage($id);
            $this->flashMessage('Zásilku se podařilo úspěšně smazat.', 'success');
        } catch (MyDpdMessage $ex) {
            $this->flashMessage($ex->getMessage(), 'danger');
        } catch (\Exception $ex) {
            Debugger::log($ex);
            $this->flashMessage('Při mazání zásilky došlo k chybě. Kontaktujte podporu.', 'danger');
        }
        if ($this->isAjax()) {
            $this['table']->reload();
        } else {
            $this->redirect('this');
        }
    }

    public function handlePrintLabel($shipmentId)
    {
        try {
            $pdf = $this->orderFacade->getShipmentLabelDpd($shipmentId);
            header("Content-type: application/pdf");
            header("Content-Disposition: inline; filename=stitky.pdf");
            echo $pdf;
            die;
        } catch (MyDpdMessage $ex) {
            $this->flashMessage($ex->getMessage(), 'danger');
        } catch (\Exception $ex) {
            Debugger::log($ex);
            $this->flashMessage('Při tisku štítků došlo k chybě. Kontaktujte podporu.', 'danger');
        }
    }
}