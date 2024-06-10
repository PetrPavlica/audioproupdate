<?php

namespace Intra\Components\Exporter;

use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Control;
use Intra\Model\Facade\ProductFacade;
use Nette\Application\UI\ITemplateFactory;
use Nette\Application\LinkGenerator;
use Nette\Utils\Strings;
use Symfony\Component\Config\Definition\Exception\Exception;
use Intra\Model\Utils\ProductHelper\ProductHelper;

class XMLExporter extends UI\Control {

    /** @var ITemplateFactory */
    public $templateFactory;

    /** @var LinkGenerator */
    public $linkGenerator;

    /** @var ProductFacade */
    public $productFac;

    /** @var string */
    public $dir;

    /** @var string */
    public $basePath;

    /** ProductHelper */
    public $productHelper;

    public function __construct(ITemplateFactory $templateFactory, LinkGenerator $linkGenerator,
        ProductFacade $productFac, ProductHelper $productHelper) {
        parent::__construct();
        $this->templateFactory = $templateFactory;
        $this->productFac = $productFac;
        $this->linkGenerator = $linkGenerator;
        $this->productHelper = $productHelper;
    }

    public function setDir($dir) {
        $this->dir = $dir;
    }

    public function setBasePath($basePath) {
        $this->basePath = $basePath;
    }

    public function createHeurekaXML($currency = 'CZK')
    {
        /*$currencyObj = $this->productFac->gEMCurrency()->findOneBy(['code' => $currency]);
        if ($currency == "CZK") {
            $currency = "";
        }
        @unlink($this->dir . 'heureka' . $currency . '.xml');
        $xml = fopen($this->dir . 'heureka' . $currency . '.xml', "w") or die("Unable to open file!");
        fwrite($xml, '<?xml version="1.0" encoding="utf-8"?>'."\r\n");
        fwrite($xml, '<SHOP>'."\r\n");

        $products = $this->productFac->gEMProduct()->findBy(['active' => '1', 'notInFeeds !=' => 1]);

        if ($products) {
            $template = $this->templateFactory->createTemplate();
            $template->setFile(__DIR__ . '/templates/heurekaProduct.latte');
            $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
            $template->control = $this;
            $template->basePath = $this->basePath;
            $template->deliveryMethods = $this->productFac->gEMDeliveryMethod()->findBy(['active' => 1]);

            if ($currency == "") {
                $template->locale = 'cs';
            }
            if ($currency == 'EUR') {
                $template->locale = 'sk';
            }

            $template->currency = $currencyObj;
            $template->productHelper = $this->productHelper;
            $template->currencyArr = $template->currency->toArray();
            foreach($products as $p) {
                $template->product = $p;

                fwrite($xml, (string)$template."\r\n");
                //$this->productFac->getEm()->clear();
            }
        }

        fwrite($xml, '</SHOP>');
        fclose($xml);*/
        $template = $this->templateFactory->createTemplate();
        $template->setFile(__DIR__ . '/templates/heureka.latte');
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->control = $this;
        $template->basePath = $this->basePath;
        $template->products = $this->productFac->gEMProduct()->findBy(['active' => '1', 'notInFeeds !=' => 1]);
        $template->deliveryMethods = $this->productFac->gEMDeliveryMethod()->findBy(['active' => 1]);
        $template->currency = $this->productFac->gEMCurrency()->findOneBy(['code' => $currency]);
        $template->productHelper = $this->productHelper;
        $template->currencyArr = $template->currency->toArray();
        $template->em = $this->productFac->getEM();

        if ($template->currency) {

            if ($currency == "CZK") {
                $currency = "";
                $template->locale = 'cs';
            }
            if ($currency == 'EUR') {
                $template->locale = 'sk';
            }

            file_put_contents($this->dir . 'heureka' . $currency . '.xml', $template);
        } else {
            throw new Exception("Nebyla nalezena definovaná měna");
        }
    }

    public function createZboziCzXML() {
        $template = $this->templateFactory->createTemplate();
        $template->setFile(__DIR__ . '/templates/zbozi.latte');
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->control = $this;
        $template->basePath = $this->basePath;
        $template->products = $this->productFac->gEMProduct()->findBy(['active' => '1', 'notInFeeds !=' => 1]);
        $template->deliveryMethods = $this->productFac->gEMDeliveryMethod()->findBy(['active' => 1]);
        $template->currency = $this->productFac->gEMCurrency()->findOneBy(['code' => 'CZK'])->toArray();
        $template->productHelper = $this->productHelper;
        $template->em = $this->productFac->getEM();
        file_put_contents($this->dir . 'zbozi.xml', $template);
    }

    public function createGoogleMerchantsXML() {
        $template = $this->templateFactory->createTemplate();
        $template->setFile(__DIR__ . '/templates/googleMerchants.latte');
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->control = $this;
        $template->basePath = $this->basePath;
        $template->products = $this->productFac->gEMProduct()->findBy(['active' => '1', 'notInFeeds !=' => 1]);
        $template->deliveryMethods = $this->productFac->gEMDeliveryMethod()->findBy(['active' => 1]);
        $template->currency = $this->productFac->gEMCurrency()->findOneBy(['code' => 'CZK'])->toArray();
        $template->productHelper = $this->productHelper;
        $template->em = $this->productFac->getEM();
        file_put_contents($this->dir . 'googleMerchants.xml', $template);
    }

    public function getBreadcrump($product) {
        if ($product->category) {
            $menuPath = $tmp2 = "";
            do {
                if (!isset($tmp))
                    $tmp = $product->category;
                else
                    $tmp = $tmp->parentCategory;

                $tmp2 = $tmp->name . ' | ';

                $menuPath = $tmp2 . $menuPath;
            } while ($tmp->parentCategory != NULL);
            return substr($menuPath, 0, -3);
        }
    }

    public function getSlug($name) {
        return Strings::webalize($name);
    }

    public function createMallXML($currency = 'CZK')
    {
        $template = $this->templateFactory->createTemplate();
        $template->setFile(__DIR__ . '/templates/mall.latte');
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->control = $this;
        $template->basePath = $this->basePath;
        $template->products = $this->productFac->gEMProduct()->findBy(['active' => '1', 'exportMall' => 1]);
        $template->currency = $this->productFac->gEMCurrency()->findOneBy(['code' => $currency]);
        $template->productHelper = $this->productHelper;
        $template->currencyArr = $template->currency->toArray();
        $template->em = $this->productFac->getEM();
        $template->settings = $this->productFac->getAllsettings();

        if ($template->currency) {

            if ($currency == "CZK") {
                $currency = "";
                $template->locale = 'cs';
            }
            if ($currency == 'EUR') {
                $template->locale = 'sk';
            }

            file_put_contents($this->dir . 'mall' . $currency . '.xml', $template);
        } else {
            throw new Exception("Nebyla nalezena definovaná měna");
        }
    }

    public function createAvailabilityMallXML()
    {
        $template = $this->templateFactory->createTemplate();
        $template->setFile(__DIR__ . '/templates/availbilityMall.latte');
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->control = $this;
        $template->basePath = $this->basePath;
        $template->products = $this->productFac->gEMProduct()->findBy(['active' => '1', 'exportMall' => 1]);
        $template->em = $this->productFac->getEM();

        file_put_contents($this->dir . 'availabilityMall.xml', $template);
    }
}
