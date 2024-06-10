<?php

namespace App\Core\Model;

use Nette\Application\UI;
use Kdyby\Doctrine\EntityManager;
use App\Core\Model\Database\Utils\AnnotationParser;
use App\Core\Model\ACLMapper;
use Intra\Model\Database\Entity\Clanek;
use Nette\Caching\IStorage;


class DoctrineFormGenerator {
    
    /** prefix for anotation */
    const PREFIX = 'FORM';

    /** @var EntityManager */
    private $em;

    /** @var ACLForm */
    private $form;

    /** @var ACLMapper */
    private $mapper;

    /** @var IStorage */
    private $storage;

    /** presenter where write message and do redirect */
    private $presenter;

    /** default values for components */
    private $defaultsValues;

    public function __construct(EntityManager $em, ACLMapper $mapper, IStorage $storage) {
        $this->em = $em;
        $this->mapper = $mapper;
        $this->storage = $storage;
    }

    /**
     * Generate form by doctrine annotation. Prepare all form whit save/update method.
     * @param string $class
     * @param Identity $user
     * @param object $presenter
     * @param string $function
     * @param string $captionSubmit
     * @return ACLForm form whit emelements from class
     */
    public function generateFormByAnnotation($class, $user, $presenter, $function, $captionSubmit = 'Uložit') {
        $this->form = new ACLForm;
        $this->form->setScope($user, get_class($presenter), $function, $this->mapper);
        $this->presenter = $presenter;
        $this->form->class = $class;

        // prepare key for cash  - připravené cashe, ale kvůli rychlosti se to asi zatím nevyplatí
        /* $ent = explode("\\", $class);
          $pres = explode("\\", get_class($presenter));
          $key = end($ent) . '-' . end($pres) . '-form';

          //cash annotations - read
          $annotations = $this->storage->read($key);
          if ($annotations == NULL) { // if null, create and cash
          $annotations = AnnotationParser::getClassPropertyAnnotations($class, self::PREFIX);
          $this->storage->write($key, $annotations, []);
          } */
        $annotations = AnnotationParser::getClassPropertyAnnotations($class, self::PREFIX);

        foreach ($annotations as $name => $annotation) {
            // if property dont have annotation - dont create component
            if (count($annotation) == 0)
                continue;

            $this->createAnnotationComponent($name, $annotation);
        }

        $this->form->addSubmitAcl('send', $captionSubmit);
        $this->form->onSuccess[] = [$this, 'processForm'];
        if ($this->defaultsValues)
            $this->form->setDefaults($this->defaultsValues);
        return $this->form;
    }

    /**
     * Generate form whithout doctrine annotation.
     * @param string $class
     * @param Identity $user
     * @param object $presenter
     * @param string $function
     * @param string $captionSubmit
     * @return ACLForm form
     */
    public function generateFormWithoutAnnotation($class, $user, $presenter, $function) {
        $this->form = new ACLForm;
        $this->form->setScope($user, get_class($presenter), $function, $this->mapper);
        $this->presenter = $presenter;
        $this->form->class = $class;

        $this->form->onSuccess[] = [$this, 'processForm'];
        if ($this->defaultsValues)
            $this->form->setDefaults($this->defaultsValues);
        return $this->form;
    }

