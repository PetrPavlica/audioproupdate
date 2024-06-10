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
class ThePayPayment extends ABaseEntity {

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
     * @ORM\Column(type="string", nullable=true)
     */
    protected $merchantId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $accountId;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $value;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $currency;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $methodId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $merchantData;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $ipRating;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isOffline;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $needConfirm;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $signature;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $foundedDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dateAccept;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isPay;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isPrepared;

    /**
     * @ORM\Column(type="integer")
     */
    protected $countCheck;

    public function __construct($data = null) {
        $this->foundedDate = new \DateTime();
        $this->isPay = false;
        $this->isPrepared = false;
        $this->countCheck = 0;
        parent::__construct($data);
    }

}

?>