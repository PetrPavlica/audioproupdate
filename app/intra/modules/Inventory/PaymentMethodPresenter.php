<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Image;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\PaymentMethodFacade;

class PaymentMethodPresenter extends BaseIntraPresenter
{

    /** @var PaymentMethodFacade @inject */
    public $facade;

    /**
     * ACL name='Správa metod placení'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $this->template->method = $method = $this->facade->get()->find($id);
            $this[ 'form' ]->setDefaults($method->toArray());
        }
    }

    /**
     * ACL name='Tabulka s metodami placení'
     */
    public function createComponentTable()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(),
            __FUNCTION__);
        $action = $grid->addAction('edit', '', 'PaymentMethod:edit');
        if ($action) {
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        }
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit metod placení'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);
        $form->addUpload('methodImg', 'Ikonka')
            ->setRequired(false)// nepovinný
            ->addRule(Form::IMAGE, 'Ikona musí být JPEG, PNG nebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální možná velikost ikonky je 3 Mb', 3072 * 1024/* v bytech */);
        $form->setMessages(['Podařilo se uložit platební metodu', 'success'],
            ['Nepodařilo se uložit platební metodu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'methodFormSuccess'];
        return $form;
    }

    public function methodFormSuccess($form, $values)
    {
        // ukládám formulář  pomocí automatického save
        $method = $this->formGenerator->processForm($form, $values, true);

        if ($values->methodImg->name != null) {
            if ($method->image) {
                if (file_exists($method->image)) {
                    unlink($method->image);
                }
            }
            $type = substr($values->methodImg->name, strrpos($values->methodImg->name, '.'));
            $tmp = 'category-img/' . $method->id . '_' . str_replace(".", "", microtime(true)) . $type;
            $values->methodImg->move($tmp);
            $this->facade->saveImage($tmp, $method);

            $image = Image::fromFile($tmp);
            $image->resize(45, null); // výška daná, šířka se dopočítá
            $image->save($tmp);
        }
        $this->redirect(':PaymentMethod:default');
    }

    public function handleDeleteImg($methodId)
    {
        $res = $this->facade->deleteImage($methodId);
        if ($res) {
            $this->flashMessage('Podařilo se smazat obrázek.');
        } else {
            $this->flashMessage('Nepodařilo se smazat obrázek.', 'warning');
        }

        if ($this->isAjax()) {
            $this->redrawControl('method-img');
        } else {
            $this->redirect('this');
        }
    }

}
