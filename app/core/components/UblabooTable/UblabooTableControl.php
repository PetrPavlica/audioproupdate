<?php

namespace App\Core\Components\UblabooTable;

use App\Core\Model\Database\Utils\AnnotationParser;
use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Control;
use App\Core\Components\UblabooTable\Model\ACLGrid;
use App\Core\Model\Database\Entity\PermisionItem;
use Doctrine\ORM\EntityManager;

class UblabooTableControl extends UI\Control
{

    /** @var \Kdyby\Translation\Translator */
    protected $traslator;

    /** @var ACLGrid */
    protected $grid;

    /** @var EntityManager */
    protected $em;

    /**
     * Construct
     * @param SimpleUblabooTranslator $translator
     */
    public function __construct(EntityManager $em, \Kdyby\Translation\Translator $translator, ACLGrid $grid)
    {
        parent::__construct();
        $this->traslator = $translator;
        $this->grid = $grid;
        $this->em = $em;
    }

    public function render()
    {
        $result = $this->grid->getMapper()->mapHtmlControl(
            $this->grid->getUser(), get_class($this->parent), $this->grid->getNameGrid(), '', PermisionItem::TYPE_FORM);

        if ($result != 'NULL') {
            $this->template->render(__DIR__ . '/templates/table.latte');
        }
    }

    public function createComponentSimpleGrid()
    {
        return $this->grid;
    }

    private function removeRecursive($entity, $id)
    {
        $foreignKeys = AnnotationParser::getOneToManyPropertiesOfClass($entity);
        foreach($foreignKeys as $k => $f) {
            $entities = $this->em->getRepository($f['targetEntity'])->findBy([$f['mappedBy'] => $id]);
            if ($entities) {
                foreach($entities as $e) {
                    $this->removeRecursive($f['targetEntity'], $e->id);
                    $this->em->remove($e);
                }
                $this->em->flush();
            }
        }
    }

    public function handleDelete($id)
    {
        $action = $this->grid->getMapper()->mapInput(
            $this->grid->getUser(), $this->grid->getNamePresenter(), $this->grid->getNameGrid(), 'delete', '');

        if ($action == 'write' || $action == 'read') {
            try {
                $this->removeRecursive($this->grid->getEntity(), $id);
                $entity = $this->em->getRepository($this->grid->getEntity())->find($id);
                $this->em->remove($entity);
                $this->em->flush();
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'SQLSTATE[23000]')) {
                    $this->parent->flashMessage('Pozor! Záznam nelze smazat, protože se využívá!', 'warning');
                    $this->parent->redirect('this');
                    return;
                } else {
                    throw $e;
                }
            }
            $this->parent->flashMessage('Záznam se podařilo úspěšně smazat', 'success');
            $this->parent->redirect('this');
        } else {
            $this->parent->flashMessage('Pro tuto akci nemáte oprávnění', 'warning');
            $this->parent->redirect('this');
        }
    }

    public function handleEditMode($id)
    {
        $action = $this->grid->getMapper()->mapInput(
            $this->grid->getUser(), $this->grid->getNamePresenter(), $this->grid->getNameGrid(), 'editMode', '');

        if ($action == 'write' || $action == 'read') {
            $this->parent->handleEditMode($id);
        }
    }

    /**
     * Return grid
     * @return ACLGrid
     */
    public function getGrid()
    {
        return $this->grid;
    }

    public function handleHandleId($to, $id)
    {
        $presenter = $this->parent;
        if (strpos(get_class($presenter), 'Presenter') == false) {
            $presenter = $this->parent->parent;
        }
        $to = 'handle' . ucfirst($to);
        return $presenter->$to($id);
    }

}
