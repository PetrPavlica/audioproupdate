<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;

class PagePresenter extends BaseFrontPresenter {

    public function startup() {
        parent::startup();
    }

    public function renderDefault($id)
    {
        $slug = $this->request->getParameter('wslug');
        $isCategory = true;
        $condition = [];
        if (!($this->user->loggedIn && !$this->user->isInRole('visitor'))) { // adminovi zobrazíme i stránky, které nejsou veřejné
            $condition[ 'visible' ] = 1;
        }
        $condition[ 'slug' ] = $slug;
        $menu = $this->facade->gEMWebMenu()->findOneBy($condition);
        if (!$menu) { // Pokud menu nenaleznu, tak pokusím spojit slug a název - dohledávám menu, které routa rozdělila špatně slug
            $condition[ 'slug' ] = $id . '-' . $slug;
            $isCategory = false;
            $menu = $this->facade->gEMWebMenu()->findOneBy($condition);
        }
        if ($menu) {
            $isCategory = false;
            $this->setView($menu->template->path);
            $this->template->resource = $this->facade->gEMWebResources()->findAssoc(['pageId' => $menu->id], 'divId');
            $this->template->menu = $menu;
            if ($menu->template->path == 'blog') {
                $this->template->subMenu = $this->facade->gEMWebMenu()->findBy(['parentMenu' => $menu->id, 'visible' => true], ['orderPage' => 'ASC', 'name' => 'ASC']);
            }
            if ($menu->template->path == 'article') {
                $this->template->articles = $articles = $this->facade->gEMWebArticles()->findBy(['active' => 1, 'menu.menu.id' => $menu->id], ['orderArticle' => 'ASC']);
                $images = [];
                foreach ($articles as $a) {
                    $images[$a->id] = $this->facade->gEMWebArticlesImage()->findBy(['article' => $a->id], ['orderImg' => 'ASC']);
                }
                $this->template->images = $images;
                $this->template->slugProd = $slug;
            }
            if ($id == 1) {
                $this->template->banners = $this->facade->gEMBannerProduct()->findBy(['active' => 1, 'onFront' => 0, 'type' => 5]);
            }elseif($id == 2) {
                $this->template->banners = $this->facade->gEMBannerProduct()->findBy(['active' => 1, 'onFront' => 0, 'type' => 6]);
            }elseif($id == 3) {
                $this->template->banners = $this->facade->gEMBannerProduct()->findBy(['active' => 1, 'onFront' => 0, 'type' => 7]);
            }elseif($id == 4) {
                $this->template->banners = $this->facade->gEMBannerProduct()->findBy(['active' => 1, 'onFront' => 0, 'type' => 8]);
            }elseif ($id == 7) {
                $this->template->banners = $this->facade->gEMBannerProduct()->findBy(['active' => 1, 'onFront' => 0, 'type' => 2]);
            }elseif ($id == 8) {
                $this->template->buyLogo = $this->facade->gEMSellers()->findBy(['active' => 1], ['orderSellers' => 'ASC']);
                $this->template->banners = $this->facade->gEMBannerProduct()->findBy(['active' => 1, 'onFront' => 0, 'type' => 4]);
            }elseif ($id == 9) {
                $this->template->banners = $this->facade->gEMBannerProduct()->findBy(['active' => 1, 'onFront' => 0, 'type' => 3]);
            }
        } else {
            $this->flashMessage('Hledaná stránka neexistuje!', 'warning');
            $this->redirect('Front:default');
        }

        if ($id && $isCategory) {
            $this->template->category = $this->facade->gEMProductCategory()->find($id);
        }
    }

}
