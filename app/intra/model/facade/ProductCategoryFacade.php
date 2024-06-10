<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Intra\Model\Database\Entity\MallCategory;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\ProductCategory;
use Intra\Model\Database\Entity\GroupProductFilter;
use Intra\Model\Database\Entity\ProductFilter;
use Intra\Model\Database\Entity\ProductInFilter;
use Intra\Model\Database\Entity\ZboziCategory;
use Intra\Model\Database\Entity\HeurekaCategory;
use Intra\Model\Database\Entity\GoogleMerchantCategory;
use App\Core\Model\Database\Utils\SQLHelper;
use Intra\Model\Database\Entity\ProductInCategory;
use Intra\Model\Database\Entity\Product;

class ProductCategoryFacade extends BaseFacade
{

    /**
     * Construct
     * @param \Kdyby\Doctrine\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, ProductCategory::class);
    }

    public function getGroupProductFilter()
    {
        return $this->getEm()->getRepository(GroupProductFilter::class);
    }

    public function getProductFilter()
    {
        return $this->getEm()->getRepository(ProductFilter::class);
    }

    public function getProductInFilter()
    {
        return $this->getEm()->getRepository(ProductInFilter::class);
    }

    public function setMainCategory($category)
    {
        $tmp = $category;
        while (1) {
            if ($tmp->parentCategory) {
                $tmp = $tmp->parentCategory;
            } else {
                break;
            }
        }
        $mainCategory = $this->get()->find($tmp->id);
        $category->setMainCategory($mainCategory);
        $this->save();
    }

    public function addGroup($name, $order, $active, $categoryId)
    {
        $category = $this->get()->find($categoryId);
        $group = new GroupProductFilter();
        $group->setName($name);
        $group->setOrderState($order);
        $group->setActive($active);
        $group->setCategory($category);
        $this->insertNew($group);
    }

    public function removeGroupFilters($groupId)
    {
        $group = $this->getGroupProductFilter()->find($groupId);

        if (count($group) && count($group->filters) == 0) {
            $this->getEm()->remove($group);
            $this->getEm()->flush();
        } else {
            return false;
        }
        return true;
    }

    public function saveProductGroupFilters($values)
    {
        if (isset($values[ 'groupId' ]) && count($values[ 'groupId' ])) {
            foreach ($values[ 'groupId' ] as $idx => $id) {
                $group = $this->getGroupProductFilter()->find($id);
                $group->setName($values[ 'groupName' ][ $idx ]);
                if (is_numeric($values[ 'groupOrder' ][ $idx ])) {
                    $group->setOrderState($values[ 'groupOrder' ][ $idx ]);
                }
                if (isset($values[ 'groupActive' ][ $idx ])) {
                    $group->setActive(1);
                } else {
                    $group->setActive(0);
                }
                $this->save();
            }
        }
    }

    public function addFilterToGroup($values)
    {
        $idx = "";
        foreach ($values[ 'addFilter' ] as $i => $v) {
            $idx = $i;
        }
        $filter = new ProductFilter();

        $filter->setName($values[ 'filterNameNew' ][ $idx ]);
        if (is_numeric($values[ 'filterOrderNew' ][ $idx ])) {
            $filter->setOrderState($values[ 'filterOrderNew' ][ $idx ]);
        }
        $filter->setMinValue($values[ 'filterMinNew' ][ $idx ]);
        $filter->setStep($values[ 'filterStepNew' ][ $idx ]);
        $filter->setMaxValue($values[ 'filterMaxNew' ][ $idx ]);
        if (isset($values[ 'filterSliderNew' ][ $idx ])) {
            $filter->setSlider(1);
        } else {
            $filter->setSlider(0);
        }
        if (isset($values[ 'filterIntervalNew' ][ $idx ])) {
            $filter->setIntervalV(1);
        } else {
            $filter->setIntervalV(0);
        }
        $group = $this->getGroupProductFilter()->find($values[ 'filterGroupIdNew' ][ $idx ]);
        $filter->setFilterGroup($group);

        $this->insertNew($filter);
    }

    public function removeFilterProducts($filterId)
    {
        $filter = $this->getProductFilter()->find($filterId);

        if (count($filter) && count($filter->product)) {
            foreach ($filter->product as $pr) {
                $pro = $this->getProductInFilter()->find($pr->id);
                if (count($pro)) {
                    $this->getEm()->remove($pro);
                    $this->getEm()->flush();
                }
            }
        }

        if (count($filter)) {
            $this->getEm()->remove($filter);
            $this->getEm()->flush();
        } else {
            return false;
        }
        return true;
    }

    public function saveFiltersProduct($values)
    {
        if (isset($values[ 'filterId' ]) && count($values[ 'filterId' ])) {
            foreach ($values[ 'filterId' ] as $idx => $id) {
                $filter = $this->getProductFilter()->find($id);
                $filter->setName($values[ 'filterName' ][ $idx ]);
                if (is_numeric($values[ 'filterOrder' ][ $idx ])) {
                    $filter->setOrderState($values[ 'filterOrder' ][ $idx ]);
                }
                $tmp = str_replace(',', '.', $values[ 'filterMin' ][ $idx ]);
                $filter->setMinValue($tmp);
                $tmp = str_replace(',', '.', $values[ 'filterStep' ][ $idx ]);
                $filter->setStep($tmp);
                $tmp = str_replace(',', '.', $values[ 'filterMax' ][ $idx ]);
                $filter->setMaxValue($tmp);
                if (isset($values[ 'filterSlider' ][ $idx ])) {
                    $filter->setSlider(1);
                } else {
                    $filter->setSlider(0);
                }

                if (isset($values[ 'filterInterval' ][ $idx ])) {
                    $filter->setIntervalV(1);
                } else {
                    $filter->setIntervalV(0);
                }

                $this->save();
            }
        }
    }

    public function getTableCategoryAll($categories = null)
    {
        if ($categories === null) {
            $categories = $this->get()->findAll();
        }
        $output = "";

        $this->index = 0;
        foreach ($categories as $category) {
            if ($category->parentCategory === null) {
                $output = $this->getElement($category, $categories, $output);
            }
        }
        return $output;
    }

    private function getElement($element, $categories, $output, $level = 0)
    {
        $output .= $this->getTr($element, $level);
        foreach ($categories as $category) {
            if ($category->parentCategory !== null && $category->parentCategory->id == $element->id) {
                $output = $this->getElement($category, $categories, $output, $level + 1);
            }
        }
        return $output;
    }

    private function getTr($element, $level)
    {
        $nbsp = "";
        for ($i = 0; $i < $level; $i++) {
            $nbsp .= "&nbsp ";
        }

        $level++;

        $parentName = $element->parentCategory != null ? $element->parentCategory->name : " - ";
        $parentName = "[ $parentName | $level ]";
        $this->index++;
        $str = "
              <td>$this->index</td>
              <td>$nbsp $element->name</td>
              <td>" .
            $parentName
            . "</td>
              <td>
                <a class=\"btn btn-xs btn-default\" href=\"edit/$element->id\"><i class=\"fa fa-pencil\"/></i></a>
                <a href=\"?idCategory=$element->id&amp;do=delete\" class=\"btn btn-xs btn-danger confirmLink\"><span class=\"fa fa-trash\"></span></a>
              </td>
            </tr>";
        return $str;
    }

    public function getSelectBoxCategoryAll($categories = null)
    {
        if ($categories === null) {
            $categories = $this->get()->findAll();
        }
        $output = [];

        foreach ($categories as $category) {
            if ($category->parentCategory === null) {
                $output = $this->getElementSelectBox($category, $categories, $output);
            }
        }
        return $output;
    }

    private function getElementSelectBox($element, $categories, $output, $level = 0)
    {
        $output[ $element->id ] = $this->getOption($element, $level);
        foreach ($categories as $category) {
            if ($category->parentCategory !== null && $category->parentCategory->id == $element->id) {
                $output = $this->getElementSelectBox($category, $categories, $output, $level + 1);
            }
        }
        return $output;
    }

    private function getOption($element, $level)
    {
        $nbsp = "";
        for ($i = 0; $i < $level; $i++) {
            $nbsp .= " . ";
        }

        $level++;

        $parentName = $element->parentCategory != null ? $element->parentCategory->name : " - ";
        $parentName = " [ $parentName | $level ]";

        $str = "$nbsp $element->name $parentName";
        return $str;
    }

    public function saveImage($path, $category)
    {
        $category->setImage($path);
        $this->save();
    }

    public function deleteImage($categoryId)
    {
        $category = $this->get()->find($categoryId);
        if (count($category)) {
            if (file_exists($category->image)) {
                unlink($category->image);
            }
            $category->setImage('');
            $this->save();
            return true;
        }
        return false;
    }

    public function getZboziCzCategories()
    {
        return $this->gEMZboziCategory()->findPairs([], "name", [], "id");
    }

    public function getHeurekaCategories()
    {
        return $this->gEMHeurekaCategory()->findPairs([], "name", [], "id");
    }

    public function getGoogleMerchantCategories()
    {
        return $this->gEMGoogleMerchantCategory()->findPairs([], "name", [], "id");
    }

    public function getAutocompleteCategoryZbozi($term)
    {
        $columns = ['name'];
        $alias = 'p';
        $like = SQLHelper::termToLike($term, $alias, $columns);
        $result = $this->em->getRepository(ZboziCategory::class)
            ->createQueryBuilder($alias)
            ->where($like)
            ->setMaxResults('30')
            ->getQuery()
            ->getResult();
        $arr = [];
        foreach ($result as $item) {
            $arr[ $item->id ] = $item->name;
        }
        return $arr;
    }

    public function getAutocompleteCategoryHeureka($term)
    {
        $columns = ['name'];
        $alias = 'p';
        $like = SQLHelper::termToLike($term, $alias, $columns);
        $result = $this->em->getRepository(HeurekaCategory::class)
            ->createQueryBuilder($alias)
            ->where($like)
            ->setMaxResults('30')
            ->getQuery()
            ->getResult();
        $arr = [];
        foreach ($result as $item) {
            $arr[ $item->id ] = $item->name;
        }
        return $arr;
    }

    public function getAutocompleteCategoryGoogleMerchants($term)
    {
        $columns = ['name'];
        $alias = 'p';
        $like = SQLHelper::termToLike($term, $alias, $columns);
        $result = $this->em->getRepository(GoogleMerchantCategory::class)
            ->createQueryBuilder($alias)
            ->where($like)
            ->setMaxResults('30')
            ->getQuery()
            ->getResult();
        $arr = [];
        foreach ($result as $item) {
            $arr[ $item->id ] = $item->name;
        }
        return $arr;
    }

    public function addCategoryToProduct($idProduct, $idCategory) {
        if (!$idProduct || !$idCategory) {
            return false;
        }
        $item = $this->gEMProductInCategory()->findOneBy(['product' => $idProduct, 'category' => $idCategory]);
        if ($item) {
            return false;
        }

        $item = new ProductInCategory();
        $item->setProduct($this->getEm()->getRepository(Product::class)->find($idProduct));
        $item->setCategory($this->get()->find($idCategory));
        $this->insertNew($item);
        return true;
    }

    public function deleteCategory($idCategory) {
        $item = $this->gEMProductInCategory()->find($idCategory);
        if ($item) {
            $this->em->remove($item);
            $this->em->flush();
        }
        return true;
    }

    public function getAutocompleteCategoryMall($term)
    {
        $columns = ['name'];
        $alias = 'p';
        $like = SQLHelper::termToLike($term, $alias, $columns);
        $result = $this->em->getRepository(MallCategory::class)
            ->createQueryBuilder($alias)
            ->where($like)
            ->setMaxResults('30')
            ->getQuery()
            ->getResult();
        $arr = [];
        foreach ($result as $item) {
            $arr[ $item->id ] = $item->name;
        }
        return $arr;
    }
}
