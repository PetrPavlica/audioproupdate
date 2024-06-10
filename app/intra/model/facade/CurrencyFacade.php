<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\Currency;

class CurrencyFacade extends BaseFacade {

    /** @var string */
    private $normalCurrency;

    /** @var string */
    private $exoticCurrency;

    /** @var string */
    private $ourCurrency;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        parent::__construct($em, Currency::class);
    }

    /**
     * Check aktual exchange rates
     * @return message array of messages
     */
    public function checkActualExchangeRates() {
        $kurzy = file($this->normalCurrency);
        $kurzyExotic = file($this->exoticCurrency);

        $arr = [];
        foreach ($kurzy as $v) {
            $h = explode("|", $v);
            if (isset($h[3]) && isset($h[4]))
                $arr[$h[3]] = str_replace(',', '.', $h[4]);
        }
        foreach ($kurzyExotic as $v) {
            $h = explode("|", $v);
            if (isset($h[3]) && isset($h[4]))
                $arr[$h[3]] = str_replace(',', '.', $h[4]);
        }
        $message = [];
        $entity = $this->get()->findAll();
        foreach ($entity as $item) {
            if (isset($arr[$item->code])) {
                $item->exchangeRate = $arr[$item->code];
            } else if ($item->code == $this->ourCurrency) {
                continue;
            } else {
                $message[] = "Nepodařilo se najít na lístku ČNB měnu: $item->name [$item->code]";
            }
        }
        $this->save();
        return $message;
    }

    /**
     * Set url for CNB actual Exchange Rates
     * @param string $url
     */
    public function setUrlNormalCurrency($url) {
        $this->normalCurrency = $url;
    }

    /**
     * Set url for CNB actual Exchange Rates of exotic currency
     * @param string $url
     */
    public function setUrlExoticCurrency($url) {
        $this->exoticCurrency = $url;
    }

    /**
     * Set our Currency
     * @param string $str
     */
    public function setOurCurrency($str) {
        $this->ourCurrency = $str;
    }

}
