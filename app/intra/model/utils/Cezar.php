<?php

namespace Intra\Model\Utils;

use Intra\Model\Database\Entity\NewsletterEmail;
use Intra\Model\Database\Entity\WebMenu;
use Kdyby\Doctrine\EntityManager;
use Nette\Database\Context;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;
use Intra\Model\Database\Entity\Product;
use Intra\Model\Database\Entity\ProductMark;
use Intra\Model\Database\Entity\ProductProducer;
use Intra\Model\Database\Entity\ProductCategory;
use Intra\Model\Database\Entity\Customer;
use Intra\Model\Database\Entity\Currency;
use Nette\Utils\Strings;

class Cezar
{
    /** @var string */
    private $csvData = null;

    /** @var string */
    private $csvHeader = null;

    /** @var string */
    private $defaultEncoding = "windows-1250";

    /** @var array */
    private $productSlug = [];

    /** @var EntityManager */
    protected $em;

    /** @var Context */
    public $db;

    /** @var IStorage */
    private $storage;

    /** @var Cache */
    private $cache;

    /** @var string */
    private $path = 'import-export/';

    /**
     * Construct
     * @param EntityManager $em
     * @param Context $db
     * @param IStorage $storage
     */
    public function __construct(EntityManager $em, Context $db, IStorage $storage)
    {
        $this->em = $em;
        $this->db = $db;
        $this->storage = $storage;
        $this->cache = new Cache($this->storage);
    }

    private function loadCsv($file)
    {
        if ($file !== null && file_exists($file)) {
            $data = explode("\r\n", trim(iconv($this->defaultEncoding, "UTF-8", file_get_contents($file))));
            $this->csvHeader = $data[0];
            unset($data[0]);
            $this->csvData = implode("\r\n", $data);
            return true;
        } else {
            return false;
        }
    }

    private function findParentCategory($code)
    {
        $codeTmp = explode('.', $code);
        if (count($codeTmp) > 1) {
            unset($codeTmp[count($codeTmp) - 1]);
            $code = implode('.', $codeTmp);
            $category = $this->em->getRepository(ProductCategory::class)->findOneBy(['origCode' => $code]);
            if ($category) {
                return $category;
            }
        } else {
            $category = $this->em->getRepository(ProductCategory::class)->find(1);
            if ($category) {
                return $category;
            }
        }
        return null;
    }

    private function findMainCategory($code)
    {
        if (stripos($code, '.') !== false) {
            $code = substr($code, 0, stripos($code, '.'));
            $category = $this->em->getRepository(ProductCategory::class)->findOneBy(['origCode' => $code]);
            if ($category) {
                return $category;
            }
        } else {
            $category = $this->em->getRepository(ProductCategory::class)->find(1);
            if ($category) {
                return $category;
            }
        }
        return null;
    }

    private function fixValues($values)
    {
        return array_filter($values, function ($value) {
            return trim($value) !== '';
        });
    }

    public function category()
    {
        $file = $this->path.'sortimenty.csv';
        if (file_exists($file)) {
            if ($this->loadCsv($file)) {
                $lines = explode("\r\n", $this->csvData);
                foreach ($lines as $l) {
                    $categoryArr = $this->fixValues(explode("\t", $l));
                    $category = $this->em->getRepository(ProductCategory::class)->findOneBy(['origCode' => $categoryArr[0]]);
                    if (!$category) {
                        $parentCategory = $this->findParentCategory($categoryArr[0]);
                        $mainCategory = $this->findMainCategory($categoryArr[0]);
                        $categoryData = [
                            'name' => $categoryArr[1],
                            'parentCategory' => $parentCategory,
                            'mainCategory' => $mainCategory,
                            'active' => $categoryArr[2],
                            'origCode' => $categoryArr[0]
                        ];
                        $category = new ProductCategory($categoryData);
                        $this->em->persist($category);
                    } else {
                        $category->name = $categoryArr[1];
                        $category->active = $categoryArr[2];
                    }
                    $this->em->flush();
                }
                //$this->db->query('UPDATE product_category SET main_category_id = id WHERE main_category_id IS NULL');
                //unlink($file);
                for ($i = 0; $i < 10; $i++) {
                    $this->storage->clean([
                        Cache::TAGS => ["categories"],
                    ]);
                    $this->cache->clean([
                        Cache::TAGS => ["categories"],
                    ]);
                }
                return true;
            }
        }
        return false;
    }

