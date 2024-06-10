<?php

namespace App\Core\Components\UblabooTable\Model;

use Nette\Application\UI;
use App\Core\Model\Database\Utils\AnnotationParser;
use App\Core\Model\Database\Entity\PermisionItem;
use App\Core\Model\ACLMapper;
use Doctrine\ORM\EntityManager;
use App\Core\Components\UblabooTable\Model\ACLGrid;
use Kdyby\Translation\Translator;
use App\Core\Model\Database\Utils\SQLHelper;
use Nette\Caching\IStorage;

class DoctrineGridGenerator {

    /** prefix for anotation */
    const PREFIX = 'GRID';

    /** @var Identity */
    private $user;

    /** @var ACLGrid */
    private $grid;

    /** @var array summary collumns for grid */
    private $summary = [];

    /** @var ACLMapper  */
    protected $mapper;

    /** @var IStorage @inject */
    private $storage;

    /** @var EntityManager */
    protected $em;
    public $aliasQB = 'a';

    /** @var \Kdyby\Translation\Translator */
    protected $translator;

    public function __construct(ACLMapper $mapper, EntityManager $em, \Kdyby\Translation\Translator $translator, IStorage $storage) {
        $this->mapper = $mapper;
        $this->em = $em;
        $this->translator = $translator;
        $this->storage = $storage;
    }

    public function setGrid($grid)
    {
        $this->grid = $grid;
    }

    /**
     * @param $grid
     * @return ACLGrid
     */
    public function setScope($grid)
    {
        $grid->setColumnsHideable();
        $grid->addExportCsvFiltered('Csv export (dle filtru)', 'examples.csv');
        $grid->setTranslator($this->translator);
        $grid->setRefreshUrl(FALSE);
        $grid->setStrictSessionFilterValues(FALSE);
        $grid->setAutoSubmit(FALSE);
        return $grid;
    }

    //@todo phpdoc
    public function generateGridByAnnotation($entity, $user, $presenter, $function, $findBy = NULL, $orderBy = NULL) {
        $this->grid = new ACLGrid($user, $presenter, $function, $this->mapper);
        $this->user = $user;
        $this->grid->setEntity($entity);

        $this->grid = $this->setScope($this->grid);
        $this->grid->setDataSource($this->createQueryBuilder($entity, $findBy, $orderBy));
        if ($orderBy && count($orderBy) == 2) {
            $this->grid->setDefaultSort([$orderBy[0] => $orderBy[1]]);
        } else {
            $this->grid->setDefaultSort(['id' => 'DESC']);
        }

        // prepare key for cash
        $ent = explode("\\", $entity);
        $pres = explode("\\", $presenter);
        $key = end($ent) . '-' . end($pres) . '-grid';

        //cash annotations - read
        $annotations = NULL; //$this->storage->read($key);
        if ($annotations == NULL) { // if null, create and cash
            $annotations = AnnotationParser::getClassPropertyAnnotations($entity, self::PREFIX);
            $this->storage->write($key, $annotations, []);
        }
        //$annotations = AnnotationParser::getClassPropertyAnnotations($entity, self::PREFIX);

        foreach ($annotations as $name => $annotation) {
            // if property dont have annotation - dont create column
            if (count($annotation) == 0)
                continue;

            $this->createAnnotationColumn($name, $annotation);
        }

        if (count($this->summary))
            $this->grid->setColumnsSummary($this->summary);

        if ($this->grid->inlineSettings) {
            $this->addBigInlineEdit($this->grid);
        }

        $this->grid->setItemsPerPageList([20, 40, 60, 80, 100, 200]);
        $this->grid->setDefaultPerPage(40);

        return $this->grid;
    }