    /**
     * Function for handler onSuccess form
     * @param form $form
     * @param array $values
     */
    public function processForm($form, $values, $enforce = false) {
        // if exist another onSuccess on form - return this and stop it
        if (count($form->onSuccess) > 1 && $enforce == false)
            return;
        $id = "";
        if (isset($values['id'])) {
            $id = $values['id'];
            unset($values['id']);
        }
        if (isset($values['ID'])) {
            $id = $values['ID'];
            unset($values['ID']);
        }
        if (isset($values['Id'])) {
            $id = $values['Id'];
            unset($values['Id']);
        }
        if (isset($values['iD'])) {
            $id = $values['iD'];
            unset($values['iD']);
        }

        try {
            //if $id=="" - new item, else update existing
            $entity = NULL;
            if ($id == "") {
                $entity = new $form->class();
            } else {
                $entity = $this->em->getRepository($form->class)->find($id);
            }

            //Save foreign entity - need find it
            if (isset($form->arrayForeignEntity)) {
                foreach ($form->arrayForeignEntity as $name => $value) {
                    //if entity exist in array for N:N save - save information to arrayNNForeignEntity and unset post value, continue and save as last
                    if (isset($form->arrayNNForeignEntity[$name])) {
                        $form->arrayNNForeignEntity[$name]['value'] = $values[$name];
                        $form->arrayNNForeignEntity[$name]['foreign-entity'] = $value;
                        unset($values[$name]);
                        continue;
                    }
                    if (isset($values[$name]) && $values[$name]) {
                        $a = $this->em->getRepository($value)->find($values[$name]);
                        // Check if exist foreign entity - if not, dont save.
                        if ($a)
                            $values[$name] = $a;
                        else
                            unset($values[$name]);
                    } elseif (isset($values[$name]) && !$values[$name]) { //If value not set, set entity cell to NULL
                        $values[$name] = NULL;
                    }
                }
            }
            $entity->data($values);
            if ($id == "") {
                $this->em->persist($entity);
            }
            $this->em->flush();

            // Save foreign entity whit N:N relationship
            if (isset($form->arrayNNForeignEntity)) {
                foreach ($form->arrayNNForeignEntity as $name => $value) {
                    // Delete old values
                    $query = $this->em->createQuery("DELETE " . $value['entity'] . " c WHERE c." . $value['this'] . " = " . $entity->id);
                    $query->execute();

                    if ($value['value']) {
                        // Save new
                        foreach ($value['value'] as $item) {
                            $entityForeign = new $value['entity'];
                            $foreignEntity = $this->em->getRepository($value['foreign-entity'])->find($item);
                            $data = [
                                $value['this'] => $entity,
                                $value['foreign'] => $foreignEntity
                            ];
                            $entityForeign->data($data);
                            $this->em->persist($entityForeign);
                        }
                        $this->em->flush();
                    }
                }
            }
        } catch (\Exception $e) {
            // Check Integrity constraint viloadin - duplicate entry
            if (strpos($e, 'SQLSTATE[23000]')) {
                $n = explode("'", $e->getMessage());
                $this->presenter->flashMessage('Formulář se nepodařilo uložit - hodnota "' . $n[3] . '" není jedinečná - jiný záznam již má tuto hodnotu!', 'warning');
                return;
            }
            \Tracy\Debugger::log($e);
            if (isset($this->form->messageEr)) {
                $this->presenter->flashMessage($this->form->messageEr[0], $this->form->messageEr[1]);
            } else {
                throw $e;
            }
            return;
        }
        if (isset($this->form->messageOk)) {
            $this->presenter->flashMessage($this->form->messageOk[0], $this->form->messageOk[1]);
        }

        if ($form->isRedirect) {
            if ($form->target) {
                if ($form->targetPar)
                    $this->presenter->redirect($form->target, $form->targetPar);
                else
                    $this->presenter->redirect($form->target);
            } else {
                $this->presenter->redirect('this');
            }
        }
        // If not redirect - return entity
        else {
            return $entity;
        }
    }

