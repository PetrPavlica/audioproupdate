<?php

namespace App\Presenters;

use Front\Components\HomeCredit\HomeCredit;
use Intra\Model\Utils\ProductHelper\ProductHelper;
use Nette;
use Nette\Application\UI\Form;
use Intra\Model\Facade\ProductFacade;
use Intra\Components\MailSender\MailSender;
use Front\Model\Utils\Text\UnitParser;
use Nette\Utils\Strings;

class ProductDetailPresenter extends BaseFrontPresenter
{

    /** @var ProductFacade @inject */
    public $productFac;

    /** @var MailSender @inject */
    public $mailSender;

    /** @var HomeCredit @inject */
    public $homeCredit;

    /** @var string @persistent */
    public $ratingPage = 1;

    /** @var string @persistent */
    public $searchTerm = '';

    /** @var string @persistent */
    public $discussionPage = 1;

    /** @var Nette\Caching\IStorage @inject */
    public $storage;

    protected function createComponentHomeCredit()
    {
        return $this->homeCredit;
    }

    public function renderDetail($id, $slug, $discussionPage = 1, $ratingPage = 1, $searchTerm = '')
    {
        $this->discussionPage = $discussionPage;
        $this->ratingPage = $ratingPage;
        $this->searchTerm = $searchTerm;
        $this->template->slugProd = $slug;

        $qb = $this->productFac->getEm()->createQueryBuilder();
        $qb->select('p')
            ->from($this->productFac->entity(), 'p')
            ->where('p.id = :id');
        $qb->setParameter('id', $id);

        $product = $qb->getQuery()->getOneOrNullResult();
        if ($product) {
            if ($slug !== Strings::webalize($product->name)) {
                $this->redirect(':ProductDetail:detail',
                    ["id" => $product->id, "slug" => Strings::webalize($product->name)]);
            }

            $this->template->product = $product;
            $this->template->mainImg = $mainImg = $this->facade->gEMProductImage()->findOneBy(['product' => $product->id],
                ['isMain' => 'DESC']);
            bdump($mainImg);
            $this->template->countRatings = $this->facade->getCountRatings($id);

            $colorVariants = [];
            $colorProducts = $product->colorProducts;
            if (!$colorProducts) {
                $variant = $this->facade->gEMProductColorItems()->findOneBy(['colorVariant' => $product->id]);
                if ($variant) {
                    $colorProducts = $variant->product->colorProducts;
                    $colorVariants[] = $variant->product;
                }
            } else {
                $colorVariants[] = $product;
            }
            if ($colorProducts) {
                foreach($colorProducts as $c) {
                    $colorVariants[] = $c->colorVariant;
                }
            }

            $this->template->colorVariants = $colorVariants;

            $productHelper = new ProductHelper($this->storage, $this->productFac, $this->user);
            $productHelper->setProduct($product, $this->sess->actualCurrency);
            $this->template->productHelper = $productHelper;

            $this->template->productHelperGift = new ProductHelper($this->storage, $this->productFac, $this->user);

            // Discussions
            $limitDis = 10;
            $idsDiscussions = $this->searchFac->findIdsDiscussions($id, $searchTerm);
            $paginator = new Nette\Utils\Paginator;
            $paginator->setItemCount(count($idsDiscussions)); // celkový počet položek
            $paginator->setItemsPerPage($limitDis); // počet položek na stránce
            $paginator->setPage($this->discussionPage);
            $this->template->paginatorDis = $paginator;
            $this->template->discussions = $this->searchFac->getDiscussions($idsDiscussions, $limitDis,
                $paginator->getOffset());
            $this->template->accessoriesProducts = $this->searchFac->gEMProductAccessories()->findBy([
                'product' => $product->id,
                'products.active' => 1,
                'products.saleTerminated' => 0
            ]);

            $this->template->alternativesProducts = $this->searchFac->gEMProductAlternatives()->findBy([
                'product' => $product->id,
                'alternative.active' => 1,
                'alternative.saleTerminated' => 0
            ]);

            // Ratings
            $limitRat = 10;
            $paginator2 = new Nette\Utils\Paginator;
            $paginator2->setItemCount($product->sumRating); // celkový počet položek
            $paginator2->setItemsPerPage($limitRat); // počet položek na stránce
            $paginator2->setPage($this->ratingPage);
            $this->template->paginatorRat = $paginator2;
            $this->template->ratings = $this->facade->gEMProductRating()->findBy(['product' => $id, 'approved' => 1],
                ['foundedDate' => 'DESC'], $limitRat, $paginator2->getOffset());

            $this->template->images = $this->facade->gEMProductImage()->findBy([
                'product' => $product->id,
                'isMain' => 0
            ], ['orderImg' => 'ASC', 'id' => 'DESC']);
            $this->template->parameters = $this->facade->gEMProductParameter()->findBy(['product' => $product->id],
                ['orderParam' => 'ASC']);

            $this[ 'searchAnswerForm' ]->setDefaults(['text' => $searchTerm]);

            $this[ 'sendProductForm' ]->setDefaults(['product' => $product->id]);

            //Last visited manage
            if (!isset($this->sess->lastVisited)) {
                $this->sess->lastVisited = [];
            }
            if (in_array($product->id, $this->sess->lastVisited)) {
                unset($this->sess->lastVisited[ array_search($product->id, $this->sess->lastVisited) ]);
                $this->sess->lastVisited = array_values($this->sess->lastVisited);
            }
            array_unshift($this->sess->lastVisited, $product->id);
            if (isset($this->sess->lastVisited[ 3 ])) {
                unset($this->sess->lastVisited[ 3 ]);
            }

            $this->template->unitParser = UnitParser::class;

            if ($this->isAjax()) {
                $this->redrawControl('discussion-rating-snipp');
            }

            /** @var array GARemarketing - kódy pro remarketing Google */
            $this->template->GARemarketing = [
                'productId' => $product->id,
                'page' => 'product',
                'totalValue' => $productHelper->getPrice()
            ];
            $slug = \Nette\Utils\Strings::webalize($product->name);
            $this->template->thisUrl = $this->link("//ProductDetail:detail", ["id" => $product->id, "slug" => $slug]);


            // get product in set
            $productInSet = $this->facade->gEMProductSetItems()->findBy(["products" => $product->id, "showSet" => true],
                ["product" => "ASC"]);

            $ids = [];
            foreach ($productInSet as $p) {
                if ($p->product->active && !$p->product->saleTerminated) {
                    $ids[] = $p->product;
                }
            }

            $setsItems = $this->facade->gEMProductSetItems()->findBy(["product" => $ids], ["product" => "ASC"]);
            $groupedSetsItems = [];

            foreach ($setsItems as $item) {
                if ($item->products->active && !$item->products->saleTerminated) {
                    if (!array_key_exists($item->product->id, $groupedSetsItems)) {
                        $groupedSetsItems[$item->product->id] = ["set" => $item->product, "items" => []];
                    }
                    array_push($groupedSetsItems[$item->product->id]["items"], $item->products);
                }
            }

            $this->template->productSets = $groupedSetsItems;

            // get set contents
            if ($product->isSet) {
                $setConsistsOf = $this->facade->gEMProductSetItems()->findBy(["product" => $product->id], ["product" => "ASC"]);
                $setContent = [];
                foreach ($setConsistsOf as $item) {
                    if ($item->products->active)
                        $setContent[] = $item->products;
                }
                $this->template->setContent = $setContent;
            }

            $specialDate = $productHelper->getSpecialDateTo();

            $this->template->specialView = isset($this->sess->specialView[$product->id]) ? $this->sess->specialView[$product->id] : 0;
            $this->template->specialDate = isset($specialDate) ? $specialDate->diff(new \DateTime()) : null;
            $countPackageProducts = 0;
            if ($product->packages) {
                foreach($product->packages as $package) {
                    $countProductsInPackage = 0;
                    if ($package->products) {
                        foreach($package->products as $p) {
                            if ($p->product->active && !$p->product->saleTerminated) {
                                $countProductsInPackage++;
                            }
                        }
                    }
                    if ($countProductsInPackage > 1) {
                        $countPackageProducts++;
                    }
                }
            }
            $this->template->countPackageProducts = $countPackageProducts;

        } else {
            $this->flashMessage('Pokoušeli jste se přejít na neexistující produkt. Buďto máte špatnou adresu a nebo produkt již neexistuje.',
                'warning');
            $this->redirect('Front:default');
        }
    }

