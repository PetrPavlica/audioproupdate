<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use App\Core\Model\Database\Utils\SQLHelper;
use Intra\Model\Database\Entity\ProductAction;
use Intra\Model\Database\Entity\ProductAlternatives;
use Intra\Model\Database\Entity\ProductColorItems;
use Intra\Model\Database\Entity\ProductGifts;
use Intra\Model\Database\Entity\ProductInCategory;
use Intra\Model\Database\Entity\ProductPackage;
use Intra\Model\Database\Entity\ProductPackageItems;
use Intra\Model\Database\Entity\ProductSetItems;
use Kdyby\Doctrine\EntityRepository;
use Nette\Database\Context;
use Nette\Utils\Strings;
use Kdyby\Doctrine\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Kdyby\Doctrine\Mapping\ResultSetMappingBuilder;
use Intra\Model\Database\Entity\Product;
use Intra\Model\Database\Entity\ProductImage;
use Intra\Model\Database\Entity\ProductAccessories;
use Intra\Model\Database\Entity\ProductOperation;
use Intra\Model\Database\Entity\Orders;
use Intra\Model\Database\Entity\ProductParameter;
use Intra\Model\Database\Entity\GroupProductFilter;
use Intra\Model\Database\Entity\ProductFilter;
use Intra\Model\Database\Entity\ProductInFilter;
use Intra\Model\Database\Entity\ProductParameterComplete;
use Intra\Model\Database\Entity\ProductRating;
use Intra\Model\Database\Entity\ProductDiscussion;
use Intra\Model\Utils\ImageManager\ImagesEditor;
use Tracy\Debugger;

class ProductFacade extends BaseFacade
{
    /** @var Context */
    public $db;

    /** @var ImagesEditor */
    public $imgEditor;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(
        EntityManager $em,
        Context $db,
        ImagesEditor $imgEditor
    ) {
        parent::__construct($em);
        $this->imgEditor = $imgEditor;
        $this->db = $db;
    }

    /**
     * Get em repository
     * @return EntityRepository
     */
    public function get()
    {
        return $this->em->getRepository(Product::class);
    }

    /**
     * Get em repository
     * @return EntityRepository
     */
    public function getProductOperation()
    {
        return $this->em->getRepository(ProductOperation::class);
    }

    public function getOrders()
    {
        return $this->em->getRepository(Orders::class);
    }

    public function getProductParameters()
    {
        return $this->em->getRepository(ProductParameter::class);
    }

    public function getGroupProductFilter()
    {
        return $this->em->getRepository(GroupProductFilter::class);
    }

    public function getProductFilter()
    {
        return $this->em->getRepository(ProductFilter::class);
    }

    public function getProductInFilter()
    {
        return $this->em->getRepository(ProductInFilter::class);
    }

    /**
     * Return entity class
     * @return string
     */
    public function entity()
    {
        return Product::class;
    }

    /**
     * Return entity class or ProductOperation
     * @return string
     */
    public function entityProductOperation()
    {
        return ProductOperation::class;
    }

    /**
     * Get searching data for product autocomplete
     * @param string $term of search
     * @return array of results
     */
    public function getDataAutocompleteProducts($term)
    {
        $columns = ['code', 'name', 'ean'];
        $alias = 'p';
        $like = SQLHelper::termToLike($term, $alias, $columns);

        $result = $this->em->getRepository(Product::class)
            ->createQueryBuilder($alias)
            ->where($like)
            ->setMaxResults('20')
            ->getQuery()
            ->getResult();
        $arr = [];
        foreach ($result as $it) {
            $arr[ $it->id ] = ['' . $it->name . '', 100, $it->id];
        }
        barDump($arr);
        return $arr;
    }

    public function addImage($pathImg, $pathThumb, $productId)
    {
        $img = new ProductImage();
        $img->setProduct($this->get()->find($productId));
        $img->setPath($pathImg);
        $img->setPathThumb($pathThumb);
        $this->insertNew($img);
    }

