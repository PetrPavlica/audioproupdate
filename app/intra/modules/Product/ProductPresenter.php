<?php

namespace App\Presenters;

use Intra\Model\Database\Entity\Product;
use Intra\Model\Database\Entity\ProductArticle;
use Intra\Model\Database\Entity\ProductGifts;
use Intra\Model\Database\Entity\ProductPackage;
use Intra\Model\Database\Entity\ProductPackageItems;
use Intra\Model\Database\Entity\ProductSetItems;
use Intra\Model\Utils\ProductHelper\ProductHelper;
use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Utils\Image;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\ProductFacade;
use Intra\Model\Facade\ProductCategoryFacade;
use Intra\Model\Facade\ProductActionFacade;
use Intra\Components\MultiUploader\MultiUploaderControl;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;
use Intra\Model\Database\Entity\ProductInFilter;

class ProductPresenter extends BaseIntraPresenter
{

    /** @var ProductFacade @inject */
    public $productFac;

    /** @var ProductCategoryFacade @inject */
    public $productCatFac;

    /** @var ProductActionFacade @inject */
    public $productActionFac;

    /** @var MultiUploaderControl @inject */
    public $multiuploader;

    /** @var \Intra\Model\Utils\ImageManager\ImagesEditor @inject */
    public $imgEditor;

    /** @var IStorage @inject */
    public $storage;

    /** @var ProductHelper @inject */
    public $productHelper;

