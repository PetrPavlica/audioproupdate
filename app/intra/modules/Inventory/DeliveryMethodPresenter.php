<?php

namespace App\Presenters;

use Intra\Model\Utils\BalikobotAdapter;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Image;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\DeliveryMethodFacade;

class DeliveryMethodPresenter extends BaseIntraPresenter
{

    /** @var DeliveryMethodFacade @inject */
    public $facade;

    /**
     * ACL name='Správa metod dodání'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $this->template->method = $methods = $this->facade->get()->find($id);
            $arr = $methods->toArray();
            /* unset($arr['paymentMethod']);
              foreach ($methods->paymentMethod as $item) {
             * $arr['paymentMethod'][] = $item->payment->id;
              } */
            $this[ 'form' ]->setDefaults($arr);
        }
    }

    /**
     * ACL name='Tabulka s metodami dodání'
     */
    public function createComponentTable()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(),
            __FUNCTION__);
        $action = $grid->addAction('edit', '', 'DeliveryMethod:edit');
        if ($action) {
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        }
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit metod dodání'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);
        $form->addUpload('methodImg', 'Ikonka')
            ->setRequired(false)// nepovinný
            ->addRule(Form::IMAGE, 'Ikona musí být JPEG, PNG nebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální možná velikost ikonky je 3 Mb', 3072 * 1024/* v bytech */);
        $form->setMessages(['Podařilo se uložit dodací metodu', 'success'],
            ['Nepodařilo se uložit dodací metodu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'methodFormSuccess'];
        return $form;
    }

    public function methodFormSuccess($form, $values)
    {
        $values2 = $this->getRequest()->getPost();
        $values->balikobotDelivery = $values2[ 'balikobotDelivery' ];
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
            $image->resize(null, 45); // výška daná, šířka se dopočítá
            $image->save($tmp);
        }
        $this->redirect(':DeliveryMethod:default');
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