    public function deleteImg($imgId, $productId)
    {
        $img = $this->gEMProductImage()->find($imgId);
        $product = $this->gEMProduct()->find($productId);

        if (isset($product->mainImage) && isset($img->pathThumb) && $product->mainImage == $img->pathThumb) {
            $product->setMainImage(null);
        }
        if ($img) {
            if (file_exists($img->path)) {
                unlink($img->path);
            }
            if (file_exists($img->pathThumb)) {
                unlink($img->pathThumb);
            }
            $this->getEm()->remove($img);
            $this->getEm()->flush();
        }
    }

    public function saveImgDetails($values, $product)
    {
        if (isset($values[ 'imgId' ]) && count($values[ 'imgId' ])) {
            foreach ($values[ 'imgId' ] as $idx => $id) {
                $fileImg = $this->gEMProductImage()->find($id);
                if ($fileImg) {
                    $fileImg->setAlt($values['imgAlt'][$idx]);
                    if (isset($values['isMain']) && $values['isMain'] == $idx) {
                        $fileImg->setIsMain(true);
                        if ($fileImg->pathThumb) {
                            $product->setMainImage($fileImg->pathThumb);
                        } else {
                            $product->setMainImage($fileImg->path);
                        }
                    } else {
                        $fileImg->setIsMain(false);
                    }
                    if (is_numeric($values['imgOrder'][$idx])) {
                        $fileImg->setOrderImg($values['imgOrder'][$idx]);
                    } else {
                        $fileImg->setOrderImg(0);
                    }
                }
            }
            $this->save();
        }
    }

    public function generateSlug($idProduct, $values)
    {
        $counter = null;
        $slug = Strings::webalize($values[ 'name' ]);

        update:
        $res = $this->get()->findBy(['slug' => $slug . $counter, 'id !=' => $idProduct]);
        if (count($res)) {
            $counter++;
            goto update;
        }

        $product = $this->get()->find($idProduct);
        $product->setSlug($slug . $counter);
        $this->save();
    }

    public function addAccessoryProduct($idProduct, $idAccessory)
    {
        $entity = new ProductAccessories();
        $entity->setProduct($this->get()->find($idProduct));
        $entity->setProducts($this->get()->find($idAccessory));
        $this->insertNew($entity);
        return true;
    }

    public function addAlternativeProduct($idProduct, $idAlternative)
    {
        $entity = new ProductAlternatives();
        $entity->setProduct($this->get()->find($idProduct));
        $entity->setAlternative($this->get()->find($idAlternative));
        $this->insertNew($entity);
        return true;
    }

    public function addSetItem($idProduct, $idSet)
    {
        $entity = new ProductSetItems();
        $entity->setProduct($this->get()->find($idProduct));
        $entity->setProducts($this->get()->find($idSet));
        $this->insertNew($entity);
        return true;
    }

    public function addPackageItem($products, $items)
    {
        if ($products) {
            foreach($products as $packageId => $product) {
                if (empty($product)) {
                    continue;
                }
                $entity = new ProductPackageItems();
                $entity->setPackage($this->gEMProductPackage()->find($packageId));
                $entity->setProduct($this->get()->find($product));
                $entity->setDiscountCZK(isset($items[$packageId]['CZK']) && intval($items[$packageId]['CZK']) ? intval($items[$packageId]['CZK']) : null);
                $entity->setDiscountEUR(isset($items[$packageId]['EUR']) && intval($items[$packageId]['EUR']) ? intval($items[$packageId]['EUR']) : null);
                $this->insertNew($entity);
                return true;
            }
        }

        return false;
    }

    public function updateSetItem($id, $data)
    {

        $setItem = $this->getEm()->getRepository(ProductSetItems::class)->find($id);
        if ($setItem) {
            $setItem->showSet = (($data == 1) ? true : false);

            $this->save();
        }
    }

    public function getSelectBoxProductsAll()
    {
        $entities = $this->get()->findAll();
        $arr = [];
        foreach ($entities as $entity) {
            $arr[ $entity->id ] = $entity->name;
        }
        return $arr;
    }

    public function deleteAccessoryProduct($id)
    {
        $entity = $this->getEm()->getRepository(ProductAccessories::class)->find($id);
        if ($entity) {
            $this->getEm()->remove($entity);
            $this->getEm()->flush();
        }
    }

