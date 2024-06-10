<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Presenter;
use Nette\Caching\IStorage;
use Kdyby\Translation\Translator;
use Nette\Caching\Cache;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter {

    /** @persistent */
    public $locale;

    /** @var Translator @inject */
    public $translator;

    /** @var IStorage @inject */
    public $storage;

    /** @var Cache */
    public $cache;

    protected function startup() {
        parent::startup();
        $this->cache = new Cache($this->storage);
        //$this->translator->setLogger($this->storage); // pro logování chybějících překladů
    }

    public function afterRender() {
        if ($this->isAjax() && $this->hasFlashSession())
            $this->redrawControl('flashess');
    }

    public function t($message, $count = NULL, $parameters = [], $domain = NULL, $locale = NULL) {
        return $this->translator->trans($message, $count, $parameters, $domain, $locale);
    }

}
