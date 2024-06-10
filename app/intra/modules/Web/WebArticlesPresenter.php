<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\WebArticlesFacade;
use Nette\Application\UI\Form;
use Ublaboo\ImageStorage\ImageStorage;
use App\Core\Model\ACLForm;


class WebArticlesPresenter extends BaseIntraPresenter
{

    /** @var WebArticlesFacade @inject */
    public $facade;

    /** @var ImageStorage @inject */
    public $imageStorage;

    /**
     * ACL name='Správa článků - sekce'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id) {
        if ($id) {
            $this->template->article = $article = $this->facade->get()->find($id);
            $arrArticle = $article->toArray();
            unset($arrArticle['menu']);
            if ($article->menu) {
                foreach($article->menu as $m) {
                    $arrArticle['menu'][] = $m->menu->id;
                }
            }
            $this['form']->setDefaults($arrArticle);
        }
    }

    /**
     * ACL name='Tabulka článků'
     */
    public function createComponentTable() {
        $grid = $this->doctrineGrid->generateGridByAnnotation($this->facade->entity(), $this->user, get_class(), __FUNCTION__);
        $action = $grid->addAction('edit', '', 'WebArticles:edit');
        if ($action)
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-default');
        $this->doctrineGrid->addButonDelete();
        return $this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit článku'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation($this->facade->entity(), $this->user, $this, __FUNCTION__);
        $form->components['menu']->setItems($this->facade->getSelectBoxCategoryAll());
        //$form->components['menu']->setAttribute('data-iconBase', 'glyphhicon');
        $form->addUpload('articleImg', 'Náhledový obrázek')
            ->setRequired(false)// nepovinný
            ->addRule(Form::IMAGE, 'Ikona musí být JPEG, PNG nebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální možná velikost ikonky je 3 Mb', 3072 * 1024/* v bytech */);
        $form->setMessages(['Podařilo se uložit článek', 'success'], ['Nepodařilo se uložit článek!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'articleFormSuccess'];
        return $form;
    }

    public function articleFormSuccess($form, $values) {
        $values2 = $this->request->getPost();
        // ukládám formulář  pomocí automatického save
        $article = $this->formGenerator->processForm($form, $values, true);

        if ($values->articleImg->name != null) {

            if ($article->image) {
                if (file_exists($article->image)) {
                    unlink($article->image);
                }
            }

            $mili = str_replace(".", "", microtime(true));
            $type = substr($values->articleImg->name, strrpos($values->articleImg->name, '.'));

            $nameEx = $article->id . '_' . $mili . $type;
            $dir = 'article-img/';
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            $tmp = $dir.$nameEx;

            $values->articleImg->move($tmp);
            $this->facade->saveImage($tmp, $article);
        }

        // Uložit a zpět
        if (isset($values2['sendBack'])) {
            $this->redirect(':WebArticles:default');
            return;
        }
        // Uložit
        if (isset($values2['send'])) {
            $this->redirect(':WebArticles:edit', ['id' => $article->id]);
            return;
        }

        // Uložit a nový
        if (isset($values2['sendNew'])) {
            $this->redirect(':WebArticles:edit');
            return;
        }
    }

    public function handleDelete($id)
    {
        $article = $this->facade->getEm()->getConnection()->query('SELECT * FROM web_articles WHERE id = '.$id)->fetch();
        if ($article) {
            $this->facade->getEm()->getConnection()->beginTransaction();
            $images = $this->facade->getEm()->getConnection()->query('SELECT * FROM web_articles_image WHERE article_id = ' . $id)->fetchAll();
            if ($images) {
                foreach ($images as $i) {
                    if ($i->path) {
                        $this->imageStorage->delete($i->path);
                    }
                }
                $this->facade->getEm()->getConnection()->query('DELETE FROM web_articles_image WHERE article_id = ' . $id);
            }
            if (is_dir('article-images/' . $id)) {
                @rmdir('article-images/' . $id);
            }

            $this->facade->getEm()->getConnection()->commit();
            $this->facade->getEm()->getConnection()->query('DELETE FROM web_articles_in_menu WHERE article_id = ' . $id);
            $this->facade->getEm()->getConnection()->query('DELETE FROM web_articles WHERE id = ' . $id);
            $this->flashMessage('Článek byl úspěšně smazán.');
        } else {
            $this->flashMessage('Článek nebyl naleze.', 'error');
        }
        $this->redirect('this');
    }

    public function handleDeleteImg($articleId)
    {
        $res = $this->facade->deleteImage($articleId);
        if ($res) {
            $this->flashMessage('Podařilo se smazat obrázek.');
        } else {
            $this->flashMessage('Nepodařilo se smazat obrázek.', 'warning');
        }

        if ($this->isAjax()) {
            $this->redrawControl('article-img');
        } else {
            $this->redirect('this');
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit galerie článku'
     */
    public function createComponentGalleryForm()
    {
        $form = new ACLForm();
        $form->setScope($this->user, get_class(), __FUNCTION__, $this->acl);
        $form->addHidden('id');
        $form->addSubmitAcl('send', 'Uložit změny');
        $form->onSuccess[] = [$this, 'galleryFormSuccess'];
        return $form;
    }

    public function galleryFormSuccess(Form $form, $values)
    {
        $values2 = $this->getRequest()->getPost();

        $this->facade->updateGallery($values2);

        $this->flashMessage('Fotogalerie byla úspěšně upravena!', 'success');

        if ($this->isAjax()) {
            $this->redrawControl('images');
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit fotogalerie článku'
     */
    public function createComponentPhotogalleryForm()
    {
        $form = new ACLForm();
        $form->addHidden('id');
        $form->onSuccess[] = [$this, 'photogalleryFormSuccess'];
        return $form;
    }

    public function photogalleryFormSuccess(Form $form, $values)
    {
        if (!isset($this->ses->files)) {
            $this->ses->files = [];
        }
        if (!isset($this->ses->msg)) {
            $this->ses->msg = [];
        }
        $path = 'article-images/'.$values->id.'/';
        if (!is_dir('article-images')) {
            mkdir('article-images', 0777);
        }
        if (!is_dir($path)) {
            mkdir($path, 0777);
        }
        $files = $this->request->getFiles();
        if ($files) {
            foreach($files as $f) {
                $res = $this->facade->addImage($values->id, $path.$f->getName());
                if ($res) {
                    $f->move($path . $f->getName());
                    $this->ses->files[] = $f->getName();
                } else {
                    $this->ses->msg[] = 'Soubor '.$f->getName().' je již nahraný.';
                }
            }
        }
    }

    public function handleUpdatePhotogallery()
    {
        if ($this->ses->files) {
            foreach ($this->ses->files as $f) {
                $this->flashMessage('Obrázek ' . $f . ' byl úspěšně nahrán.', 'success');
            }
        }
        if ($this->ses->msg) {
            foreach ($this->ses->msg as $m) {
                $this->flashMessage($m, 'info');
            }
        }
        unset($this->ses->files);
        unset($this->ses->msg);
        if ($this->isAjax()) {
            $this->redrawControl('images');
        }
    }

    public function handleDeleteImgGalerie($imgId)
    {
        $res = $this->facade->deleteImageGalerie($imgId);
        if ($res) {
            $this->flashMessage('Obrázek '.$res.' byl úspěšně smazán.', 'success');
        } else {
            $this->flashMessage('Obrázek se nepodařilo smazat.', 'error');
        }

        if ($this->isAjax()) {
            $this->redrawControl('images');
        }
    }
}