    /**
     * Create column to grid by entity annotations
     * @param string $name of column
     * @param Array $annotation
     */
    public function createAnnotationColumn($name, $annotation) {
        $columnInfo = [];
        $columnInfo['type'] = 'text';
        $column = NULL;

        foreach ($annotation as $item) {
            $item = AnnotationParser::cleanAnnotation($item);
            $columnInfo[$item[0]] = $item[1];
        }
        $title = isset($columnInfo['title']) ? $columnInfo['title'] : $name;
        $entityLink = isset($columnInfo['entity-link']) ? $columnInfo['entity-link'] : NULL;
        if ($entityLink)
            $entityLink = $name . '.' . $entityLink;
        // create form component by doctrine annotation specification
        switch ($columnInfo['type']) {
            case 'number':
            case 'integer':
                $column = $this->grid->addColumnNumber($name, $title, $entityLink);
                break;

            case 'float':
                $column = $this->grid->addColumnNumber($name, $title, $entityLink)
                        ->setFormat(2, ',', '.');
                break;


            case 'text':
                $column = $this->grid->addColumnText($name, $title, $entityLink);
                break;

            case 'translate-text':
                $column = $this->grid->addColumnTranslateText($name, $title, $entityLink);
                break;

            case 'datetime':
                $column = $this->grid->addColumnDateTime($name, $title, $entityLink);
                break;

            case 'link':
                if (!isset($columnInfo['link-target']))
                    throw new \Exception('Error in DoctrineGrid annotation. For type Link you need set link-target. Name: ' . $name);
                $params = NULL;
                if (isset($columnInfo['link-params']))
                    $params = AnnotationParser::parseArray($columnInfo['link-params']);

                $column = $this->grid->addColumnLink($name, $title, $columnInfo['link-target'], $entityLink, $params);
                if (isset($columnInfo['link-new-tab']))
                    if ($columnInfo['link-new-tab'] == 'true')
                        $column->setOpenInNewTab(true);
                break;

            case 'status':
                $column = $this->grid->addColumnStatus($name, $title, $entityLink);
                /* @TODO dodělat tlačítka do tabulky */
                break;

            case 'bool':
                $column = $this->grid->addColumnBoolean($name, $title, $entityLink);
                if ($column) {
                    $column->setCaret(FALSE)
                            ->addOption(1, '')
                            ->setIcon('check')
                            ->setClass('label label-success')
                            ->endOption()
                            ->addOption(0, '')
                            ->setIcon('times')
                            ->setClass('label label-danger')
                            ->endOption();
                }
                break;

            case 'text-abstract':
                if (isset($columnInfo['abstract']) && isset($columnInfo['entity-link'])) {
                    $entityLink = $columnInfo['abstract'] . '.' . $columnInfo['entity-link'];
                } else {
                    throw new \Exception('Error in DoctrineGrid annotation - for text-abstract column you need annotation "GRID abstract" whit name of property for foreign key and annotation "entity-link"! Column: ' . $name);
                }
                $column = $this->grid->addColumnText($name, $title, $entityLink);
                break;
            default:
                throw new \Exception('Unknow DoctrineGrid annotation - type of column: ' . $columnInfo['type']);
        }
        $this->addOtherProperties($column, $columnInfo, $columnInfo['type'], $name, $entityLink);
    }

