<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Intra\Components\MailSender\MailSender;
use Intra\Model\Database\Entity\BalikobotOrder;
use Intra\Model\Database\Entity\BalikobotPackage;
use Intra\Model\Database\Entity\BalikobotShop;
use Intra\Model\Database\Entity\BalikobotTypeDelivery;
use Intra\Model\Utils\Balikobot\Balikobot;
use Intra\Model\Utils\BalikobotAdapter;
use Kdyby\Doctrine\EntityManager;
use Nette\Neon\Exception;
use Tracy\Debugger;

class BalikobotShopFacade extends BaseFacade
{
    /** @var BalikobotAdapter */
    public $balikobot;

    /**
     * Construct
     * @param EntityManager $em
     * @param BalikobotAdapter $balikobot
     */
    public function __construct(EntityManager $em, BalikobotAdapter $balikobot)
    {
        parent::__construct($em, BalikobotShop::class);
        $this->balikobot = $balikobot;
    }

    /**
     * @param BalikobotPackage $package
     * @return bool|string
     */
    public function add(array $packages)
    {
        $this->balikobot->init($packages[0]->balikobotShop);
        $order = $packages[0]->orders;
        $shipper = $packages[0]->balikobotTypeDelivery->shipper;
        $service = $packages[0]->balikobotTypeDelivery->serviceCode;

        if (!$order->variableSymbol) { // Pokud obj ještě nemá var. symbol, tak jej vygenerujeme
            $n = 4 - strlen($order->id);
            $no = substr(date("Y"), 2);
            for ($i = 0; $i < $n; $i++) {
                $no .= "0";
            }
            $order->setVariableSymbol($no . $order->id);
            $this->save();
        }
        foreach($packages as $k => $p) {
            $optionData = [
                'price' => $p->orderNumber == 1 ? $order->totalPrice : 0,
                /*< package price; float */
                'real_order_id' => $order->id,
                'order_number' => intval($p->orderNumber),
                'eid' => $p->eid,
                /*< order id; string; max length 10 characters */
                //'services' => $order->price,            /*< additional services; array */
                'weight' => $p->weight,
                /*< weight in kg; float */
                'sms_notification' => true,
                /*< notifies customer by SMS; boolean */
                //'branch_id' => '',                      /*< branch id for pickup service */ // - setting late
                //'del_insurance' => false,               /*< insurance; boolean - připojištění - nad 50 tis. je automatické */
                //'note' => '',                           /*< note */
                'del_exworks' => false,
                /*< pay by customer; boolean */
                //'mu_type' => '',                        /*< manipulation unit code; call getManipulationUnits */
                'pieces_count' => 1,
                /*< number of items if bigger than one; int */
                'phone_notificati' => true,
                /*< notifies customer by phone; boolean */
                'b2c_notification' => false,
                /*< B2C service; boolean */
                //'note_driver' => '',                    /*< note - only Cargo */
                //'note_recipient' => '',                 /*< note for customer - only Cargo*/
                //'comfort_service' => '',                /*< carry to the floor and others; boolean */
                //'app_disp' => false,                    /*< return old household appliance; boolean - odvoz starého spotřebiče - PPL sprint palet only */
                //'require_full_age' => 0,                /*< taking delivery requires full age; boolean */
                //'password' => ''                        /*< taking delivery requires password */ // TODO na uloženku jde dát heslo
                'return_track' => true,
                'credit_card' => true,
            ];

            // Delivery places - branch_id set
            if ($order->deliveryPlace) {
                if ($order->deliveryMethod->isDPD) {
                    $pickups = $this->getDPDPickups();
                    if (isset($pickups[$order->deliveryPlace])) {
                        $optionData['branch_id'] = $order->deliveryPlace;
                    }
                }
                if ($order->deliveryMethod->isUlozenka) {
                    $pickups = $this->getUlozenkaPickups();
                    if (isset($pickups[$order->deliveryPlace])) {
                        $optionData['branch_id'] = $order->deliveryPlace;
                    }
                }
            }

            $dataForThis = [];
            foreach ($this->balikobot->get()->getOptions($shipper) as $item) {
                if (isset($optionData[$item])) {
                    $dataForThis[$item] = $optionData[$item];
                }
            }

            if (isset($dataForThis[Balikobot::OPTION_CREDIT_CARD]) && isset($optionData['branch_id'])) {
                unset($dataForThis[Balikobot::OPTION_CREDIT_CARD]);
            }

            // Add package to balikobot API
            try {
                $balikobotPackage = $this->balikobot->get()->service($k, $shipper, $service, $dataForThis);
            } catch(\InvalidArgumentException $e) {
                if ($e->getMessage() == 'The branch option is required for pickup service.') {
                    return 'Místo dodání je povinné u Pickup služby.';
                } else {
                    return $e->getMessage();
                }
            } catch (\UnexpectedValueException $e) {
                return $e->getMessage();
            }

            if ($order->deliveryToOther == false) {
                try {
                    $balikobotPackage->customer(
                        $k,
                        $order->name,
                        $order->street,
                        $order->city,
                        str_replace(' ', '', $order->zip),
                        $order->phone,
                        $order->email,
                        $order->company ? $order->company : null,
                        $order->country
                    );
                } catch (\InvalidArgumentException $e) {
                    if ($e->getMessage() == 'Invalid country code has been entered.') {
                        return 'Je zadaný neplatný kód země.';
                    } elseif ($e->getMessage() == 'Invalid argument has been entered.') {
                        return 'Není zadaná některá položka: jméno, ulice, město, psč, telefon, e-mail.';
                    }
                    return $e->getMessage();
                }
            } else {
                try {
                    $balikobotPackage->customer(
                        $k,
                        $order->contactPerson ? $order->contactPerson : $order->name,
                        $order->streetDelivery,
                        $order->cityDelivery,
                        str_replace(' ', '', $order->zipDelivery),
                        $order->phone,
                        $order->email,
                        null,
                        $order->countryDelivery ? $order->countryDelivery : $order->country
                    );
                } catch (\InvalidArgumentException $e) {
                    if ($e->getMessage() == 'Invalid country code has been entered.') {
                        return 'Je zadaný neplatný kód země.';
                    } elseif ($e->getMessage() == 'Invalid argument has been entered.') {
                        return 'Není zadaná některá položka: jméno, ulice, město, psč, telefon, e-mail.';
                    }
                    return $e->getMessage();
                }
            }

            if ($p->orderNumber == 1 && $order->paymentMethod->deliveryCash) { // pokud je platba na dobírku
                $balikobotPackage->cashOnDelivery($k, $order->totalPrice, $order->variableSymbol, $order->currency->code);
            }
        }
        try {
            $response = $balikobotPackage->add();
            //bdump($response);
        } catch (\InvalidArgumentException $e) {
            return $e->getMessage();
        }

        foreach($packages as $k => $p) {
            $p->data($response[$k]);
            if (isset($response[$k]['track_url'])) {
                $p->trackUrl = $response[$k]['track_url'];
            }
        }
        $this->save();

        /*$package->data($response);
        if (isset($response['track_url'])) {
            $package->trackUrl = $response['track_url'];
        }
        $this->save();*/
        return true;
    }

