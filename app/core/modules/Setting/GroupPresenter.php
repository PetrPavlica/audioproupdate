<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use App\Core\Model\Facade\PermisionGroupFacade;
use Doctrine\ORM\Tools\SchemaTool;

class GroupPresenter extends BaseIntraPresenter
{

    /** @var PermisionGroupFacade @inject */
    public $perGroupFacade;

    /**
     * ACL name='Správa skupin rolí'
     * ACL rejection='Nemáte přístup k správě skupin rolí.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidání skupin rolí uživatelů'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);
        if ($id) {
            $this[ 'form' ]->setDefaults($this->perGroupFacade->get()->find($id)->toArray());
        }
    }

    /**
     * ACL name='Tabulka s přehledem rolí'
     */
    public function createComponentTable()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->perGroupFacade->entity(), $this->user, get_class(),
            __FUNCTION__);

        $action = $grid->addAction('editMode', 'Oprávnění', 'editMode!');
        if ($action) {
            $action->setIcon('cogs')
                ->setTitle('Oprávnění')
                ->setClass('btn btn-xs btn-default');
        }
        $action = $grid->addAction('edit', '', ':Group:edit');
        if ($action) {
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        }
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/editaci skupin rolí'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation($this->perGroupFacade->entity(), $this->user, $this,
            __FUNCTION__);
        $form->setMessages(['Podařilo se uložit skupinu rolí', 'success'],
            ['Nepodařilo se uložit skupinu rolí!', 'warning']);
        $form->setRedirect(':Group:default');
        return $form;
    }

    /**
     * ACL name='Doctrine update schematu akce'
     */
    public function handleSchemaUpdate($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

        try {
            $cacheDriver = new \Doctrine\Common\Cache\ArrayCache();
            $deleted = $cacheDriver->deleteAll();
            $schemaTool = new SchemaTool($this->perGroupFacade->getEm());
            $this->perGroupFacade->getEm()->getMetadataFactory()->setCacheDriver(null);
            $metadatas = $this->perGroupFacade->getEm()->getMetadataFactory()->getAllMetadata();
            $updateSchemaSql = $schemaTool->getUpdateSchemaSql($metadatas, false);
            $this->template->sql = $updateSchemaSql;
            foreach ($updateSchemaSql as $s) {
                echo "<pre>$s;</pre><br/>";
            }
            die;
        } catch (\Exception $i) {
            $this->flashMessage('Chyba:' . $i->getMessage(), 'error');
            \Tracy\Debugger::log($i);
            //$this->setRedirect('this');
        }
        $this->flashMessage('Ok', 'success');
    }

    /**
     * ACL name='Smazání mapovaných elementů akce'
     */
    public function handleDeleteMapping($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

        try {
            $em = $this->perGroupFacade->getEm();
            $q = $em->createQuery('DELETE FROM App\Core\Model\Database\Entity\PermisionItem');
            $numDeleted = $q->execute();
        } catch (\Exception $i) {
            $this->flashMessage('Chyba:' . $i->getMessage(), 'error');
            \Tracy\Debugger::log($i);
            $this->redirect('this');
        }
        $this->flashMessage('Ok. Smazáno záznamů: ' . $numDeleted, 'success');
        $this->redirect('this');
    }

}
