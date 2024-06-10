<?php

namespace App\Presenters;

use Intra\Model\Facade\ProductFacade;
use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\BannerProductFacade;
use Nette\Application\UI\Form;

class BannerProductPresenter extends BaseIntraPresenter
{

    /** @var BannerProductFacade @inject */
    public $facade;

    /** @var ProductFacade @inject */
    public $productFac;

    /**
     * ACL name='Správa bannerů'
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
                $this->redirect('BannerProduct:');
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
     * ACL name='Tabulka s všech bannerů'
     */
    public function createComponentTable()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'BannerProduct:edit');
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
            if (!is_dir('banners-img')) {
                mkdir('banners-img');
            }
            if (!is_dir('banners-img/' . $banner->id)) {
                mkdir('banners-img/' . $banner->id);
            }
            if ($banner->image) {
                if (file_exists($banner->image)) {
                    unlink($banner->image);
                }
            }
            $mili = str_replace(".", "", microtime(true));
            $type = substr($values->img->name, strrpos($values->img->name, '.'));
            $tmp = 'banners-img/' . $banner->id . '/' . $banner->id . '_' . $mili . $type;
            $values->img->move($tmp);
            $this->facade->saveImage($tmp, $banner);
            /*$image = Image::fromFile($tmp);
            $image->resize(160, null); // šířka 75, výška se dopočítá
            $image->save($tmp);

            $this->facade->saveImage($tmp, $category);*/
        }

        // Uložit
        if (isset($values2['send'])) {
            $this->redirect('BannerProduct:default');
            return;
        }
    }

    public function handleGetProducts($term)
    {
        $result = $this->productFac->getDataAutocompleteProducts($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }
}