    /**
     * Drop package from Balikobot api
     * @param array $packages
     */
    public function drop(array $packages)
    {
        $this->balikobot->init($packages[0]->balikobotShop);
        $shipper = $packages[0]->balikobotTypeDelivery->shipper;
        foreach($packages as $p) {
            try {
                $this->balikobot->get()->dropPackage($shipper, $p->package_id);
                $p->data([
                    'carrier_id' => null,
                    'package_id' => null,
                    'label_url' => null,
                    'track_url' => null,
                    'status' => 'dropped'
                ]);
                $this->save();
            } catch (\UnexpectedValueException $e) {
                return $e->getMessage();
            }
        }

        return true;
    }

    /**
     * Update DeliveryTypes from Balikobot
     * @param MailSender $mailSender
     */
    public function updateDeliveryTypes(MailSender $mailSender)
    {
        $balikobotShop = $this->gEMBalikobotShop()->findOneBy(['defaultVal' => 1]);
        if (!count($balikobotShop)) {
            Debugger::log(new Exception('Default balikobotShop is not set!'));
        }
        $this->balikobot->init($balikobotShop);

        $ids = [];
        foreach ($this->balikobot->get()->getShippers() as $shipper) {
            try {
                $services = $this->balikobot->get()->getServices($shipper);
            } catch (\InvalidArgumentException $e) {
                $services = null;
            }
            $name2 = mb_strtoupper($shipper);
            if ($name2 === 'CP') {
                $name2 = 'ČP';
            }
            if ($name2 === 'ZASILKOVNA') {
                $name2 = 'Zásilkovna';
            }
            if ($name2 === 'ULOZENKA') {
                $name2 = 'Uloženka';
            }
            if ($services) {
                foreach ($services as $idx => $service) {
                    $ids[] = $this->editBalikobotTypeDelivery($shipper, $idx, $service, $service . " (" . $name2 . ")");
                }
            }
            if (!$services) {
                $ids[] = $this->editBalikobotTypeDelivery($shipper, '', '', $name2);
            }
        }

        // Nyní smaži staré a upozorním pokud mi to deaktivuje nějaký typ dopravy a pošlu email.
        foreach ($this->gEMBalikobotTypeDelivery()->findAll() as $entity) {
            if (!in_array($entity->id, $ids)) {
                foreach ($this->gEMDeliveryMethod()->findBy(['balikobotDelivery' => $entity->id]) as $method) { // Deaktivuji všechny dodací metody, které na této dodací metodě balíkobotu byly závislé.
                    $method->setBalikobotDelivery(null);
                    $method->setActive(false);
                    $this->save();
                    $mailSender->sendProblemEmailToAdmin(
                        'Na Balíkobotu zanikla možnost dopravy přes "' . $entity->name . '". 
                        Toto ovlivnilo Vaší možnost dopravy: "' . $method->name . '", kterou jsme proto automaticky deaktivovali. 
                        Prosím prověřte nastavení typů dopravy.'
                    );
                }
                $entity->setActive(false);
                $this->save();
            }
        }
    }

