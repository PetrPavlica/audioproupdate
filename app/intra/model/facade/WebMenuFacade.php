<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\WebMenu;
use Nette\Utils\Strings;

class WebMenuFacade extends BaseFacade {

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        parent::__construct($em, WebMenu::class);
    }

    public function generateSlug($idMenu, $name) {
        $counter = NULL;
        $slug = Strings::webalize($name);

        update:
        $res = $this->get()->findBy(['slug' => $slug . $counter, 'id !=' => $idMenu]);
        if (count($res)) {
            $counter++;
            goto update;
        }

        $menu = $this->get()->find($idMenu);
        $menu->setSlug($slug . $counter);
        $this->save();
    }

    public function saveImage($path, $menu)
    {
        $menu->setImage($path);
        $this->save();
    }

    public function deleteImage($menuId)
    {
        $menu = $this->get()->find($menuId);
        if (count($menu)) {
            if (file_exists($menu->image)) {
                unlink($menu->image);
            }
            $menu->setImage('');
            $this->save();
            return true;
        }
        return false;
    }

}
