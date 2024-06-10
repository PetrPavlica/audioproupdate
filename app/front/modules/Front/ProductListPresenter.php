<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Intra\Model\Facade\ProductFacade;

class ProductListPresenter extends BaseFrontPresenter
{

    /** @var ProductFacade @inject */
    public $productFac;

    /** @var string @persistent */
    public $searchTerm = '';

    /** @var integer @persistent */
    public $page;

    public function renderList($id = '0', $slug = null, $page = '1', $searchTerm = '', $more = '15')
    {
        $id = intval($id);
        $this->page = intval($page) > 0 ? intval($page) : 1;
        $more = intval($more) > 0 ? intval($more) : 15;
        $category = $this->facade->gEMProductCategory()->findOneBy(['active' => 1, 'id' => $id]);

        if ($category) {
            $this->template->category = $category;
            $this->template->childsCategory = $this->facade->gEMProductCategory()->findBy([
                'parentCategory' => $category->id,
                'active' => 1
            ], ['orderCategory' => 'ASC']);
        } else {
            $category = 0;
        }

        $valuesFilters = null;
        $limit = $limitProducts = 15;

        if (!isset($this->sess->currentSearchTerm)) {
            $this->sess->currentSearchTerm = $searchTerm;
        }

        // Pokud jsou vyplněné filtry ve stejné kategorii, jako se nyní nacházím, tak je zachovám
        if (isset($this->sess->filtersVal) && isset($this->sess->filtersVal[ 'category' ]) && $this->sess->filtersVal[ 'category' ] == $id && $this->sess->currentSearchTerm == $searchTerm) {
            $valuesFilters = $this->sess->filtersVal;
            if (isset($valuesFilters[ 'limit' ])) {
                $limit = $valuesFilters[ 'limit' ];
            }
        }
        if ($more) {
            $limitProducts = $more;
        }

        if ($this->sess->currentSearchTerm != $searchTerm) {
            $this->sess->currentSearchTerm = $searchTerm;
        }

        $ids = $this->searchFac->getProductsListCount($category, $valuesFilters, $searchTerm,
            $this->sess->actualCurrency, $this->locale);

        if ($category) {
            $this->template->filters = $this->searchFac->getCategoryFilters($category, $ids, $this->locale);
        }

        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount(count($ids)); // celkový počet položek (např. článků)
        $paginator->setItemsPerPage($limitProducts); // počet položek na stránce
        $paginator->setPage($this->page);
        $this->template->paginator = $paginator;

        $this->template->products = $products = $this->searchFac->getProductsList($ids, $valuesFilters, $limitProducts,
            $paginator->getOffset());

        if ($searchTerm != "") {
            $this->template->accessoriesProducts = $this->searchFac->getRelevantProducts($products, $this->locale);
        }

        $this->template->ourTipProducts = $this->searchFac->getOurTipProducts($category, $this->locale);

        if ($category) {
            $this->template->topPrices = $topPrices = $this->searchFac->getTopPrices($category);
        } else {
            $idss = $this->searchFac->getProductsListCount($category, [], $searchTerm,
                $this->sess->actualCurrency, $this->locale);
            $this->template->topPrices = $topPrices = $this->searchFac->getTopPricesProducts($idss);
        }
        $arrImg = [];
        foreach ($products as $product) {
            if (!$product->mainImage) {
                $tmp = $this->facade->gEMProductImage()->findOneBy(['product' => $product->id], ['orderImg' => 'ASC']);
                if (count($tmp)) {
                    $arrImg[ $product->id ] = $tmp->path;
                }
            }
        }

        $this->template->producers = $this->searchFac->getProducersArr($category, $this->locale);
        $this->template->otherImages = $arrImg;
        /*$this[ 'filterForm' ]->components[ 'producer' ]->setItems($producersArr)
            ->setPrompt('-- vyberte výrobce');*/

        $this[ 'filterForm' ]->setDefaults([
            'category' => $id,
        ]);

        $this[ 'sortLimitForm' ]->setDefaults([
            'limit' => $limit,
        ]);

        barDump($this->template->products);
        barDump($valuesFilters);
        $this->template->idCat = $id;
        $this->template->slugCat = $slug;

        if (!isset($valuesFilters[ 'priceFrom' ]) || $valuesFilters[ 'priceFrom' ] == '') {
            $valuesFilters[ 'priceFrom' ] = $topPrices[ 'low' ];
        }
        if (!isset($valuesFilters[ 'priceTo' ]) || $valuesFilters[ 'priceTo' ] == '') {
            $valuesFilters[ 'priceTo' ] = $topPrices[ 'high' ];
        }

        $this->template->fVal = $valuesFilters;

        if ($searchTerm !== "") {
            $this->template->searchTerm = $searchTerm;
        }

        //Last visited:
        if (isset($this->sess->lastVisited) && count($this->sess->lastVisited)) {
            $this->template->lastVisitedProduct = $this->searchFac->findLastVisited($this->sess->lastVisited, $this->locale);
            $this->template->lastVisitedIdx = $this->sess->lastVisited;
        }

        // Last buy products
        $this->template->lastBuy = array_reverse($this->searchFac->getLastBuyProducts());

        $this->template->more = $more;
        $this->template->itemCount = $paginator->getItemCount();
        $this->template->limit = $limit;

        /** @var array GARemarketing - kódy pro remarketing Google */
        $this->template->GARemarketing = [
            'page' => 'category',
        ];

        if ($this->isAjax()) {
            $this->redrawControl('products-snipp');
        }
    }