    public function producer()
    {
        $file = $this->path.'vyrobci.csv';
        $i = 0;
        if (file_exists($file)) {
            if ($this->loadCsv($file)) {
                $lines = explode("\r\n", $this->csvData);
                foreach ($lines as $k => $l) {
                    $producerArr = $this->fixValues(explode("\t", $l));
                    if (!isset($producerArr[0])) {
                        continue;
                    }
                    $producer = $this->em->getRepository(ProductProducer::class)->findOneBy(['code' => $producerArr[0]]);
                    if ($producer) {
                        $producer->company = $producerArr[1];
                        //$producer->active = $producerArr[2];
                        $producer->code = $producerArr[0];
                    } else {
                        $producerData = [
                            'company' => $producerArr[1],
                            'code' => $producerArr[0],
                            'isCompany' => 1,
                            'payVat' => 1,
                            'active' => $producerArr[2]
                        ];
                        $producer = new ProductProducer($producerData);
                        $this->em->persist($producer);
                    }
                    $i++;
                    if ($i == 250) {
                        $i = 0;
                        $this->em->flush();
                        $this->em->clear();
                    }
                }
                if ($i) {
                    $this->em->flush();
                    $this->em->clear();
                }
                $producersArr = [];
                $producers = $this->em->getRepository(ProductProducer::class)->createQueryBuilder('p')->getQuery()->getResult();
                foreach ($producers as $p) {
                    $producersArr[$p->code] = $p;
                }
                unset($producers);
                $i = 0;
                foreach ($producersArr as $p) {
                    $mark = $this->em->getRepository(ProductMark::class)->findOneBy(['producer' => $p->id]);
                    if ($mark) {
                        $mark->publicName = $p->company;
                    } else {
                        $markArr = [
                            'producer' => $p,
                            'publicName' => $p->company
                        ];
                        $mark = new ProductMark($markArr);
                        $this->em->persist($mark);
                    }
                    $i++;
                    if ($i == 250) {
                        $i = 0;
                        $this->em->flush();
                    }
                }
                if ($i) {
                    $this->em->flush();
                    $this->em->clear();
                }
                //unlink($file);
                return true;
            }
        }
        return false;
    }

    public function products()
    {
        $productFile = $this->path.'audiopro.csv';
        if (file_exists($productFile)) {
            if ($this->loadCsv($productFile)) {
                $lines = explode("\r\n", $this->csvData);
                $i = 0;
                $activeTrans = false;
                foreach ($lines as $l) {
                    $productArr = $this->fixValues(explode(";", $l));
                    if (!isset($productArr[3])) {
                        continue;
                    }
                    if (!$activeTrans) {
                        $this->db->beginTransaction();
                        $activeTrans = true;
                    }
                    $product = false;
                    if (isset($productArr[3])) {
                        $product = $this->db->table('product')->select('id')->where('plu = ?', $productArr[3])->fetch();
                    }
                    if ($product) { //update
                        $data = [
                            //'code' => $productArr[4],
                            'count' => floor(floatval($productArr[0])),
                            'seling_price' => floor(floatval($productArr[2]))
                        ];
                        $this->db->table('product')
                            ->where('id', $product['id'])
                            ->update($data);
                        $i++;
                    } else { //insert
                        $data = [
                            'code' => $productArr[4],
                            'count' => floor(floatval($productArr[0])),
                            'seling_price' => floor(floatval($productArr[2])),
                            'plu' => $productArr[3],
                            'active' => 0,
                        ];
                        $product = $this->db->table('product')->insert($data);
                        $i++;
                    }
                    if ($i == 250) {
                        $this->db->commit();
                        $activeTrans = false;
                        $i = 0;
                    }
                }
                if ($i && $activeTrans) {
                    $this->db->commit();
                }
                return true;
            }
        }
        return false;
    }

