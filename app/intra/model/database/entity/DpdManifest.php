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
class DpdManifest extends ABaseEntity
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
     * GRID type='text'
     * GRID title="Referenční číslo v DPD systému"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $referenceNumber;

    /**
     * @ORM\OneToMany(targetEntity="DpdPackage", mappedBy="manifest")
     */
    protected $packages;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}