    /**
     * Add other properties to column
     * @param Column $column column for defination
     * @param Array $columnInfo annotation array
     * @param String $type of column
     * @param String $nameColumn
     */
    protected function addOtherProperties($column, $columnInfo, $type, $nameColumn, $entityLink) {

        if ($column == NULL)
            return;

        foreach ($columnInfo as $name => $value) {
            switch ($name) {
                // type, and title are already prepared = do nothing
                case 'type':
                case 'entity':
                case 'title':
                case 'link-target':
                case 'link-params':
                case 'link-new-tab':
                case 'entity-link':
                case 'entity-alias':
                    break;

                case 'format-number':
                    if ($type != 'number')
                        throw new \Exception('Error in DoctrineGrid annotation. Cannot set ' . $name . ' = ' . $value . ' on type: "' . $type);
                    //@TODO format number
                    break;
                case 'sum':
                    if ($type != 'number')
                        throw new \Exception('Error in DoctrineGrid annotation. Cannot set ' . $name . ' = ' . $value . ' on type: "' . $type);
                    if ($value == 'true')
                        $this->summary[] = $nameColumn;
                    break;
                case 'format-time':
                    if ($type != 'datetime')
                        throw new \Exception('Error in DoctrineGrid annotation. Cannot set ' . $name . ' = ' . $value . ' on type: "' . $type);
                    $column->setFormat($value);
                    break;
                case 'sortable':
                    if ($value == 'true')
                        $column->setSortable();
                    break;
                case 'visible':
                    if ($value == 'false')
                        $column->setDefaultHide();
                    break;
                case 'align':
                    $column->setAlign($value);
                    break;
                case 'filter':
                    if ($value == 'range')
                        $column->setFilterRange();
                    else if ($value == 'date')
                        $column->setFilterDate();
                    else if ($value == 'date-range')
                        $this->grid->addFilterDateRange($nameColumn, $columnInfo['title'], $nameColumn);
                    //$this->grid->addFilterDateRange($nameColumn, $columnInfo['title'], $entityLink);
                    else if ($value == 'single') {
                        $this->grid->addFilterText($nameColumn, $columnInfo['title'], $entityLink)
                                ->setSplitWordsSearch(FALSE);
                    } else if (strpos($value, 'single-entity') === 0) {

                        if (!isset($columnInfo['entity'])) {
                            throw new Exception('Missing DoctrineGrid annotation "entity" - its require for annotation "filter=single-entity"');
                        }
                        if (!isset($columnInfo['entity-alias'])) {
                            throw new Exception('Missing DoctrineGrid annotation "entity-alias" - its require for annotation "filter=single-entity"');
                        }
                        $nameC = $nameColumn;
                        if (isset($columnInfo['abstract'])) {
                            $nameC = $columnInfo['abstract'];
                        }

                        $arr = AnnotationParser::parseArray(substr($value, strpos($value, '#[') + 1));

                        $this->grid->addFilterText($nameColumn, $columnInfo['title'])
                                ->setCondition(function($qb, $value) use (&$columnInfo, &$arr, &$nameC) {
                                    $alias = $columnInfo['entity-alias'];
                                    $search = SQLHelper::termToLike($value, $alias, $arr);
                                    $qb->leftJoin($columnInfo['entity'], $alias, 'WITH', $alias . '.id = ' . $this->aliasQB . '.' . $nameC);
                                    $qb->andWhere($search);
                                });
                    } else if (strpos($value, 'select-entity') === 0) {
                        $n = str_replace("]", "", $value);
                        $n = explode('[', $n);
                        $orderBy = [];
                        if (isset($n[2]) && $n[2] && $n[2] != "") {
                            $orderBy = AnnotationParser::parseArray($n[2]);
                        }
                        $items = $this->em->getRepository($columnInfo['entity'])->findBy([], $orderBy);
                        $arr = [];
                        $arr = ['' => 'Vše'];
                        foreach ($items as $item) {
                            if (isset($item->isHidden) && $item->isHidden == 1)
                                continue;
                            $idx = $n[1];
                            $arr[$item->id] = $item->$idx;
                        }
                        $column->setFilterSelect($arr)
                                ->setTranslateOptions()
                                ->setCondition(function($qb, $value) use (&$columnInfo, &$nameColumn) {
                                    $alias = $columnInfo['entity-alias'];
                                    $qb->leftJoin($columnInfo['entity'], $alias, 'WITH', $alias . '.id = ' . $this->aliasQB . '.' . $nameColumn);
                                    $qb->andWhere($alias . '.id = ' . $value);
                                });
                    } else if (strpos($value, 'select') === 0) {
                        $arr = substr($value, strpos($value, '#[') + 1);
                        $column->setFilterSelect(AnnotationParser::parseArray($arr))
                                ->setTranslateOptions();
                    } else
                        throw new \Exception('Unknow DoctrineGrid annotation for filter: ' . $name . ' = ' . $value);
                    break;
                case 'abstract': //for abstract grid value - use foreign key other property
                    break;
                case 'inline-type':
                    $this->grid->inlineSettings[$nameColumn] = $value;
                    break;
                case strpos($name, 'inline-data'):
                    if (!isset($columnInfo['inline-type'])) {
                        throw new Exception('Missing DoctrineGrid annotation "inline-type" - its require for annotation "inline-data-<type>"');
                    }
                    $n = str_replace("inline-data-", "", $name);
                    if ($n == 'entity') {
                        $this->grid->arrayForeignEntity[$nameColumn] = $value;
                    }
                    break;
                case 'inline-prompt':
                    $this->grid->arrayForeignEntity['prompt-default'][$nameColumn] = $value;
                    break;
                case 'replacement':
                    $arr = substr($value, strpos($value, '#[') + 1);
                    $column->setReplacement(AnnotationParser::parseArray($arr));
                    break;
                default:
                    throw new \Exception('Unknow DoctrineGrid annotation: ' . $name . ' = ' . $value);
            }
        }
    }

