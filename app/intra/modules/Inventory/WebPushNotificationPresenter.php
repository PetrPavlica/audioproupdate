<?php

namespace App\Presenters;

use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\ProductFacade;
use Intra\Model\Facade\WebPushNotificationFacade;
use Intra\Model\Facade\WebPushSubscriptionFacade;
use Nette\Application\UI\Form;

class WebPushNotificationPresenter extends BaseIntraPresenter
{
    /** @var WebPushNotificationFacade @inject */
    public $facade;

    /** @var WebPushSubscriptionFacade @inject */
    public $webPushSubFac;

    /** @var ProductFacade @inject */
    public $productFac;

    /**
     * ACL name='Správa notifikací'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $notification = $this->facade->get()->find($id);

            if (!$notification) {
                $this->flashMessage('Požadovaný záznam nebyl nalezen!', 'warning');
                $this->redirect('WebPushNotification:');
            }

            $data = $notification->toArray();

            if ($data['product']) {
                $product = $this->facade->gEMProduct()->find($data['product']);
                if ($product) {
                    $data['product'] = $product->name;
                }
            } else {
                unset($data['product']);
            }
            $this['form']->setDefaults($data);
            $this->template->notification = $notification;
        }
    }

    /**
     * ACL name='Tabulka se všemi notifikacemi'
     */
    public function createComponentTable()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'WebPushNotification:edit');
        if ($action)
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Tabulka se všemi zařízeními'
     */
    public function createComponentSubscriptionTable()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->webPushSubFac->entity(), $this->user, get_class(), __FUNCTION__);
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit notifikace'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);
        $form->addUpload('img', 'Obrázek')
            ->setRequired(false)// nepovinný
            ->addRule(Form::IMAGE, 'Ikona musí být JPEG, PNG nebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální možná velikost ikonky je 5 MB', 5 * 1024 * 1024/* v bytech */);

        $form->addSubmit('sendDevice', 'Uložit a odeslat');

        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'notificationSuccess'];
        return $form;
    }

    public function notificationSuccess($form, $values)
    {
        $values2 = $this->getRequest()->getPost();
        // ukládám formulář  pomocí automatického save
        $notification = $this->formGenerator->processForm($form, $values, true);
        if ($values->img->name != null) {
            if (!is_dir('notification-img')) {
                mkdir('notification-img');
            }
            if (!is_dir('notification-img/' . $notification->id)) {
                mkdir('notification-img/' . $notification->id);
            }
            if ($notification->image) {
                if (file_exists($notification->image)) {
                    @unlink($notification->image);
                }
            }
            $mili = str_replace(".", "", microtime(true));
            $type = substr($values->img->name, strrpos($values->img->name, '.'));
            $tmp = 'notification-img/' . $notification->id . '/' . $notification->id . '_' . $mili . $type;
            $values->img->move($tmp);
            $this->facade->saveImage($tmp, $notification);
        }

        // Uložit
        if (isset($values2['send'])) {
            $this->redirect('WebPushNotification:default');
            return;
        } else if (isset($values2['sendDevice'])) {
            //$this->facade->sendNotification($notification);
            $this->facade->saveForSend($notification);

            $this->redirect('WebPushNotification:default');
        }
    }

    public function handleGetProducts($term)
    {
        $result = $this->productFac->getDataAutocompleteProducts($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }
}