    public function deleteAlternativeProduct($id)
    {
        $entity = $this->getEm()->getRepository(ProductAlternatives::class)->find($id);
        if ($entity) {
            $this->getEm()->remove($entity);
            $this->getEm()->flush();
        }
    }

    public function deleteSetItem($id)
    {
        $entity = $this->getEm()->getRepository(ProductSetItems::class)->find($id);
        if ($entity) {
            $this->getEm()->remove($entity);
            $this->getEm()->flush();
        }
    }

    public function deletePackageItem($id)
    {
        try {
            $entity = $this->getEm()->getRepository(ProductPackageItems::class)->find($id);
            if ($entity) {
                $productsInOrder = $this->gEMProductInOrder()->findBy(['packageItem' => $entity]);
                if ($productsInOrder) {
                    foreach($productsInOrder as $p) {
                        $p->setPackageItem(null);
                    }
                    $this->save();
                }
                $this->getEm()->remove($entity);
                $this->getEm()->flush();
                return true;
            }
        } catch (\Exception $ex) {

        }

        return false;
    }

    public function deletePackage($id)
    {
        try {
            $package = $this->getEm()->getRepository(ProductPackage::class)->find($id);
            if ($package) {
                if ($package->products) {
                    foreach ($package->products as $p) {
                        $productsInOrder = $this->gEMProductInOrder()->findBy(['packageItem' => $p]);
                        if ($productsInOrder) {
                            foreach ($productsInOrder as $pio) {
                                $pio->setPackageItem(null);
                            }
                            $this->save();
                        }
                        $this->getEm()->remove($p);
                    }
                    $this->save();
                }
                $this->remove($package);
                return true;
            }
        } catch (\Exception $ex) {

        }

        return false;
    }

    public function addPackage($product)
    {
        if (is_numeric($product)) {
            $product = $this->gEMProduct()->find($product);
        }

        $package = new ProductPackage();
        $package->setProduct($product);
        $this->insertNew($package);
    }

    public function addStockOperation(
        $product,
        $count,
        $unitPrice,
        $comment,
        $userId = null,
        $order = null,
        $isReservation = false,
        $removeReservation = false
    ) {
        if (!(is_numeric($count))) {
            return false;
        }
        if (is_numeric($product)) {
            $product = $this->get()->find($product);
        }

        if (is_numeric($order)) {
            $order = $this->gEMOrders()->find($order);
        }

        if ($unitPrice === null) {
            $unitPrice = $product->avaragePurchasePrice;
        }
        if ($removeReservation) {
            $entity = $this->gEMProductOperation()->findOneBy([
                'product' => $product->id,
                'orders' => $order->id,
                'isReservation' => 1
            ]);
            if ($entity) {
                $this->remove($entity);
            }
        }

        $oper = new ProductOperation();
        $oper->setCount($count);
        $oper->setPrice($unitPrice);
        $oper->setSumPrice($count * $unitPrice);
        $oper->setProduct($product);
        $oper->setComment($comment);
        $oper->setIsReservation($isReservation);
        if ($userId != null) {
            $oper->setOriginator($this->gEMUser()->find($userId));
        }
        if ($order != null) {
            $oper->setOrders($order);
        }

        $this->insertNew($oper);

        $product->setCount($product->count + $count);
        $product->setLastPurchasePrice($unitPrice);
        $this->save();
        //$this->recountCountProduct($product);
        return true;
    }

    public function recountCountProduct($product)
    {
        $res = $this->db->query('SELECT sum(o.count) cn FROM product_operation o WHERE o.product_id = ' . $product->id)->fetch();
        $product->setCount($res[ 'cn' ]);
        $res = $this->db->query('SELECT sum(o.count) cn FROM product_operation o WHERE o.is_reservation = 1 AND o.product_id = ' . $product->id)->fetch();
        $product->setRezerveCount(-$res[ 'cn' ]);
        $res = $this->db->query("SELECT sum(o.count) cn FROM product_operation o WHERE o.is_reservation = 0 AND o.orders_id != '' AND o.product_id = " . $product->id)->fetch();
        $product->setCountOfSell(-$res[ 'cn' ]);
        $res = $this->db->query('SELECT sum(o.count * o.price)/sum(o.count) cn FROM product_operation o WHERE count > 0 AND o.product_id = ' . $product->id)->fetch();
        $product->setAvaragePurchasePrice(round($res[ 'cn' ], 2));
        $last = $this->getProductOperation()->findOneBy(['product' => $product->id], ['foundedDate' => 'DESC']);
        if ($last) {
            $product->setLastPurchasePrice($last->price);
        }
        $this->save();
    }

