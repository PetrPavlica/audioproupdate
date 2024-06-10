<?php

namespace Front\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\Customer;

class CustomerFrontFacade extends BaseFacade {

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        parent::__construct($em, Customer::class);
    }

    public function saveCustomer($values, $allowRewritePass = false) {
        $resPass = false;
        $email = false;
        $entity = NULL;
        if (isset($values['id']) && $values['id'] != "") {
            $entity = $this->get()->find($values['id']);
            if (count($entity)) {
                $entity->data($values);
            } else {
                throw new Exception('Cannot find customer - bad ID: ' . $values['id']);
            }
        } else {
            if ($allowRewritePass) {
                $entity = $this->get()->findOneBy(['email' => trim($values['email'])]);
                $email = true;
            }
        }
        unset($values['id']);
        if (is_null($entity)) {
            barDump($values);
            $entity = new Customer($values);
            $this->insertNew($entity);
            $resPass = true;
        } else {
            if ($email) // pokud je stejný email ale není přihlášen, tak resetuji heslo
                $resPass = true;
            $entity->data($values);
        }
        $this->getEm()->flush();
        return [$entity, $resPass];
    }

    public function createFromFb($customerData, $customerFBData){

        if(isset($customerData["id"])){

            $customer = $this->gEMCustomer()->find($customerData["id"]);
            $customer->setFbId($customerFBData->getId());

            $this->save();

        } else {

            $name = explode(" ", $customerFBData->getName());
            $surname = array_pop($name);
            $name = join(" ", $name);

            $customer = new Customer();

            $customer->setFbId($customerFBData->getId());
            $customer->setName($name);
            $customer->setSurname($surname);
            $customer->setEmail($customerData["email"]);

            $this->insertNew($customer);

        }

        return $customer;
    }

    public function hash($password) {
        return \Nette\Security\Passwords::hash($password);
    }

}