    /**
     * ACL name='Správa druhů produktů'
     */
    public function startup()
    {
        parent::startup();

        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Přidávání/edit produktů'
     */
    public function renderEdit($id)
    {
        //unset($_SESSION['_tracy']);
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

        if ($this->isAjax()) {  // Oprava chyby v nette 2.4 se snippetem includovaným v části formu
            $template = $this->getTemplate();
            $template->getLatte()->addProvider('formsStack', [$this[ 'form' ]]);
        }

        if ($id) {
            $product = $this->productFac->get()->find($id);
            if (!$product) {
                $this->flashMessage('Nebyl nalezen požadovaný produkt.', 'warning');
                $this->redirect('Product:default');
            }
            $this->template->product = $product;
            $arr = $product->toArray();
            unset($arr[ 'filter' ]);
            foreach ($product->filter as $item) {
                $arr[ 'filter' ][] = $item->filter->id;
            }
            unset($arr['categories']);

            $this[ 'form' ]->setDefaults($arr);
            /*$this[ 'form' ]->setAutocmp('newichProductRef',
                $product->newichProductRef ? $product->newichProductRef->name : "");
            $this[ 'form' ]->setAutocmp('betterProduct', $product->betterProduct ? $product->betterProduct->name : "");*/

            $this->template->productEshopCategory = $product->categories;

            $this->template->operations = $this->productFac->getProductOperation()->findBy(['product' => $id],
                ['foundedDate' => 'DESC']);
            /*$this->template->parameters = $this->productFac->getProductParameters()->findBy(['product' => $id],
                ['orderParam' => 'ASC']);*/
            $this->template->actions = $this->productFac->gEMProductAction()->findBy([
                'product' => $id,
                'isTypeOfPrice' => 0,
                'special' => 0
            ], ['id' => 'DESC']);
            $this->template->prices = $this->productFac->gEMProductAction()->findBy([
                'product' => $id,
                'isTypeOfPrice' => 1,
                'special' => 0
            ], ['id' => 'DESC']);

            $this->template->specialActions = $this->productFac->gEMProductAction()->findBy([
                'product' => $id,
                'isTypeOfPrice' => 0,
                'special' => 1
            ], ['id' => 'DESC']);

            /*$arr = [];
            $filters = $this->productFac->getGroupProductFilter()->findBy([
                'active' => '1'
            ], ['orderState' => 'ASC']);
            foreach ($filters as $f) {
                $arr[ $f->id ] = $f->toArray();
                $arr[ $f->id ][ 'filters' ] = [];

                $filter = $this->productFac->getProductFilter()->findBy(['filterGroup' => $f->id],
                    ['orderState' => 'ASC']);
                foreach ($filter as $fil) {
                    $arr[ $f->id ][ 'filters' ][ $fil->id ] = $fil->toArray();
                    $res = $this->productFac->getProductInFilter()->findOneBy([
                        'product' => $product->id,
                        'filter' => $fil->id
                    ]);
                    if (count($res)) {
                        $arr[ $f->id ][ 'filters' ][ $fil->id ][ 'val' ] = $res->value;
                        $arr[ $f->id ][ 'filters' ][ $fil->id ][ 'valMax' ] = $res->valueMax;
                        $arr[ $f->id ][ 'filters' ][ $fil->id ][ 'check' ] = 1;
                    }
                }
            }
            $this->template->filters = $arr;*/
        }
    }

    public function renderEditAction($id, $productId)
    {
        if (!$productId) {
            $this->flashMessage('Nepodařilo se vybrat produkt pro přidání akce!', 'error');
            $this->redirect('Product:default');
        }
        $this[ 'editActionForm' ]->setDefaults(['product' => $productId]);
        $this->template->product = $product = $this->productFac->get()->find($productId);

        $this[ 'editActionForm' ]->setDefaults([
            'lastPrice' => $product->selingPrice,
            'feedShow' => true
        ]);
        $this[ 'editActionForm' ]->components[ 'dateForm' ]->setRequired('Toto pole je povinné');
        $this[ 'editActionForm' ]->components[ 'dateTo' ]->setRequired('Toto pole je povinné');
        $this[ 'editActionForm' ]->components[ 'percent' ]->setRequired('Toto pole je povinné');

        if ($id && $id !== 0) {
            $this->template->isEdit = true;
            $action = $this->productActionFac->get()->find($id);
            $this->template->active = $action->active;
            $this[ 'editActionForm' ]->setDefaults($action->toArray());
        } else {
            $this->template->active = false;
        }
    }

    public function renderEditPrice($id, $productId)
    {
        if (!$productId) {
            $this->flashMessage('Nepodařilo se vybrat produkt pro přidání ceny!', 'error');
            $this->redirect('Product:default');
        }
        $this[ 'editActionForm' ]->setDefaults(['product' => $productId]);
        $this->template->product = $product = $this->productFac->get()->find($productId);

        $this[ 'editActionForm' ]->setDefaults([
            'lastPrice' => $product->selingPrice
        ]);

        if ($id && $id !== 0) {
            $this->template->isEdit = true;
            $action = $this->productActionFac->get()->find($id);
            $this[ 'editActionForm' ]->setDefaults($action->toArray());
        }
    }

    public function renderEditSpecial($id, $productId)
    {
        if (!$productId) {
            $this->flashMessage('Nepodařilo se vybrat produkt pro přidání speciální akce!', 'error');
            $this->redirect('Product:default');
        }
        $this[ 'editActionForm' ]->setDefaults(['product' => $productId]);
        $this->template->product = $product = $this->productFac->get()->find($productId);

        $this[ 'editActionForm' ]->components[ 'dateForm' ]->setRequired('Toto pole je povinné');
        $this[ 'editActionForm' ]->components[ 'dateTo' ]->setRequired('Toto pole je povinné');
        $this[ 'editActionForm' ]->components[ 'percent' ]->setRequired('Toto pole je povinné');

        $this[ 'editActionForm' ]->setDefaults([
            'lastPrice' => $product->selingPrice
        ]);

        if ($id && $id !== 0) {
            $this->template->isEdit = true;
            $action = $this->productActionFac->get()->find($id);
            $this->template->feedShow = $action->feedShow;
            $this[ 'editActionForm' ]->setDefaults($action->toArray());
        } else {
            $this->template->feedShow = false;
        }
    }

    /**
     * ACL name='Přidávání/edit článků produktů'
     */
    public function renderEditArticle($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_METHOD);

        if ($id) {
            $article = $this->productFac->gEMProductArticle()->find($id);
            if (!$article) {
                $this->flashMessage('Nebyl nalezen požadovaný článek.', 'warning');
                $this->redirect('Product:default');
            }
            $this->template->article = $article;
            $this->template->product = $article->product;

            $this[ 'articleForm' ]->setDefaults($article->toArray());
        } else {
            $params = $this->getParameters();
            if (!isset($params['idProduct']) || !is_numeric($params['idProduct'])) {
                $this->redirect('Product:');
            }
            $this[ 'articleForm' ]->setDefaults([
                'product' => $params['idProduct']
            ]);
            $this->template->product = $this->productFac->get()->find($params['idProduct']);
        }
    }

