<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\SellersFacade;
use Nette\Application\UI\Form;
use Ublaboo\ImageStorage\ImageStorage;
use App\Core\Model\ACLForm;


class SellersPresenter extends BaseIntraPresenter
{

    /** @var SellersFacade @inject */
    public $facade;

    /** @var ImageStorage @inject */
    public $imageStorage;

    /**
     * ACL name='Správa prodejců - sekce'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id) {
        if ($id) {
            $this->template->sellers = $sellers = $this->facade->get()->find($id);
            $this['form']->setDefaults($sellers->toArray());
        }
    }

    /**
     * ACL name='Tabulka prodejců'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'Sellers:edit');
        if ($action)
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit prodejce'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this, __FUNCTION__);
        $form->addUpload('sellersImg', 'Logo')
            ->setRequired(false)// nepovinný
            ->addRule(Form::IMAGE, 'Logo musí být JPEG, PNG nebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální možná velikost loga je 3 Mb', 3072 * 1024/* v bytech */);
        $form->setMessages(['Podařilo se uložit prodejce', 'success'], ['Nepodařilo se uložit prodejce!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'sellersFormSuccess'];
        return $form;
    }

    public function sellersFormSuccess($form, $values) {
        $values2 = $this->request->getPost();
        // ukládám formulář  pomocí automatického save
        $sellers = $this->formGenerator->processForm($form, $values, true);

        if ($values->sellersImg->name != null) {

            if ($sellers->image) {
                if (file_exists($sellers->image)) {
                    unlink($sellers->image);
                }
            }

            $mili = str_replace(".", "", microtime(true));
            $type = substr($values->sellersImg->name, strrpos($values->sellersImg->name, '.'));

            $nameEx = $sellers->id . '_' . $mili . $type;
            $dir = 'sellers-img/';
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            $tmp = $dir.$nameEx;

            $values->sellersImg->move($tmp);
            $this->facade->saveImage($tmp, $sellers);
        }

        // Uložit a zpět
        if (isset($values2['sendBack'])) {
            $this->redirect(':Sellers:default');
            return;
        }
        // Uložit
        if (isset($values2['send'])) {
            $this->redirect(':Sellers:edit', ['id' => $sellers->id]);
            return;
        }

        // Uložit a nový
        if (isset($values2['sendNew'])) {
            $this->redirect(':Sellers:edit');
            return;
        }
    }

    public function handleDeleteImg($sellersId)
    {
        $res = $this->facade->deleteImage($sellersId);
        if ($res) {
            $this->flashMessage('Podařilo se smazat logo.');
        } else {
            $this->flashMessage('Nepodařilo se smazat logo.', 'warning');
        }

        if ($this->isAjax()) {
            $this->redrawControl('sellers-img');
        } else {
            $this->redirect('this');
        }
    }
}