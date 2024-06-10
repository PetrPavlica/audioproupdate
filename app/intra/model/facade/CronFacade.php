<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Intra\Model\Database\Entity\ProductPackage;
use Kdyby\Doctrine\EntityManager;
use Nette\Database\Context;
use Tracy\Debugger;

class CronFacade extends BaseFacade
{
    /** @var Context */
    public $db;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, Context $db)
    {
        parent::__construct($em);
        $this->db = $db;
    }

    public function transferPackages()
    {
        $items = $this->gEMProductPackageItems()->findAll();
        if ($items) {
            foreach($items as $p) {
                if ($p->product == $p->products) {
                    $productPackage = new ProductPackage();
                    $productPackage->setProduct($p->product);
                    $this->getEm()->persist($productPackage);
                    $p->setPackage($productPackage);
                }
            }
            $this->save();
        }

        $items = $this->gEMProductPackageItems()->findBy(['package != ' => null]);
        if ($items) {
            foreach($items as $i) {
                $packageItems = $this->gEMProductPackageItems()->findBy(['product' => $i->product, 'package' => null]);
                if ($packageItems) {
                    foreach($packageItems as $p) {
                        $p->setPackage($i->package);
                    }
                }
            }
            $this->save();
        }

        // Zbývající položky s nulovým package_id smazat
    }

    public function importDpdPickup()
    {
        try {
            $json_url = "https://pickup.dpd.cz/api/get-all";
            $json = file_get_contents($json_url);
            $data = json_decode($json, TRUE);
            if ($data['status'] == 'ok') {
                $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
                $this->db->query('TRUNCATE dpd_pickup');
                $this->db->query('TRUNCATE dpd_pickup_hours');
                $this->db->beginTransaction();
                foreach($data['data']['items'] as $item) {
                    $dpd = [
                        'code' => $item['id'],
                        'company' => $item['company'],
                        'street' => $item['street'],
                        'city' => $item['city'],
                        'house_number' => $item['house_number'],
                        'postcode' => $item['postcode'],
                        'phone' => $item['phone'],
                        'fax' => $item['fax'],
                        'email' => $item['email'],
                        'homepage' => $item['homepage'],
                        'pickup_allowed' => $item['pickup_allowed'],
                        'return_allowed' => $item['return_allowed'],
                        'express_allowed' => $item['express_allowed'],
                        'cardpayment_allowed' => $item['cardpayment_allowed'],
                        'service' => $item['service'],
                        'latitude' => $item['latitude'],
                        'longitude' => $item['longitude']
                    ];
                    $row = $this->db->table('dpd_pickup')->insert($dpd);
                    foreach($item['hours'] as $hour) {
                        $hour_d = [
                            'dpd_pickup_id' => $row->id,
                            'day' => $hour['day'],
                            'day_name' => $hour['dayName'],
                            'open_morning' => $hour['openMorning'],
                            'close_morning' => $hour['closeMorning'],
                            'open_afternoon' => $hour['openAfternoon'],
                            'close_afternoon' => $hour['closeAfternoon']
                        ];
                        $this->db->table('dpd_pickup_hours')->insert($hour_d);
                    }
                }
                $this->db->commit();
                $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
            }
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }
    }
}