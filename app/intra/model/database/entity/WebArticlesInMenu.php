<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 */
class WebArticlesInMenu extends ABaseEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * FORM type="hidden"
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="WebArticles", inversedBy="menu")
     */
    protected $article;

    /**
     * @ORM\ManyToOne(targetEntity="WebMenu", inversedBy="articles")
     */
    protected $menu;

    public function __construct($data = null) {
        parent::__construct($data);
    }
}