<?php

namespace Intra\Model\Utils\ProductHelper;

use Intra\Model\Database\Entity\Product;
use Intra\Model\Facade\ProductFacade;
use Intra\Model\Utils\DPHCounter;
use Latte\Engine;
use Nette\Application\UI\Control;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Security\User;
use Nette\Utils\Strings;

class ProductHelper extends Control
{
    /** @var DPHCounter */
    protected $dphCounter;

    /** @var DPHCounter */
    protected $dphCounterLast;

    /** @var DPHCounter */
    protected $dphCounterSpecial;

    /** @var Product */
    protected $product;

    /** @var array */
    protected $currency;

    /** @var IStorage */
    protected $storage;

    /** @var ProductFacade */
    protected $productFacade;

    /** @var bool */
    protected $feedPrice;

    /** @var integer */
    protected $discountPercent;

    /** @var Cache */
    protected $cache;

    /** @var User */
    protected $user;

    /** @var array */
    protected $discount = null;

    /**
     * Construct
     */
    public function __construct(IStorage $storage, ProductFacade $productFacade, User $user)
    {
        parent::__construct();
        $this->storage = $storage;
        $this->cache = new Cache($this->storage);
        $this->productFacade = $productFacade;
        $this->user = $user;
    }

    /****************************/
    /*****  Setters        ******/
    /****************************/

    public function setProduct(Product $product, $currency, $feedPrice = false, $discountPercent = null, $customer = null)
    {
        $this->product = $product;
        $this->feedPrice = $feedPrice;
        $this->discountPercent = $discountPercent;
        $this->setCurrency($currency);

        if ($this->discount == null) {
            if ($customer && $customer->sales) {
                foreach($customer->sales as $s) {
                    $this->discount[$s->mark->id] = $s->value;
                }
            } else {
                if ($this->user->isLoggedIn()) {
                    $user = $this->productFacade->gEMCustomer()->find($this->user->id);
                    if ($user && $user->sales) {
                        foreach ($user->sales as $s) {
                            $this->discount[$s->mark->id] = $s->value;
                        }
                    }
                }
            }
        }

        $discount = isset($product->productMark->id) && isset($this->discount[$product->productMark->id]) ? $this->discount[$product->productMark->id] : 0;

        if ($this->discountPercent) {
            $this->dphCounter = new DPHCounter();
            $this->dphCounter->setPriceWithDPH($this->getSellingPrice() * (1 - $this->discountPercent / 100), $product->vat->value);

            $this->dphCounterLast = new DPHCounter();
            $this->dphCounterLast->setPriceWithDPH($this->getSellingPrice(), $product->vat->value);
        } else {
            $this->dphCounter = new DPHCounter();
            $this->dphCounter->setPriceWithDPH($this->getSellingPrice() * (1 - $discount / 100), $product->vat->value);

            $this->dphCounterLast = new DPHCounter();
            $this->dphCounterLast->setPriceWithDPH($this->getLastSellingPrice() * (1 - $discount / 100), $product->vat->value);
        }

        $this->dphCounterSpecial = new DPHCounter();
        $this->dphCounterSpecial->setPriceWithDPH($this->getSpecialSellingPrice(), $product->vat->value);
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }


    /****************************/
    /*****  Getters        ******/
    /****************************/

    public function getId()
    {
        return $this->product->id;
    }

    public function getSlug()
    {
        return Strings::webalize($this->product->name);
    }

    public function getDPHCounter()
    {
        return $this->dphCounter;
    }

    public function getSpecialDPHCounter()
    {
        return $this->dphCounterSpecial;
    }

    public function getProductFacade()
    {
        return $this->productFacade;
    }

    public function getUser()
    {
        return $this->user;
    }

    /****************************/
    /*****  Renders price  ******/
    /****************************/

    public function renderPriceWithoutDPH()
    {
        return $this->renderSomePrice($this->getWithoutDPH());
    }

    public function renderPrice()
    {
        return $this->renderSomePrice($this->getPrice());
    }

    public function renderLastPriceWithoutDPH()
    {
        return $this->renderSomePrice($this->getLastWithoutDPH());
    }

    public function renderLastPrice()
    {
        return $this->renderSomePrice($this->getLastPrice());
    }

