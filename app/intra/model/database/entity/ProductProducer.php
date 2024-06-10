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
class ProductProducer extends ABaseEntity {

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
     * GRID visible='true'
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
     * GRID visible='true'
     * GRID align='center'
     */
    protected $payVat;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Jméno a příjmení zástupce"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Jméno a příjmení'
     *
     * GRID type='text'
     * GRID title="Jméno a příjmení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

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

    public function __construct($data = null) {
        parent::__construct($data);
    }

}

?>