<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\WebMenuFacade;
use Front\Model\Facade\FrontFacade;
use Front\Components\InlineElem\IInlineControlFactory;
use Nette\Application\UI\Form;

class WebMenuPresenter extends BaseIntraPresenter {

    /** @var FrontFacade @inject */
    public $frontFacade;

    /** @var WebMenuFacade @inject */
    public $facade;

    /** @var IInlineControlFactory @inject */
    public $inlineElemFac;

    protected function createComponentInline() {
        return $this->inlineElemFac->create();
    }

    /**
     * ACL name='Správa menu - sekce'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id) {
        if ($id) {
            $this->template->menu = $menu = $this->facade->get()->find($id);
            $this['form']->setDefaults($menu->toArray());
            $this->template->allowEdit = true;
            $this->template->isBack = true;
            $this->template->resource = $this->facade->gEMWebResources()->findAssoc(['pageId' => $menu->id], 'divId');
        }
    }

    /**
     * ACL name='Tabulka s všech menů'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'WebMenu:edit');
        if ($action)
            $action->setIcon('pencil')
                    ->setTitle('Úprava')
                    ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit menů'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this, __FUNCTION__);
        $form->addUpload('menuImg', 'Obrázek')
            ->setRequired(false)// nepovinný
            ->addRule(Form::IMAGE, 'Ikona musí být JPEG, PNG nebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální možná velikost ikonky je 3 Mb', 3072 * 1024/* v bytech */);
        $form->setMessages(['Podařilo se uložit menu', 'success'], ['Nepodařilo se uložit menu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'menuFormSuccess'];
        return $form;
    }

    public function menuFormSuccess($form, $values) {
        $values2 = $this->request->getPost();
        // ukládám formulář  pomocí automatického save
        $menu = $this->formGenerator->processForm($form, $values, true);

        $this->facade->generateSlug($menu->id, $menu->name);

        if ($values->menuImg->name != null) {
            if ($menu->image) {
                if (file_exists($menu->image)) {
                    unlink($menu->image);
                }
            }
            if (!is_dir('menu-img')) {
                mkdir('menu-img');
            }
            $mili = str_replace(".", "", microtime(true));
            $type = substr($values->menuImg->name, strrpos($values->menuImg->name, '.'));
            $tmp = 'menu-img/' . $menu->id . '_' . $mili . $type;
            $values->menuImg->move($tmp);
            $this->facade->saveImage($tmp, $menu);
        }

        // Uložit a zpět
        if (isset($values2['sendBack'])) {
            $this->redirect(':WebMenu:default');
            return;
        }
        // Uložit
        if (isset($values2['send'])) {
            $this->redirect(':WebMenu:edit', ['id' => $menu->id]);
            return;
        }

        // Uložit a nový
        if (isset($values2['sendNew'])) {
            $this->redirect(':WebMenu:edit');
            return;
        }
    }

    public function handleSaveInline($content, $content_id, $page_id) {
        if ($this->isAjax() && $this->user->loggedIn && !$this->getUser()->isInRole('visitor')) {
            $res = $this->frontFacade->addUpdateResources($content, $content_id, $page_id);
            if ($res == true)
                $this->presenter->flashMessage('Text pole byl úspěšně uložen!', 'success');
            else
                $this->presenter->flashMessage('Text se nepodařilo uložit!', 'error');
        }
    }

    public function handleDeleteImg($menuId)
    {
        $res = $this->facade->deleteImage($menuId);
        if ($res) {
            $this->flashMessage('Podařilo se smazat obrázek.');
        } else {
            $this->flashMessage('Nepodařilo se smazat obrázek.', 'warning');
        }

        if ($this->isAjax()) {
            $this->redrawControl('menu-img');
        } else {
            $this->redirect('this');
        }
    }
}
