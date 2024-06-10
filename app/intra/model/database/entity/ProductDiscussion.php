<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity
 */
class ProductDiscussion extends ABaseEntity {

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
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='date'
     * FORM title="Datum vložení"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Datum vložení'
     *
     * GRID type='datetime'
     * GRID title="Datum vložení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $foundedDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Text"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Text'
     * FORM attribute-rows='10'
     *
     * GRID type='text'
     * GRID title="Text"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $text;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="id")
     * FORM type='hidden'
     * FORM data-entity=Intra\Model\Database\Entity\Customer[name]
     *
     * GRID type='text'
     * GRID title="Zákazník"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\Customer'
     * GRID entity-alias='cus'
     * GRID filter=single-entity #['name']
     */
    protected $customer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Core\Model\Database\Entity\User", inversedBy="id")
     * FORM type='hidden'
     * FORM data-entity=App\Core\Model\Database\Entity\User[name]
     *
     * GRID type='text'
     * GRID title="Administrator"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='App\Core\Model\Database\Entity\User'
     * GRID entity-alias='adm'
     * GRID filter=single-entity #['name']
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="ProductDiscussion", inversedBy="reply")
     * FORM type='hidden'
     * FORM data-entity=Intra\Model\Database\Entity\ProductDiscussion[text]
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="ProductDiscussion", mappedBy="parent")
     */
    protected $reply;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="id")
     * FORM type='hidden'
     * FORM data-entity=Intra\Model\Database\Entity\Product[name]
     *
     * GRID type='text'
     * GRID title="Produkt"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\Product'
     * GRID entity-alias='prod'
     * GRID filter=single-entity #['name']
     */
    protected $product;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přezdívka"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Přezdívka'
     *
     * GRID type='text'
     * GRID title="Přezdívka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $nickname;

    public function __construct($data = null) {
        $this->foundedDate = new DateTime();
        parent::__construct($data);
    }

}
