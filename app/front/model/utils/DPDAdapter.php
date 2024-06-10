<?php

namespace Front\Model\Utils;

use Nette\Caching\IStorage;
use Nette\Caching\Cache;

class DPDAdapter
{

    /** @var IStorage */
    private $storage;

    /** @var String */
    protected $url;

    /**
     * Construct
     */
    public function __construct(IStorage $storage)
    {
        $this->storage = $storage;
    }

    public function getDPDPickupData()
    {
        $key = 'getDPDPickup';
        $arr = $this->storage->read($key);
        if ($arr == null) { // if null, create and cash
            $arr = $this->createCache($key);
        }
        return $arr;
    }

    public function createCache($key)
    {
        $arr = [];
        // Check if url exist
        if (@get_headers($this->url)) {
            try {
                $json = file_get_contents($this->url);
                $data = json_decode($json, true);
            } catch (\Exception $ex) {

            }
            if (isset($data[ 'data' ])) {
                $arr = [];
                foreach ($data[ 'data' ][ 'items' ] as $d) {
                    $arr[] = $d[ 'company' ] . ', ' . $d[ 'street' ] . ' ' . $d[ 'house_number' ] . ', ' . $d[ 'city' ];
                }

                $this->storage->write($key, $arr, [
                    Cache::TAGS => ["dpdPickap"]
                ]);
            }
        }
        return $arr;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

}