    public function setFilterProducer()
    {
        $formInsert = [];
        $producers = $this->em->getRepository(ProductProducer::class)->findBy(['id not' => [35,36,37,38]]);
        $filters = $this->db->query('SELECT id FROM product_filter WHERE filter_group_id = 1')->fetchAll();
        if ($filters) {
            foreach($filters as $f) {
                $this->db->table('product_in_filter')->where('filter_id = '.$f['id'])->delete();
            }
        }
        $this->db->table('product_filter')->where('filter_group_id = 1')->delete();
        $this->db->beginTransaction();
        foreach ($producers as $p) {
            if ($p->name == 'Vyřazené' || $p->code == '999') {
                continue;
            }
            $data = [
                'name' => $p->company,
                'order_state' => 1,
                'min_value' => 0,
                'step' => 0,
                'max_value' => 0,
                'filter_group_id' => 1,
                'slider' => 0,
                'interval_v' => 0,
                'code' => $p->id
            ];
            $formInsert[] = $data;
            if (count($formInsert) == 250) {
                $this->db->table('product_filter')->insert($formInsert);
                $formInsert = [];
            }
        }
        if (count($formInsert)) {
            $this->db->table('product_filter')->insert($formInsert);
        }
        $this->db->commit();

        $filtersArr = [];
        $filters = $this->db->query('SELECT id, code FROM product_filter WHERE filter_group_id = 1')->fetchAll();
        if ($filters) {
            foreach($filters as $f) {
                $filtersArr[$f['code']] = $f['id'];
            }
        }
        unset($filters);
        $productsInsert = [];
        $products = $this->em->getRepository(Product::class)->findBy(['productMark !=' => null]);
        if ($products) {
            foreach($products as $p) {
                $producerId = $p->productMark->producer->id;
                if (in_array($producerId, [35,36])) {
                    $producerId = 1;
                } elseif (in_array($producerId, [37])) {
                    $producerId = 2;
                } elseif (in_array($producerId, [38])) {
                    $producerId = 9;
                }
                if (!isset($filtersArr[$producerId])) {
                    continue;
                }
                $productEntityArr = [
                    'product_id' => $p->id,
                    'filter_id' => $filtersArr[$producerId],
                    'value' => 0,
                    'value_max' => 0
                ];
                $productsInsert[] = $productEntityArr;
                if (count($productsInsert) >= 250) {
                    $this->db->table('product_in_filter')->insert($productsInsert);
                    $productsInsert = [];
                }
            }
        }
        if (count($productsInsert)) {
            $this->db->table('product_in_filter')->insert($productsInsert);
        }

        for ($i = 0; $i < 10; $i++) {
            $this->storage->clean([
                Cache::PRIORITY => 10
            ]);
            $this->cache->clean([
                Cache::PRIORITY => 10
            ]);
        }

        return true;
    }

