<?php

namespace App\Presenters;

use Intra\Model\Facade\ProductFacade;
use Intra\Model\Facade\SaleBannerFacade;
use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Nette\Application\UI\Form;

class SaleBannerPresenter extends BaseIntraPresenter
{
    /** @var SaleBannerFacade @inject */
    public $facade;

    /** @var ProductFacade @inject */
    public $productFac;

    /**
     * ACL name='Správa slevových bannerů'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $banner = $this->facade->get()->find($id);

            if (!$banner) {
                $this->flashMessage('Požadovaný záznam nebyl nalezen!', 'warning');
                $this->redirect('SaleBanner:');
            }

            $data = $banner->toArray();

            if ($data['product']) {
                $product = $this->facade->gEMProduct()->find($data['product']);
                if ($product) {
                    $data['product'] = $product->name;
                }
            } else {
                unset($data['product']);
            }

            $data['languages'] = [];

            if ($banner->languages) {
                foreach ($banner->languages as $l) {
                    $data['languages'][$l->language->id] = $l->language->id;
                }
            }

            $this['form']->setDefaults($data);
            $this->template->banner = $banner;
        }
    }

    /**
     * ACL name='Tabulka všech slevových bannerů'
     */
    public function createComponentTable()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'SaleBanner:edit');
        if ($action)
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit banneru'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this, __FUNCTION__);
        $form->addUpload('img', 'Obrázek')
            ->setRequired(false)// nepovinný
            ->addRule(Form::IMAGE, 'Ikona musí být JPEG, PNG nebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální možná velikost ikonky je 5 MB', 5 * 1024 * 1024/* v bytech */);
        $form->setMessages(['Podařilo se uložit banner.', 'success'], ['Nepodařilo se uložit banner!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'bannersFormSuccess'];
        return $form;
    }

    public function bannersFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        // ukládám formulář  pomocí automatického save
        $banner = $this->formGenerator->processForm($form, $values, true);

        if ($values->img->name != null) {
            $path = 'sale-banners-img/' . $banner->id.'/';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            if ($banner->image) {
                if (file_exists($banner->image)) {
                    $this->imageStorage->delete($banner->image);
                }
            }
            $mili = str_replace(".", "", microtime(true));
            $type = substr($values->img->name, strrpos($values->img->name, '.'));
            $tmp = 'sale-banners-img/' . $banner->id . '/' . $banner->id . '_' . $mili . $type;
            $values->img->move($tmp);
            $this->facade->saveImage($tmp, $banner);
        }

        // Uložit
        if (isset($values2['send'])) {
            $this->redirect('SaleBanner:default');
        }
    }

    public function handleGetProducts($term)
    {
        $result = $this->productFac->getDataAutocompleteProducts($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }
}