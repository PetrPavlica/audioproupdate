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
class Orders extends ABaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * FORM type="hidden"
     *
     * GRID type='number'
     * GRID title="Id"
     * GRID sortable='true'
     * GRID visible='true'
     * GRID align='left'
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Variabilní symbol"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='vygeneruje se sám po uložení'
     * FORM disabled=true
     *
     * GRID type='text'
     * GRID title="Variabilní symbol"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $variableSymbol;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='date'
     * FORM title="Datum založení"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='datetime'
     * GRID title="Datum založení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $foundedDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='date'
     * FORM title="Datum poslední úpravy"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='datetime'
     * GRID title="Datum poslední úpravy"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $lastUpdate;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Jméno a příjmení"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Jméno a příjmení'
     * FORM required="Jméno a příjmení je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Jméno a příjmení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="OrderState", inversedBy="id")
     * FORM type='select'
     * FORM title='Stav objednávky'
     * FORM prompt='-- vyberte stav objednávky'
     * FORM data-entity=Intra\Model\Database\Entity\OrderState[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Stav objednávky"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\OrderState'
     * GRID entity-alias='orstt'
     * GRID filter=single-entity #['name']
     */
    protected $orderState;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="id")
     * FORM type='select'
     * FORM title='Zákazník'
     * FORM prompt='-- vyberte zákazníka'
     * FORM data-entity=Intra\Model\Database\Entity\Customer[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Zákazník"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\Customer'
     * GRID entity-alias='zak'
     * GRID filter=single-entity #['name']
     */
    protected $customer;

    /**
     * @ORM\ManyToOne(targetEntity="PaymentMethod", inversedBy="id")
     * FORM type='select'
     * FORM title='Platební podmínky'
     * FORM prompt='-- vyberte platební podmínky'
     * FORM data-entity=Intra\Model\Database\Entity\PaymentMethod[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Platební podmínky"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\PaymentMethod'
     * GRID entity-alias='paym'
     * GRID filter=single-entity #['name']
     */
    protected $paymentMethod;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Cena platební metody"
     * FORM attribute-placeholder='Cena platební metody'
     * FORM required='0'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Cena platební metody"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $payMethodPrice;

    /**
     * @ORM\ManyToOne(targetEntity="DeliveryMethod", inversedBy="id")
     * FORM type='select'
     * FORM title='Metoda dodání zboží'
     * FORM prompt='-- vyberte metodu'
     * FORM data-entity=Intra\Model\Database\Entity\DeliveryMethod[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Metoda dodání zboží"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\DeliveryMethod'
     * GRID entity-alias='delm'
     * GRID filter=single-entity #['name']
     */
    protected $deliveryMethod;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Místo pro doručení"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Místo pro doručení'
     * FORM disabled="true"
     *
     * GRID type='text'
     * GRID title="Místo pro doručení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $deliveryPlace;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Objednaná doprava?"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Objednaná doprava?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $deliveryOrdered;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $freeDelivery;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="EU odběratel (nulové DPH)"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="EU odběratel?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $euVat;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Cena dopravy"
     * FORM attribute-placeholder='Cena dopravy'
     * FORM required='0'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Cena dopravy"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $payDeliveryPrice;

    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     * FORM type='text'
     * FORM title="Číslo faktury"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Číslo faktury'
     * FORM disabled="true"
     *
     * GRID type='text'
     * GRID title="Číslo faktury"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $codeInvoice;

    /**
     * @ORM\OneToMany(targetEntity="ProductInOrder", mappedBy="orders")
     */
    protected $products;

    /**
     * @ORM\OneToMany(targetEntity="ProductInCreditNote", mappedBy="order")
     */
    protected $productsInCreditNote;

    /**
     * @ORM\OneToMany(targetEntity="DiscountInOrder", mappedBy="orders")
     */
    protected $discount;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Firma"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Firma'
     *
     * GRID type='text'
     * GRID title="Firma"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $company;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Firma?"
     *
     * GRID type='bool'
     * GRID title="Firma?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Soukromá osoba'|'1' > 'Firma']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $isCompany;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Plátce DPH?"
     *
     * GRID type='bool'
     * GRID title="Plátce DPH?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neplátce DPH'|'1' > 'Plátce DPH']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $payVat;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $expeditionToday;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='email'
     * FORM title="Email"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Email'
     *
     * GRID type='text'
     * GRID title="Email"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='integer'
     * FORM title="IČ"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='IČ'
     *
     * GRID type='text'
     * GRID title="IČ"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $idNo;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="IČ DPH"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='IČ DPH'
     *
     * GRID type='text'
     * GRID title="IČ DPH"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $vatNo2;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="DIČ"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='DIČ'
     *
     * GRID type='text'
     * GRID title="DIČ"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $vatNo;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Doručit na dodací adresu?"
     *
     * GRID type='bool'
     * GRID title="Doručit na dodací adrs.?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $deliveryToOther;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Jméno kontaktní osoby"
     * FORM attribute-class='form-control'
     * FORM attribute-placeholder='Jméno'
     *
     * GRID type='text'
     * GRID title="Jméno kontaktní osoby"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $contactPerson;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='email'
     * FORM title="Email kontaktní osoby"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Email'
     *
     * GRID type='text'
     * GRID title="Email kontaktní os."
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $emailDelivery;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Telefon"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Telefon'
     *
     * GRID type='text'
     * GRID title="Telefon"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Ulice a č. p."
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Ulice'
     *
     * GRID type='text'
     * GRID title="Ulice doručení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $streetDelivery;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Město"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Město'
     *
     * GRID type='text'
     * GRID title="Město doručení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $cityDelivery;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="PSČ"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='PSČ'
     *
     * GRID type='text'
     * GRID title="PSČ doručení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $zipDelivery;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Stát"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Stát'
     *
     * GRID type='text'
     * GRID title="Stát dodání"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $countryDelivery;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Stát"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Stát'
     *
     * GRID type='text'
     * GRID title="Stát"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $country;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Ulice a č. p."
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Ulice'
     *
     * GRID type='text'
     * GRID title="Ulice fakturační"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $street;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Město"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Město'
     *
     * GRID type='text'
     * GRID title="Město fakturační"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="PSČ"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='PSČ'
     *
     * GRID type='text'
     * GRID title="PSČ fakturační"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $zip;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Číslo účtu"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Číslo účtu'
     *
     * GRID type='text'
     * GRID title="Číslo účtu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $accountNumber;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Poznámka od zákazníka"
     * FORM attribute-placeholder='poznámka'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Poznámka od zákazníka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $comment;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Cena celkem"
     * FORM attribute-placeholder='Cena celkem'
     * FORM required='0'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='number'
     * GRID title="Cena celkem"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID sum='true'
     */
    protected $totalPrice;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $totalPriceWithoutDeliPay;

    /**
     * @ORM\ManyToOne(targetEntity="Currency", inversedBy="id")
     * FORM type='select'
     * FORM title='Měna'
     * FORM prompt='-- vyberte měnu'
     * FORM data-entity=Intra\Model\Database\Entity\Currency[code]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Měna"
     * GRID entity-link='code'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\Currency'
     * GRID entity-alias='currr'
     * GRID filter=single-entity #['code']
     */
    protected $currency;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Konstantní symbol"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Konstantní symbol"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $constantSymbol;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Specifický symbol"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Specifický symbol"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $specificSymbol;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Forma úhrady"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Forma úhrady"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $typeOfPayment;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='date'
     * FORM title="Datum splatnosti"
     * FORM attribute-class='form-control input-md'
     * FORM required='Datum splatnosti je povinné pole!'
     *
     * GRID type='datetime'
     * GRID title="Datum splatnosti"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $dueDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='date'
     * FORM title="Datum úhrady"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='datetime'
     * GRID title="Datum úhrady"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $paymentDate;

    /**
     * @ORM\OneToMany(targetEntity="OrderRefund", mappedBy="orders")
     */
    protected $refund;

    /**
     * @ORM\OneToMany(targetEntity="BalikobotPackage", mappedBy="orders")
     */
    protected $packages;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $errorInPayment;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='number'
     * FORM title="Heuréka ID"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Heuréka ID"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $heurekaId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $heurekaPaymentId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $heurekaPaymentTitle;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $heurekaPaymentStatus;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $heurekaPaymentDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $ratingDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $unfinishedDate;

    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     * FORM type='text'
     * FORM title="Číslo dobropisu"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Číslo dobropisu'
     * FORM disabled="true"
     *
     * GRID type='text'
     * GRID title="Číslo dobropisu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $codeCreditNote;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='date'
     * FORM title="Datum vystavení dobropisu"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='datetime'
     * GRID title="Datum vystavení dobropisu"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $creditNoteDate;

    /**
     * @ORM\OneToMany(targetEntity="DpdPackage", mappedBy="order")
     * @ORM\OrderBy({"orderNumber" = "ASC"})
     */
    protected $dpdPackages;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Dobropis včetně dopravy"
     *
     * GRID type='bool'
     * GRID title="Dobropis včetně dopravy"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $creditNoteWithDelivery;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $webPushNotify;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Poznámka"
     * FORM attribute-placeholder='Poznámka'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $commentInternal;

    public function __construct($data = null)
    {
        $this->foundedDate = new DateTime();
        $this->isCompany = false;
        $this->payVat = false;
        $this->deliveryToOther = false;
        $this->euVat = false;
        $this->freeDelivery = false;
        $this->webPushNotify = false;
        parent::__construct($data);
    }

}