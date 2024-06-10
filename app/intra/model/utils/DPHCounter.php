<?php

namespace Intra\Model\Utils;

class DPHCounter
{

    /** @var float */
    protected $onePriceWithoutDPH;

    /** @var float */
    protected $dph;

    /** @var float */
    protected $oneDph;

    /** @var float */
    protected $totalPriceWithoutDPH;

    /** @var float */
    protected $totalDph;

    /** @var float */
    protected $count;

    /** @var float */
    protected $coef;

    /** @var float */
    protected $rate = 1;

    /** @var bool */
    protected $disableVat = false;

    /** @var integer */
    protected $round = 2;

    public function setPriceWithDPH($price, $dph, $count = 1)
    {
        $price = round($price / $this->rate, $this->round);

        $this->coef = round(($dph / (100 + $dph)), 4);
        $this->count = $count;
        $this->dph = $dph;
        $this->oneDph = round($price * $this->coef, $this->round);
        $this->onePriceWithoutDPH = $price - $this->oneDph;

        $this->totalPriceWithoutDPH = $this->onePriceWithoutDPH * $count;
        $this->totalDph = $this->oneDph * $count;
    }

    public function setDisableDPH($disable = false)
    {
        $this->disableVat = $disable;
    }

    public function setPriceWithoutDPH($price, $dph, $count = 1)
    {
        $price = round($price / $this->rate, $this->round);

        $this->coef = ($dph / 100);
        $this->count = $count;
        $this->dph = $dph;

        $this->onePriceWithoutDPH = round($price, $this->round);
        $this->oneDph = round($this->onePriceWithoutDPH * $this->coef, $this->round);

        $this->totalPriceWithoutDPH = $this->onePriceWithoutDPH * $count;
        $this->totalDph = $this->oneDph * $count;
    }

    public function setRound($count = 2)
    {
        $this->round = $count;
    }

    public function setExchangeRate($rate = 1)
    {
        $this->rate = $rate;
    }

    public function getTotalPrice()
    {
        if ($this->disableVat) {
            return $this->totalPriceWithoutDPH;
        }
        return $this->totalPriceWithoutDPH + $this->totalDph;

    }

    public function getTotalDPH()
    {
        if ($this->disableVat) {
            return 0;
        }
        return $this->totalDph;
    }

    public function getTotalWithoutDPH()
    {
        return $this->totalPriceWithoutDPH;
    }

    public function getOnePrice()
    {
        if ($this->disableVat) {
            return $this->onePriceWithoutDPH;
        }
        return $this->onePriceWithoutDPH + $this->oneDph;
    }

    public function getOneDph()
    {
        if ($this->disableVat) {
            return 0;
        }
        return $this->oneDph;
    }

    public function getOneWithoutDPH()
    {
        return $this->onePriceWithoutDPH;
    }

    public function getDPHPercent()
    {
        if ($this->disableVat) {
            return 0;
        }
        return $this->dph;
    }

    public function getCount()
    {
        return $this->count;
    }

}