    /**
     * Create form component by doctrine annotation
     * @param string $name of property
     * @param array $annotation annotations of property
     */
    public function createAnnotationComponent($name, $annotation) {

        $componentInfo = [];
        $componentInfo['name'] = $name;
        $componentInfo['type'] = 'text';

        foreach ($annotation as $item) {
            $item = AnnotationParser::cleanAnnotation($item);
            $componentInfo[$item[0]] = $item[1];
        }

        // create form component by doctrine annotation specification
        switch ($componentInfo['type']) {
            case 'text':
                $component = $this->form->addTextAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                break;
            case 'select':
                $component = $this->form->addSelectAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                break;
            case 'multiselect':
                $component = $this->form->addMultiSelectAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                break;
            case 'checkbox':
                $component = $this->form->addCheckboxAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                break;
            case 'checkboxlist':
                $component = $this->form->addCheckboxListAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                break;
            case 'email':
                $component = $this->form->addEmailAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                break;
            case 'hidden': //hidden dont acl map
                $component = $this->form->addHidden($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                break;
            case 'image':
                $component = $this->form->addImageAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                break;
            case 'integer':
            case 'number':
                $component = $this->form->addIntegerAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                break;
            case 'password':
                $component = $this->form->addPasswordAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                break;
            case 'textarea':
                $component = $this->form->addTextAreaAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                break;
            case 'upload':
                $component = $this->form->addUploadAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                break;
            case 'editor':
                $component = $this->form->addEditorAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                break;
            case 'time':
                $component = $this->form->addTextAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                $component->setAttribute('autocomplete', 'off');
                $component->setRequired(false);
                $component->addRule(UI\Form::PATTERN, 'Čas musí být ve formátu 15:20', '(^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9])');
                break;
            case 'date':
                $component = $this->form->addTextAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                $component->setAttribute('data-provide', 'datepicker');
                $component->setAttribute('data-date-orientation', 'bottom');
                $component->setAttribute('data-date-format', 'd. m. yyyy');
                $component->setAttribute('data-date-today-highlight', 'true');
                $component->setAttribute('data-date-autoclose', 'true');
                $component->setAttribute('autocomplete', 'off');
                $component->setRequired(false);
                $component->addRule(UI\Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011', '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})');
                break;
            case 'datetime':
                $component = $this->form->addTextAcl($componentInfo['name'], isset($componentInfo['title']) ? $componentInfo['title'] : '');
                $component->setRequired(false);
                $component->addRule(UI\Form::PATTERN, 'Datum a čas musí být ve formátu 15. 10. 2011 15:20 (pozor na mezery)', '(^(0?[1-9]|[12][0-9]|3[01]). (0?[1-9]|1[0-2]). \d\d\d\d ([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9])');
                break;
            case 'autocomplete':
                $component = $this->form->addHiddenAcl($componentInfo['name']);
                $component->setAttribute('class', 'autocomplete-input');
                $component->setAttribute('data-toggle', 'completer');
                $component->setAttribute('autocomplete', 'true');
                $component->setAttribute('title', isset($componentInfo['title']) ? $componentInfo['title'] : '');
                break;

            case 'multiupload':
                /* TODO multiupload */
                break;
            case 'radiolist':
                /* TODO radiolist */
                break;
            default:
                throw new \Exception('Unknow type of input - Doctrine-Form annotation. Type: ' . $componentInfo['type']);
        }
        $this->addOtherProperties($component, $componentInfo);
    }

    protected function addOtherProperties($component, $componentInfo) {

        if ($component == NULL)
            return;
        foreach ($componentInfo as $name => $value) {
            switch ($name) {
                // type, name and title are already prepared = do nothing
                case 'type':
                case 'name':
                case 'size':
                case 'title':
                    break;
                case 'required':
                    if ($value == 'false')
                        $value = false;
                    $component->setRequired($value);
                    break;
                case 'prompt':
                    $component->setPrompt($value);
                    break;
                case 'disabled':
                    $component->setDisabled();
                    break;
                case 'default-value':
                    $this->defaultsValues[$componentInfo['name']] = $value;
                    break;
                case strpos($name, 'attribute'):
                    $n = str_replace("attribute-", "", $name);
                    if (isset($component->control->attrs[$n]))
                        $value .= ' ' . $component->control->attrs[$n];
                    $component->setAttribute($n, $value);
                    break;
                case strpos($name, 'rule'):
                    $this->addRuleAnnotation($name, $value, $component);
                    break;
                case strpos($name, 'data'):
                    $n = str_replace("data-", "", $name);
                    if ($n == 'entity') {
                        $n = explode('[', $value);
                        $n[1] = str_replace("]", "", $n[1]);
                        if ($componentInfo['type'] !== 'hidden') { // its allow to add data-entity for hidden field. but in hidden field you dont fill component
                            $items = $this->em->getRepository($n[0])->findAll();
                            $arr = [];
                            $idName = 'id';
                            foreach ($items as $item) {
                                if (isset($item->isHidden) && $item->isHidden == 1)
                                    continue;
                                $value = $n[1];
                                $arr[$item->$idName] = $item->$value;
                            }
                            $component->setItems($arr);
                        }
                        $this->form->arrayForeignEntity[$componentInfo['name']] = $n[0];
                    } else if ($n == 'entity-values') {
                        $n = str_replace("]", "", $value);
                        $n = explode('[', $n);
                        if ($componentInfo['type'] !== 'hidden') { // its allow to add data-entity for hidden field. but in hidden field you dont fill component
                            $findBy = [];
                            if (isset($n[2]) && $n[2] && $n[2] != "") {
                                $findBy = AnnotationParser::parseArray($n[2]);
                            }
                            $orderBy = [];
                            if (isset($n[3]) && $n[3] && $n[3] != "") {
                                $orderBy = AnnotationParser::parseArray($n[3]);
                            }
                            $items = $this->em->getRepository($n[0])->findBy($findBy, $orderBy);
                            $properties = AnnotationParser::getPropertiesOfClass(new $n[0]);
                            $arr = [];
                            $idVal = 'id';
                            foreach ($items as $item) {
                                if (isset($item->isHidden) && $item->isHidden == 1)
                                    continue;
                                $resVal = $resNew = $n[1];
                                foreach ($properties as $prop) {
                                    if (strpos($resVal, $prop) !== false) {
                                        if (!isset($item->$prop)) {
                                            throw new \Exception('Error in doctrine annotation - FORM data-entity-values=' . $value . ' - error in unknow entity property: ' . $prop);
                                            return;
                                        }
                                        if (is_array($item->$prop)) {
                                            throw new \Exception('Error in doctrine annotation - FORM data-entity-values=' . $value . ' - entity property: ' . $prop . ' is foreign key - you cannot use foreign key to this annotation');
                                            return;
                                        }
                                        $resVal = str_replace("$" . $prop . "$", '', $resVal);
                                        $resNew = str_replace("$" . $prop . "$", $item->$prop, $resNew);
                                    }
                                }
                                $arr[$item->$idVal] = $resNew;
                            }
                            $component->setItems($arr);
                        }
                        $this->form->arrayForeignEntity[$componentInfo['name']] = $n[0];
                    } else if ($n == 'own') {
                        $value = AnnotationParser::parseArray($value);
                        $component->setItems($value);
                    }
                    break;
                case 'multiselect-entity': //FORM multiselect-entity=Intra\Model\Database\Entity\UserInParcelShop[this entity][foreign entity]
                    $n = explode('[', $value);
                    $n[1] = str_replace("]", "", $n[1]);
                    $n[2] = str_replace("]", "", $n[2]);
                    $this->form->arrayNNForeignEntity[$componentInfo['name']] = [ 'entity' => $n[0], 'this' => $n[1], 'foreign' => $n[2]];
                    break;
                case 'autocomplete-entity':
                    $this->form->arrayForeignEntity[$componentInfo['name']] = $value;
                    break;
                default:
                    throw new \Exception('Unknow Doctrine-Form annotation: ' . $name . ' = ' . $value);
            }
        }
    }

