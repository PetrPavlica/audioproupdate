<?php

namespace App\Presenters;

use Nette;
use Nette\Utils\Image;
use Nette\Application\UI\Form;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\ProductCategoryFacade;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;

class ProductCategoryPresenter extends BaseIntraPresenter
{

    /** @var ProductCategoryFacade @inject */
    public $facade;

    /** @var \Intra\Model\Utils\ImageManager\ImagesEditor @inject */
    public $imgEditor;

    /** @var IStorage @inject */
    public $storage;

    /**
     * ACL name='Správa kategorií produktů'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderDefault()
    {
        $this->template->category = $this->facade->getTableCategoryAll();
    }

    public function renderEdit($id)
    {

        if ($id) {
            $category = $this->facade->get()->find($id);
            if (!$category) {
                $this->flashMessage('Nepodařilo se najít kategorii!', 'danger');
                $this->redirect('ProductCategory:');
            }
            $this[ 'form' ]->setDefaults($category->toArray());
            if ($category->mallCategory) {
                $this['form']->setAutocmp('mallCategory', $category->mallCategory->name);
            }
            $this->template->category = $category;
            /*$this->template->filtersGroup = $this->facade->getGroupProductFilter()->findBy(['category' => $id],
                ['orderState' => 'ASC']);
            $this->template->hideFilters = $this->facade->gEMGroupProductFilter()->findBy(['category' => $category->mainCategory],
                ['orderState' => 'ASC']);*/
            $this->template->formValues = [
                'categoryZbozi' => $category->categoryZbozi,
                'categoryHeureka' => $category->categoryHeureka,
                'categoryGoogleMerchants' => $category->categoryGoogleMerchants
            ];
        } else {
            $this->template->formValues = [
                'categoryZbozi' => '',
                'categoryHeureka' => '',
                'categoryGoogleMerchants' => '',
            ];
        }
    }

    /**
     * ACL name='Tabulka s přehledem kategorie produktů'
     */
    public function createComponentTable()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(),
            __FUNCTION__);
        $action = $grid->addAction('edit', '', 'ProductCategory:edit');
        if ($action) {
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        }
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit kategorie produktů'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this,
            __FUNCTION__);
        $form->addUpload('categoryImg', 'Ikonka')
            ->setRequired(false)// nepovinný
            ->addRule(Form::IMAGE, 'Ikona musí být JPEG, PNG nebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální možná velikost ikonky je 3 Mb', 3072 * 1024/* v bytech */);


        $form->addHidden('categoryZbozi', 'Kategorie pro Zboží.cz')
            ->setAttribute('data-preload', "false")
            ->setAttribute('data-suggest', "true")
            ->setAttribute('data-minlen', "3")
            ->setAttribute('class', "form-control autocomplete-input")
            ->setAttribute('data-toggle', 'completer')
            ->setAttribute('title', 'Kategorie pro Zboží.cz')
            ->setAttribute('autocomplete', 'true');

        $form->addHidden('categoryHeureka', 'Kategorie pro Heureku')
            ->setAttribute('data-preload', "false")
            ->setAttribute('data-suggest', "true")
            ->setAttribute('data-minlen', "3")
            ->setAttribute('class', "form-control autocomplete-input")
            ->setAttribute('data-toggle', 'completer')
            ->setAttribute('title', 'Kategorie pro Heureku')
            ->setAttribute('autocomplete', 'true');

        $form->addHidden('categoryGoogleMerchants', 'Kategorie pro Google Merchants')
            ->setAttribute('data-preload', "false")
            ->setAttribute('data-suggest', "true")
            ->setAttribute('data-minlen', "3")
            ->setAttribute('class', "form-control autocomplete-input")
            ->setAttribute('data-toggle', 'completer')
            ->setAttribute('title', 'Kategorie pro Google Merchants')
            ->setAttribute('autocomplete', 'true');

        $form->setMessages(['Podařilo se uložit kategorii produktů', 'success'],
            ['Nepodařilo se uložit kategorii produktů!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'categoryFormSuccess'];
        return $form;
    }

    public function categoryFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();

        if (isset($values['categoryZbozi']) && is_numeric($values['categoryZbozi'])) {
            $zbozi = $this->facade->gEMZboziCategory()->find($values['categoryZbozi']);
            if ($zbozi) {
                $values['categoryZbozi'] = $zbozi->name;
            } else {
                $values['categoryZbozi'] = '';
            }
        }
        if (isset($values['categoryHeureka']) && is_numeric($values['categoryHeureka'])) {
            $heureka = $this->facade->gEMHeurekaCategory()->find($values['categoryHeureka']);
            if ($heureka) {
                $values['categoryHeureka'] = $heureka->name;
            } else {
                $values['categoryHeureka'] = '';
            }
        }
        if (isset($values['categoryGoogleMerchants']) && is_numeric($values['categoryGoogleMerchants'])) {
            $google = $this->facade->gEMGoogleMerchantCategory()->find($values['categoryGoogleMerchants']);
            if ($google) {
                $values['categoryGoogleMerchants'] = $google->name;
            } else {
                $values['categoryGoogleMerchants'] = '';
            }
        }

        // ukládám formulář  pomocí automatického save
        $category = $this->formGenerator->processForm($form, $values, true);

        $this->facade->setMainCategory($category);

        // save group filters
        /*$this->facade->saveProductGroupFilters($values2);

        // save filters
        $this->facade->saveFiltersProduct($values2);

        // save filters group for hide
        $this->facade->saveFiltersGroupForHide($values2, $category);*/

        if ($values->categoryImg->name != null) {
            if ($category->image) {
                if (file_exists($category->image)) {
                    unlink($category->image);
                }
            }
            $mili = str_replace(".", "", microtime(true));
            $type = substr($values->categoryImg->name, strrpos($values->categoryImg->name, '.'));
            $tmp = 'category-img/' . $category->id . '_' . $mili . $type;
            $values->categoryImg->move($tmp);
            $this->facade->saveImage($tmp, $category);
            $image = Image::fromFile($tmp);
            $image->resize(75, null); // šířka 75, výška se dopočítá
            $image->save($tmp);

            $this->facade->saveImage($tmp, $category);
        }

        // clean cache
        for($i = 0; $i < 10; $i++) {
            $this->storage->clean([
                Cache::TAGS => ["category/$category->id", "categories", "categoryUrl"],
            ]);
        }

        if (isset($values2[ 'addGroup' ])) {
            $active = isset($values2[ 'groupActiveNew' ]) ? 1 : 0;
            $this->facade->addGroup($values2[ 'groupNameNew' ], $values2[ 'groupOrderNew' ], $active, $category->id);
            $this->flashMessage('Skupinu se podařilo přidat.', 'success');

            if ($this->isAjax()) {
                $this->redrawControl('category-filters');
            } else {
                $this->redirect('this');
            }
            return;
        }

        if (isset($values2[ 'addFilter' ])) {
            $this->facade->addFilterToGroup($values2);
            $this->flashMessage('Filtr se podařilo přidat.', 'success');
            if ($this->isAjax()) {
                $this->redrawControl('category-filters');
            } else {
                $this->redirect('this');
            }
            return;
        }


        // Uložit a zpět
        if (isset($values2[ 'sendBack' ])) {
            $this->redirect(':ProductCategory:default');
            return;
        }
        // Uložit
        if (isset($values2[ 'send' ])) {
            $this->redirect(':ProductCategory:edit', ['id' => $category->id]);
            return;
        }

        // Uložit a nový
        if (isset($values2[ 'sendNew' ])) {
            $this->redirect(':ProductCategory:edit');
            return;
        }
    }

    public function handleGetCategoryZbozi($term)
    {
        $result = $this->facade->getAutocompleteCategoryZbozi($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    public function handleGetCategoryHeureka($term)
    {
        $result = $this->facade->getAutocompleteCategoryHeureka($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    public function handleGetCategoryGoogleMerchants($term)
    {
        $result = $this->facade->getAutocompleteCategoryGoogleMerchants($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    public function handleGetCategoryMall($term)
    {
        $result = $this->facade->getAutocompleteCategoryMall($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    public function handleRemoveProductGroupFilter($idGroup)
    {
        $res = $this->facade->removeGroupFilters($idGroup);
        if ($res) {
            $this->flashMessage('Podařilo se smazat skupinu filtrů.');
        } else {
            $this->flashMessage('Nepodařilo se smazat skupinu filtrů. Zkontrolujte, zda skupina neobsahuje nějaké filtry.',
                'warning');
        }

        if ($this->isAjax()) {
            $this->redrawControl('category-filters');
        } else {
            $this->redirect('this');
        }
    }

    public function handleRemoveProductFilter($idFilter)
    {
        $res = $this->facade->removeFilterProducts($idFilter);
        if ($res) {
            $this->flashMessage('Podařilo se smazat filtr.');
        } else {
            $this->flashMessage('Nepodařilo se smazat filtr.', 'warning');
        }

        if ($this->isAjax()) {
            $this->redrawControl('category-filters');
        } else {
            $this->redirect('this');
        }
    }

    public function handleDeleteImg($categoryId)
    {
        $res = $this->facade->deleteImage($categoryId);
        if ($res) {
            $this->flashMessage('Podařilo se smazat obrázek.');
        } else {
            $this->flashMessage('Nepodařilo se smazat obrázek.', 'warning');
        }

        if ($this->isAjax()) {
            $this->redrawControl('category-img');
        } else {
            $this->redirect('this');
        }
    }

}