    /**
     * ACL name='Tabulka s přehledem druhů produktů'
     */
    public function createComponentTable()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->productFac->entity(), $this->user, get_class(),
            __FUNCTION__);

        /*$currencyCZK = $this->productFac->gEMCurrency()->findOneBy(['code' => 'CZK']);
        $currencyEur = $this->productFac->gEMCurrency()->findOneBy(['code' => 'EUR']);
        $currencyCZKArr = $currencyCZK->toArray();
        $currencyEurArr = $currencyEur->toArray();

        $column = $grid->addColumnNumber('priceEur', 'Cena v €');
        if ($column) {
            $column->setRenderer(function($item) use ($currencyEurArr) {
                $this->productHelper->setProduct($item, $currencyEurArr);
                return number_format($this->productHelper->getPrice(), 2, ',', '.');
            });
        }

        $column = $grid->addColumnNumber('priceAction', 'Akční cena');
        if ($column) {
            $column->setRenderer(function($item) use ($currencyCZKArr) {
                $this->productHelper->setProduct($item, $currencyCZKArr);
                if ($this->productHelper->actionExists()) {
                    return number_format($this->productHelper->getPrice(), 2, ',', '.');
                } else {
                    return 0;
                }
            });
        }

        $column = $grid->addColumnNumber('priceSpecial', 'Speciální cena');
        if ($column) {
            $column->setRenderer(function($item) use ($currencyCZKArr) {
                $this->productHelper->setProduct($item, $currencyCZKArr);
                return number_format($this->productHelper->getSpecialPrice(), 2, ',', '.');
            });
        }*/

        $action = $grid->addAction('edit', '', 'Product:edit');
        if ($action) {
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        }
        /*$action2 = $grid->addAction('rating', '', 'ProductRating:default');
        if ($action2) {
            $action2->setIcon('star-half-o')
                ->setTitle('Hodnocení')
                ->setClass('btn btn-xs btn-default');
        }

        $action2 = $grid->addAction('discussion', '', 'ProductDiscussion:default');
        if ($action2) {
            $action2->setIcon('comments')
                ->setTitle('Diskuze')
                ->setClass('btn btn-xs btn-default');
        }*/

        $this->doctrineGrid->addButonDelete();

        $grid->addGroupAction('Udělat kopii')->onSelect[] = [$this, 'makeCopy'];

        return $this->tblFactory->create($grid);
    }

    /**
     * Make kopies of selected products in grid
     * @param array $ids of products
     */
    public function makeCopy($ids)
    {
        $this->productFac->createCopiesProducts($ids);
        $this->flashMessage('Kopírování bylo ukončeno. Kopie produtků naleznete v tabulce s prefixem "KOPIE".',
            'success');
        $this->redirect('this');
    }

    /**
     * ACL name='Tabulka s přehledem balíčků produktu'
     */
    public function createComponentTablePackages()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation(ProductPackageItems::class, $this->user, get_class(),
            __FUNCTION__);

        $package = $grid->getFilter('package');
        if ($package) {
            $package->setCondition(function($qb, $value) {
                $qb->leftJoin(ProductPackage::class, 'prp', 'WITH', 'a.package = prp.id');
                $qb->leftJoin(Product::class, 'pr', 'WITH', 'pr.id = prp.product');
                $qb->andWhere('pr.name LIKE :param');
                $qb->setParameters(['param' => '%'.$value.'%']);
            });
        }

        $action = $grid->addAction('edit', '', 'Product:edit', ['id' => 'package.product.id']);
        if ($action) {
            $action->setTitle('Upravit')
                ->setIcon('pencil')
                ->setClass('btn btn-xs btn-default');
        }

        //$this->doctrineGrid->addButonDelete();

        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Tabulka s přehledem setů produktu'
     */
    public function createComponentTableSets()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation(ProductSetItems::class, $this->user, get_class(),
            __FUNCTION__);

        $this->doctrineGrid->addButonDelete();

        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Tabulka s přehledem darků produktu'
     */
    public function createComponentTableGifts()
    {
        $grid = $this->doctrineGrid->generateGridByAnnotation(ProductGifts::class, $this->user, get_class(),
            __FUNCTION__);

        $this->doctrineGrid->addButonDelete();

        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit druhů produktů'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation($this->productFac->entity(), $this->user, $this,
            __FUNCTION__);
        $form->setMessages(['Podařilo se uložit produkt', 'success'], ['Nepodařilo se uložit produkt!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'productFormSuccess'];
        $form->onError[] = [$this, 'productFormError'];

        // Set custom source for product category
        //$form->components[ 'category' ]->setItems($this->productCatFac->getSelectBoxCategoryAll());

        $form->addSelect('categoriesEshop', 'Kategorie', $this->productCatFac->getSelectBoxCategoryAll())
            ->setAttribute('class', 'form-control selectpicker')
            ->setAttribute('data-live-search', 'true');

        $form->addHidden('allProducts', 'Produkt jako příslušenství')
            ->setAttribute('data-preload', "false")
            ->setAttribute('data-suggest', "true")
            ->setAttribute('data-minlen', "3")
            ->setAttribute('class', "form-control autocomplete-input")
            ->setAttribute('data-toggle', 'completerr')
            ->setAttribute('autocomplete', 'true');

        $form->addHidden('allProducts2', 'Produkt jako položka sady')
            ->setAttribute('data-preload', "false")
            ->setAttribute('data-suggest', "true")
            ->setAttribute('data-minlen', "3")
            ->setAttribute('class', "form-control autocomplete-input")
            ->setAttribute('data-toggle', 'completerr')
            ->setAttribute('autocomplete', 'true');

        /*$form->addHidden('allProducts3', 'Produkt jako položka balíčku')
            ->setAttribute('data-preload', "false")
            ->setAttribute('data-suggest', "true")
            ->setAttribute('data-minlen', "3")
            ->setAttribute('class', "form-control autocomplete-input")
            ->setAttribute('data-toggle', 'completerr')
            ->setAttribute('autocomplete', 'true');*/

        $form->addHidden('allProducts4', 'Produkt jako barevná varianta')
            ->setAttribute('data-preload', "false")
            ->setAttribute('data-suggest', "true")
            ->setAttribute('data-minlen', "3")
            ->setAttribute('class', "form-control autocomplete-input")
            ->setAttribute('data-toggle', 'completerr')
            ->setAttribute('autocomplete', 'true');

        $form->addHidden('allProducts5', 'Produkt jako alternativní zboží')
            ->setAttribute('data-preload', "false")
            ->setAttribute('data-suggest', "true")
            ->setAttribute('data-minlen', "3")
            ->setAttribute('class', "form-control autocomplete-input")
            ->setAttribute('data-toggle', 'completerr')
            ->setAttribute('autocomplete', 'true');

        $form->addHidden('allProducts6', 'Produkt jako dárek')
            ->setAttribute('data-preload', "false")
            ->setAttribute('data-suggest', "true")
            ->setAttribute('data-minlen', "3")
            ->setAttribute('class', "form-control autocomplete-input")
            ->setAttribute('data-toggle', 'completerr')
            ->setAttribute('autocomplete', 'true');

        $form->addText('colorVariant', '')->setAttribute('class', 'spectrum');
        $form->addText('colorTextVariant', '')->setAttribute('placeholder', 'Barva - text')->setAttribute('class', 'form-control');

        $form->addHidden('lastIdImg', 'a');

        $form->addTextAcl('countAdd', 'Počet kusů')
            ->setAttribute('class', 'form-control ')
            ->setAttribute('placeholder', 'Počet kusů')
            ->setRequired(false)
            ->addRule(UI\Form::INTEGER, 'Počet kusů musí být celé číslo!');

        $form->addTextAcl('priceAdd', 'Pořizovací cena/kus')
            ->setAttribute('class', 'form-control ')
            ->setAttribute('placeholder', 'Cena')
            ->setRequired(false)
            ->addRule(UI\Form::FLOAT, 'Pořizovací cena musí být číslo!');

        $form->addTextAcl('commentAdd', 'Poznámka')
            ->setAttribute('class', 'form-control ')
            ->setAttribute('placeholder', 'Poznámka');

        return $form;
    }

    public function productFormError(UI\Form $form)
    {
        if ($form->hasErrors()) {
            foreach($form->errors as $e) {
                $this->flashMessage($e, 'warning');
            }
        }
    }

    public function productFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        $id = $values[ 'id' ];

        //$values["betterProductAdvantages"] = nl2br($values["betterProductAdvantages"]);

        // ukládám formulář  pomocí automatického save
        $product = $this->formGenerator->processForm($form, $values, true);
        $this->productFac->generateSlug($product->id, $values);

        if (isset($values2[ 'addFoto' ])) {
            $lastImgId = $values->lastIdImg;
            if (isset($values2[ 'newImage' ]) && isset($values2[ 'pathToNewImg' ])) {
                $this->imgEditor->setInputDir($values2[ 'pathToNewImg' ]);

                foreach ($values2[ 'newImage' ] as $img) {
                    $type = substr($img, strrpos($img, '.'));
                    $folder = $tmp = 'productImages/' . $product->id . '/';
                    if (!file_exists($folder)) {
                        mkdir($folder, 0755, true);
                    }
                    $tmp = $lastImgId . '_' . str_replace(".", "", microtime(true));
                    if (file_exists($values2[ 'pathToNewImg' ] . $img)) {
                        /*$this->imgEditor->setOutputDir($folder);
                        $image = $this->imgEditor->loadImage($img);
                        $image->autoRotate();
                        $image->resize(500, 336, 'supplement');
                        $image->save('thumb_' . $tmp);
                        $tmpThumb = $folder . 'thumb_' . $tmp . '.' . $image->extension();*/

                        /*$image = $this->imgEditor->loadImage($img);
                        $image->autoRotate();
                        $image->resize(720, 0, 'supplement');
                        $image->save($tmp);*/

                        $tmp2 = $folder . $tmp . '.' . $type;

                        rename($values2[ 'pathToNewImg' ] . $img, $tmp2);

                        $this->productFac->addImage($tmp2, null, $product->id);
                        $lastImgId++;
                        //unlink($values2[ 'pathToNewImg' ] . $img);
                    }
                }
                $this->flashMessage('Obrázky se podařilo přidat.', 'success');
            } else {
                $this->flashMessage('Obrázky se nepodařilo přidat!', 'warning');
            }
            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('product-galery');
            } else {
                $this->redirect('this');
            }
            return;
        }

        if (isset($values2[ 'addCategory' ])) {
            $res = $this->productCatFac->addCategoryToProduct($id, $values[ 'categoriesEshop' ]);
            $this->storage->clean([
                Cache::TAGS => ["category/" . $values[ 'categoriesEshop' ]]
            ]);
            if (!$res) {
                $this->flashMessage('Kategorii se nepodařilo uložit - zkontrolujte, zda již není tato kategorie produktu přiřazena.', 'warning');
            } else {
                $this->flashMessage('Kategorie byla úspěšně přidána.', 'success');
            }

            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('eshop-category');
            } else {
                $this->redirect('this');
            }
            return;
        }

        if (isset($values2[ 'addAccessory' ])) {
            $res = $this->productFac->addAccessoryProduct($product->id, $values[ 'allProducts' ]);
            if (!$res) {
                $this->flashMessage('Příslušenství se nepodařilo uložit', 'warning');
            } else {
                $this->flashMessage('Příslušenství bylo úspěšně přidáno.', 'success');
            }

            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('eshop-accessories-products');
            } else {
                $this->redirect('this');
            }
            return;
        }

        if (isset($values2[ 'addAlternative' ])) {
            $res = $this->productFac->addAlternativeProduct($product->id, $values[ 'allProducts5' ]);
            if (!$res) {
                $this->flashMessage('Alternativní zboží se nepodařilo uložit', 'warning');
            } else {
                $this->flashMessage('Alternativní zboží bylo úspěšně přidáno.', 'success');
            }

            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('eshop-alternative-products');
            } else {
                $this->redirect('this');
            }
            return;
        }

        if (isset($values2['addGift'])) {
            $res = $this->productFac->addGift($product->id, $values['allProducts6']);
            if (!$res) {
                $this->flashMessage('Dárek se nepodařilo uložit', 'warning');
            } else {
                $this->flashMessage('Dárek byl úspěšně přidán.', 'success');
            }

            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('eshop-gifts');
            } else {
                $this->redirect('this');
            }
            return;
        }

        if (isset($values2[ 'addSetItem' ])) {

            $res = $this->productFac->addSetItem($product->id, $values[ 'allProducts2' ]);
            if (!$res) {
                $this->flashMessage('Položka k produktové sadě nebyla úspěšně přidána', 'warning');
            } else {
                $this->flashMessage('Položka k sadě byla úspěšně přidána.', 'success');
            }

            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('eshop-set-items');
            } else {
                $this->redirect('this');
            }
            return;
        }

        if (isset($values2[ 'addColorItem' ])) {
            $res = $this->productFac->addColorItem($product->id, $values['allProducts4'], $values['colorVariant'], $values['colorTextVariant']);
            if (!$res) {
                $this->flashMessage('Nepodařil ose přidat barevnou variantu.', 'warning');
            } else {
                $this->flashMessage('Podařilo se přidat barevnou variantu.', 'success');
            }

            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('eshop-color-items');
            } else {
                $this->redirect('this');
            }
            return;
        }

        if (isset($values2['addPackageItem'])) {
            $res = $this->productFac->addPackageItem($values2['product'], $values2['package']);
            if (!$res) {
                $this->flashMessage('Položka k produktovému balíčku nebyla úspěšně přidána', 'warning');
            } else {
                $this->flashMessage('Položka k produktovému balíčku byla úspěšně přidána.', 'success');
            }

            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('eshop-package-items');
            } else {
                $this->redirect('this');
            }
            return;
        }

        if (isset($values2[ "setItems" ])) {

            $i = 1;
            foreach ($values2[ "setItems" ][ "id" ] as $id) {
                $this->productFac->updateSetItem($id,
                    isset($values2[ "setItems" ][ "show" ][ $i ]) ? $values2[ "setItems" ][ "show" ][ $i ] : 0);
                $i++;
            }

        }

        if (isset($values2[ 'addStocks' ])) {
            $res = $this->productFac->addStockOperation($product->id, $values2[ 'countAdd' ], $values2[ 'priceAdd' ],
                $values2[ 'commentAdd' ], $this->user->id);
            if (!$res) {
                $this->flashMessage('Zboží na sklad se nepodařilo přidat!', 'warning');
            } else {
                $this->flashMessage('Podařilo se přidat zboží na sklad.', 'success');
            }

            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('eshop-stock-products');
            } else {
                $this->redirect('this');
            }
            return;
        }

        if (isset($values2[ 'addParameter' ])) {
            $this->productFac->addParameter($values2[ 'paramNewName' ], $values2[ 'paramNewValue' ],
                $values2[ 'paramNewOrder' ], $product->id);
            $this->flashMessage('Parametr se podařilo přidat.', 'success');

            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('parameter-product');
            } else {
                $this->redirect('this');
            }
            return;
        }

        if (isset($values2['packageItem'])) {
            foreach($values2['packageItem'] as $k => $v) {
                $this->productFac->updatePackageItem($k, $v);
            }
        }

        if (isset($values2['giftRank'])) {
            foreach ($values2['giftRank'] as $k => $v) {
                $this->productFac->updateGiftRank($k, $v);
            }
        }

        // save images
        $this->productFac->saveImgDetails($values2, $product);

        // save parameters
        $this->productFac->saveProductParametersDetails($values2);

        //save filters
        $this->productFac->saveFilters($values2, $product);

        // clean cache
        for($i = 0; $i < 10; $i++) {
            $this->cache->clean([
                Cache::TAGS => ["product-" . $product->id]
            ]);
        }

        // Uložit a zpět
        if (isset($values2[ 'sendBack' ])) {
            $this->redirect(':Product:default');
            return;
        }
        // Uložit
        if (isset($values2[ 'send' ])) {
            $this->redirect(':Product:edit', ['id' => $product->id]);
            return;
        }

        // Uložit a nový
        if (isset($values2[ 'sendNew' ])) {
            $this->redirect(':Product:edit');
            return;
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit sazeb DPH'
     */
    public function createComponentEditActionForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation($this->productActionFac->entity(), $this->user, $this,
            __FUNCTION__);
        $form->setMessages(['Podařilo se uložit akci/cenu.', 'success'],
            ['Nepodařilo se uložit akci/cenu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'actionFromSuccess'];
        return $form;
    }

    public function actionFromSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        $action = $this->formGenerator->processForm($form, $values, true);

        if ($action->isTypeOfPrice && isset($values2[ 'active' ])) { // deaktivuji všechny ostatní
            $prices = $this->productFac->gEMProductAction()->findBy([
                'product' => $action->product->id,
                'active' => 1,
                'isTypeOfPrice' => 1,
                'currency' => $action->currency->id
            ]);
            foreach ($prices as $p) {
                $p->setActive(false);
            }
        }
        if (isset($values2[ 'active' ])) {
            $action->setActive(true);
        }
        $this->perItemFac->save();

        // clean cache
        for($i = 0; $i < 10; $i++) {
            $this->storage->clean([
                Cache::TAGS => ["product-" . $action->product->id]
            ]);
        }
        if ($action) {
            $this->redirect('Product:edit', ['id' => $action->product->id]);
        }
    }

    protected function createComponentMultiUploader()
    {
        return $this->multiuploader;
    }

    public function handleGetProducts($term)
    {
        $result = $this->productFac->getDataAutocompleteProducts($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }


    public function handleDeleteImg($imgId, $productId)
    {
        $this->productFac->deleteImg($imgId, $productId);
        $this->flashMessage('Obrázek byl odebrán');
        if ($this->isAjax()) {
            $this->redrawControl('product');
            $this->redrawControl('product-galery');
        } else {
            $this->redirect('this');
        }
    }

    public function handleDeleteAccessoryProduct($idAccessory)
    {
        $this->productFac->deleteAccessoryProduct($idAccessory);
        $this->flashMessage('Příslušenství bylo odebráno.');
        if ($this->isAjax()) {
            $this->redrawControl('product');
            $this->redrawControl('eshop-accessories-products');
        } else {
            $this->redirect('this');
        }
    }

    public function handleDeleteAlterProduct($idAlter)
    {
        $this->productFac->deleteAlternativeProduct($idAlter);
        $this->flashMessage('Alternativní zboží bylo odebráno.');
        if ($this->isAjax()) {
            $this->redrawControl('product');
            $this->redrawControl('eshop-alternative-products');
        } else {
            $this->redirect('this');
        }
    }

    public function handleDeleteGift($idGift)
    {
        $res = $this->productFac->deleteGift($idGift);
        if ($res) {
            $this->flashMessage('Dárek byl odebrán.');
        } else {
            $this->flashMessage('Dárek se nepodařilo odebrat.', 'warning');
        }
        if ($this->isAjax()) {
            $this->redrawControl('product');
            $this->redrawControl('eshop-gifts');
        } else {
            $this->redirect('this');
        }
    }

    public function handleDeleteProductSetItem($idSet)
    {
        if (isset($idSet) && is_numeric($idSet)) {
            $this->productFac->deleteSetItem($idSet);

            $this->flashMessage('Položka produktové sady byla úspěšně odebrána.');
            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('eshop-set-items');
            } else {
                $this->redirect('this');
            }
        }
    }

    public function handleDeleteProductColorItem($idColor)
    {
        if (isset($idColor) && is_numeric($idColor)) {
            $this->productFac->deleteColorItem($idColor);

            $this->flashMessage('Barevná varianta byla úspěšně odebrána.');
            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('eshop-color-items');
            } else {
                $this->redirect('this');
            }
        }
    }

    public function handleDeleteProductPackageItem($idPackage)
    {
        if (isset($idPackage) && is_numeric($idPackage)) {
            $res = $this->productFac->deletePackageItem($idPackage);

            if ($res) {
                $this->flashMessage('Položka balíčku byla úspěšně odebrána.');
            } else {
                $this->flashMessage('Položku balíčku se nepodařilo smazat.', 'warning');
            }
            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('eshop-package-items');
            } else {
                $this->redirect('this');
            }
        }
    }

    public function handleDeleteOperation($operId)
    {
        $res = $this->productFac->removeStockOperation($operId);
        if ($res) {
            $this->flashMessage('Operace byla odebrána');
        } else {
            $this->flashMessage('Operaci se nepodařilo odebrat', 'warning');
        }
        if ($this->isAjax()) {
            $this->redrawControl('product');
            $this->redrawControl('eshop-stock-products');
        } else {
            $this->redirect('this');
        }
    }

    public function handleDeleteParam($paramId)
    {
        $res = $this->productFac->removeProductParameter($paramId);
        if ($res) {
            $this->flashMessage('Parametr byl odebrán.');
        } else {
            $this->flashMessage('Parametr se nepodařilo odebrat', 'warning');
        }
        if ($this->isAjax()) {
            $this->redrawControl('product');
            $this->redrawControl('parameter-product');
        } else {
            $this->redirect('this');
        }
    }

    /**
     * Get parameters for autocomplete
     * @param type $term
     */
    public function handleGetParameters($term)
    {
        $result = $this->productFac->getDataAutocompleteParameters($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    public function handlePrepareParametersFromFilters($productId)
    {
        $res = $this->productFac->prepareParametersFromFilters($productId);
        if ($res) {
            $this->flashMessage('Podařilo se předvyplnit parametry dle filtrů', 'success');
        } else {
            $this->flashMessage('Nepodařilo se předvyplnit parametry dle filtrů - zkontrolujte, zda máte nějaké vyplněné.',
                'warning');
        }
        $this->redirect('this');
    }

    public function handleActivationAction($actionId)
    {
        $res = $this->productActionFac->activationAction($actionId);
        if ($res) {
            // clean cache
            for($i = 0; $i < 10; $i++) {
                $this->storage->clean([
                    Cache::TAGS => ["product-" . $res->id]
                ]);
            }
            $this->flashMessage('Podařilo aktivovat akci.', 'success');
        } else {
            $this->flashMessage('Nepodařilo se aktivovat tuto akci. Prosím zkontrolujte, zda akce může dnes běžet (časově od - do).',
                'warning');
        }

        $this->redirect('this');
    }

    public function handleStopAction($actionId)
    {
        $res = $this->productActionFac->stopAction($actionId);
        if ($res) {
            // clean cache
            for($i = 0; $i < 10; $i++) {
                $this->storage->clean([
                    Cache::TAGS => ["product-" . $res->id]
                ]);
            }
            $this->flashMessage('Podařilo deaktivovat akci.', 'success');
        } else {
            $this->flashMessage('Nepodařilo se deaktivovat tuto akci!', 'warning');
        }
        $this->redirect('this');
    }

    public function handleDeleteAction($actionId)
    {
        $res = $this->productActionFac->deleteAction($actionId);
        if ($res) {
            $this->flashMessage('Podařilo se záznam smazat.', 'success');
        } else {
            $this->flashMessage('Nepodařilo se záznam smazat. Prosím zkontrolujte, zda není právě aktivní.', 'warning');
        }
        $this->redirect('this');
    }

    public function handleDeleteCategory($idCategory) {
        $res = $this->productCatFac->deleteCategory($idCategory);
        $this->storage->clean([
            Cache::TAGS => ["category/" . $idCategory]
        ]);

        if (!$res) {
            $this->flashMessage('Kategorii se nepodařilo smazat!', 'error');
        } else {
            $this->flashMessage('Kategorie byla smazána', 'notice');
        }

        if ($this->isAjax()) {
            $this->redrawControl('product');
            $this->redrawControl('eshop-category');
        } else {
            $this->redirect('this');
        }
    }

    public function handleDeleteProductPackage($idPackage)
    {
        if (isset($idPackage) && is_numeric($idPackage)) {
            $this->productFac->deletePackage($idPackage);

            $this->flashMessage('Balíček byl úspěšně odebrán.');
            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('eshop-package-items');
            } else {
                $this->redirect('this');
            }
        }
    }

    public function handleAddPackage($product)
    {
        if (isset($product) && is_numeric($product)) {
            $this->productFac->addPackage($product);

            $this->flashMessage('Balíček byl úspěšně přidán.');
            if ($this->isAjax()) {
                $this->redrawControl('product');
                $this->redrawControl('eshop-package-items');
            } else {
                $this->redirect('this');
            }
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit sazeb DPH'
     */
    public function createComponentArticleForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(ProductArticle::class, $this->user, $this,
            __FUNCTION__);
        $form->setMessages(['Podařilo se uložit článek.', 'success'],
            ['Nepodařilo se uložit článek!', 'warning']);

        $form->addUploadAcl('imageUpload', 'Obrázek')
            ->setRequired(false)// nepovinný
            ->addRule(Form::IMAGE, 'Ikona musí být JPEG, PNG nebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální možná velikost ikonky je 3 Mb', 3 * 1024 * 1024/* v bytech */);

        $form->addUploadAcl('videoUpload', 'Video (.mp4)')
            ->setRequired(false)// nepovinný
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální možná velikost ikonky je 10 Mb', 20 * 1024 * 1024/* v bytech */);

        $form->isRedirect = false;
        $form->onError[] = function(UI\Form $form) {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'warning');
                }
            }
        };
        $form->onSuccess[] = [$this, 'articleFromSuccess'];
        return $form;
    }

    public function articleFromSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        $article = $this->formGenerator->processForm($form, $values, true);

        $articlesPath = 'product-articles/'.$article->id.'/';

        if (!is_dir($articlesPath)) {
            mkdir($articlesPath, 0755, true);
        }

        if ($values->imageUpload->name != null) {
            if ($article->image) {
                if (file_exists($article->image)) {
                    @unlink($article->image);
                }
            }
            $info = pathinfo($values->imageUpload->name);
            $tmp = $articlesPath.md5($values->imageUpload->name).'.'.$info['extension'];
            $values->imageUpload->move($tmp);
            $article->setImage($tmp);
        }

        if ($values->videoUpload->name != null) {
            if ($article->video) {
                if (file_exists($article->video)) {
                    @unlink($article->video);
                }
            }
            $info = pathinfo($values->videoUpload->name);
            $tmp = $articlesPath.md5($values->videoUpload->name).'.'.$info['extension'];
            $values->videoUpload->move($tmp);
            $article->setVideo($tmp);
        }

        $this->productFac->save();

        // Uložit a zpět
        if (isset($values2[ 'sendBack' ])) {
            $this->redirect('Product:edit', ['id' => $article->product->id]);
        }
        // Uložit
        if (isset($values2[ 'send' ])) {
            $this->redirect('Product:editArticle', ['id' => $article->id]);
        }

        // Uložit a nový
        if (isset($values2[ 'sendNew' ])) {
            $this->redirect('Product:editArticle', ['idProduct' => $article->product->id]);
        }
    }

    public function handleDeleteArticle($articleId)
    {
        $res = $this->productFac->removeArticle($articleId);
        if ($res) {
            $this->flashMessage('Článek byl odebrán');
        } else {
            $this->flashMessage('Článek se nepodařilo odebrat', 'warning');
        }
        if ($this->isAjax()) {
            $this->redrawControl('articles');
        } else {
            $this->redirect('this');
        }
    }

    public function handleDeleteArticleImg($articleId)
    {
        $this->productFac->deleteArticleImg($articleId);
        $this->flashMessage('Obrázek byl odebrán');
        if ($this->isAjax()) {
            $this->redrawControl('article-img');
        } else {
            $this->redirect('this');
        }
    }

    public function handleDeleteArticleVideo($articleId)
    {
        $this->productFac->deleteArticleVideo($articleId);
        $this->flashMessage('Video bylo odebráno');
        if ($this->isAjax()) {
            $this->redrawControl('video-img');
        } else {
            $this->redirect('this');
        }
    }
}
