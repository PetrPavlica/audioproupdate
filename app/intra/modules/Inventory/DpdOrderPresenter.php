<?php

namespace App\Presenters;

use App\Core\Model\ACLForm;
use Intra\Model\Facade\DpdOrderFacade;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Utils\MyDpdMessage;
use Tracy\Debugger;

class DpdOrderPresenter extends BaseIntraPresenter {

    /** @var DpdOrderFacade @inject */
    public $facade;

    /**
     * ACL name='Správa objednávek svozu pro DPD'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $order = $this->facade->get()->find($id);
            if (!$order) {
                $this->flashMessage('Nepodařilo se najít uvedenou objednávku svozu!', 'error');
                $this->redirect('DpdOrder:');
            }
            $this['form']->setDefaults($order->toArray());
        } else {
            $this['form']->setDefaults([
                'date' => $this->facade->getOrderDate()->format('j. n. Y'),
                'fromTime' => '08:00',
                'toTime' => '18:00',
                'quantity' => 0,
                'weight' => 0
            ]);
        }
    }

    public function renderDelete($id)
    {
        if ($id) {
            $order = $this->facade->get()->find($id);
            if (!$order) {
                $this->flashMessage('Nepodařilo se najít uvedenou objednávku svozu!', 'error');
                $this->redirect('DpdOrder:');
            }
            $this['deleteForm']->setDefaults($order->toArray());
        } else {
            $this->redirect('DpdOrder:');
        }
    }

    /**
     * ACL name='Tabulka všech objednávek svozu pro DPD'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'DpdOrder:edit');
        if ($action)
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');

        $delete = $grid->addAction('delete', '', 'DpdOrder:delete');
        if ($delete)
            $delete->setIcon('trash')
                ->setTitle('Smazat')
                ->setClass('btn btn-xs btn-danger');

        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit objednávky svozu'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);
        $form->setMessages(['Podařilo se uložit objednávku svozu', 'success'],
            ['Nepodařilo se uložit objednávku svozu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'dpdFormSuccess'];
        return $form;
    }

    public function dpdFormSuccess($form, $values)
    {
        $values2 = $this->getRequest()->getPost();
        // ukládám formulář  pomocí automatického save
        $dpdOrder = $this->formGenerator->processForm($form, $values, true);

        if ($dpdOrder) {
            if (!$dpdOrder->referenceNumber && $dpdOrder->quantity) {
                try {
                    $this->facade->createPickupOrder($dpdOrder);
                } catch (MyDpdMessage $ex) {
                    $this->flashMessage($ex->getMessage(), 'danger');
                } catch (\Exception $ex) {
                    Debugger::log($ex);
                    $this->flashMessage('Při objednávce svozu došlo k chybě. Kontaktujte podporu.', 'danger');
                }

            }

            $this->redirect('DpdOrder:default');
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit objednávky svozu'
     */
    public function createComponentDeleteForm()
    {
        $form = $this->formGenerator->generateFormWithoutAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);
        $form->addHidden('id');

        $form->addTextAcl('description', 'Důvod zrušení svozu')
            ->setAttribute('placeholder', 'Důvod zrušení svozu')
            ->setAttribute('class', 'form-control input-md');

        $form->addSubmitAcl('send', 'Smazat svoz');

        $form->setMessages(['Podařilo se smazat svoz.', 'success'],
            ['Nepodařilo se smazat svoz!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'dpdDeleteFormSuccess'];
        return $form;
    }

    public function dpdDeleteFormSuccess($form, $values)
    {
        $values2 = $this->getRequest()->getPost();
        // ukládám formulář  pomocí automatického save
        $dpdOrder = $this->formGenerator->processForm($form, $values, true);

        if ($dpdOrder) {
            if ($dpdOrder->referenceNumber) {
                try {
                    $this->facade->deletePickupOrder($dpdOrder);
                } catch (MyDpdMessage $ex) {
                    $this->flashMessage($ex->getMessage(), 'danger');
                } catch (\Exception $ex) {
                    Debugger::log($ex);
                    $this->flashMessage('Při smazání svozu došlo k chybě. Kontaktujte podporu.', 'danger');
                }

            }

            $this->redirect('DpdOrder:default');
        }
    }
}