    /**
     * Appent button delete to grid
     * @param string $key
     * @param string $name
     * @param string $href
     * @param string $params
     */
    public function addButonDelete($key = 'delete', $name = '', $href = 'delete!', $params = NULL) {
        $delete = $this->grid->addAction($key, $name, $href, $params);
        if ($delete)
            $delete->setIcon('trash')
                    ->setTitle('Smazat')
                    ->setClass('btn btn-xs btn-danger confirmLink');
        return $delete;
    }

    /**
     * Add inline edit to grid
     */
    public function addBigInlineEdit($grid) {
        $t = $this;
        $item = $grid->addInlineEdit()
                ->onControlAdd[] = function($container) use ($t) {
            foreach ($t->grid->inlineSettings as $name => $type) {
                switch ($type) {
                    case 'id':
                        break;
                    case 'text':
                        $container->addText($name, '');
                        break;
                    case 'email':
                        $container->addEmail($name, '');
                        break;
                    case 'integer':
                        $container->addText($name, '')
                                ->setRequired(False)
                                ->addRule(UI\Form::INTEGER);
                        break;
                    case 'number':
                        $container->addText($name, '')
                                ->setRequired(False)
                                ->addRule(UI\Form::NUMERIC);
                        break;
                    case 'float':
                        $container->addText($name, '')
                                ->setRequired(False)
                                ->addRule(UI\Form::FLOAT);
                        break;
                    case 'checkbox':
                        $container->addCheckbox($name, '');
                        break;
                    case 'select':
                        $for = $t->grid->arrayForeignEntity;
                        if (!count($for) && !isset($for[$name])) {
                            throw new \Exception('Missing anotation inline-data-<type> on type: ' . $type . ' and name: ' . $name);
                        }
                        $n = explode('[', $for[$name]);
                        $n[1] = str_replace("]", "", $n[1]);
                        $items = $t->em->getRepository($n[0])->findAll();
                        $arr = [];
                        foreach ($items as $item) {
                            if (isset($item->isHidden) && $item->isHidden == 1)
                                continue;
                            $idx = $n[1];
                            $arr[$item->id] = $item->$idx;
                        }
                        $s = $container->addSelect($name, '', $arr);
                        if (isset($for['prompt-default'][$name])) {
                            $s->setPrompt($for['prompt-default'][$name]);
                        }
                        break;
                    default:
                        throw new \Exception('Unknow inline type: ' . $type . ' on name: ' . $name);
                }
            }
        };

        $grid->getInlineEdit()->onSetDefaults[] = function($container, $item) use ($t) {
            $for = $t->grid->arrayForeignEntity;
            $arr = [];
            foreach ($t->grid->inlineSettings as $name => $type) {
                if (isset($for[$name]) && isset($item->$name->id)) {
                    $arr[$name] = $item->$name->id;
                    continue;
                }
                $arr[$name] = $item->$name;
            }
            $container->setDefaults($arr);
        };

        $generator = $this;
        $grid->getInlineEdit()->onSubmit[] = function($id, $values) use ($grid, $generator) {
            $generator->saveEntity($grid, $values, $id);
        };

        $grid->addInlineAdd()
                        ->setPositionTop()
                ->onControlAdd[] = function($container) use ($t) {
            foreach ($t->grid->inlineSettings as $name => $type) {
                switch ($type) {
                    case 'id':
                        break;
                    case 'text':
                        $container->addText($name, '');
                        break;
                    case 'integer':
                        $container->addText($name, '')
                                ->setRequired(False)
                                ->addRule(UI\Form::INTEGER);
                        break;
                    case 'number':
                        $container->addText($name, '')
                                ->setRequired(False)
                                ->addRule(UI\Form::NUMERIC);
                        break;
                    case 'float':
                        $container->addText($name, '')
                                ->setRequired(False)
                                ->addRule(UI\Form::FLOAT);
                        break;
                    case 'checkbox':
                        $container->addCheckbox($name, '');
                        break;
                    case 'select':
                        $for = $t->grid->arrayForeignEntity;
                        if (!count($for) && !isset($for[$name])) {
                            throw new \Exception('Missing anotation inline-data-<type> on type: ' . $type . ' and name: ' . $name);
                        }
                        $n = explode('[', $for[$name]);
                        $n[1] = str_replace("]", "", $n[1]);
                        $items = $t->em->getRepository($n[0])->findAll();
                        $arr = [];
                        foreach ($items as $item) {
                            if (isset($item->isHidden) && $item->isHidden == 1)
                                continue;
                            $item = $item->toArray();
                            $arr[$item['id']] = $item[$n[1]];
                        }
                        $s = $container->addSelect($name, '', $arr);
                        if (isset($for['prompt-default'][$name])) {
                            $s->setPrompt($for['prompt-default'][$name]);
                        }
                        break;
                    default:
                        throw new \Exception('Unknow inline type: ' . $type . ' on name: ' . $name);
                }
            }
        };

        $grid->getInlineAdd()->onSubmit[] = function($values) use ($grid, $generator) {
            $generator->saveEntity($grid, $values);
            if ($grid->presenter)
                $grid->presenter->redirect('this');
        };

        return $grid;
    }

