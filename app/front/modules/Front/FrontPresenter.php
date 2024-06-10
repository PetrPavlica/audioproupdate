<?php

namespace App\Presenters;

use Intra\Model\Database\Entity\Product;
use Intra\Model\Facade\ProductFacade;
use Nette;
use Intra\Model\Facade\BalikobotShopFacade;
use Nette\Application\UI\Form;

class FrontPresenter extends BaseFrontPresenter
{

    /** @var BalikobotShopFacade @inject */
    public $balikobotShopFacade;

    /** @var ProductFacade @inject */
    public $productFac;

    public function renderDefault()
    {
        $qb = $this->productFac->getEm()->createQueryBuilder()
            ->select('p')
            ->from($this->productFac->entity(), 'p')
            ->where('p.active = 1 and p.onFront = 1 and p.saleTerminated = 0')
            ->setMaxResults($this->isMobile ? 10 : 4)
            ->orderBy('p.orderOnFront', 'ASC');
        $this->template->onFrontProducts = $qb->getQuery()->getResult();

        if (!$this->isMobile) {
            $qb = $this->productFac->getEm()->createQueryBuilder()
                ->select('p')
                ->from($this->productFac->entity(), 'p')
                ->where('p.active = 1 and p.isRecomanded = 1 and p.saleTerminated = 0')
                ->setMaxResults(30)
                ->orderBy('p.orderIdRecomanded', 'ASC');

            $this->template->onFrontRecomandedProducts = $qb->getQuery()->getResult();

            $qb = $this->productFac->getEm()->createQueryBuilder()
                ->select('p')
                ->from($this->productFac->entity(), 'p')
                ->where('p.active = 1 and p.saleTerminated = 0')
                ->setMaxResults(30)
                ->orderBy('p.id', 'DESC');

            $this->template->onFrontMostSold = $qb->getQuery()->getResult();
        }

        $this->template->onFrontSpecialOffer = $offer = $this->searchFac->getActualRandomSpecialOffer($this->locale);
        if (isset($offer)) {
            $this->template->diffSpecialOffer = $offer->timeTo->diff(new \DateTime());
        }
        $this->template->banners = $this->searchFac->getBanners($this->locale, $this->isMobile);

        $productsType = Product::HOME;
        if (isset($this->sess->productsType)) {
            $productsType = $this->sess->productsType;
        }

        $this->template->productsType = $productsType;

        $valuesFilters = null;

        if (isset($this->sess->filtersVal)) {
            $valuesFilters = $this->sess->filtersVal;
        }

        $this->template->fVal = $valuesFilters;

        $ids = $this->searchFac->getProductsListCount($productsType, $valuesFilters);

        $sortItems = isset($this->sess->sortItems) ? $this->sess->sortItems : 'name';

        $orderBy = ['name' => 'ASC'];
        if ($sortItems == 'cheap') {
            $orderBy = ['selingPrice' => 'ASC'];
        } elseif ($sortItems == 'expensive') {
            $orderBy = ['selingPrice' => 'DESC'];
        }

        $this->template->products = $this->productFac->get()->findBy(['id' => $ids], $orderBy);

        $this->template->sortItems = $sortItems;

        /** @var array GARemarketing - kódy pro remarketing Google */
        $this->template->GARemarketing = [
            'page' => 'home',
        ];
        $this->template->homepage = true;
    }

    public function renderUnsubscribeNewsletter($email)
    {
        $res = $this->facade->unsubscribeEmail($email);
        $this->template->res = $res;
        $this->template->email = $email;
    }

    public function renderSitemap()
    {
        $this->template->webMenus = $this->facade->gEMWebMenu()->findBy(['visible' => '1']);
        $this->template->categories = $this->facade->gEMProductCategory()->findBy(['active' => '1']);
        $this->template->products = $this->facade->gEMProduct()->findBy(['active' => '1']);
    }

    public function renderTracking($hash)
    {
        $id = intval(str_replace('order_id: ', '', base64_decode($hash)));
        if ($id) {
            $this->template->packages = $packages = $this->facade->gEMBalikobotPackage()->findBy(['orders' => $id]);
            $this->template->orderId = $id;
            $this->template->tracks = [];
            foreach ($packages as $package) {
                $trackArr = $this->balikobotShopFacade->track($package);
                if ($trackArr) {
                    $traceNew = [];
                    foreach($trackArr as $t) {
                        $traceNew[] = [
                            'date' => date("j.n.Y H:i", strtotime(mb_substr($t, 0, 17))),
                            'text' => trim(mb_substr($t, 19, mb_strlen($t) - 19))
                        ];
                    }
                    $this->template->tracks[$package->id] = $traceNew;
                }
            }
        }
    }

    protected function createComponentAddToBasketForm()
    {
        return new Nette\Application\UI\Multiplier(function () {
            $form = new Nette\Application\UI\Form();
            $form->setTranslator($this->translator);

            $form->addHidden("productId");
            $form->addHidden('count');
            $form->addSubmit("addToBasket");

            $form->onSuccess[] = [$this, 'addToBasketSucc'];
            return $form;
        });
    }

    public function addToBasketSucc(Nette\Application\UI\Form $form, $values)
    {
        $this->handleAddToCart($values['productId']);
        $this->redirect('this');
    }

    public function createComponentFilterForm()
    {
        $form = new Form;

        $form->onSuccess[] = function(Form $form, $values) {
            $values2 = $this->request->getPost();

            $this->sess->filtersVal = $values2;

            if ($this->isAjax()) {
                $this->redrawControl('products');
                $this->redrawControl('products-filter');
            } else {
                $this->redirect('Front:');
            }
        };

        return $form;
    }

    public function handleSortItems($sort)
    {
        $this->sess->sortItems = $sort;

        if ($this->isAjax()) {
            $this->redrawControl('products');
            $this->redrawControl('products-filter');
        } else {
            $this->redirect('Front:');
        }
    }
}
