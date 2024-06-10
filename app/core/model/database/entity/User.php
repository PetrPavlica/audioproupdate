<?php

namespace App\Core\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity
 */
class User extends ABaseEntity {

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
     * @ORM\Column(type="string")
     * FORM type='text'
     * FORM title="Jméno a příjmení"
     * FORM attribute-placeholder='Jméno a příjmení'
     * FORM attribute-class='form-control input-md'
     * FORM required="Jméno a příjmení je povinné pole"
     *
     * GRID type='text'
     * GRID title="Jméno a příjmení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID align='center'
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * FORM type='password'
     * FORM title="Heslo"
     * FORM attribute-placeholder='Heslo'
     * FORM attribute-class='form-control input-md'
     * FORM rule-min_length='Minimální délka hesla je %d' #[5]
     * FORM required='0'
     */
    protected $password;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='email'
     * FORM title="Email"
     * FORM attribute-placeholder='email'
     * FORM attribute-class='form-control input-md'
     * FORM default-value='@'
     *
     * GRID type='text'
     * GRID title="Email"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID align='center'
     */
    protected $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Telefon"
     * FORM attribute-placeholder='telefon'
     * FORM attribute-class='form-control input-md'
     * FORM default-value='+420 '
     *
     * GRID type='text'
     * GRID title="Telefon"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID align='center'
     */
    protected $phone;

    /**
     * @ORM\Column(type="string", unique=true)
     * FORM type='text'
     * FORM title="Přihlašovací jméno (login)"
     * FORM attribute-placeholder='login'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Přihlašovací jméno (login)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $login;

    /**
     * @ORM\ManyToOne(targetEntity="PermisionGroup", inversedBy="user")
     * FORM type='select'
     * FORM title='Vyber pozici uživatele'
     * FORM prompt='-- vyberte pozici uživatele'
     * FORM required='Pozice uživatele je povinné pole!'
     * FORM data-entity=App\Core\Model\Database\Entity\PermisionGroup[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='translate-text'
     * GRID title="Pozice"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='App\Core\Model\Database\Entity\PermisionGroup'
     * GRID entity-alias='pg'
     * GRID filter=select-entity #[name]['id' > 'ASC']
     */
    protected $group;

    /**
     * @ORM\Column(type="string")
     * FORM type='text'
     * FORM title="Označení provozovny (EET)"
     * FORM attribute-placeholder='Označení provozovny (EET)'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Označení provozovny (EET)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     * GRID align='center'
     */
    protected $eetProvoz;

    /**
     * @ORM\Column(type="string")
     * FORM type='text'
     * FORM title="Označení pokladního zařízení (EET)"
     * FORM attribute-placeholder='Označení pokladního zařízení (EET)'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Označení pokladního zařízení (EET)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     * GRID align='center'
     */
    protected $eetPokl;

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
     * FORM title="Dostávat upozornění o nových objednávkách?"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Upozornění na nové OBJ"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $noticeNewOrder;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Dostávat upozornění o zaplacení objednávky?"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Upozornění na zaplacení OBJ"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $noticeAcceptPay;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     *
     * GRID type='datetime'
     * GRID title="Naposledy přihlášen"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $last_logon;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isHidden;

    public function __construct($data = null) {
        $this->active = TRUE;
        $this->isHidden = FALSE;
        parent::__construct($data);
    }

}

?>