    public function createComponentAddRatingForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $nickname = $form->addText('nickname')->setRequired(true);
        if ($this->user->isLoggedIn() && !$this->user->isInRole('visitor')) {
            $nickname->setAttribute('value', $this->user->identity->data['name'].' '.$this->user->identity->data['surname']);
        }
        $form->addTextArea('plus');
        $form->addTextArea('minus');
        $form->addHidden('rating');
        $form->addHidden('product');
        $form->addInvisibleReCaptcha('captcha', true, 'Nejste robot?');
        $form->addSubmit('send', 'Přidat hodnocení');
        $form->onSuccess[] = [$this, 'addRatingSucc'];
        $form->onError[] = [$this, 'addRatingError'];
        return $form;
    }

    public function addRatingError(Form $form)
    {
        if ($form->getErrors()) {
            foreach($form->getErrors() as $e) {
                $this->flashMessage($e, 'warning');
            }
        }
    }

    public function addRatingSucc($form, $values)
    {
        $user = null;
        if ($this->user->isLoggedIn() && $this->user->isInRole('visitor')) {
            $user = $this->user->id;
        }
        $this->productFac->addRating($values, $user);
        $this->productFac->recountRating($values->product);
        $this->flashMessage('Hodnocení bylo přidáno. Děkujeme za Vaší zpětnou vazbu.', 'success');
        $this->redirect('this');
    }

    public function createComponentAddAnswer()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addTextArea('text')
            ->setRequired('Pozor pole s textem je povinné!');
        $form->addHidden('product');
        $form->addSubmit('send', 'Poslat dotaz');
        $form->onSuccess[] = [$this, 'addAnswerSucc'];
        return $form;
    }

    public function addAnswerSucc($form, $values)
    {
        if ($this->user->loggedIn) {
            if (isset($this->user->roles[ 0 ]) && $this->user->roles[ 0 ] == 'visitor') {
                $this->productFac->addDiscussion($values, $this->user->id);
                $this->flashMessage('Dotaz byl přijat. Jakmile na něj náš operátor odpoví, tak Vás budeme informovat na Vašem emailu.',
                    'success');
            } else {
                $this->flashMessage('Tento uživatel nemůže zde přidávat dotazy!', 'warning');
            }
        } else {
            $this->flashMessage('Pro přidání dotazů se prosím přihlašte.', 'warning');
        }
        $this->redirect('this');
    }

    public function createComponentSearchAnswerForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('text')
            ->setAttribute('placeholder', 'hledat odpověď');
        $form->addSubmit('send', '');
        $form->onSuccess[] = [$this, 'searchAnswerSuccess'];
        return $form;
    }

    public function searchAnswerSuccess(
        $form,
        $values
    ) {
        $this->redirect('this#diskuze_produktu_nadpis', ['searchTerm' => $values->text]);
    }

    public function createComponentSendProductForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addHidden("id");
        $form->addEmail('to')
            ->setAttribute('placeholder', 'komu')
            ->setRequired('Toto pole je povinné.');
        $form->addText('from')
            ->setAttribute('placeholder', 'od koho')
            ->setRequired('Toto pole je povinné.');
        $form->addTextArea('text');
        $form->addHidden('product');
        $form->addSubmit('send', 'Poslat produkt');
        $form->onSuccess[] = [$this, 'sendProductSuccess'];
        return $form;
    }

    public function renderShareByEmail($id)
    {
        $this[ 'sendProductForm' ]->setDefaults(["id" => $id]);
        $this->redrawControl('share-by-email');
        $this->payload->completed = 1;
    }

    public function sendProductSuccess($form, $values)
    {
        $this->mailSender->sendProduct($values, $this->sess->actualCurrency);
        $this->flashMessage('Email byl úspěšně odeslán.', 'success');
        $this->redirect(':ProductDetail:detail', ["id" => $values->id, "slug" => "x"]);
    }

    public function handleSpecialView()
    {
        if (!isset($this->sess->specialView)) {
            $this->sess->specialView = [];
        }
        if (isset($this->sess->specialView[$_POST['productId']])) {
            $this->sess->specialView[$_POST['productId']] += 1;
        } else {
            $this->sess->specialView[$_POST['productId']] = 1;
            $this->sess->setExpiration('30 minutes', 'specialView');
        }
    }

    protected function createComponentAddToBasketForm()
    {
        return new Nette\Application\UI\Multiplier(function () {
            $form = new Form;
            $form->setTranslator($this->translator);

            $form->addHidden("productId");
            $form->addHidden('count');
            $form->addSubmit("addToBasket");

            $form->onSuccess[] = [$this, 'addToBasketSucc'];
            return $form;
        });
    }

    public function addToBasketSucc(Form $form, $values)
    {
        $this->handleAddToCart($values['productId']);
        $this->redirect('this');
    }

    protected function createComponentAddToBasketPackageForm()
    {
        return new Nette\Application\UI\Multiplier(function () {
            $form = new Form;
            $form->setTranslator($this->translator);

            $form->addHidden('packageId');
            $form->addSubmit("addToBasket");

            $form->onSuccess[] = [$this, 'addToBasketPackageSucc'];
            return $form;
        });
    }

    public function addToBasketPackageSucc(Form $form, $values)
    {
        $this->handleAddToCartPackage($values->packageId);
        $this->redirect('this');
    }

    public function handleRedrawProduct($id, $slug)
    {
        if ($this->isAjax()) {
            $this->redrawControl();
        }
    }
}
