<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity
 */
class DpdPickup extends ABaseEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    protected $code;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $company;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $street;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $houseNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $postcode;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fax;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $homepage;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $pickupAllowed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $returnAllowed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $expressAllowed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $cardpaymentAllowed;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $service;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $longitude;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}