    public function removeStockOperation($idOper)
    {
        if (!(is_numeric($idOper))) {
            return false;
        }

        $oper = $this->getProductOperation()->find($idOper);
        if ($oper) {
            $this->getEm()->remove($oper);
            $this->getEm()->flush();
            $product = $this->get()->find($oper->product->id);
            $this->recountCountProduct($product);
            return true;
        }
        return false;
    }

    public function addStockOperationsForInvoice($orderId)
    {
        $order = $this->getOrders()->find($orderId);
        foreach ($order->products as $product) {
            if ($product->product && !$product->isPojisteni && !$product->isNastaveni && !$product->parentProduct) // jenom pokud se jedná o produkt s kartou
            {
                if ($product->product->isSet) {
                    foreach ($product->product->setProducts as $p) {
                        $this->addStockOperation($p->products, -$product->count,
                            $p->products->avaragePurchasePrice, '', null, $order, false, true);
                    }
                } else {
                    $this->addStockOperation($product->product, -$product->count,
                        $product->product->avaragePurchasePrice, '', null, $order, false, true);
                }
            }
        }
    }

    public function addStockOperationsForCreditNote($orderId)
    {
        $order = $this->getOrders()->find($orderId);
        $products = $this->gEMProductInCreditNote()->findBy(['order' => $order]);
        foreach ($products as $product) {
            if ($product->productInOrder && $product->productInOrder->product && !$product->productInOrder->isPojisteni && !$product->productInOrder->isNastaveni && !$product->productInOrder->parentProduct) // jenom pokud se jedná o produkt s kartou
            {
                if ($product->productInOrder->product->isSet) {
                    foreach ($product->productInOrder->product->setProducts as $p) {
                        $this->addStockOperation($p->products, $product->count,
                            $p->products->avaragePurchasePrice, 'Vráceno - dobropis č. '.$order->codeCreditNote, null, $order, false, true);
                    }
                } else {
                    $this->addStockOperation($product->productInOrder->product, $product->count,
                        $product->productInOrder->product->avaragePurchasePrice, 'Vráceno - dobropis č. '.$order->codeCreditNote, null, $order, false, true);
                }
            }
        }
    }

    public function addParameter($name, $value, $order, $productId)
    {
        $product = $this->get()->find($productId);

        $param = new ProductParameter();
        $param->setName($name);
        $param->setValue($value);
        $param->setOrderParam($order);
        $param->setProduct($product);
        $this->insertNew($param);
        return;
    }

    public function removeProductParameter($idParam)
    {
        if (!(is_numeric($idParam))) {
            return false;
        }

        $param = $this->getProductParameters()->find($idParam);
        if (count($param)) {
            $this->getEm()->remove($param);
            $this->getEm()->flush();
            return true;
        }
        return false;
    }

    public function saveProductParametersDetails($values)
    {
        if (isset($values[ 'paramId' ]) && count($values[ 'paramId' ])) {
            foreach ($values[ 'paramId' ] as $idx => $id) {
                $param = $this->getProductParameters()->find($id);
                if ($param) {
                    $param->setName($values['paramName'][$idx]);
                    $param->setValue($values['paramValue'][$idx]);
                    if (is_numeric($values['paramOrder'][$idx])) {
                        $param->setOrderParam($values['paramOrder'][$idx]);
                    }
                    $this->save();
                }
            }
        }
    }

