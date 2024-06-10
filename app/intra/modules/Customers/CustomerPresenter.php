<?php

namespace App\Presenters;

use App\Core\Model\ACLForm;
use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\CustomerFacade;
use App\Core\Model\Database\Entity\User;
use Nette\Caching\Cache;
use Nette\Utils\DateTime;

class CustomerPresenter extends BaseIntraPresenter {

	/** @var CustomerFacade @inject */
	public $cusFac;

	/**
	 * ACL name='Správa zákazníků - sekce'
	 */
	public function startup() {
		parent::startup();
		$this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
	}

	/**
	 * ACL name='Přidávání/edit zákazníků'
	 */
	public function renderEdit($id, $backUrl, $backId) {
		$this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

		if ($id) {
			$customer = $this->cusFac->get()->find($id);
			$this->template->customerOrders = $this->cusFac->gEMOrders()->findBy(['customer' => $id], ['foundedDate' => 'DESC']);
			$this['form']->setDefaults($customer->toArray());
            $this->template->customer = $customer;
            $this->template->marks = $this->cusFac->gEMProductMark()->findAll();
		}
	}

	/**
	 * ACL name='Tabulka přehled zákazníků'
	 */
	public function createComponentTable() {
		$grid = $this->doctrineGrid->generateGridByAnnotation($this->cusFac->entity(), $this->user, get_class(), __FUNCTION__);
		$action = $grid->addAction('edit', '', 'Customer:edit');
		if ($action)
			$action->setIcon('pencil')
					->setTitle('Úprava')
					->setClass('btn btn-xs btn-default');
		$this->doctrineGrid->addButonDelete();
		return $this->tblFactory->create($grid);
	}

	/**
	 * ACL name='Formulář pro přidání/edit zákazníků'
	 */
	public function createComponentForm() {
		$form = $this->formGenerator->generateFormByAnnotation($this->cusFac->entity(), $this->user, $this, __FUNCTION__);
		$form->setMessages(['Podařilo se uložit zákazníka', 'success'], ['Nepodařilo se uložit zákazníka!', 'warning']);
		$form->setRedirect(':Customer:default');
		$form->onSuccess[] = [$this, 'processForm'];
		return $form;
	}

	public function processForm($form, $values) {
		if (isset($values['password'])) {
			if ($values['password'] != '')
				$values['password'] = $this->cusFac->hash($values['password']);
			else
				unset($values['password']);
		}
		$this->formGenerator->processForm($form, $values, true);
	}

    /**
     * ACL name='Formulář pro přidání/edit zákazníků'
     */
    public function createComponentFormSales() {
        $form = new ACLForm();
        $form->setMessages(['Podařilo se uložit slevu', 'success'], ['Nepodařilo se uložit slevu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'salesForm'];
        return $form;
    }

    public function salesForm($form, $values) {
        $values2 = $this->request->getPost();
        if (isset($values2['addSale'])) {
            if ($this->cusFac->insertSale($values2['customer'], $values2['newSaleMark'], $values2['newSaleValue'])) {
                $this->flashMessage('Slevu se podařilo přidat.', 'success');
            } else {
                $this->flashMessage('Slevu se nepodařilo přidat.');
            }
        } elseif(isset($values2['updateSale'])) {
            $idSale = $values2['updateSale'];
            $key = array_search ($idSale, $values2['saleId']);
            if ($this->cusFac->updateSale($idSale, $values2['saleValue'][$key])) {
                $this->flashMessage('Slevu se podařilo upravit.', 'success');
            } else {
                $this->flashMessage('Slevu se nepodařilo upravit.');
            }
        }

        // clean cache
        for($i = 0; $i < 10; $i++) {
            $this->storage->clean([
                Cache::TAGS => ["products"],
                Cache::PRIORITY => 15
            ]);
        }

        $this->redrawControl('sales-customer');
    }

    public function handleDeleteSale($saleId)
    {
        if ($this->cusFac->removeSale($saleId)) {
            $this->flashMessage('Slevu se podařilo smazat.', 'success');
        } else {
            $this->flashMessage('Slevu se nepodařilo smazat.');
        }

        // clean cache
        for($i = 0; $i < 10; $i++) {
            $this->storage->clean([
                Cache::TAGS => ["products"],
                Cache::PRIORITY => 15
            ]);
        }

        $this->redrawControl('sales-customer');
    }
}
