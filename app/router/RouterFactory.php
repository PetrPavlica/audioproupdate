<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;
use Nette\Caching\Cache;

class RouterFactory
{

    use Nette\StaticClass;

    /**
     * @param Nette\Database\Context $db
     * @param Nette\Caching\IStorage $storage
     * @return array
     */
    public static function getCategoryUrl($db, $storage)
    {
        $key = 'getCategoryUrl';
        $output = $storage->read($key);
        if ($output == null) {
            $output = [];
            $categories = $db->query('SELECT id, url FROM product_category')->fetchAll();
            if ($categories) {
                foreach($categories as $c) {
                    $output[$c['id']] = $c['url'];
                }
            }
            $storage->write($key, $output, [
                Cache::TAGS => ["categoryUrl"],
            ]);
        }
        return $output;
    }

    /**
     * @param Nette\Database\Context $db
     * @param Nette\Caching\IStorage $storage
     * @return Nette\Application\IRouter
     */
    public static function createRouter(Nette\Database\Context $db, Nette\Caching\IStorage $storage)
    {
        $baseLanguage = 'cs';
        $categoryUrl = self::getCategoryUrl($db, $storage);
        $router = new RouteList;
        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/]api/1/products/availability', 'Api:productsAvailability');
        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/]api/1/payment/delivery', 'Api:paymentDelivery');
        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/]api/1/order/status', 'Api:orderStatus');
        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/]api/1/order/send', 'Api:orderSend');
        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/]api/1/order/cancel', 'Api:orderCancel');
        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/]api/1/payment/status', 'Api:paymentStatus');
        $router[] = new Route('sitemap.xml', 'Front:sitemap');
        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/]tracking/<hash>', 'Front:tracking');
        $router[] = new Route('admin', 'Sign:in');
        $router[] = new Route('unsubscribe-newsletter/<email>', 'Front:unsubscribeNewsletter');
        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/]page/<id [0-9]+>-<wslug>', 'Page:default');
        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/]page/<wslug>', 'Page:default');

        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/]<id>[/page-<page>][/search-<searchTerm>]', [
            'id' => [
                Route::FILTER_IN => function ($id) use ($db, $categoryUrl) {
                    if (is_numeric($id)) {
                        //$category = $db->query('SELECT id FROM product_category WHERE id = ?', $id)->fetch();
                        if (isset($categoryUrl[$id])) {
                            return $categoryUrl[$id];
                        } else {
                            return null;
                        }
                    } else {
                        $search = array_search($id, $categoryUrl);
                        //$category = $db->query('SELECT id FROM product_category WHERE url = ?', $id)->fetch();
                        if ($search) {
                            return $search;
                        } else {
                            return null;
                        }
                    }
                },
                Route::FILTER_OUT => function ($id) use ($db, $categoryUrl) {
                    if (!is_numeric($id)) {
                        return $id;
                    } else {
                        //$category = $db->query('SELECT url FROM product_category WHERE id = ?', $id)->fetch();
                        if (isset($categoryUrl[$id])) {
                            return $categoryUrl[$id];
                        } else {
                            return null;
                        }
                    }
                }
            ],
            'presenter' => 'ProductList',
            'action' => 'list',
            null => [
                Route::FILTER_IN => function (array $params) {
                    unset($params['slug']);
                    return $params;
                },
                Route::FILTER_OUT => function (array $params) {
                    unset($params['slug']);
                    return $params;
                },
            ],
        ]);

        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/]category/<id [0-9]+>-<slug>[/page-<page>][/search-<searchTerm>]',
            'ProductList:list');

        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/]product/<id [0-9]+>-<slug>', 'ProductDetail:detail');

        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/]orders[/<slug>]', 'Order:default');
        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/]<presenter>/<action>[/<id>]', "Front:default"); //Front

        $router[] = new Route('[<locale='.$baseLanguage.' cs|en|sk>/][/<slug>]', 'ErrorFront:default');
        return $router;
    }

}