    public function saveFilters($values, $product)
    {
        $actualFilters = $this->getProductInFilter()->findBy(['product' => $product->id]);
        foreach ($actualFilters as $item) {
            $this->getEm()->remove($item);
            $this->getEm()->flush();
        }

        if (isset($values[ 'filterCheck' ])) {
            foreach ($values[ 'filterCheck' ] as $idx => $val) {
                $filter = $this->getProductFilter()->find($idx);
                $pif = new ProductInFilter();
                $pif->setProduct($product);
                $pif->setFilter($filter);
                if (isset($values[ 'filterValue' ][ $idx ])) {
                    $tmp = str_replace(',', '.', $values[ 'filterValue' ][ $idx ]);
                    $pif->setValue($tmp);
                } else {
                    $pif->setValue('');
                }
                if (isset($values[ 'filterValueMax' ][ $idx ])) {
                    $tmp = str_replace(',', '.', $values[ 'filterValueMax' ][ $idx ]);
                    $pif->setValueMax($tmp);
                } else {
                    $pif->setValueMax('');
                }
                $this->insertNew($pif);
            }
        }
    }

    /**
     * Get searching data for product autocomplete
     * @param string $term of search
     * @return array of results
     */
    public function getDataAutocompleteParameters($term)
    {
        $columns = ['value'];
        $alias = 'p';
        $like = SQLHelper::termToLike($term, $alias, $columns);

        $result = $this->em->getRepository(ProductParameterComplete::class)
            ->createQueryBuilder($alias)
            ->where($like)
            ->setMaxResults('20')
            ->getQuery()
            ->getResult();
        $arr = [];
        foreach ($result as $it) {
            $arr[ $it->id ] = $it->value;
        }
        return $arr;
    }

    /**
     * Make deep copy of products
     * @param array $ids os products
     */
    function createCopiesProducts($ids)
    {
        foreach ($ids as $id) {
            $oldProduct = $this->get()->find($id);

            $newProduct = new Product($oldProduct->toArray());

            $newProduct->setName('KOPIE - ' . $oldProduct->name);
            if ($oldProduct->productMark) {
                $newProduct->setProductMark($this->gEMProductMark()->find($oldProduct->productMark->id));
            }
            if ($oldProduct->vat) {
                $newProduct->setVat($this->gEMVat()->find($oldProduct->vat->id));
            }
            $newProduct->setId(null);
            $newProduct->setSlug(null);
            $newProduct->setActions(null);
            $newProduct->setActiveAction(null);
            $newProduct->setCount(0);
            $newProduct->setMainImage(null);
            $newProduct->setOnFront(0);
            $newProduct->setCountOfSell(0);
            $newProduct->setOurSortingMostSold(0);
            $newProduct->setCountOfSellForSearch(0);
            $newProduct->setNewichProductRef(null);
            $newProduct->setRezerveCount(0);
            $newProduct->setLastPurchasePrice(0);
            $newProduct->setAvaragePurchasePrice(0);
            $newProduct->setImages(null);
            $newProduct->setAccessoriesProducts(null);
            $newProduct->setAlternativesProducts(null);
            $newProduct->setParameters(null);
            $newProduct->setFilter(null);
            $newProduct->setBetterProduct(null);
            $newProduct->setIsSet(false);
            $newProduct->setSetProducts(null);
            $newProduct->setPackages(null);
            $newProduct->setCategories(null);
            $newProduct->setColorProducts(null);
            $newProduct->setGifts(null);
            $newProduct->setIsColorVariant(0);
            $newProduct->setArticles(null);

            #barDump($newProduct);
            #die;
            $this->insertNew($newProduct);

            $this->generateSlug($newProduct->id, $oldProduct->toArray());

            foreach ($oldProduct->filter as $fil) {
                $newFil = new ProductInFilter();
                $newFil->setValue($fil->value);
                $newFil->setValueMax($fil->valueMax);
                if ($fil->filter) {
                    $newFil->setFilter($this->gEMProductFilter()->find($fil->filter->id));
                    $newFil->setProduct($newProduct);
                    $this->em->persist($newFil);
                }
            }

            foreach ($oldProduct->parameters as $param) {
                $newParam = new ProductParameter();
                $newParam->setName($param->name);
                $newParam->setValue($param->value);
                $newParam->setOrderParam($param->orderParam);
                $newParam->setProduct($newProduct);
                $this->em->persist($newParam);
            }

            foreach ($oldProduct->accessoriesProducts as $alt) {
                $newAlt = new ProductAccessories();
                $newAlt->setProduct($newProduct);
                if ($alt->products) {
                    $newAlt->setProducts($alt->products);
                    $this->em->persist($newAlt);
                }
            }

            foreach ($oldProduct->alternativesProducts as $alt) {
                $newAlt = new ProductAlternatives();
                $newAlt->setProduct($newProduct);
                if ($alt->alternative) {
                    $newAlt->setAlternative($alt->alternative);
                    $this->em->persist($newAlt);
                }
            }

            foreach ($oldProduct->categories as $cat) {
                $category = new ProductInCategory();
                $category->setCategory($cat->category);
                $category->setProduct($newProduct);
                $this->em->persist($category);
            }

            foreach ($oldProduct->gifts as $g) {
                $gift = new ProductGifts();
                $gift->setProduct($newProduct);
                $gift->setGift($g->gift);
                $gift->setRank($g->rank);
                $this->em->persist($gift);
            }

            $prices = $this->gEMProductAction()->findBy(['product' => $oldProduct]);
            if ($prices) {
                foreach($prices as $p) {
                    $entPrice = new ProductAction($p->toArray());
                    $entPrice->setProduct($newProduct);
                    $entPrice->setCurrency($p->currency);
                    $this->em->persist($entPrice);
                }
            }

            $this->save();
        }
    }