    /**
     * Create specific queryBuilder by condition findBy
     * @param entity $entity
     * @param array $findBy
     * @param array $orderBy
     * @return QueryBuilder
     */
    public function createQueryBuilder($entity, $findBy = NULL, array $orderBy = NULL) {
        $qb = $this->em->createQueryBuilder();
        $qb->select($this->aliasQB)
                ->from($entity, $this->aliasQB);
        if ($findBy) {
            foreach ($findBy as $key => $val) {
                $qb->andWhere($this->aliasQB . '.' . $key . "='$val'");
            }
        }
        /*if ($orderBy) {
            $qb->orderBy($this->aliasQB . '.' . $orderBy[0], $orderBy[1]);
        }*/
        return $qb;
    }

    public function findBy($qb, $findBy) {
        $i = 1;
        $arr = [];
        $where = '';
        if (is_array($findBy)) {
            foreach ($findBy as $name => $value) {
                $where .= $this->aliasQB . '.' . $name . ' = ?' . $i . ' AND ';
                $arr[$i] = $value;
                $i++;
            }
            $where = substr($where, 0, -4);
        } else {
            $where = $findBy;
        }
        $qb->where($where);
        $qb->setParameters($arr);
        return $qb;
    }

    /**
     * Function for handler onSuccess grid in inline save mode
     * @param form $form
     * @param array $values
     */
    public function saveEntity($grid, $values, $id = "") {
        try {
            //if $id=="" - new item, else update existing
            $entity = NULL;
            if ($id == "") {
                $entity = new $grid->entity();
            } else {
                $entity = $this->em->getRepository($grid->entity)->find($id);
            }

            //Save foreign entity - need find it
            if (isset($grid->arrayForeignEntity)) {
                foreach ($grid->arrayForeignEntity as $name => $value) {
                    if ($name === 'prompt-default') {
                        continue;
                    }
                    if (isset($values[$name])) {
                        $n = explode('[', $value);
                        $a = $this->em->getRepository($n[0])->find($values[$name]);
                        // Check if exist foreign entity - if not, dont save.
                        if (count($a))
                            $values[$name] = $a;
                        else
                            unset($values[$name]);
                    } else { //If value not set, set entity cell to NULL
                        $values[$name] = NULL;
                    }
                }
            }

            $entity->data($values);
            if ($id == "") {
                $this->em->persist($entity);
            }
            $this->em->flush();
        } catch (\Exception $e) {
            // Check Integrity constraint viloadin - duplicate entry
            if (strpos($e, 'SQLSTATE[23000]')) {
                $n = explode("'", $e->getMessage());
                if (isset($grid->presenter)) {
                    $grid->presenter->flashMessage('Hodnoty se nepodařilo uložit - hodnota "' . $n[3] . '" není jedinečná - jiný záznam již má tuto hodnotu!', 'warning');
                }
                return;
            }
            \Tracy\Debugger::log($e);
            if (isset($grid->messageEr)) {
                $grid->flashMessage($grid->messageEr[0], $grid->messageEr[1]);
            } else {
                throw $e;
            }
            return;
        }
        if (isset($grid->messageOk)) {
            $grid->presenter->flashMessage($grid->messageOk[0], $grid->messageOk[1]);
        }
    }

}
