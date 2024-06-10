<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use App\Core\Model\Database\Utils\SQLHelper;
use Intra\Model\Database\Entity\CustomerSales;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\Customer;
use Nette\Database\Context;
use Nette\Security\Passwords;
use Tracy\Debugger;

class CustomerFacade extends BaseFacade {

    /** @var Context */
    public $db;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, Context $db) {
        parent::__construct($em, Customer::class);
        $this->db = $db;
    }

    /**
     * Return prepared CMR states whit sums prices and customers
     * @param array $filters
     * @return type
     */
    public function getPreparedCMRStates($filters) {
        $whereDealer = "";
        $whereCountry = "";
        if (isset($filters[ 'dealer' ]) && $filters[ 'dealer' ] != NULL) {
            $whereDealer = ' AND c.dealer_id = ' . $filters[ 'dealer' ];
        }
        if (isset($filters[ 'country' ]) && $filters[ 'country' ] != NULL) {
            $whereCountry = ' AND c.country_id = ' . $filters[ 'country' ];
        }
        $states = $this->db->query(
            "SELECT cs.*, ((SELECT SUM(c.price_estimation) FROM customer c
                    WHERE c.customer_state_id = cs.id " . $whereDealer . " " . $whereCountry . ") * (cs.percent / 100)) price
                            FROM customer_state cs
                            WHERE cs.visible = 1
                        Order by cs.state_order"
        );

        $arr = [];
        foreach ($states as $state) {
            $arr[ $state->id ] = $state;
            $arr[ $state->id ][ 'customers' ] = $this->db->query(
                "SELECT c.id, c.company, c.name, c.price_estimation,
                                DATEDIFF(NOW(), c.date_change_state) dateState FROM customer c
                            WHERE c.customer_state_id = ? " . $whereDealer . " " . $whereCountry . " ORDER By dateState DESC"
                , $state->id)->fetchAll();
        }
        return $arr;
    }

    /**
     * Return array of users for selectbox
     * @return array
     */
    public function getDealersForFilter() {
        $users = $this->db->query("SELECT u.* FROM `customer` c JOIN `user` u ON c.dealer_id = u.id GROUP BY u.id");
        $arr = [];
        foreach ($users as $u) {
            $arr[ $u[ 'id' ] ] = $u[ 'name' ] . ' (' . $u[ 'email' ] . ')';
        }
        return $arr;
    }

    /**
     * Return array for selectBox of country
     * @return array
     */
    public function getCountryForFilter() {
        $country = $this->em->getRepository(\Intra\Model\Database\Entity\Country::class)->findAll();
        $arr = [];
        foreach ($country as $c) {
            $arr[ $c->id ] = $c->name;
        }
        return $arr;
    }

    public function createNewCredencial($user) {
        $password = $this->randomCharacters(6);
        $user->setPassword($this->hash($password));
        $this->save();
        return $password;
    }

    private function randomCharacters($delka_hesla) {
        $skupina_znaku = 'abcdefghjkopqrstuvwx123456789ABCDEFGHJKLMNOPQRSTUVWX';
        $vystup = '';
        $pocet_znaku = strlen($skupina_znaku) - 1;
        for ($i = 0; $i < $delka_hesla; $i++) {
            $vystup .= $skupina_znaku[ mt_rand(0, $pocet_znaku) ];
        }
        return $vystup;
    }

    /**
     * Hash password
     * @param string $password
     * @return string
     */
    public function hash($password) {
        return Passwords::hash($password);
    }

    /**
     * Get searching data for customer autocomplete
     * @param string $term of search
     * @return array
     */
    public function getAutocompleteData($term)
    {
        $columns = ['name', 'surname', 'company'];
        $alias = 'c';
        $like = SQLHelper::termToLike($term, $alias, $columns);

        $result = $this->em->getRepository(Customer::class)
            ->createQueryBuilder($alias)
            ->where($like)
            ->setMaxResults('20')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function createFromOrder($values)
    {
        $ent = null;

        try {
            $ent = new Customer();
            $ent->setCompany($values->company);
            $ent->setname($values->name);
            $ent->setIdNo($values->idNo);
            $ent->setVatNo($values->vatNo);
            $ent->setPhone($values->phone);
            $ent->setEmail($values->email);
            $ent->setVatPay($values->payVat);
            $ent->setStreet($values->street);
            $ent->setZip($values->zip);
            $ent->setCity($values->city);
            $ent->setCountry($values->country);
            $ent->setDeliveryToOther($values->deliveryToOther);

            $ent = $this->insertNew($ent);
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return $ent;
    }

    public function insertSale($idCustomer, $saleMark, $saleValue)
    {
        $customer = $this->get()->find($idCustomer);
        $mark = $this->gEMProductMark()->find($saleMark);
        $sale = $this->gEMCustomerSales()->findBy(['customer' => $idCustomer, 'mark' => $saleMark]);
        if ($customer && $mark && !$sale) {
            $entity = new CustomerSales();
            $entity->setCustomer($customer);
            $entity->setMark($mark);
            $entity->setValue($saleValue);
            return $this->insertNew($entity);
        }
        return false;
    }

    public function updateSale($idSale, $saleValue)
    {
        $sale = $this->gEMCustomerSales()->find($idSale);
        if ($sale) {
            $sale->setValue($saleValue);
            $this->save();
            return true;
        }
        return false;
    }

    public function removeSale($idSale)
    {
        $sale = $this->gEMCustomerSales()->find($idSale);
        if ($sale) {
            $this->remove($sale);
            $this->save();
            return true;
        }
        return false;
    }
}
