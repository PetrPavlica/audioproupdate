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
class OrderState extends ABaseEntity {

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
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název pro zákazníka"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Název pro zákazníka'
     * FORM required="Název pro zákazníka je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Název pro zákazníka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $nameForCustomer;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Url"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Url'
     * FORM required="Url je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Url"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $slug;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Pořadí"
     * FORM attribute-placeholder='Pořadí'
     * FROM required='Toto je povinné pole'
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Pořadí"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $orderState;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Zasílat email zákazníkovi při přepnutí do tohoto stavu?"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Zasílat email zákazníkovi?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $notification;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Předmět emailu (dopravce)"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Předmět emailu (dopravce)'
     *
     * GRID type='text'
     * GRID title="Předmět emailu (dopravce)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $subjectEmail;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Předmět emailu (osobní odběr)"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Předmět emailu (osobní odběr)'
     *
     * GRID type='text'
     * GRID title="Předmět emailu (osobní odběr)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $subjectEmailPersonal;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Text emailu (dopravce)"
     *
     * GRID type='text'
     * GRID title="Text emailu (dopravce)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $textEmail;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title="Text emailu (osobní odběr)"
     *
     * GRID type='text'
     * GRID title="Text emailu (osobní odběr)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $textEmailPersonal;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Aktivní"
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
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Přesunout do tohoto stavu při přijmutí objednávky S čekáním na platbu"
     *
     * GRID type='bool'
     * GRID title="Po přijmutí - čekáme"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $acceptOrder;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Přesunout do tohoto při přijmutí objednávky BEZ čekáním na platbu"
     *
     * GRID type='bool'
     * GRID title="Po přijmutí - nečekáme"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $noWaitPay;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Přesunout do tohoto stavu při chybě s platbou"
     *
     * GRID type='bool'
     * GRID title="Po chybě platby"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $afterErrorPay;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Přesunout do tohoto stavu při přijmutí celé platby"
     *
     * GRID type='bool'
     * GRID title="Po zaplacení"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $paySuccess;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Přesunout do tohoto stavu po splatnosti faktury"
     *
     * GRID type='bool'
     * GRID title="Po splatnost"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $overDue;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Přesunout do tohoto stavu při ručním vytvoření objednávky"
     *
     * GRID type='bool'
     * GRID title="Stav ruční vytvoření"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $stateForNew;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Přesunout do tohoto stavu, když je zákazník registrován, a nedokončí objednávku
     *
     * GRID type='bool'
     * GRID title="Stav nedokončené objednávky"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $stateNotFinished;

    /**
     * @ORM\Column(type="boolean",options={"default":1})
     * FORM type='checkbox'
     * FORM title="Započítávat položky stavu do posledně zakoupených produktů"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Zahrnut v posledně koupené"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $includeInLastBuy = 1;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Hlídat v tomto stavu splatnost faktury?"
     *
     * GRID type='bool'
     * GRID title="Hlídat splatnost"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $checkDueDate;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Nezapočítávat do statistik zisku? - v tomto stavu budou stornované a nebo objednávky bez vlivu na zisk"
     *
     * GRID type='bool'
     * GRID title="Stornované?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $storno;

    /**
     * @ORM\ManyToOne(targetEntity="HeurekaCartOrderStates")
     * FORM type='select'
     * FORM title='Heuréka - stav objednávky'
     * FORM prompt='-- vyberte typ'
     * FORM data-entity-values=Intra\Model\Database\Entity\HeurekaCartOrderStates[$name$][][]
     * FORM attribute-class="form-control selectpicker"
     * FORM attribute-data-live-search="true"
     *
     * GRID type='text'
     * GRID title="Heuréka - stav objednávky"
     * GRID sortable='true'
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='Intra\Model\Database\Entity\HeurekaCartOrderStates'
     * GRID entity-alias='heurekat'
     * GRID filter=single-entity #['name']
     */
    protected $heurekaState;

    public function __construct($data = null) {
        parent::__construct($data);
    }

}

?>