    /**
     * Function addRule by annotations
     * @param string $name type of rule format: rule-{type or rule}
     * @param string $value text + arg to rule
     * @param component $component component from form
     * @throws \Exception
     */
    protected function addRuleAnnotation($name, $value, $component) {
        $n = str_replace("rule-", "", $name);
        $arg = NULL;
        if (strpos($value, "#[") != FALSE) {
            $item = trim(substr($value, strpos($value, "#[") + 2));
            $arg = AnnotationParser::createAndCleanArg($item);
            $value = substr($value, 0, strpos($value, "#["));
        }

        //@TODO udělat pattern addRule na PSČ, Telefon, IČ, Rodné číslo,
        switch ($n) {
            case 'integer':
            case 'number':
                $component->addRule(UI\Form::INTEGER, $value, $arg);
                break;
            case 'range':
                $component->addRule(UI\Form::RANGE, $value, $arg);
                break;
            case 'min_length':
                $component->addRule(UI\Form::MIN_LENGTH, $value, $arg);
                break;
            case 'max_length':
                $component->addRule(UI\Form::MAX_LENGTH, $value, $arg);
                break;
            case 'email':
                $component->addRule(UI\Form::EMAIL, $value, $arg);
                break;
            case 'length':
                $component->addRule(UI\Form::LENGTH, $value, $arg);
                break;
            case 'equal':  //TODO udělat equal na druhé políčko - např hesla
                $component->addRule(UI\Form::EQUAL, $value, $arg);
                break;
            case 'url':
                $component->addRule(UI\Form::URL, $value, $arg);
                break;
            case 'numeric':
            case 'number':
                $component->addRule(UI\Form::NUMERIC, $value, $arg);
                break;
            case 'float':
                $component->addRule(UI\Form::FLOAT, $value, $arg);
                break;
            case 'min':
                $component->addRule(UI\Form::MIN, $value, $arg);
                break;
            case 'max':
                $component->addRule(UI\Form::MAX, $value, $arg);
                break;
            case 'psc':
                //$component->addRule(Form::PATTERN, $value, '([0-9]\s*){5}');
                $component->addRule(UI\Form::PATTERN, $value, '([0-9]\s*){5}');
                break;
            default:
                throw new \Exception('Unknow Doctrine-Form annotation for addRule: ' . $name . ' = ' . $value);
        }
    }

}