    public function renderSpecialPriceWithoutDPH()
    {
        return $this->renderSomePrice($this->getSpecialWithoutDPH());
    }

    public function renderSpecialPrice()
    {
        return $this->renderSomePrice($this->getSpecialPrice());
    }

    public function renderDiscount()
    {
        $price = $this->getLastPrice() - $this->getPrice();
        return $this->renderSomePrice($price);
    }

    public function renderDiscountPercent() {
        if ($this->discountPercent) {
            return $this->discountPercent;
        } else {
            if (isset($this->getActualPrices()['action']['percent'])) {
                return $this->getActualPrices()['action']['percent'];
            } else {
                return 0;
            }
        }
    }

    public function renderDiscountDate() {
        if (isset($this->getActualPrices()['action']['dateTo'])) {
            return $this->getActualPrices()['action']['dateTo']->format('j. n.');
        } else {
            return null;
        }
    }

    /****************************/
    /*****  Getters price  ******/
    /****************************/

    // Normal price //
    public function getPrice()
    {
        return $this->dphCounter->getTotalPrice();
    }

    public function getDPH()
    {
        return $this->dphCounter->getTotalDPH();
    }

    public function getWithoutDPH()
    {
        return $this->dphCounter->getTotalWithoutDPH();
    }

    // Last price //
    public function getLastPrice()
    {
        return $this->dphCounterLast->getTotalPrice();
    }

    public function getLastDPH()
    {
        return $this->dphCounterLast->getTotalDPH();
    }

    public function getLastWithoutDPH()
    {
        return $this->dphCounterLast->getTotalWithoutDPH();
    }

    public function getSpecialPrice()
    {
        return $this->dphCounterSpecial->getTotalPrice();
    }

    public function getSpecialDPH()
    {
        return $this->dphCounterSpecial->getTotalDPH();
    }

    public function getSpecialWithoutDPH()
    {
        return $this->dphCounterSpecial->getTotalWithoutDPH();
    }

    // Other //
    public function getDPHPercent()
    {
        return $this->dphCounter->getDPHPercent();
    }

    public function getDiscountPercent()
    {
        if ($this->getLastPrice() == 0) {
            return 0;
        }
        return round((($this->getLastPrice() - $this->getPrice()) / $this->getLastPrice()) * 100);
    }

    public function actionExists() {
        return $this->discountPercent || isset($this->getActualPrices()['action']);
    }

    public function specialExists() {
        return isset($this->getActualPrices()['special']);
    }

    public function getSpecialDateTo()
    {
        if (isset($this->getActualPrices()['special']['dateTo'])) {
            return $this->getActualPrices()['special']['dateTo'];
        } else {
            return null;
        }
    }

    /****************************/
    /*****  Helper functions ****/
    /****************************/

    private function renderSomePrice($price, $enableCurrencyTags = true)
    {
        $template = new Engine();
        $template->render(__DIR__ . '/templates/price.latte', [
            'currency' => $this->currency,
            'sellingPrice' => $price,
            'tags' => $enableCurrencyTags
        ]);
    }

    private function getSellingPrice()
    {
        if ($this->feedPrice) {
            if (isset($this->getActualPrices()['action']) && $this->getActualPrices()['action']['feedShow']) {
                return $this->getActualPrices()['action']['sellingPrice'];
            } else {
                return $this->getActualPrices()['normal']['sellingPrice'];
            }
        } else {
            if (isset($this->getActualPrices()['action'])) {
                return $this->getActualPrices()['action']['sellingPrice'];
            } else {
                return $this->getActualPrices()['normal']['sellingPrice'];
            }
        }
    }

    private function getLastSellingPrice()
    {
        if ($this->feedPrice) {
            if (isset($this->getActualPrices()['action']) && $this->getActualPrices()['action']['feedShow']) {
                return $this->getActualPrices()['action']['lastPrice'];
            } else {
                return $this->getActualPrices()['normal']['lastPrice'];
            }
        } else {
            if (isset($this->getActualPrices()['action'])) {
                return $this->getActualPrices()['action']['lastPrice'];
            } else {
                return $this->getActualPrices()['normal']['lastPrice'];
            }
        }
    }

