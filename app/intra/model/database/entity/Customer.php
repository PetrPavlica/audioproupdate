<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity
 */
class Customer extends ABaseEntity
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
     */
    protected $fbId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Titul"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Titul'
     *
     * GRID type='text'
     * GRID title="Titul"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $degree;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Jméno"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Jméno'
     * FORM required="Jméno je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Jméno"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Příjmení"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Příjmení'
     * FORM required="Příjmení je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Příjmení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $surname;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='password'
     * FORM title="Heslo"
     * FORM attribute-placeholder='Heslo'
     * FORM attribute-class='form-control input-md'
     * FORM rule-min_length='Minimální délka hesla je %d' #[5]
     * FORM required='0'
     */
    protected $password;


    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
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
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Firma?"
     *
     * GRID type='bool'
     * GRID title="Firma?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Soukromá osoba'|'1' > 'Firma']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $isCompany;

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
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Firma"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Firma'
     *
     * GRID type='text'
     * GRID title="Firma doručovací"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $companyDelivery;

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
     * FORM type='integer'
     * FORM title="IČ"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='IČ'
     *
     * GRID type='text'
     * GRID title="IČ doručovací"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $idNoDelivery;

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
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="DIČ"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='DIČ'
     *
     * GRID type='text'
     * GRID title="DIČ doručovací"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $vatNoDelivery;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Doručovat na dodací adresu?"
     *
     * GRID type='bool'
     * GRID title="Doručovat na dodací adrs.?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $deliveryToOther;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Plátce DPH?"
     *
     * GRID type='bool'
     * GRID title="Plátce DPH?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $vatPay;

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
    protected $nameDelivery;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Příjmení kontaktní osoby"
     * FORM attribute-class='form-control'
     * FORM attribute-placeholder='Příjmení'
     *
     * GRID type='text'
     * GRID title="Příjmení kontaktní osoby"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $surnameDelivery;

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
     * FORM title="Telefon"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Telefon'
     *
     * GRID type='text'
     * GRID title="Telefon doručení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $phoneDelivery;

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
     * GRID title="Stát"
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
     * @ORM\ManyToOne(targetEntity="Currency", inversedBy="id")
     * FORM type='select'
     * FORM title='Preferovaná měna'
     * FORM prompt='-- vyberte měnu'
     * FORM data-entity=Intra\Model\Database\Entity\Currency[code]
     * FORM required="Měna je povinné pole!"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Měna"
     * GRID entity-link='code'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\Currency'
     * GRID entity-alias='curr'
     * GRID filter=single-entity #['code']
     */
    protected $currency;

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
     * FORM title="Poznámka"
     * FORM attribute-placeholder='volitelná poznámka'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="PaymentMethod", inversedBy="id")
     * FORM type='select'
     * FORM title='Poslední platební metoda'
     * FORM prompt='-- vyberte'
     * FORM data-entity=Intra\Model\Database\Entity\PaymentMethod[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Poslední platební metoda"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\PaymentMethod'
     * GRID entity-alias='paym'
     * GRID filter=single-entity #['name']
     */
    protected $paymentMethod;

    /**
     * @ORM\ManyToOne(targetEntity="DeliveryMethod", inversedBy="id")
     * FORM type='select'
     * FORM title='Poslední metoda dodání zboží'
     * FORM prompt='-- vyberte metodu'
     * FORM data-entity=Intra\Model\Database\Entity\DeliveryMethod[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Poslední metoda dodání zboží"
     * GRID entity-link='name'
     * GRID visible='false'
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
     * @ORM\OneToMany(targetEntity="FavouriteProduct", mappedBy="customer")
     */
    protected $favourite;

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
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='date'
     * FORM title="Datum"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Datum'
     * FROM disabled='true'
     * FORM attribute-readonly='true'
     *
     * GRID type='datetime'
     * GRID title="Datum"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $foundedDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="IP adresa"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='IP adresa'
     * FROM disabled='true'
     * FORM attribute-readonly='true'
     *
     * GRID type='text'
     * GRID title="IP adresa"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $ip;

    /**
     * @ORM\OneToMany(targetEntity="CustomerSales", mappedBy="customer")
     */
    protected $sales;

    public function __construct($data = null)
    {
        $this->isCompany = false;
        $this->deliveryToOther = false;
        $this->vatPay = false;
        $this->euVat = false;
        $this->foundedDate = new DateTime();
        $this->ip = $_SERVER['REMOTE_ADDR'];

        parent::__construct($data);
    }

}

?>