    public function prepareParametersFromFilters($productId)
    {
        $filters = $this->gEMProductInFilter()->findBy(['product' => $productId]);
        $product = $this->get()->find($productId);

        $order = 10;
        foreach ($filters as $filtr) {
            $newParam = new ProductParameter();
            $newParam->setName($filtr->filter->filterGroup->name . ' - ' . $filtr->filter->name);
            if (!$filtr->filter->slider) {
                $newParam->setName($filtr->filter->filterGroup->name);
                $newParam->setValue($filtr->filter->name);
            } else {
                if ($filtr->valueMax) {
                    $newParam->setValue($filtr->value . ' - ' . $filtr->valueMax);
                } else {
                    $newParam->setValue($filtr->value);
                }
            }
            $newParam->setOrderParam($order);
            $newParam->setProduct($product);
            $this->insertNew($newParam);
            $order += 10;
        }
        if ($order > 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Manually add ProductRating
     * @param array $values array from POST
     * @param integer $customer id of Customer
     */
    public function addRating($values, $customerId)
    {
        $entity = new ProductRating($values);
        $entity->setProduct($this->gEMProduct()->find($values->product));
        if ($customerId) {
            $entity->setCustomer($this->gEMCustomer()->find($customerId));
        }
        $this->insertNew($entity);
    }

    /**
     * Recount rating on product by id product
     * @param integer $idProduct
     */
    public function recountRating($idProduct)
    {
        $ratings = $this->gEMProductRating()->findBy(['product' => $idProduct, 'approved' => 1]);
        $product = $this->gEMProduct()->find($idProduct);

        $count = 0;
        $sum = 0;
        foreach ($ratings as $rating) {
            $count++;
            $sum += $rating->rating;
        }
        if ($count != 0) {
            $product->setTotalRating(round(($sum / $count), 2));
            $product->setSumRating($count);
            $this->save();
        }
    }

    /**
     * Manually add ProductDiscussion
     * @param array $values array from POST
     * @param integer $customer id of Customer
     */
    public function addDiscussion($values, $customerId)
    {
        $entity = new ProductDiscussion($values);
        $entity->setProduct($this->gEMProduct()->find($values->product));
        $entity->setCustomer($this->gEMCustomer()->find($customerId));
        $this->insertNew($entity);
    }

    public function recreateImages()
    {
        $images = $this->gEMProductImage()->findBy(['recreated' => null], ['id' => 'DESC']);

        foreach ($images as $img) {
            if (!file_exists($img->path)) {
                continue;
            }

            $folder = substr($img->path, 0, strrpos($img->path, '/'));
            $this->imgEditor->setInputDir($folder);
            $this->imgEditor->setOutputDir($folder);

            $tmp = $img->product->id . '_thumb_O_' . str_replace(".", "", microtime(true));

            $name = substr($img->path, strrpos($img->path, '/'));
            $name = str_replace('/', '', $name);
            $image = $this->imgEditor->loadImage($name);

            $image->autoRotate();
            $image->resize(500, 336, 'supplement');
            $image->save($tmp, false);
            $tmpThumb = $folder . '/' . $tmp . '.' . $image->extension();

            // Odeberu starý náhledový obrázek
            if ($img->pathThumb) {
                if (file_exists($img->pathThumb)) {
                    unlink($img->pathThumb);
                }
            }

            // kontrola na produktu, zda tam není zapsán jako main
            $product = $img->product;
            if ($product->mainImage == $img->path) {
                $product->setMainImage($tmpThumb);
            }
            if ($product->mainImage == $img->pathThumb) {
                $product->setMainImage($tmpThumb);
            }
            $img->setPathThumb($tmpThumb);
            $img->setRecreated(true);
            $this->save();

            dump($product->id);
        }
    }

    public function recreateCategoryImages()
    {
        $images = $this->gEMProductImage()->findBy(['recreated' => null], ['id' => 'DESC']);

        foreach ($images as $img) {
            if (!file_exists($img->path)) {
                continue;
            }

            $folder = substr($img->path, 0, strrpos($img->path, '/'));
            $this->imgEditor->setInputDir($folder);
            $this->imgEditor->setOutputDir($folder);

            $tmp = $img->product->id . '_thumb_O_' . str_replace(".", "", microtime(true));

            $name = substr($img->path, strrpos($img->path, '/'));
            $name = str_replace('/', '', $name);
            $image = $this->imgEditor->loadImage($name);

            $image->autoRotate();
            $image->resize(500, 336, 'supplement');
            $image->save($tmp, false);
            $tmpThumb = $folder . '/' . $tmp . '.' . $image->extension();

            // Odeberu starý náhledový obrázek
            if ($img->pathThumb) {
                if (file_exists($img->pathThumb)) {
                    unlink($img->pathThumb);
                }
            }

            // kontrola na produktu, zda tam není zapsán jako main
            $product = $img->product;
            if ($product->mainImage == $img->path) {
                $product->setMainImage($tmpThumb);
            }
            if ($product->mainImage == $img->pathThumb) {
                $product->setMainImage($tmpThumb);
            }
            $img->setPathThumb($tmpThumb);
            $img->setRecreated(true);
            $this->save();

            //dump($product->id);
        }
    }

    public function getProductStockAlerts()
    {
        $rsm = new ResultSetMappingBuilder(
            $this->getEm(), ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT
        );
        $rsm->addRootEntityFromClassMetadata(Product::class, 'p');

        $query = $this->getEm()->createNativeQuery("
            SELECT " . $rsm->generateSelectClause() . "
             FROM product p
            WHERE p.min_count > 0 AND p.min_count > p.count and p.active = 1
          ", $rsm);
        return $products = $query->getResult();
    }

    public function getCashFlowByTime($dateStart, $dateEnd)
    {
        $dateStart = date_create_from_format('d. m. Y', $dateStart);
        $dateEnd = date_create_from_format('d. m. Y', $dateEnd);

        $incomes = $this->gEMOrderRefund()->findBy(['foundedDate >=' => $dateStart, 'foundedDate <=' => $dateEnd],
            ['foundedDate' => 'ASC']);
        $costs = $this->gEMProductOperation()->findBy(['foundedDate >=' => $dateStart, 'foundedDate <=' => $dateEnd],
            ['foundedDate' => 'ASC']);
        return [$incomes, $costs];
    }

    public function getProfitByTime($dateStart, $dateEnd)
    {
        $dateStart = date_create_from_format('d. m. Y', $dateStart);
        $dateEnd = date_create_from_format('d. m. Y', $dateEnd);

        $orders = $this->gEMOrders()->findBy([
            'foundedDate >=' => $dateStart,
            'foundedDate <=' => $dateEnd,
            'orderState.storno !=' => 1
        ], ['foundedDate' => 'ASC']);

        $arr = [];
        foreach ($orders as $itm) {
            $arr[] = $itm->id;
        }
        $products = $this->gEMProductInOrder()->findBy(['orders =' => $arr]);

        return ['orders' => $orders, 'products' => $products];
    }

    public function addColorItem($idProduct, $idColor, $color, $colorText)
    {
        bdump($color);
        $product = $this->get()->find($idProduct);
        $productColor = $this->get()->find($idColor);
        if ($product && $productColor) {
            $entity = new ProductColorItems();
            $entity->setProduct($product);
            $entity->setColorVariant($productColor);
            $this->em->persist($entity);
            if (!empty($color)) {
                $productColor->setColor($color);
            }
            if (!empty($colorText)) {
                $productColor->setColorText($colorText);
            }
            $productColor->setIsColorVariant(true);
            $this->save();
            return true;
        }
        return false;
    }

    public function deleteColorItem($id)
    {
        $entity = $this->getEm()->getRepository(ProductColorItems::class)->find($id);
        if ($entity) {
            $entity->colorVariant->setIsColorVariant(false);
            $this->getEm()->remove($entity);
            $this->save();
            return true;
        }
        return false;
    }

    public function updatePackageItem($id, $value)
    {
        $package = $this->gEMProductPackageItems()->find($id);
        if ($package) {
            $discountCZK = intval($value['CZK']);
            $discountEUR = intval($value['EUR']);
            $package->setDiscountCZK($discountCZK ? $discountCZK : null);
            $package->setDiscountEUR($discountEUR ? $discountEUR : null);
            $this->save();
            return true;
        }

        return false;
    }

    public function addGift($idProduct, $idGift)
    {
        try {
            $rank = 1;
            $giftRank = $this->gEMProductGifts()->findOneBy(['product' => $idProduct], ['rank' => 'DESC']);
            if ($giftRank) {
                $rank = $giftRank->rank + 1;
            }
            $product = $this->get()->find($idProduct);
            $gift = $this->get()->find($idGift);
            if ($product && $gift) {
                $entity = new ProductGifts();
                $entity->setProduct($product);
                $entity->setGift($gift);
                $entity->setRank($rank);
                $this->insertNew($entity);
                return true;
            }
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return false;
    }

    public function deleteGift($id)
    {
        try {
            $entity = $this->getEm()->getRepository(ProductGifts::class)->find($id);
            if ($entity) {
                $this->getEm()->remove($entity);
                $this->getEm()->flush();
                return true;
            }
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return false;
    }

    public function updateGiftRank($id, $value)
    {
        try {
            $gift = $this->gEMProductGifts()->find($id);
            if ($gift) {
                $gift->setRank(intval($value));
                $this->save();
                return true;
            }
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return false;
    }

    public function removeArticle($article)
    {
        try {
            $article = $this->gEMProductArticle()->find($article);
            if (file_exists($article->image)) {
                @unlink($article->image);
            }
            if (file_exists($article->video)) {
                @unlink($article->video);
            }
            $articlesPath = 'product-articles/'.$article->id.'/';
            @rmdir($articlesPath);
            $this->remove($article);

            return true;
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return false;
    }

    public function deleteArticleImg($articleId)
    {
        $article = $this->gEMProductArticle()->find($articleId);

        if ($article) {
            if (file_exists($article->image)) {
                @unlink($article->image);
            }
            $article->setImage(null);
            $this->getEm()->flush($article);

            $articlesPath = 'product-articles/'.$article->id.'/';
            @rmdir($articlesPath);
        }
    }

    public function deleteArticleVideo($articleId)
    {
        $article = $this->gEMProductArticle()->find($articleId);

        if ($article) {
            if (file_exists($article->video)) {
                @unlink($article->video);
            }
            $article->setVideo(null);
            $this->getEm()->flush($article);

            $articlesPath = 'product-articles/'.$article->id.'/';
            @rmdir($articlesPath);
        }
    }
}
