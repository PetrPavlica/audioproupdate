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
class ProductMark extends ABaseEntity
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
     * FORM title="Název"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Název'
     * FORM required='Toto pole je povinné'
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $publicName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='text'
     * FORM title="Prodloužená záruka na"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Zadejte počet let prodloužené záruky'
     *
     * GRID type='number'
     * GRID title="Prodloužená záruka na x let"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $extendedWarranty;

    /**
     * @ORM\ManyToOne(targetEntity="ProductProducer", inversedBy="id")
     * FORM type='select'
     * FORM title='Výrobce značky'
     * FORM prompt='-- vyberte výrobce'
     * FORM data-entity=Intra\Model\Database\Entity\ProductProducer[company]
     * FORM attribute-class="form-control selectpicker"
     * FORM attribute-data-live-search="true"
     *
     * GRID type='text'
     * GRID title="Výrobce"
     * GRID entity-link='company'
     * GRID visible='true'
     * GRID entity='Intra\Model\Database\Entity\ProductProducer'
     * GRID entity-alias='pp'
     * GRID filter=single-entity #['company']
     */
    protected $producer;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Prodloužená záruka zdarma"
     *
     * GRID type='bool'
     * GRID title="Prodloužená záruka zdarma"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $extendedWarrantyFree;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}