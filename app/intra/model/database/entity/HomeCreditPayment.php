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
class HomeCreditPayment extends ABaseEntity {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * FORM type="hidden"
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $action;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $locale;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $hcRet;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $hcOrderCode;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $hcEvid;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $customerName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $customerSurName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $customerContactStreet;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $customerContactCp;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $customerContactCity;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $customerContactZip;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $foundedDate;

    public function __construct($data = null) {
        $this->foundedDate = new \DateTime();
        parent::__construct($data);
    }

}

?>