    private function getSpecialSellingPrice()
    {
        if (isset($this->getActualPrices()['special'])) {
            return $this->getActualPrices()['special']['sellingPrice'];
        } else {
            return 0;
        }
    }

    private function getActualPrices()
    {
        $key = 'productPrices-' . $this->getId();
        $arr = $this->cache->load($key);
        if ($arr == null) { // if null, create and cash
            $arr = [];
            // Prvně si vezmu základní cenu
            $arr['CZK']['normal']['sellingPrice'] = $this->product->selingPrice;
            $arr['CZK']['normal']['lastPrice'] = $this->product->lastPrice;
            $arr[$this->currency[ 'code' ]]['normal']['sellingPrice'] = $this->product->selingPrice / $this->currency[ 'exchangeRate' ];
            $arr[$this->currency[ 'code' ]]['normal']['lastPrice'] = $this->product->lastPrice / $this->currency[ 'exchangeRate' ];

            // Kontrola vlastních cen
            $prices = $this->productFacade->gEMProductAction()->findBy([
                'product' => $this->getId(),
                'isTypeOfPrice' => 1,
                'active' => 1,
                'special' => 0
            ]);
            foreach ($prices as $price) {
                $arr[ $price->currency->code ]['normal'] = [];
                $arr[ $price->currency->code ]['normal'][ 'sellingPrice' ] = $price->selingPrice;
                $arr[ $price->currency->code ]['normal'][ 'lastPrice' ] = $price->lastPrice;
            }

            // Kontrola, zda jsou akce. Pokud ano, tak ty přepisují vše ostatní.
            $actions = $this->productFacade->gEMProductAction()->findBy([
                'product' => $this->getId(),
                'active' => 1,
                'isTypeOfPrice' => 0,
                'special' => 0
            ]);
            foreach ($actions as $action) {
                $arr[ $action->currency->code ]['action'] = [];
                $arr[ $action->currency->code ]['action'][ 'sellingPrice' ] = $action->selingPrice;
                $arr[ $action->currency->code ]['action'][ 'lastPrice' ] = $action->lastPrice;
                $arr[ $action->currency->code ]['action'][ 'percent' ] = $action->percent;
                $arr[ $action->currency->code ]['action'][ 'feedShow' ] = $action->feedShow;
                $arr[ $action->currency->code ]['action'][ 'dateTo' ] = $action->dateTo;
            }

            // Kontrola, zda jsou speciální akce.
            $actions = $this->productFacade->gEMProductAction()->findBy([
                'product' => $this->getId(),
                'active' => 1,
                'isTypeOfPrice' => 0,
                'special' => 1
            ]);
            foreach ($actions as $action) {
                $arr[ $action->currency->code ]['special'] = [];
                $arr[ $action->currency->code ]['special'][ 'sellingPrice' ] = $action->selingPrice;
                $arr[ $action->currency->code ]['special'][ 'lastPrice' ] = $action->lastPrice;
                $arr[ $action->currency->code ]['special'][ 'percent' ] = $action->percent;
                $arr[ $action->currency->code ]['special'][ 'feedShow' ] = $action->feedShow;
                $arr[ $action->currency->code ]['special'][ 'dateTo' ] = $action->dateTo;
            }

            // Zapíši do cache, protože zjišťování je šílené, tak aby to nebylo jako celek pomalé ;)
            $this->cache->save($key, $arr, [
                Cache::TAGS => ["products", "products-prices", "product-" . $this->getId()],
                Cache::PRIORITY => 10,
                Cache::EXPIRE => '8 hours',
            ]);
        }

        // Pokud existuje cena v dané měně, tak ji vracím
        if (isset($arr[ $this->currency[ 'code' ]])) {
            return $arr[ $this->currency[ 'code' ] ];
        } else { // Pokud ne, tak vracím v CZK / aktuální kurz
            return ['normal' => [
                'sellingPrice' => $arr[ 'CZK' ]['normal'][ 'sellingPrice' ] / $this->currency[ 'exchangeRate' ],
                'lastPrice' => $arr[ 'CZK' ]['normal'][ 'lastPrice' ] / $this->currency[ 'exchangeRate' ],
            ]];
        }
    }
}