    public function setFilterColor()
    {
        $productFile = $this->path.'zbozi.csv';
        if (file_exists($productFile)) {
            if ($this->loadCsv($productFile)) {
                $formInsert = [];
                $filters = $this->db->query('SELECT id FROM product_filter WHERE filter_group_id = 2')->fetchAll();
                if ($filters) {
                    foreach ($filters as $f) {
                        $this->db->table('product_in_filter')->where('filter_id = ' . $f['id'])->delete();
                    }
                }
                $this->db->table('product_filter')->where('filter_group_id = 2')->delete();
                //dump(explode(';', $this->csvHeader));
                $lines = explode("\r\n", $this->csvData);
                $colors = [];
                foreach ($lines as $l) {
                    $productArr = $this->fixValues(explode("\t", $l));
                    if (!isset($productArr[19])) {
                        continue;
                    }
                    $slug = Strings::webalize($productArr[19]);
                    if (!isset($colors[$slug])) {
                        $colors[$slug] = $productArr[19];
                    }
                }
                $this->db->beginTransaction();
                foreach ($colors as $k => $c) {
                    $data = [
                        'name' => $c,
                        'order_state' => 1,
                        'min_value' => 0,
                        'step' => 0,
                        'max_value' => 0,
                        'filter_group_id' => 2,
                        'slider' => 0,
                        'interval_v' => 0,
                        'code' => $k
                    ];
                    $formInsert[] = $data;
                    if (count($formInsert) == 250) {
                        $this->db->table('product_filter')->insert($formInsert);
                        $formInsert = [];
                    }
                }
                if (count($formInsert)) {
                    $this->db->table('product_filter')->insert($formInsert);
                }
                $this->db->commit();

                $filtersArr = [];
                $filters = $this->db->query('SELECT id, code FROM product_filter WHERE filter_group_id = 2')->fetchAll();
                if ($filters) {
                    foreach ($filters as $f) {
                        $filtersArr[$f['code']] = $f['id'];
                    }
                }
                unset($filters);
                $productsInsert = [];
                //$products = $this->em->getRepository(Product::class)->findBy(['colorCode !=' => null]);
                $products = $this->db->table('product')->where('NOT (color_code ?)', null)->fetchAll();
                if ($products) {
                    foreach ($products as $p) {
                        if (!isset($filtersArr[$p['color_code']])) {
                            continue;
                        }
                        $productEntityArr = [
                            'product_id' => $p['id'],
                            'filter_id' => $filtersArr[$p['color_code']],
                            'value' => 0,
                            'value_max' => 0
                        ];
                        $productsInsert[] = $productEntityArr;
                        if (count($productsInsert) >= 250) {
                            $this->db->table('product_in_filter')->insert($productsInsert);
                            $productsInsert = [];
                        }
                    }
                }
                if (count($productsInsert)) {
                    $this->db->table('product_in_filter')->insert($productsInsert);
                }

                for ($i = 0; $i < 10; $i++) {
                    $this->storage->clean([
                        Cache::PRIORITY => 100
                    ]);
                    $this->cache->clean([
                        Cache::PRIORITY => 10
                    ]);
                }
                return true;
            }
        }

        return false;
    }

    public function setFilterLength()
    {
        $productFile = $this->path.'zbozi.csv';
        if (file_exists($productFile)) {
            if ($this->loadCsv($productFile)) {
                $formInsert = [];
                $filters = $this->db->query('SELECT id FROM product_filter WHERE filter_group_id = 3')->fetchAll();
                if ($filters) {
                    foreach ($filters as $f) {
                        $this->db->table('product_in_filter')->where('filter_id = ' . $f['id'])->delete();
                    }
                }
                $this->db->table('product_filter')->where('filter_group_id = 3')->delete();
                /*dump(explode(';', $this->csvHeader));
                die;*/
                $lines = explode("\r\n", $this->csvData);
                $lengths = [];
                foreach ($lines as $l) {
                    $productArr = $this->fixValues(explode("\t", $l));
                    if (!isset($productArr[30])) {
                        continue;
                    }
                    $slug = Strings::webalize($productArr[30]);
                    if (!isset($lengths[$slug])) {
                        $lengths[$slug] = $productArr[30];
                    }
                }
                $this->db->beginTransaction();
                foreach ($lengths as $k => $l) {
                    $data = [
                        'name' => $l,
                        'order_state' => 1,
                        'min_value' => 0,
                        'step' => 0,
                        'max_value' => 0,
                        'filter_group_id' => 3,
                        'slider' => 0,
                        'interval_v' => 0,
                        'code' => $k
                    ];
                    $formInsert[] = $data;
                    if (count($formInsert) == 250) {
                        $this->db->table('product_filter')->insert($formInsert);
                        $formInsert = [];
                    }
                }
                if (count($formInsert)) {
                    $this->db->table('product_filter')->insert($formInsert);
                }
                $this->db->commit();

                $filtersArr = [];
                $filters = $this->db->query('SELECT id, code FROM product_filter WHERE filter_group_id = 3')->fetchAll();
                if ($filters) {
                    foreach ($filters as $f) {
                        $filtersArr[$f['code']] = $f['id'];
                    }
                }
                unset($filters);
                $productsInsert = [];
                //$products = $this->em->getRepository(Product::class)->findBy(['colorCode !=' => null]);
                $products = $this->db->table('product')->where('NOT (length_code ?)', null)->fetchAll();
                if ($products) {
                    foreach ($products as $p) {
                        if (!isset($filtersArr[$p['length_code']])) {
                            continue;
                        }
                        $productEntityArr = [
                            'product_id' => $p['id'],
                            'filter_id' => $filtersArr[$p['length_code']],
                            'value' => 0,
                            'value_max' => 0
                        ];
                        $productsInsert[] = $productEntityArr;
                        if (count($productsInsert) >= 250) {
                            $this->db->table('product_in_filter')->insert($productsInsert);
                            $productsInsert = [];
                        }
                    }
                }
                if (count($productsInsert)) {
                    $this->db->table('product_in_filter')->insert($productsInsert);
                }

                for ($i = 0; $i < 10; $i++) {
                    $this->storage->clean([
                        Cache::PRIORITY => 100
                    ]);
                    $this->cache->clean([
                        Cache::PRIORITY => 10
                    ]);
                }
                return true;
            }
        }

        return false;
    }

