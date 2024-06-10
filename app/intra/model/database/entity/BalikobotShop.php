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
class BalikobotShop extends ABaseEntity
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
     * FORM title="Název e-shopu"
     * FORM attribute-placeholder='Název e-shopu'
     * FROM required='Toto je povinné pole'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Název e-shopu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Testovací API uživatel"
     * FORM attribute-placeholder='Testovací API uživatel'
     * FROM required='Toto je povinné pole'
     * FORM attribute-class='form-control input-md'
     */
    protected $apiUserTest;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Testovací API klíč"
     * FORM attribute-placeholder='Testovací API klíč'
     * FROM required='Toto je povinné pole'
     * FORM attribute-class='form-control input-md'
     */
    protected $apiKeyTest;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="API uživatel"
     * FORM attribute-placeholder='API uživatel'
     * FROM required='Toto je povinné pole'
     * FORM attribute-class='form-control input-md'
     */
    protected $apiUser;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="API klíč"
     * FORM attribute-placeholder='API klíč'
     * FROM required='Toto je povinné pole'
     * FORM attribute-class='form-control input-md'
     */
    protected $apiKey;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Výchozí?"
     * FORM default-value='false'
     *
     * GRID type='bool'
     * GRID title="Výchozí?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $defaultVal;


    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}

?>