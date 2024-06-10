<?php


namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity
 */
class DpdAddress extends ABaseEntity
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
     * FORM title="Název svozového místa"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Název svozového místa'
     * FORM required="Toto pole je povinné."
     *
     * GRID type='text'
     * GRID title="Název svozového místa"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="ID svozového místa"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='ID svozového místa'
     * FORM required="Toto pole je povinné."
     *
     * GRID type='text'
     * GRID title="ID svozového místa"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $addressId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='select'
     * FORM title="Země"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Země'
     * FORM data-own=['CZ' > 'Česká Republika']
     *
     * GRID type='translate-text'
     * GRID title="Země"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     * GRID filter=select #['' > 'Vše'|'CZ' > 'Česká Republika']
     */
    protected $countryCode;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Město"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Město'
     * FORM required='Toto pole je povinné.'
     *
     * GRID type='text'
     * GRID title="Město"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Ulice"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Ulice'
     * FORM required='Toto pole je povinné.'
     *
     * GRID type='text'
     * GRID title="Ulice"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $street;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="PSČ"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='PSČ'
     * FORM required='Toto pole je povinné.'
     *
     * GRID type='text'
     * GRID title="PSČ"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $zipCode;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}