    public function setParameters()
    {
        $productFile = $this->path.'zbozi.csv';
        if (file_exists($productFile)) {
            if ($this->loadCsv($productFile)) {
                $formInsert = [];
                $this->db->query('TRUNCATE product_parameter');
                $lines = explode("\r\n", $this->csvData);
                $this->db->beginTransaction();
                foreach ($lines as $l) {
                    $productArr = $this->fixValues(explode("\t", $l));
                    if (!isset($productArr[0])) {
                        continue;
                    }
                    $product = $this->db->table('product')->where('code', $productArr[0])->fetch();
                    if ($product) {
                        $data = [
                            'product_id' => $product->id,
                            'name' => 'Hmotnost (kg)',
                            'value' => isset($productArr[13]) ? $productArr[13] : '',
                            'order_param' => 1
                        ];
                        $formInsert[] = $data;
                        $data = [
                            'product_id' => $product->id,
                            'name' => 'Šířka (mm)',
                            'value' => isset($productArr[14]) ? $productArr[14] : '',
                            'order_param' => 1
                        ];
                        $formInsert[] = $data;
                        $data = [
                            'product_id' => $product->id,
                            'name' => 'Výška (mm)',
                            'value' => isset($productArr[15]) ? $productArr[15] : '',
                            'order_param' => 1
                        ];
                        $formInsert[] = $data;
                        $data = [
                            'product_id' => $product->id,
                            'name' => 'Hloubka (mm)',
                            'value' => isset($productArr[16]) ? $productArr[16] : '',
                            'order_param' => 1
                        ];
                        $formInsert[] = $data;
                        $data = [
                            'product_id' => $product->id,
                            'name' => 'Barva',
                            'value' => isset($productArr[19]) ? $productArr[19] : '',
                            'order_param' => 1
                        ];
                        $formInsert[] = $data;
                        $data = [
                            'product_id' => $product->id,
                            'name' => 'Konstrukce',
                            'value' => isset($productArr[20]) ? $productArr[20] : '',
                            'order_param' => 1
                        ];
                        $formInsert[] = $data;
                        $data = [
                            'product_id' => $product->id,
                            'name' => 'Osazení',
                            'value' => isset($productArr[21]) ? $productArr[21] : '',
                            'order_param' => 1
                        ];
                        $formInsert[] = $data;
                        $data = [
                            'product_id' => $product->id,
                            'name' => 'Impedance',
                            'value' => isset($productArr[22]) ? $productArr[22] : '',
                            'order_param' => 1
                        ];
                        $formInsert[] = $data;
                        $data = [
                            'product_id' => $product->id,
                            'name' => 'Výkon',
                            'value' => isset($productArr[23]) ? $productArr[23] : '',
                            'order_param' => 1
                        ];
                        $formInsert[] = $data;
                        $data = [
                            'product_id' => $product->id,
                            'name' => 'Frekvenční rozsah',
                            'value' => isset($productArr[24]) ? $productArr[24] : '',
                            'order_param' => 1
                        ];
                        $formInsert[] = $data;
                        $data = [
                            'product_id' => $product->id,
                            'name' => 'Citlivost',
                            'value' => isset($productArr[25]) ? $productArr[25] : '',
                            'order_param' => 1
                        ];
                        $formInsert[] = $data;
                        $data = [
                            'product_id' => $product->id,
                            'name' => 'Dělící frekvence',
                            'value' => isset($productArr[26]) ? $productArr[26] : '',
                            'order_param' => 1
                        ];
                        $formInsert[] = $data;
                        $data = [
                            'product_id' => $product->id,
                            'name' => 'Konektivita',
                            'value' => isset($productArr[27]) ? $productArr[27] : '',
                            'order_param' => 1
                        ];
                        $formInsert[] = $data;
                        $data = [
                            'product_id' => $product->id,
                            'name' => 'Délka (m)',
                            'value' => isset($productArr[30]) ? $productArr[30] : '',
                            'order_param' => 1
                        ];
                        $formInsert[] = $data;
                        $this->db->table('product_parameter')->insert($formInsert);
                        $formInsert = [];
                    }
                }
                $this->db->commit();
                return true;
            }
        }

        return false;
    }

