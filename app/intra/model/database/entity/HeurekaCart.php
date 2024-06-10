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
class HeurekaCart extends ABaseEntity {

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
     * FORM title="API klíč"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='API klíč'
     * FORM required="API klíč je povinné pole!"
     *
     * GRID type='text'
     * GRID title="API klíč"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $api;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='select'
     * FORM title="Země"
     * FORM prompt='-- vyberte'
     * FORM data-own=['CZ' > 'Česká Republika'|'SK' > 'Slovenská Republika']
     * FORM attribute-class="form-control"
     * FORM required="Země je povinné pole!"
     *
     * GRID type='translate-text'
     * GRID title="Země"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'CZ' > 'Česká Republika'|'SK' > 'Slovenská Republika']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $country;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}

?>