<?php

namespace Intra\Model\Utils;

use Intra\Model\Database\Entity\BalikobotShop;
use Intra\Model\Utils\Balikobot\Balikobot;
use Nette;

class BalikobotAdapter
{
    /** @var BalikobotShop */
    protected $shop;

    /** @var bool */
    protected $isProduction;

    /** @var Balikobot */
    protected $balikobot;

    /**
     * Init Balikobot and setup base settings
     * @param BalikobotShop $shop
     */
    public function init(BalikobotShop $shop)
    {
        $this->shop = $shop;
        if ($this->isProduction === true) {
            $this->balikobot = new Balikobot($shop->apiUser, $shop->apiKey, $shop->id);
        } else {
            $this->balikobot = new Balikobot($shop->apiUserTest, $shop->apiKeyTest, $shop->id);
        }
    }

    /**
     * @param bool $isProduction
     */
    public function setProduction($isProduction)
    {
        $this->isProduction = $isProduction;
    }

    /**
     * @return Balikobot
     */
    public function get()
    {
        return $this->balikobot;
    }


}
