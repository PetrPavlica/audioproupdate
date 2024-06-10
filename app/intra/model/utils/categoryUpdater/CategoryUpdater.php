<?php

namespace Intra\Model\Utils\CategoryUpdater;

use Intra\Model\Database\Entity\MallCategory;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\GoogleMerchantCategory;
use Intra\Model\Database\Entity\HeurekaCategory;
use Intra\Model\Database\Entity\ZboziCategory;

class CategoryUpdater {

    /** @var string */
    private $urlGoogleMerchants;

    /** @var string */
    private $urlZbozi;

    /** @var string */
    private $urlHeureka;

    /** @var string */
    private $urlMall;

    /** @var EntityManager */
    protected $em;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    /**
     * Check and update category for google merchants
     */
    public function checkGoogleMerchantCategory() {
        $contents = file($this->urlGoogleMerchants);
        if ($contents !== NULL) {
            $query = $this->em->getConnection()->prepare("DELETE FROM google_merchant_category");
            $query->execute();
            $i = 0;
            foreach ($contents as $line) {
                $item = explode(" - ", $line);
                if (count($item) == 2) {
                    $a = new GoogleMerchantCategory();
                    $a->setId($item[0]);
                    $a->setName(substr($item[1], 0, -1));
                    $this->em->persist($a);
                }
                $i++;
                if ($i > 100) {
                    $this->em->flush();
                    $i = 0;
                }
            }
            $this->em->flush();
        }
    }

    /**
     * Check and update category for heureka
     */
    public function checkHeurekaCategory() {
        $contents = $this->fOpenRequest($this->urlHeureka);
        preg_match_all("'<CATEGORY_FULLNAME>([^<]*)</CATEGORY_FULLNAME>'si", $contents, $match);
        if (isset($match[1])) {
            $query = $this->em->getConnection()->prepare("DELETE FROM heureka_category");
            $query->execute();

            $i = 1;
            foreach ($match[1] as $item) {
                $name = str_replace('Heureka.cz | ', '', $item);
                $a = new HeurekaCategory();
                $a->setId($i);
                $a->setName($name);
                $this->em->persist($a);

                $i++;
                if ($i % 100 == 0) {
                    $this->em->flush();
                }
            }
            $this->em->flush();
        }
    }

    /**
     * Check and update category for Zbozi cz
     */
    public function checkZboziCZCategory() {
        $contents = file($this->urlZbozi);
        if ($contents !== NULL) {
            $query = $this->em->getConnection()->prepare("DELETE FROM zbozi_category");
            $query->execute();

            $i = 1;
            foreach ($contents as $line) {
                $item = explode(";", $line);
                if (!is_numeric($item[0]))
                    continue;

                $name = iconv('WINDOWS-1250', 'UTF-8', $item[2]);
                $name = substr($name, 0, -2);

                $a = new ZboziCategory();
                $a->setId($item[0]);
                $a->setName($name);
                $this->em->persist($a);

                $i++;
                if ($i % 100 == 0) {
                    $this->em->flush();
                }
            }
            $this->em->flush();
        }
    }

    private function fOpenRequest($url) {
        $file = fopen($url, 'r');
        $data = stream_get_contents($file);
        fclose($file);
        return $data;
    }

    /**
     * Set url for Google Merchants product's category feed
     * @param string $urlGoogleMerchants
     */
    public function setUrlGoogleMerchants($urlGoogleMerchants) {
        $this->urlGoogleMerchants = $urlGoogleMerchants;
    }

    /**
     * Set url for Zbozi.cz product's category feed
     * @param string $urlZbozi
     */
    public function setUrlZbozi($urlZbozi) {
        $this->urlZbozi = $urlZbozi;
    }

    /**
     * Set url for Heureka product's category feed
     * @param string $urlHeureka
     */
    public function setUrlHeureka($urlHeureka) {
        $this->urlHeureka = $urlHeureka;
    }

    /**
     * Set url for Mall product's category feed
     * @param string $url
     */
    public function setUrlMall($url) {
        $this->urlMall = $url;
    }

    /**
     * Check and update category for Mall
     */
    public function checkMallCategory()
    {
        $file = 'mall.xlsx';
        file_put_contents($file, file_get_contents($this->urlMall));
        if (file_exists($file) && filesize($file)) {
            $query = $this->em->getConnection()->prepare("TRUNCATE mall_category");
            $query->execute();

            $excelReader = \PHPExcel_IOFactory::createReaderForFile($file);
            $excelObj = $excelReader->load($file);
            $worksheet = $excelObj->getSheet(0);
            $lastRow = $worksheet->getHighestRow();
            $lastColumn = $worksheet->getHighestColumn();
            $tempAlph = $alphas = range('A', 'Z');
            foreach ($tempAlph as $p) {
                foreach ($tempAlph as $l) {
                    $alphas[] = $p . $l;
                }
            }
            unset($tempAlph);
            $lastIndex = array_search($lastColumn, $alphas);

            $index = 1;
            for ($row = 1; $row <= $lastRow; $row++) {
                if ($row == 1) {
                    continue;
                }

                $values = [];
                for ($i = 0; $i <= $lastIndex; $i++) {
                    $value = html_entity_decode($worksheet->getCell($alphas[$i] . $row)->getValue());
                    $value = htmlentities($value, null, 'utf-8');
                    $value = str_replace("&nbsp;", "", $value);
                    $value = html_entity_decode($value);
                    $values[] = trim($value);
                }

                if (empty($values[0])) {
                    continue;
                }

                $categoryName = [];
                if (!empty($values[1])) {
                    $categoryName[] = $values[1];
                }
                if (!empty($values[3])) {
                    $categoryName[] = $values[3];
                }
                if (!empty($values[5])) {
                    $categoryName[] = $values[5];
                }
                if (!empty($values[7])) {
                    $categoryName[] = $values[7];
                }
                if (!empty($values[9])) {
                    $categoryName[] = $values[9];
                }
                if (!empty($values[11])) {
                    $categoryName[] = $values[11];
                }
                if (!empty($values[13])) {
                    $categoryName[] = $values[13];
                }
                $categoryId = $values[17];

                $a = new MallCategory();
                $a->setName(implode(' | ', $categoryName));
                $a->setCategoryId($categoryId);
                $this->em->persist($a);

                $index++;
                if ($index % 100 == 0) {
                    $this->em->flush();
                }
            }
            $this->em->flush();
            @unlink($file);
        }
    }
}