    public function editBalikobotTypeDelivery($shipper, $serviceCode, $service, $name)
    {
        $new = false;
        $entity = $this->gEMBalikobotTypeDelivery()->findOneBy(['shipper' => $shipper, 'serviceCode' => $serviceCode]);
        if (!count($entity)) {
            $new = true;
            $entity = new BalikobotTypeDelivery();
        }
        $entity->data([
            'shipper' => $shipper,
            'serviceCode' => $serviceCode,
            'service' => $service,
            'name' => $name,
            'active' => true
        ]);
        if ($new) {
            $this->insertNew($entity);
        } else {
            $this->save();
        }
        return $entity->id;
    }

    public function getLabels($ids)
    {
        $shipper = null;
        $balikobotShop = null;
        $packages = [];
        foreach ($ids as $id) {
            $package = $this->gEMBalikobotPackage()->find($id);
            $tmp = $package->balikobotTypeDelivery->shipper;
            if ($shipper != null && $tmp != $shipper) {
                return ['error' => 'Hromadný tisk štítků lze provést pouze pro stejné dopravce! Např. nelze tisknout štítky pro Českou poštu a DPD zároveň.'];
            }

            $tmp2 = $package->balikobotShop;
            if ($balikobotShop != null && $tmp2 != $balikobotShop) {
                return ['error' => 'Hromadný tisk štítků lze provést pouze pro stejné exportní místo / stejný Balíkobot e-shop!'];
            }
            $shipper = $tmp;
            $balikobotShop = $tmp2;
            $packages[] = $package->package_id;
        }
        $this->balikobot->init($balikobotShop);
        return $this->balikobot->get()->getLabels($shipper, $packages);
    }

    public function createOrderPicking($ids)
    {
        $shipper = null;
        $balikobotShop = null;
        $packages = [];
        $entities = [];
        foreach ($ids as $id) {
            $package = $this->gEMBalikobotPackage()->find($id);
            $tmp = $package->balikobotTypeDelivery->shipper;
            if ($shipper != null && $tmp != $shipper) {
                return 'Objednávku lze vytvořit pouze pro stejné dopravce! Např. nelze objednat naráz pro Českou poštu a DPD zároveň.';
            }

            $tmp2 = $package->balikobotShop;
            if ($balikobotShop != null && $tmp2 != $balikobotShop) {
                return 'Objednávku lze vytvořit pouze pro stejné exportní místo / stejný Balíkobot e-shop!';
            }
            $shipper = $tmp;
            $balikobotShop = $tmp2;
            $packages[] = $package->package_id;
            $entities[] = $package;
        }
        $this->balikobot->init($balikobotShop);
        $res = $this->balikobot->get()->order($shipper, $packages);
        $order = new BalikobotOrder();
        $this->insertNew($order);
        foreach ($entities as $package) {
            $package->setIsOrdered(true);
            $package->data($res);
            $package->setBalOrders($order);
            $package->orders->deliveryOrdered = true;
            $this->save();
        }
        $order->setBalikobotTypeDelivery($package->balikobotTypeDelivery);
        $order->setBalikobotShop($balikobotShop);
        $order->setCount(count($entities));

        $order->data($res);
        $this->save();
        return $order;
    }

