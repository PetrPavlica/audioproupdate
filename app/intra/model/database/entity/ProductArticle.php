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
class ProductArticle extends ABaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
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
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="articles")
     * FORM type='hidden'
     * FORM data-entity=Intra\Model\Database\Entity\Product[id]
     */
    protected $product;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-placeholder='Název produktu'
     * FORM required='Název je povinné pole!'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Text"
     *
     * GRID type='text'
     * GRID title="Text"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $content;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Typ"
     * FORM prompt='-- vyberte'
     * FORM data-own=['1' > 'Obrázek nebo video přes celé'|'2' > 'Obrázek vlevo, text vpravo'|'3' > 'Obrázek vpravo, text vlevo']
     * FORM attribute-class="form-control"
     *
     * GRID type='translate-text'
     * GRID title="Typ"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'1' > 'Obrázek nebo video přes celé'|'2' > 'Obrázek vlevo, text vpravo'|'3' > 'Obrázek vpravo, text vlevo']
     * GRID visible='true'
     * GRID align='center'
     * GRID replacement=#['1' > 'Obrázek nebo video přes celé'|'2' > 'Obrázek vlevo, text vpravo'|'3' > 'Obrázek vpravo, text vlevo']
     */
    protected $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Pořadí (priorita)"
     * FORM attribute-placeholder='Pořadí'
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Pořadí"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $orderArticle;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title=" Aktivní (zobrazen v eshopu)"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Aktivní"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $active;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $image;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $video;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

    public function getTypeText()
    {
        $types = [
            '1' => 'Obrázek nebo video přes celé',
            '2' => 'Obrázek vlevo, text vpravo',
            '3' => 'Obrázek vpravo, text vlevo'
        ];

        return isset($types[$this->type]) ? $types[$this->type] : '';
    }
}