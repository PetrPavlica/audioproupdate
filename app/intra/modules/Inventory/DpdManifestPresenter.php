<?php

namespace App\Presenters;

use App\Core\Components\UblabooTable\Model\ACLGrid;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\DpdManifestFacade;
use Intra\Model\Facade\DpdPackageFacade;
use Intra\Model\Facade\OrderFacade;
use Intra\Model\Utils\MyDpdMessage;
use Tracy\Debugger;

class DpdManifestPresenter extends BaseIntraPresenter
{
    /** @var DpdManifestFacade @inject */
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

    /**
     * ACL name='Tabulka všech zásilek pro DPD'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);

        $grid->allowRowsAction('manifest', function($item) {
            return $item->referenceNumber !== null;
        });

        $grid->allowRowsAction('label', function($item) {
            return $item->referenceNumber !== null;
        });

        $grid->setDefaultSort(['id' => 'DESC']);

        $action = $grid->addAction('manifest', '', 'printManifest!', ['manifest' => 'referenceNumber']);
        if ($action) {
            $action->setTitle('Vytisknout seznam balíků')->setClass('btn btn-xs btn-default')->setIcon('file')
                ->addAttributes(['target' => '_blank']);
        }

        $action = $grid->addAction('label', '', 'printLabel!', ['manifest' => 'referenceNumber']);
        if ($action) {
            $action->setTitle('Vytisknout štítky')->setClass('btn btn-xs btn-default')->setIcon('file')
                ->addAttributes(['target' => '_blank']);
        }

        return $grid;
    }

    public function handlePrintManifest($manifest)
    {
        try {
            $pdf = $this->orderFacade->printManifest($manifest);
            header("Content-type: application/pdf");
            header("Content-Disposition: inline; filename=seznam.pdf");
            echo $pdf;
            die;
        } catch (MyDpdMessage $ex) {
            $this->flashMessage($ex->getMessage(), 'danger');
        } catch (\Exception $ex) {
            Debugger::log($ex);
            $this->flashMessage('Při tisku seznamu zásilek došlo k chybě. Kontaktujte podporu.', 'danger');
        }
    }

    public function handlePrintLabel($manifest)
    {
        try {
            $pdf = $this->orderFacade->printLabel($manifest);
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