    public function createComponentFilterForm()
    {
        $form = new Form;
        $form->addSubmit('sendFilter')
            ->setAttribute('class', 'ajax')
            ->setAttribute('style', "display: none");

        $form->addHidden('category');

        /*$form->addSelect('limit', '', [
            '15' => '15',
            '30' => '30',
            '50' => '50',
            '70' => '70',
            '90' => '90'
        ]);*/

        //$form->addSelect('producer');

        $form->onSuccess[] = [$this, 'filterSucc'];
        return $form;
    }

    public function filterSucc($form, $values)
    {
        $values2 = $this->request->getPost();
        if (isset($values2[ 'priceFrom' ])) {
            $values2[ 'priceFrom' ] = round($values2[ 'priceFrom' ] * $this->sess->actualCurrency[ 'exchangeRate' ]);
        }
        if (isset($values2[ 'priceTo' ])) {
            $values2[ 'priceTo' ] = round($values2[ 'priceTo' ] * $this->sess->actualCurrency[ 'exchangeRate' ]);
        }

        $this->sess->filtersVal = $values2;
        unset($this->sess->currentSearchTerm);

        if ($this->isAjax()) {
            $this->redrawControl('products-snipp');
        } else {
            $this->redirect('this');
        }
    }

    public function createComponentSortLimitForm()
    {
        $form = new Form;

        $form->addSelect('limit', '', [
            '15' => '15',
            '30' => '30',
            '50' => '50',
            '70' => '70',
            '90' => '90'
        ]);

        $form->onSuccess[] = [$this, 'sortLimitSucc'];

        return $form;
    }

    public function sortLimitSucc($form, $values)
    {
        $values2 = $this->request->getPost();
        $categoryId = $values2['category'];
        if (isset($this->sess->filtersVal['category']) && $this->sess->filtersVal['category'] != $categoryId) {
            unset($this->sess->filtersVal);
        }

        if (!isset($this->sess->filtersVal)) {
            $this->sess->filtersVal = [];
        }

        if (!isset($values2['new']) && isset($this->sess->filtersVal['new'])) {
            unset($this->sess->filtersVal['new']);
        }

        if (!isset($values2['action']) && isset($this->sess->filtersVal['action'])) {
            unset($this->sess->filtersVal['action']);
        }

        if (!isset($values2['onStock']) && isset($this->sess->filtersVal['onStock'])) {
            unset($this->sess->filtersVal['onStock']);
        }

        if (!isset($values2['unPack']) && isset($this->sess->filtersVal['unPack'])) {
            unset($this->sess->filtersVal['unPack']);
        }

        $this->sess->filtersVal = array_replace($this->sess->filtersVal, $values2);
    }

    public function handleSort($type, $categoryId)
    {
        if (isset($this->sess->filtersVal['category']) && $this->sess->filtersVal['category'] != $categoryId) {
            unset($this->sess->filtersVal);
        }
        if (!isset($this->sess->filtersVal[ 'sort' ])) {
            $this->sess->filtersVal[ 'sort' ] = $type;
        } else {
            if ($this->sess->filtersVal[ 'sort' ] == $type) {
                unset($this->sess->filtersVal[ 'sort' ]);
            } else {
                $this->sess->filtersVal[ 'sort' ] = $type;
            }
        }
        $this->sess->filtersVal[ 'category' ] = $categoryId;
        if ($this->isAjax()) {
            $this->redrawControl('products-snipp');
        } else {
            $this->redirect('this');
        }
    }

    public function handleResetFilters()
    {
        unset($this->sess->filtersVal);

        if ($this->isAjax()) {
            $this->redrawControl('products-snipp');
        } else {
            $this->redirect('this');
        }
    }

    public function handleRemoveFilter($type, $value, $filterId)
    {
        if ($type == 'slider' && isset($this->sess->filtersVal['filterSlider'][$filterId])) {
            if (is_array($this->sess->filtersVal['filterSlider'][$filterId])) {
                $k = array_search($value, $this->sess->filtersVal['filterSlider'][$filterId]);
                if ($k !== false) {
                    unset($this->sess->filtersVal['filterSlider'][$filterId][$k]);
                }
            } else {
                unset($this->sess->filtersVal['filterSlider'][$filterId]);
            }
        } elseif ($type == 'check' && isset($this->sess->filtersVal['filterCheck'][$filterId])) {
            /*$key = array_search($value, $this->sess->filtersVal['filterCheck'][$filterId]);
            if ($key !== false) {*/
                unset($this->sess->filtersVal['filterCheck'][$filterId]);
            //}
        } elseif ($type == 'producer' && isset($this->sess->filtersVal['producerCheck'][$filterId])) {
            unset($this->sess->filtersVal['producerCheck'][$filterId]);
        }

        if ($this->isAjax()) {
            $this->redrawControl('products-snipp');
        } else {
            $this->redirect('this');
        }
    }
}
