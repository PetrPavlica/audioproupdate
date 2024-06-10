<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 */
class WebArticles extends ABaseEntity {

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
     * FORM required="Název je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="WebArticlesInMenu", mappedBy="article")
     * FORM type='multiselect'
     * FORM title="Zařazení"
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=Intra\Model\Database\Entity\WebMenu[name]
     * FORM multiselect-entity=Intra\Model\Database\Entity\WebArticlesInMenu[article][menu]
     * FORM attribute-placeholder='Zařazení'
     * FORM attribute-multiple='true'
     */
    protected $menu;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Článek"
     * FORM attribute-class='ckeditor'
     * FORM attribute-placeholder='Článek'
     *
     * GRID type='text'
     * GRID title="Článek"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $article;

    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Pořadí článku"
     * FORM attribute-placeholder='Pořadí článku'
     * FORM required="Toto je je povinné pole!"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Pořadí článku"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $orderArticle;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Typ"
     * FORM prompt='-- vyberte'
     * FORM data-own=['1' > 'Jenom text'|'2' > 'Obrázek přes celé'|'3' > 'Galerie vlevo, text vpravo'|'4' > 'Galerie vpravo, text vlevo'|'5' > 'Galerie přes celé']
     * FORM attribute-class="form-control"
     * FORM required="Toto je je povinné pole!"
     *
     * GRID type='translate-text'
     * GRID title="Typ"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'1' > 'Jenom text'|'2' > 'Obrázek přes celé'|'3' > 'Galerie vlevo, text vpravo'|'4' > 'Galerie vpravo, text vlevo'|'5' > 'Galerie přes celé']
     * GRID visible='true'
     * GRID align='center'
     * GRID replacement=#['1' > 'Jenom text'|'2' > 'Obrázek přes celé'|'3' > 'Galerie vlevo, text vpravo'|'4' > 'Galerie vpravo, text vlevo'|'5' > 'Galerie přes celé']
     */
    protected $type;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Odkaz"
     * FORM attribute-placeholder='Odkaz'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Odkaz"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $link;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Zobrazit článek"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Zobrazit článek"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $active;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Zobrazit nadpis"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Zobrazit nadpis"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $showTitle;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     *
     * GRID type='datetime'
     * GRID title="Datum založení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $created;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     *
     * GRID type='datetime'
     * GRID title="Datum založení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $updated;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $image;

    /**
     * @ORM\OneToMany(targetEntity="WebArticlesImage", mappedBy="article")
     */
    protected $images;

    public function __construct($data = null) {
        $this->active = true;
        $this->created = new DateTime();
        $this->updated = new DateTime();
        parent::__construct($data);
    }

}

?>