    /**
     * Get track info about your package
     * @param $package
     * @return array
     */
    public function track($package)
    {
        $this->balikobot->init($package->balikobotShop);
        $msg = $this->balikobot->get()->trackPackage($package->balikobotTypeDelivery->shipper, $package->carrier_id);
        bdump($msg);
        if (isset($msg['status']) && in_array($msg['status'], [404, 503])) {
            $msg = ['V současné době nemáme bližší informace o zásilce.'];
        }
        return $msg;
    }

    /**
     * Get info about your package
     * @param $package
     * @return array
     */
    public function overview($package)
    {
        $this->balikobot->init($package->balikobotShop);
        try {
            $msg = $this->balikobot->get()->overview($package->balikobotTypeDelivery->shipper);
        } catch(\UnexpectedValueException $e) {
            bdump($e);
            $msg = [];
        }
        return $msg;
    }

    public function packageInfo($package) {
        bdump($package);
        $this->balikobot->init($package->balikobotShop);
        try {
            $msg = $this->balikobot->get()->getPackageInfo($package->balikobotTypeDelivery->shipper, $package->package_id);
            bdump($msg);
            //$msg = $this->balikobot->get()->overview($package->balikobotTypeDelivery->shipper);
        } catch(\UnexpectedValueException $e) {
            bdump($e);
            $msg = [];
        }
        return $msg;
    }

    /**
     * Get all DPD Pickups
     * @return array
     */
    public function getDPDPickups()
    {
        if ($this->balikobot->get() === null) {
            $balikobotShop = $this->gEMBalikobotShop()->findOneBy(['defaultVal' => 1]);
            if (!count($balikobotShop)) {
                Debugger::log(new Exception('Default balikobotShop is not set!'));
                return [];
            }
            $this->balikobot->init($balikobotShop);
        }
        $res = [];
        try {
            $data = $this->balikobot->get()->getBranches('dpd', 3, false);
            foreach ($data as $d) {
                $res[$d['id']] = $d['name'] . ', ' . $d['street'] . ', ' . $d['city'] . ' (' . $d['zip'] . '), ' . $d['country'];
            }
        } catch(\UnexpectedValueException $e) {

        }
        return $res;
    }

    /**
     * Get all Ulozenka Pickups
     * @return array
     */
    public function getUlozenkaPickups()
    {
        if ($this->balikobot->get() === null) {
            $balikobotShop = $this->gEMBalikobotShop()->findOneBy(['defaultVal' => 1]);
            if (!count($balikobotShop)) {
                Debugger::log(new Exception('Default balikobotShop is not set!'));
                return [];
            }
            $this->balikobot->init($balikobotShop);
        }
        $res = [];
        try {
            $data = $this->balikobot->get()->getBranches('ulozenka', 1, false);
            foreach ($data as $d) {
                $res[$d['id']] = $d['name'] . ', ' . $d['street'] . ', ' . $d['city'] . ' (' . $d['zip'] . '), ' . $d['country'];
            }
        } catch(\UnexpectedValueException $e) {

        }
        return $res;
    }

    /**
     * Get all DPD Pickups
     * @return array
     */
    public function getZasilkovna()
    {
        if ($this->balikobot->get() === null) {
            $balikobotShop = $this->gEMBalikobotShop()->findOneBy(['defaultVal' => 1]);
            if (!count($balikobotShop)) {
                Debugger::log(new Exception('Default balikobotShop is not set!'));
                return [];
            }
            $this->balikobot->init($balikobotShop);
        }
        $res = [];
        try {
            $data = $this->balikobot->get()->getBranches('zasilkovna', null, false);
            foreach ($data as $d) {
                if (in_array($d['country'], ['CZ', 'SK'])) {
                    $res[$d['id']] = $d['name'] . ', ' . $d['street'] . ', ' . $d['city'] . ' (' . $d['zip'] . '), ' . $d['country'];
                }
            }
        } catch(\UnexpectedValueException $e) {

        }
        return $res;
    }
}