    private function generateSlugByArray($name, $beforeSlug)
    {
        $counter = null;
        $slug = \Nette\Utils\Strings::webalize($name);

        if ($slug === $beforeSlug) {
            $this->productSlug[] = $beforeSlug;
            return $beforeSlug;
        }

        update:
        if (in_array($slug . $counter, $this->productSlug)) {
            $counter++;
            goto update;
        }
        $this->productSlug[] = $slug . $counter;
        return $slug . $counter;
    }

    public function deactivateCategory()
    {
        $this->db->table('product_category')->where('web_menu_id IS NULL')->update(['active' => false]);
        $products = $this->em->getRepository(Product::class)->createQueryBuilder('p')->groupBy('p.category')->getQuery()->getResult();
        if ($products) {
            foreach ($products as $p) {
                if (is_null($p->category)) {
                    continue;
                }
                $this->db->table('product_category')->where('id', $p->category->id)->update(['active' => true]);
                $id = $p->category->parentCategory->id;
                do {
                    $category = $this->db->table('product_category')->where('id', $id)->fetch();
                    if ($category) {
                        $this->db->table('product_category')->where('id', $category['id'])->update(['active' => true]);
                        $id = $category['id'] != $category['parent_category_id'] ? $category['parent_category_id'] : null;
                    } else {
                        $id = null;
                    }
                } while ($id > 0 && !is_null($id));
            }
        }
        for ($i = 0; $i < 10; $i++) {
            $this->storage->clean([
                Cache::TAGS => ["categories"],
            ]);
            $this->cache->clean([
                Cache::PRIORITY => 10
            ]);
        }
    }

