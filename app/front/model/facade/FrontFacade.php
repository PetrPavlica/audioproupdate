<?php

namespace Front\Model\Facade;

use Nette\Caching\IStorage;
use Nette\Caching\Cache;
use Kdyby\Doctrine\EntityManager;
use App\Core\Model\BaseFacade;
use Intra\Model\Database\Entity\NewsletterEmail;
use Intra\Model\Database\Entity\Customer;
use Intra\Model\Database\Entity\WebResources;
use Intra\Model\Database\Entity\OrderRefund;
use Intra\Model\Database\Entity\FavouriteProduct;
use Tracy\Debugger;

class FrontFacade extends BaseFacade
{

    /** @var IStorage */
    private $storage;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, IStorage $storage)
    {
        parent::__construct($em);
        $this->storage = $storage;
    }

    /**
     * Get count of ratings sort and group by rating values
     * @param integer $idProduct
     * @return array
     */
    public function getCountRatings($idProduct)
    {
        $query = $this->getEm()->getConnection()->prepare(
            "SELECT rating, count(*) counts
                FROM product_rating WHERE product_id = $idProduct and approved = 1
                GROUP BY rating");
        $query->execute();
        $result = $query->fetchAll();
        $arr = [];
        foreach ($result as $item) {
            $arr[ $item[ 'rating' ] ] = $item[ 'counts' ];
        }
        return $arr;
    }

    /**
     * Add newsletter email form post values
     * @param array $values need email value
     */
    public function addNewsletterEmail($values)
    {
        $email = $this->gem(NewsletterEmail::class)->findOneBy(['email' => $values[ 'email' ]]);
        if (count($email)) {
            $email->setActive(true);
            $this->save();
        } else {
            $email = new NewsletterEmail($values);
            $email->setActive(true);
            $this->insertNew($email);
        }
        return true;
    }

    /**
     * Return cash settings
     * @return array
     */
    public function getAllCashSettings()
    {
        $key = 'getAllCashSettings';
        $arr = $this->storage->read($key);
        if ($arr == null) { // if null, create and cash
            $arr = $this->getAllsettings();
            $this->storage->write($key, $arr, [
                Cache::TAGS => ["settings"],
                Cache::PRIORITY => 50,
            ]);
        }
        return $arr;
    }

    public function getAllCashCurrency()
    {
        $key = 'getAllCashCurrency';
        $arr = $this->storage->read($key);
        if ($arr == null) { // if null, create and cash
            $currency = $this->gEMCurrency()->findBy(['active' => 1], ['orderCurrency' => 'ASC']);
            $arr = [];
            foreach ($currency as $item) {
                $arr[ $item->id ] = $item->code;
            }
            $this->storage->write($key, $arr, [
                Cache::TAGS => ["currency"],
            ]);
        }
        return $arr;
    }

    /**
     * Unsubscribe email form newsletter database - set email non-active
     * @param string $email
     * @return boolean
     */
    public function unsubscribeEmail($email)
    {
        $email = $this->gEMNewsletterEmail()->findOneBy(['email' => $email]);
        if (count($email) && $email->active == 1) {
            $email->setActive(0);
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * @param String $content
     * @param String $contentId
     * @param Int $pageId
     */
    public function addUpdateResources($content, $contentId, $pageId)
    {
        try {
            $item = $this->gEMWebResources()->findOneBy(['divId' => $contentId, 'pageId' => $pageId]);

            if (!count($item)) {
                $tmp = new WebResources();
                $tmp->setPageId($pageId);
                $tmp->setDivId($contentId);
                $tmp->setText($content);
                $this->insertNew($tmp);
            } else {
                $item->setText($content);
                $this->save();
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
        return true;
    }

    public function addPayment($order, $data, $type = "ThePay")
    {

        if (is_numeric($order)) {
            $order = $this->gEMOrders()->findOneBy(['variableSymbol' => $order]);
        }

        if ($order) {

            $refund = new OrderRefund();

            switch ($type) {

                case "ThePay":

                    $refund->setOnline(false);
                    $refund->setFoundedDate(new \DateTime());
                    $refund->setValue($data[ 'value' ]);
                    $refund->setTypePayment('ThePay úhrada');
                    $refund->setText('ThePay (' . $data[ 'paymentId' ] . ') - online úhrada');
                    $refund->setOrders($order);

                    break;
                case "HomeCredit":

                    $refund->setOnline(false);
                    $refund->setFoundedDate(new \DateTime());
                    $refund->setValue($order->totalPrice);
                    $refund->setTypePayment('Home Credit platba na splátky');
                    $refund->setText('Home Credit');
                    $refund->setOrders($order);

                    break;

                case 'Dobropis':
                    $refund->setOnline($data['online']);
                    $refund->setFoundedDate(new \DateTime());
                    $refund->setValue(-$data['price']);
                    $refund->setTypePayment('Dobropis');
                    $refund->setText('Dobropis faktury ('.$order->codeInvoice.')');
                    $refund->setOrders($order);

                    break;

                default:
                    Debugger::log("Byl proveden pokus zapsat platbu nedefinovaným způsobem (Type: " . $type . ")",
                        "pay_error");
                    break;

            }

            $this->insertNew($refund);
            return $refund;

        } else {
            return false;
        }

    }

    public function writeErrorInPayment($order, $error, $type = "ThePay")
    {
        if ($order) {

            switch ($type) {

                case "ThePay":
                    $order->setErrorInPayment('ThePay chyba: "' . $error . '"');
                    break;
                case "HomeCredit":
                    $order->setErrorInPayment('HomeCredit chyba: "' . $error . '"');
                    break;
                default:
                    Debugger::log("Byl proveden pokus zapsat neznámou chybu v platbě (Type: " . $type . ")",
                        "pay_error");
                    break;

            }

            //$order->setErrorInPayment('ThePay chyba: "' . $error . '"');
            $this->save();


        }
    }

    public function addToFavourites($productId, $customerId)
    {
        $old = $this->gEMFavouriteProduct()->findBy(['customer' => $customerId, 'product' => $productId]);
        if (!count($old)) {
            $fav = new FavouriteProduct();
            $fav->setCustomer($this->gEMCustomer()->find($customerId));
            $fav->setProduct($this->gEMProduct()->find($productId));
            $this->insertNew($fav);
            return true;
        }
        return false;
    }

    public function removeFavourite($customerId, $productId)
    {
        $fav = $this->gEMFavouriteProduct()->findBy(['customer' => $customerId, 'product' => $productId]);
        if (count($fav)) {
            $this->remove($fav);
        }
    }

    public function getPayMethodDropSource($currency, $free = false)
    {
        $methods = $this->gEMPaymentMethod()->findBy(['active' => '1'], ['orderState' => 'ASC']);
        $arr = [];
        $arrPrice = [];
        foreach ($methods as $method) {
            $arr[ $method->id ] = $method->name;
            $arrPrice[ $method->id ] = 0;
            if ($method->selingPrice != 0) {
                $price = $free ? 0 : round($method->selingPrice * $currency[ 'exchangeRate' ],
                    $currency[ 'countDecimal' ]);
                $arr[ $method->id ] .= " (" . trim($currency[ 'markBefore' ]) . $price . trim($currency[ 'markBehind' ]) . ")";
                $arrPrice[ $method->id ] = $price;
            }
        }
        return ['data' => $arr, 'price' => $arrPrice];
    }

    public function getDeliveryMethodDropSource($currency, $free = false)
    {
        $methods = $this->gEMDeliveryMethod()->findBy(['active' => '1']/* , ['orderState' => 'ASC'] */);
        $arr = [];
        $arrPrice = [];
        foreach ($methods as $method) {
            $arr[ $method->id ] = $method->name;
            $arrPrice[ $method->id ] = 0;
            if ($method->selingPrice != 0) {
                $price = $free ? 0 : round($method->selingPrice * $currency[ 'exchangeRate' ],
                    $currency[ 'countDecimal' ]);
                $arr[ $method->id ] .= " (" . trim($currency[ 'markBefore' ]) . $price . trim($currency[ 'markBehind' ]) . ")";
                $arrPrice[ $method->id ] = $price;
            }
        }
        return ['data' => $arr, 'price' => $arrPrice];
    }

}
