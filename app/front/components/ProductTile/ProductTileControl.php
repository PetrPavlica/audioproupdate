<?php

namespace Front\Components\ProductTile;

use Front\Components\StockExpedition\IStockExpeditionFactory;
use Intra\Model\Utils\ProductHelper\ProductHelper;
use Nette;
use Nette\Application\UI;
use Front\Model\Facade\FrontFacade;
use Nette\Caching\Cache;
use Nette\Utils\Strings;
use Ublaboo\ImageStorage\ImageStorage;

class ProductTileControl extends UI\Control
{

    /** @var FrontFacade */
    public $facade;

    /** @var ProductHelper */
    public $productHelper;

    /** @var ImageStorage */
    private $imageStorage;

    private $product;

    public $stockExpedition;

    /** @var Nette\Caching\IStorage */
    public $storage;

    /** @var Cache */
    public $cache;

    public function __construct(FrontFacade $facade, ProductHelper $productHelper, IStockExpeditionFactory $stock, ImageStorage $imageStorage, Nette\Caching\IStorage $storage)
    {
        parent::__construct();
        $this->facade = $facade;
        $this->productHelper = $productHelper;
        $this->stockExpedition = $stock;
        $this->imageStorage = $imageStorage;
        $this->storage = $storage;
        $this->cache = new Cache($this->storage);
    }

    protected function createComponentStockExpedition()
    {
        return $this->stockExpedition->create();
    }

    public function render($product, $sess, $class = '', $isSlider = false, $swiperId = "#swiper1", $disableTags = false)
    {
        if ($this->product) {
            $product = $this->product;
        }
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/tile.latte');
        $template->product = $product;
        $template->imageStorage = $this->imageStorage;
        /*$template->mainImg = $this->facade->gEMProductImage()->findOneBy(['product' => $product->id],
            ['isMain' => 'DESC']);*/

        $key = 'productColorVariants-' . $product->id;
        $colorVariants = $this->cache->load($key);
        if ($colorVariants === null) { // if null, create and cash
            $colorVariants = [];
            $colorProducts = $product->colorProducts;
            if (!$colorProducts) {
                $variant = $this->facade->gEMProductColorItems()->findOneBy(['colorVariant' => $product->id]);
                if ($variant) {
                    $colorProducts = $variant->product->colorProducts;
                    $colorVariants[] = [
                        'id' => $variant->product->id,
                        'color' => $variant->product->color,
                        'slug' => Strings::webalize($variant->product->name),
                    ];
                }
            } else {
                $colorVariants[] = [
                    'id' => $product->id,
                    'color' => $product->color,
                    'slug' => Strings::webalize($product->name),
                ];
            }
            if ($colorProducts) {
                foreach ($colorProducts as $c) {
                    $colorVariants[] = [
                        'id' => $c->colorVariant->id,
                        'color' => $c->colorVariant->color,
                        'slug' => Strings::webalize($c->colorVariant->name),
                    ];
                }
            }

            $this->cache->save($key, $colorVariants, [
                Cache::TAGS => ["products", "product-" . $product->id],
                Cache::PRIORITY => 10
            ]);
        }

        $template->colorVariants = $colorVariants;

        $template->class = $class;
        $template->isSlider = $isSlider;
        $template->swiperId = $swiperId;
        $template->actualCurrency = $sess->actualCurrency;
        $template->disableTags = $disableTags;
        $this->productHelper->setProduct($product, $sess->actualCurrency);
        $template->productHelper = $this->productHelper;
        $template->sess = $sess;
        $template->time = rand(0, time());

        $template->disableEshop = $this->presenter->disableEshop;

        // render template
        $template->render();
    }

    protected function createComponentAddToBasketForm()
    {
        return new Nette\Application\UI\Multiplier(function () {
            $form = new UI\Form();
            $form->setTranslator($this->presenter->translator);

            $form->addHidden("productId");
            $form->addHidden('count');
            $form->addSubmit("addToBasket");

            $form->onSuccess[] = [$this, 'addToBasketSucc'];
            return $form;
        });
    }

    public function addToBasketSucc(UI\Form $form, $values)
    {
        $this->presenter->handleAddToCart($values['productId']);
        $this->presenter->redirect('this');
    }

    public function handleRedrawProduct($id, $slug)
    {
        $this->product = $this->facade->gEMProduct()->find($id);
        if ($this->presenter->isAjax()) {
            $this->presenter->redrawControl('products');
            $this->presenter->redrawControl('product-'.$id);
        }
    }
}
