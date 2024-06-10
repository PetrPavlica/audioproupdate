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
class ProductImage extends ABaseEntity {

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
     */
    protected $alt;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="images")
     */
    protected $product;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $path;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $pathThumb;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isMain;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $recreated;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $orderImg;

    public function __construct($data = null) {
        parent::__construct($data);
        $this->isMain = false;
    }

}

?>