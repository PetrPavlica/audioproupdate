<?php

namespace Front\Model\Facade;

use App\Core\Model\BaseFacade;
use Intra\Model\Database\Entity\BannerProduct;
use Intra\Model\Database\Entity\ProductMark;
use Intra\Model\Database\Entity\SpecialOfferProduct;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;
use App\Core\Model\Database\Utils\SQLHelper;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\Product;
use Kdyby\Doctrine\Mapping\ResultSetMappingBuilder;
use Nette\Utils\DateTime;

class SearchFacade extends BaseFacade
{

    /** @var IStorage */
    private $storage;

    /** @var Cache */
    private $cache;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, IStorage $storage)
    {
        parent::__construct($em);
        $this->storage = $storage;
        $this->cache = new Cache($this->storage);
    }

    /*public function getCategoryFilters($category, $productsID)
    {
        $key = 'getCategoryFilters-' . $category->id;
        $arr = $this->storage->read($key);
        if ($arr == null) { // if null, create and cash
            $arr = [];
            // Prepare hide for some filter groups
            $filters = $this->gEMGroupProductFilter()->findBy([
                'category' => $category->mainCategory->id,
                'active' => '1'
            ], ['orderState' => 'ASC']);
            foreach ($filters as $f) {
                $arr[ $f->id ] = $f->toArray();
                $arr[ $f->id ][ 'filters' ] = [];
                $filter = $this->gEMProductFilter()->findBy(['filterGroup' => $f->id], ['orderState' => 'ASC']);
                foreach ($filter as $fil) {
                    $arr[ $f->id ][ 'filters' ][ $fil->id ] = $fil->toArray();
                }
            }
            $this->storage->write($key, $arr, [
                Cache::TAGS => ["category/$category->id"],
            ]);
        }
        return $arr;
    }*/

    public function getCategoryFilters($category, $productsID, $locale)
    {
        $ids = [];
        if ($productsID) {
            foreach ($productsID as $p) {
                if ($p['id']) {
                    $ids[] = $p['id'];
                }
            }
        }
        $key = 'getCategoryFilters-' . $category->id.'-'.$locale;
        $arr = $this->storage->read($key);
        if ($arr == null) { // if null, create and cash
            $arr = [];

            if (count($ids)) {
                $query2 = $this->getEm()->getConnection()->prepare("
                SELECT f.filter_id
                FROM product_in_filter f
                WHERE f.product_id in (" . implode(',', $ids) . ")
                GROUP BY f.filter_id
            ");

                $query2->execute();
                $values = $query2->fetchAll();
            }
            $filterIDS = [];
            if (isset($values)) {
                foreach($values as $v) {
                    $filterIDS[] = $v['filter_id'];
                }
            }

            $filters = $this->gEMGroupProductFilter()->findBy([
                'active' => '1',
            ], ['orderState' => 'ASC']);
            foreach ($filters as $f) {
                $arr[$f->id] = $f->toArray();
                $arr[$f->id]['filters'] = [];
                $filter = $this->gEMProductFilter()->findBy(['filterGroup' => $f->id], ['orderState' => 'ASC', 'name' => 'ASC']);
                foreach ($filter as $fil) {
                    if (!in_array($fil->id, $filterIDS)) {
                        continue;
                    }
                    $filterArr = $fil->toArray();
                    $query2 = $this->getEm()->getConnection()->prepare("
                        SELECT COUNT(*) as count
                        FROM product_in_filter f
                        WHERE f.filter_id = ".$fil->id." and f.product_id in (" . implode(',', $ids) . ")
                        GROUP BY f.filter_id
                    ");

                    $query2->execute();
                    $count = $query2->fetch();
                    $filterArr['productCount'] = $count['count']; //$this->gEMProductInFilter()->countBy(['filter' => $fil->id, 'product.id' => 'IN ('.implode(',', $ids).')']);
                    if ($fil->slider) {
                        $query2 = $this->getEm()->getConnection()->prepare("
                            SELECT f.filter_id, f.value
                            FROM product_in_filter f
                            WHERE f.filter_id = ".$fil->id." and f.product_id in (" . implode(',', $ids) . ")
                            GROUP BY f.value
                        ");

                        $query2->execute();
                        $values = $query2->fetchAll();
                        if ($values) {
                            $filterArr['values'] = [];
                            foreach($values as $v) {
                                $filterArr['values'][] = $v['value'];
                            }
                        }
                    }
                    $arr[$f->id]['filters'][$fil->id] = $filterArr;
                }
            }
            foreach($arr as $k => $f) {
                if (count($f['filters']) == 0) {
                    unset($arr[$k]);
                }
            }
            $this->storage->write($key, $arr, [
                Cache::TAGS => ["category/$category->id"],
                Cache::PRIORITY => 10
            ]);
        }

        return $arr;
    }

    public function getFiltersSql($filters)
    {
        //Připravení filtrů - checkboxy
        $filterSql = "";
        $filterArr = [];
        if (isset($filters[ 'filterCheck' ])) { // Prvně si je přerovnám dle skupin.
            foreach ($filters[ 'filterCheck' ] as $filerId => $groupId) {
                $filterArr[ $groupId ][] = $filerId;
            }
        }
        if (isset($filters['filterSlider'])) {
            foreach($filters['filterSlider'] as $id => $arr) {
                if (is_array($arr)) {
                    foreach ($arr as $v) {
                        $filterArr['slider'][] = ['filter_id' => $id, 'value' => $v];
                    }
                }
            }
        }
        $i = 1;
        foreach ($filterArr as $groupId => $item) {
            $filterSql .= " JOIN product_in_filter f$i ON f$i.product_id = p.id AND ( ";
            foreach ($item as $filt) {
                if ($groupId == 'slider') {
                    $filterSql .= " (f$i.filter_id = " . intval($filt['filter_id']) . " AND f$i.value = " . $filt['value'] . ") OR ";
                } else {
                    $filterSql .= " f$i.filter_id = " . intval($filt) . " OR ";
                }
            }
            $filterSql = substr($filterSql, 0, -3) . ") ";
            $i++;
        }

        //Připravení filtrů - range šoupátka
        if (isset($filters[ 'filterMinRange' ]) && $filters[ 'filterMinRange' ] != "") {
            foreach ($filters[ 'filterMinRange' ] as $filerId => $value) {
                if ($value == "") {
                    continue;
                }
                if ($filters[ 'filterMinRange' ][ $filerId ] == "1") { // Interval na produktech
                    $filterSql .= " JOIN product_in_filter f$i ON f$i.product_id = p.id AND f$i.filter_id = " . intval($filerId) . " AND f$i.value >= " . intval($value) . " AND f$i.value_max <= " . intval($filters[ 'filterMaxRange' ][ $filerId ]) . " ";
                } else {
                    $filterSql .= " JOIN product_in_filter f$i ON f$i.product_id = p.id AND f$i.filter_id = " . intval($filerId) . " AND f$i.value >= " . intval($value) . " AND f$i.value <= " . intval($filters[ 'filterMaxRange' ][ $filerId ]) . " ";
                }
                $i++;
            }
        }
        // dump($filters);
        //echo $filterSql;
        // die;
        return $filterSql;
    }

    public function getProductsListCount($type, $filters)
    {
        // Select počtu záznamů pro paginátor
        $query2 = $this->getEm()->getConnection()->prepare("
            SELECT DISTINCT p.id
             FROM product p
            WHERE                
                p.active = 1 AND p.is_color_variant = 0 AND p.type = :type
                " . (isset($filters[ 'multiroom' ]) ? " AND p.audio_pro_multiroom = 1 " : "") . "
                " . (isset($filters[ 'battery' ]) ? " AND p.battery_powered = 1 " : "") . "
                " . (isset($filters[ 'chromecast' ]) ? " AND p.chromecast = 1 " : "") . "
                " . (isset($filters[ 'bt4' ]) ? " AND p.bt4 = 1 " : "") . "
                " . (isset($filters[ 'bt5' ]) ? " AND p.bt5 = 1 " : "") . "
                " . (isset($filters[ 'heyGoogle' ]) ? " AND p.hey_google = 1 " : "") . "
                " . (isset($filters[ 'airPlay' ]) ? " AND p.air_play = 1 " : "") . "
            GROUP BY p.id");
        $query2->bindValue('type', $type);
        $query2->execute();
        $result = $query2->fetchAll();

        $arr = [];

        if ($result) {
            foreach ($result as $r) {
                $arr[] = $r['id'];
            }
        }

        return $arr;
    }

    public function getProductsList($ids, $filters, $limit, $offset)
    {
        $id = "";
        foreach ($ids as $item) {
            $id .= $item[ 'id' ] . ',';
        }
        $id = substr($id, 0, -1);
        if ($id == '') {
            return [];
        }

        $orderBy = " ORDER BY p.id DESC ";
        if (isset($filters[ 'sort' ])) {
            switch ($filters[ 'sort' ]) {
                case 'top':
                    $orderBy = " ORDER BY p.is_top DESC ";
                    break;
                case 'mostSell':
                    $orderBy = " ORDER BY IF(p.our_sorting_most_sold = '1', count_of_sell_for_search, count_of_sell) DESC ";
                    break;
                case 'chiep':
                    $orderBy = " ORDER BY p.seling_price ASC ";
                    break;
                case 'ritch':
                    $orderBy = " ORDER BY p.seling_price DESC ";
                    break;
                case 'bestRank':
                    $orderBy = " ORDER BY p.total_rating DESC ";
                    break;
            }
        }
        $rsm = new ResultSetMappingBuilder(
            $this->getEm(), ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT
        );
        $rsm->addRootEntityFromClassMetadata(Product::class, 'p');

        $query = $this->getEm()->createNativeQuery("
            SELECT " . $rsm->generateSelectClause() . "
             FROM product p
            WHERE p.id in($id)
            $orderBy
            LIMIT :limit OFFSET :offset
          ", $rsm);
        $params = [
            'limit' => intval($limit),
            'offset' => intval($offset)
        ];

        $query->setparameters($params);
        $products = $query->getResult();

        return $products;
    }


    public function getRelevantProducts($searched, $locale)
    {
        $categories = [];
        $products = [];

        foreach ($searched as $product) {

            array_push($products, $product->id);

            if ($product->categories) {
                foreach($product->categories as $c) {
                    if (!in_array($c->id, $categories)) {
                        array_push($categories, $c->id);
                    }
                }
            }

        }

        $rsm = new ResultSetMappingBuilder(
            $this->getEm(), ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT
        );
        $rsm->addRootEntityFromClassMetadata(Product::class, 'p');

        $searchedProducts = [];

        for ($i = 0; $i < 3; $i++) {
            $query = $this->getEm()->createNativeQuery("
            SELECT " . $rsm->generateSelectClause() . "
             FROM product p JOIN ( SELECT id FROM
                ( SELECT y.id, c.category_id
                    FROM (SELECT :min + (:max - :min + 1 - 50) * RAND() AS start FROM DUAL) AS init
                    JOIN product y
                    LEFT JOIN product_in_category c ON y.id = c.product_id
                    WHERE y.id > init.start AND y.id NOT IN(:product) and y.active = 1 and y.sale_terminated = 0
                    " . (count($categories) ? "AND c.category_id IN(:category)" : "") . "
                    ORDER BY y.id
                    LIMIT 50    
                ) z ORDER BY RAND()
               LIMIT 3 
             ) r ON p.id = r.id
             ;
          ", $rsm);

            $params = [
                'min' => 0,
                'max' => 700,
                'product' => join(",", $products),
                'category' => join(",", $categories)
            ];

            $query->setparameters($params);

            $searchedProducts = $query->getResult();

            if (!empty($searchedProducts)) {
                break;
            }
        }

        return $searchedProducts;
    }

    public function getOurTipProducts($category, $locale)
    {
        $searchedProducts = [];
        if ($category) {
            $categories = [$category->id];
            if ($category->childCategory) {
                foreach($category->childCategory as $c) {
                    $categories[] = $c->id;
                }
            }

            $rsm = new ResultSetMappingBuilder(
                $this->getEm(), ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT
            );
            $rsm->addRootEntityFromClassMetadata(Product::class, 'p');

                $query = $this->getEm()->createNativeQuery("
            SELECT " . $rsm->generateSelectClause() . "
             FROM product p
             LEFT JOIN product_in_category c ON p.id = c.product_id
             WHERE p.active = 1 and p.sale_terminated = 0 and p.our_tip = 1
             " . (count($categories) ? "AND c.category_id IN(:category)" : "") . "
             ORDER BY RAND()
             LIMIT 2
               ", $rsm);

            $params = [
                'category' => $categories
            ];

                $query->setparameters($params);

                $searchedProducts = $query->getResult();
        }

        return $searchedProducts;
    }

    /**
     * Get all ids of category of products by parent category, result is cashing
     * @param ProductCategory $category entity
     * @return string of ids
     */
    public function getAllIdsCategories($category)
    {
        $key = 'getAllIdsCategories-' . $category->id;
        $output = $this->storage->read($key);
        if ($output == null) { // if null, create and cash
            $output = $this->getCategoryId($category);
            $this->storage->write($key, $output, [
                Cache::TAGS => ["category/$category->id"],
            ]);
        }
        return $output;
    }

    private function getCategoryId($category, $output = "")
    {
        $output .= $category->id . ', ';
        foreach ($category->childCategory as $child) {
            $output = $this->getCategoryId($child, $output);
        }
        return $output;
    }

    /**
     * Vrátí nejprodávanější produkty seřazené dle počtu prodání
     * @param integer $limit
     * @return array of Product entity
     */
    public function getMostSoldProducts($limit = 30)
    {
        if (!is_numeric($limit)) {
            return null;
        }
        $rsm = new ResultSetMappingBuilder(
            $this->getEm(), ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT
        );
        $rsm->addRootEntityFromClassMetadata(Product::class, 'p');

        $query = $this->getEm()->createNativeQuery("
            SELECT " . $rsm->generateSelectClause() . "
            FROM product p
            WHERE p.active = 1
            ORDER BY IF(p.our_sorting_most_sold = '1', count_of_sell_for_search, count_of_sell) DESC
            LIMIT $limit
            ", $rsm);
        $products = $query->getResult();
        return $products;
    }

    /**
     * Find discussions ids by parameters for detail product
     * @param integer $id
     * @param string $searchTerm
     * @return array
     */
    public function findIdsDiscussions($id, $searchTerm)
    {
        $query2 = $this->getEm()->getConnection()->prepare("
            SELECT pd.id
            FROM product_discussion pd
            JOIN product_discussion child ON pd.id = child.parent_id
            WHERE (pd.text like :searchTerm or child.text like :searchTerm) AND pd.product_id = :product
			GROUP BY pd.id
		");
        $query2->bindValue('searchTerm', '%' . $searchTerm . '%');
        $query2->bindValue('product', $id);

        $query2->execute();
        $ids = $query2->fetchAll();
        return $ids;
    }

    /**
     * Get discussions by array ids
     * @param integer $ids of discussions
     * @param integer $limit
     * @param integer $offset
     * @return array
     */
    public function getDiscussions($ids, $limit, $offset)
    {
        $arr = [];
        foreach ($ids as $id) {
            $arr[] = $id[ 'id' ];
        }

        return $this->gEMProductDiscussion()->findBy(['id' => $arr], ['foundedDate' => 'DESC'], $limit, $offset);
    }

    /**
     * Zjistí nejvyšší a nejnižší cenu u daných produtků dle kategorie
     * @param $category
     * @return array['low' => 0, 'high' => 0]
     */
    public function getTopPrices($category)
    {
        $key = 'getTopPrices-';
        $keyId = 0;
        if ($category !== 0) {
            $keyId = $category->id;
        }
        $result = $this->storage->read($key . $keyId);
        if ($result == null) { // if null, create and cash
            $low = $high = null;
            $ids = null;
            if ($category !== 0) {
                $ids = $this->getAllIdsCategories($category);
                $ids = substr($ids, 0, -2);
            }

            // Select počtu záznamů pro paginátor - bez akcí
            $query2 = $this->getEm()->getConnection()->prepare("
                SELECT MAX(p.seling_price) mx, MIN(p.seling_price) mn
                 FROM product p
                 JOIN product_in_category c ON p.id = c.product_id
                WHERE
				" . (($ids !== null) ? " c.category_id in($ids) " : " 1 = 1 ") . "
            ");

            $query2->execute();
            $values = $query2->fetch();

            // Select počtu záznamů pro paginátor - akce - občas mají jiné range než klasické produkty
            $query3 = $this->getEm()->getConnection()->prepare("
                SELECT MAX(pa.seling_price) mx, MIN(pa.seling_price) mn
                  FROM product p
                  LEFT JOIN product_in_category c ON p.id = c.product_id
                  JOIN product_action pa ON p.active_action_id = pa.id
                WHERE
				" . (($ids !== null) ? " c.category_id in($ids) " : " 1 = 1 ") . "
            ");

            $query3->execute();
            $values2 = $query3->fetch();

            if (isset($values[ 'mn' ])) {
                $low = $values[ 'mn' ] - 5;
            } else {
                $low = 0;
            }

            if (isset($values2[ 'mn' ]) && $values2[ 'mn' ] < $low) {
                $low = $values2[ 'mn' ] - 5;
            }

            if (isset($values[ 'mx' ])) {
                $high = $values[ 'mx' ] + 5;
            } else {
                $high = 30000;
            }

            if (isset($values2[ 'mx' ]) && $values2[ 'mx' ] > $high) {
                $low = $values2[ 'mx' ] + 5;
            }

            if ($low < 0) {
                $low = 0;
            }

            $result = ['low' => $low, 'high' => $high];
            $this->storage->write($key.$keyId, $result, [
                Cache::TAGS => ["products", "category/$keyId"],
            ]);
        }
        return $result;
    }

    /**
     * Zjistí nejvyšší a nejnižší cenu u daných produtků dle kategorie
     * @param $category
     * @return array['low' => 0, 'high' => 0]
     */
    public function getTopPricesProducts($productIds)
    {
        $low = $high = null;

        $ids = null;

        if ($productIds) {
            $ids = [];
            foreach ($productIds as $p) {
                $ids[] = $p['id'];
            }
        }

        // Select počtu záznamů pro paginátor - bez akcí
        $query2 = $this->getEm()->getConnection()->prepare("
                SELECT MAX(p.seling_price) mx, MIN(p.seling_price) mn
                 FROM product p
                WHERE
				" . (($ids !== null) ? " p.id in(".implode(',', $ids).") " : " 1 = 1 ") . "
            ");

        $query2->execute();
        $values = $query2->fetch();

        // Select počtu záznamů pro paginátor - akce - občas mají jiné range než klasické produkty
        $query3 = $this->getEm()->getConnection()->prepare("
                SELECT MAX(pa.seling_price) mx, MIN(pa.seling_price) mn
                  FROM product p
                  JOIN product_action pa ON p.active_action_id = pa.id
                WHERE
				" . (($ids !== null) ? " p.id in(".implode(',', $ids).") " : " 1 = 1 ") . "
            ");

        $query3->execute();
        $values2 = $query3->fetch();

        if (isset($values['mn'])) {
            $low = $values['mn'] - 5;
        } else {
            $low = 0;
        }

        if (isset($values2['mn']) && $values2['mn'] < $low) {
            $low = $values2['mn'] - 5;
        }

        if (isset($values['mx'])) {
            $high = $values['mx'] + 5;
        } else {
            $high = 30000;
        }

        if (isset($values2['mx']) && $values2['mx'] > $high) {
            $low = $values2['mx'] + 5;
        }

        if ($low < 0) {
            $low = 0;
        }

        $result = ['low' => $low, 'high' => $high];

        return $result;
    }

    public function findLastVisited($arr, $locale)
    {
        $qb = $this->getEm()->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->where('p.active = 1 and p.id IN ('.implode(',', $arr).')');

        $products = $qb->getQuery()->getResult();
        $arr = [];
        if ($products) {
            foreach ($products as $p) {
                $arr[$p->id] = $p;
            }
        }
        return $arr;
    }

    /**
     * Get one of actual special offer, if exist more then 1, return random one
     * @return SpecialOfferProduct
     */
    public function getActualRandomSpecialOffer($locale)
    {
        $qb = $this->getEm()->createQueryBuilder()
            ->select('sop')
            ->from(SpecialOfferProduct::class, 'sop')
            ->join('sop.product', 'p')
            ->where('sop.active = 1 and p.saleTerminated = 0 and sop.timeFrom <= :timeFrom and sop.timeTo >= :timeTo');

        $qb->setParameters(['timeFrom' => new DateTime(), 'timeTo' => new DateTime()]);
        $offers = $qb->getQuery()->getResult();

        if ($offers) { // pokud existují, tak vrať náhodný
            $count = count($offers);
            return $offers[ rand(0, $count - 1) ];
        }
        return null;
    }

    /**
     * Return all active banners
     * @return array of BannerProduct
     */
    public function getBanners($locale, $isMobile)
    {
        $qb = $this->getEm()->createQueryBuilder()
            ->select('bp')
            ->from(BannerProduct::class, 'bp')
            ->where('bp.active = 1 and bp.onFront = 0 and bp.type = 1');
        $qb->orderBy('bp.orderBanner', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findProducts($term, $locale)
    {
        $columns = ['name', 'publicName'];
        $alias = 'p';
        $like = SQLHelper::termToLikeAnd($term, $alias, $columns);
        $like = str_replace('p.publicName', 'pm.publicName', $like);

        $qb = $this->em->getRepository(Product::class)
            ->createQueryBuilder($alias)
            ->leftJoin(ProductMark::class, 'pm', 'WITH', 'p.productMark = pm.id')
            ->where($like)
            ->andWhere('p.active = 1');

            //->andWhere('p.saleTerminated = 0')
        $qb->setMaxResults('12');

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function getDataForCompare($categories, $products, $locale)
    {
        $data = [];
        $productsIDS = [];
        if ($products) {
            foreach ($products as $p) {
                $productsIDS[] = $p->id;
            }
        }
        foreach ($categories as $category) {
            $tmp = $this->getCategoryFilters($category, $productsIDS, $locale);
            $data[ $category->id ] = $tmp;
            //foreach ($tmp as $t)
            //    $dataIds[$category->id][] = $t['id'];
        }
        //barDump($data);
        // barDump($dataIds);
        $filters = [];
        foreach ($products as $product) {
            $tmp = $this->gEMProductInFilter()->findBy(['product' => $product->id]);
            foreach ($tmp as $t) {
                $filters[ $product->id ][ $t->filter->id ] = $t;
            }
            #barDump($filters[ $product->id ]);
        }
        return ['data' => $data, 'filters' => $filters];
    }

    public function getLastBuyProducts()
    {
        $key = 'getLastBuyProducts';
        $output = $this->cache->load($key);

        if ($output == null) { // if null, create and cash

            $query2 = $this->getEm()->getConnection()->prepare("
               SELECT pio.id as id, pio.product_id, pio.orders_id FROM product_in_order pio JOIN orders ord ON pio.orders_id = ord.id JOIN order_state ords ON ord.order_state_id = ords.id JOIN product p ON pio.product_id = p.id WHERE ords.include_in_last_buy = 1 GROUP BY pio.orders_id ORDER BY pio.orders_id DESC, pio.id DESC LIMIT 4
            ");

            $query2->execute();
            $ids = $query2->fetchAll();

            $arr = [];
            foreach ($ids as $id) {
                $arr[] = $id[ "id" ];
            }
            $output = $arr;
            $this->cache->save($key, $output, [
                Cache::TAGS => ["orders"],
                Cache::EXPIRE => '+1 hour'
            ]);
        }

        $productInOrder = $this->gEMProductInOrder()->findBy(['id' => $output]);
        return $productInOrder;
    }

    public function getProducersArr($category, $locale)
    {
        $ids = "";
        if ($category !== 0) {
            $ids = $this->getAllIdsCategories($category);
            $ids = substr($ids, 0, -2);
        } else {
            return [];
        }
        $query2 = $this->getEm()->getConnection()->prepare("
                SELECT mark.id, mark.public_name
                FROM product_mark mark 
                JOIN product p ON p.product_mark_id = mark.id or p.product_mark_set_id = mark.id
                LEFT JOIN product_in_category c ON p.id = c.product_id
                WHERE c.category_id in ($ids) and p.active = 1
                GROUP BY mark.id
                ORDER BY mark.public_name
		    ");
        $query2->execute();
        $res = $query2->fetchAll();
        $arr = [];
        foreach ($res as $itm) {
            $arr[ $itm[ 'id' ] ] = $itm[ 'public_name' ];
        }
        return $arr;

    }

}