    public function customers()
    {
        $file = $this->path.'odberatele.csv';
        if (file_exists($file)) {
            if ($this->loadCsv($file)) {
                $lines = explode("\r\n", $this->csvData);
                $currency = $this->em->getRepository(Currency::class)->find(1);
                $banners = $this->em->getRepository(WebMenu::class)->findBy(['parentMenu' => [53,54]]);
                foreach ($lines as $l) {
                    $customerArr = $this->fixValues(explode("\t", $l));
                    if (isset($customerArr[0])) {
                        $customer = $this->em->getRepository(Customer::class)->findOneBy(['cezarId' => $customerArr[0]]);
                        if (!$customer) {
                            $customerData = [
                                'email' => isset($customerArr[17]) ? $customerArr[17] : '',
                                'emailDelivery' => isset($customerArr[17]) ? $customerArr[17] : '',
                                'isCompany' => true,
                                'company' => isset($customerArr[1]) ? $customerArr[1] : '',
                                'companyDelivery' => isset($customerArr[1]) ? $customerArr[1] : '',
                                'idNo' => isset($customerArr[2]) ? $customerArr[2] : '',
                                'idNoDelivery' => isset($customerArr[2]) ? $customerArr[2] : '',
                                'vatNo' => isset($customerArr[4]) ? $customerArr[4] : '',
                                'vatNoDelivery' => isset($customerArr[4]) ? $customerArr[4] : '',
                                'deliveryToOther' => isset($customerArr[10]) ? true : false,
                                'vatPay' => (bool)intval($customerArr[5]),
                                'phone' => isset($customerArr[15]) ? $customerArr[15] : (isset($customerArr[16]) ? $customerArr[16] : ''),
                                'phoneDelivery' => isset($customerArr[15]) ? $customerArr[15] : (isset($customerArr[16]) ? $customerArr[16] : ''),
                                'street' => isset($customerArr[6]) ? $customerArr[6] : '',
                                'streetDelivery' => isset($customerArr[10]) ? $customerArr[10] : '',
                                'city' => isset($customerArr[7]) ? $customerArr[7] : '',
                                'cityDelivery' => isset($customerArr[11]) ? $customerArr[11] : '',
                                'zip' => isset($customerArr[8]) ? $customerArr[8] : '',
                                'zipDelivery' => isset($customerArr[12]) ? $customerArr[12] : '',
                                'country' => isset($customerArr[9]) ? $customerArr[9] : null,
                                'countryDelivery' => isset($customerArr[13]) ? $customerArr[13] : null,
                                'currency' => $currency,
                                'euVat' => true,
                                'cezarId' => $customerArr[0],
                                'password' => '$2y$10$VhZJlpbZDTJrks8/fGZ/MuKeOqKJwrzEINvDhJbKOgXep8my2Yfi.',
                            ];
                            $customer = new Customer($customerData);
                            $this->em->persist($customer);
                            foreach($banners as $b) {
                                $entity = new CustomerBanners();
                                $entity->setCustomer($customer);
                                $entity->setMenu($b);
                                $this->em->persist($entity);
                            }
                            if (isset($customerArr[17])) {
                                $newsletterCheck = $this->em->getRepository(NewsletterEmail::class)->findBy(['email' => $customerArr[17]]);
                                if (!$newsletterCheck) {
                                    $news = new NewsletterEmail();
                                    $news->setEmail($customerArr[17]);
                                    $this->em->persist($news);
                                    $this->em->flush();
                                }
                            }
                        } else {
                            $customer->email = isset($customerArr[17]) ? $customerArr[17] : '';
                            $customer->emailDelivery = isset($customerArr[17]) ? $customerArr[17] : '';
                            $customer->isCompany = true;
                            $customer->company = isset($customerArr[1]) ? $customerArr[1] : '';
                            $customer->companyDelivery = isset($customerArr[1]) ? $customerArr[1] : '';
                            $customer->idNo = isset($customerArr[2]) ? $customerArr[2] : '';
                            $customer->idNoDelivery = isset($customerArr[2]) ? $customerArr[2] : '';
                            $customer->vatNo = isset($customerArr[4]) ? $customerArr[4] : '';
                            $customer->vatNoDelivery = isset($customerArr[4]) ? $customerArr[4] : '';
                            $customer->deliveryToOther = isset($customerArr[10]) ? true : false;
                            $customer->vatPay = (bool)intval($customerArr[5]);
                            $customer->phone = isset($customerArr[15]) ? $customerArr[15] : (isset($customerArr[16]) ? $customerArr[16] : '');
                            $customer->phoneDelivery = isset($customerArr[15]) ? $customerArr[15] : (isset($customerArr[16]) ? $customerArr[16] : '');
                            $customer->street = isset($customerArr[6]) ? $customerArr[6] : '';
                            $customer->streetDelivery = isset($customerArr[10]) ? $customerArr[10] : '';
                            $customer->city = isset($customerArr[7]) ? $customerArr[7] : '';
                            $customer->cityDelivery = isset($customerArr[11]) ? $customerArr[11] : '';
                            $customer->zip = isset($customerArr[8]) ? $customerArr[8] : '';
                            $customer->zipDelivery = isset($customerArr[12]) ? $customerArr[12] : '';
                            $customer->country = isset($customerArr[9]) ? $customerArr[9] : null;
                            $customer->countryDelivery = isset($customerArr[13]) ? $customerArr[13] : null;
                            $customer->currency = $currency;
                            $customer->euVat = true;
                        }
                    }
                }
                $this->em->flush();
                if (!is_dir($this->path.'backup')) {
                    mkdir($this->path.'backup');
                }
                copy($file, $this->path.'backup/odberatele.csv');
                unlink($file);
                return true;
            }
        }
        return false;
    }

}