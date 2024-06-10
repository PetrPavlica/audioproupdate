<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\WebArticles;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;
use Intra\Model\Database\Entity\WebMenu;
use Nette\Database\Context;

class WebArticlesFacade extends BaseFacade {

    /** @var IStorage */
    private $storage;

    /** @var Context */
    public $db;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, Context $db) {
        parent::__construct($em, WebArticles::class);
        $this->db = $db;
    }

    public function getSelectBoxCategoryAll($menu = null)
    {

        $menu = $this->gEMWebMenu()->findBy([], ['id' => 'ASC']);

            $output = [];

            foreach ($menu as $m) {
                $output[$m->id] = $m->name;
            }

        return $output;
    }

    public function saveImage($path, $article)
    {
        $article->setImage($path);
        $this->save();
    }

    public function deleteImage($articleId)
    {
        $article = $this->get()->find($articleId);
        if (count($article)) {
            if (file_exists($article->image)) {
                unlink($article->image);
            }
            $article->setImage('');
            $this->save();
            return true;
        }
        return false;
    }
    public function deleteImageGalerie($imgId)
    {
        $image = $this->gEMWebArticlesImage()->find($imgId);
        if ($image) {
            //$this->imageStorage->delete($image->path);
            //@unlink($image->path);
            if (file_exists($image->path)) {
                unlink($image->path);
            }
            $this->remove($image);
            return basename($image->path);
        }

        return false;
    }

    public function updateGallery($values)
    {
        if (isset($values['imgId'])) {
            foreach($values['imgId'] as $k => $v) {
                $image = $this->gEMWebArticlesImage()->find($v);
                if ($image) {
                    $image->setAlt($values['imgAlt'][$k]);
                    $image->setOrderImg(intval($values['imgOrder'][$k]));
                }
            }
            $this->save();
        }
    }

    public function addImage($articleId, $image)
    {
        $checkImage = $this->db->query('SELECT id FROM web_articles_image WHERE article_id = ? and path = ?', $articleId, $image)->fetch();
        if (!$checkImage) {
            $lastOrder = $this->db->query('SELECT order_img FROM web_articles_image WHERE article_id = ? ORDER BY order_img DESC', $articleId)->fetchField();
            if (!$lastOrder) {
                $lastOrder = 1;
            } else {
                $lastOrder++;
            }
            $data = [
                'article_id' => $articleId,
                'path' => $image,
                'order_img' => $lastOrder,
                'alt' => pathinfo($image,PATHINFO_FILENAME),
            ];
            $this->db->table('web_articles_image')->insert($data);

            return true;
        }

        return false;
    }

}
