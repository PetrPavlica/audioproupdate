<?php

namespace Front\Components\Menu;

use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Control;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\ITemplateFactory;
use Front\Model\Facade\FrontFacade;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;

class MenuControl extends UI\Control
{

    /** @var ITemplateFactory */
    public $templateFactory;

    /** @var LinkGenerator */
    public $linkGenerator;

    /** @var FrontFacade */
    public $facade;

    /** @var IStorage */
    public $storage;

    public function __construct(
        ITemplateFactory $templateFactory,
        LinkGenerator $linkGenerator,
        FrontFacade $facade,
        IStorage $storage
    ) {
        $this->templateFactory = $templateFactory;
        $this->linkGenerator = $linkGenerator;
        $this->facade = $facade;
        $this->storage = $storage;
        parent::__construct();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/layout.latte');

        $key = 'menuCategories' . $this->parent->locale;
        $content = $this->storage->read($key);
        if ($content == null) { // if null, create and cash
            $content = "";
            $mainCategory = $this->facade->gEMProductCategory()->findBy(['parentCategory' => null, 'active' => 1],
                ['orderCategory' => 'ASC']);
            foreach ($mainCategory as $category) {
                if (count($category->childCategory) == 0) {
                    $content .= $this->getSimpleItem($category);
                } else {
                    $content .= $this->getDropDownItem($category);
                }
            }
            $this->storage->write($key, $content, [
                Cache::TAGS => ["categories"],
                Cache::EXPIRE => '86400',
            ]);
        }
        $template->contentMenu = $content;
        $template->locale = $this->parent->locale;

        // render template
        $template->render();
    }

    public function getSimpleItem($category)
    {
        $template = $this->templateFactory->createTemplate();
        $template->category = $category;
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/simpleItem.latte');
        $template->locale = $this->parent->locale;

        return $template;
    }

    public function getDropDownItem($category)
    {
        $template = $this->templateFactory->createTemplate();
        $template->category = $category;
        $template->childs = $this->facade->gEMProductCategory()->findBy([
            'parentCategory' => $category->id,
            'active' => 1
        ], ['orderCategory' => 'ASC']);
        $template->locale = $this->parent->locale;

        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/dropDownItem.latte');

        return $template;
    }

}
