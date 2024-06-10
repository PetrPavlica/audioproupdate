<?php

namespace App\Core\Model;

use Nette\Application\UI;
use App\Core\Model\Database\Entity\PermisionItem;

class ACLForm extends UI\Form {

    /** @var Identity */
    private $user;

    /** @var string  */
    private $namePresenter;

    /** @var ACLMapper  */
    private $mapper;

    /** @var string */
    private $nameForm;

    /** array of foreign entity for save method */
    public $arrayForeignEntity = NULL;

    /** array of foreign entity whit N:N for save method */
    public $arrayNNForeignEntity = NULL;

    /** messages on success form */
    public $messageOk;

    /** messages on error form */
    public $messageEr;

    /** target to redirect on success */
    public $target;

    /** redirect after save? */
    public $isRedirect = true;

    /** target parameters */
    public $targetPar;

    /** class in form */
    public $class;

    public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);
    }

    /**
     * Set scope for ACL mapping
     * @param Identity $user login user identity
     * @param string $presenter name of destination presenter
     * @param string $function name of destination function / form
     * @param ACLMapper $mapper
     */
    public function setScope($user, $presenter, $function, $mapper) {
        $this->user = $user;
        $this->namePresenter = $presenter;
        $this->nameForm = $function;
        $this->mapper = $mapper;
        $this->mapper->mapFunction(NULL, $user, $presenter, $function, PermisionItem::TYPE_FORM);
    }

    /**
     * Add classic text input whit acl mapping
     * @param string $name
     * @param string $label
     * @param type $cols
     * @param type $maxLength
     * @return input
     */
    public function addTextAcl($name, $label = NULL, $cols = NULL, $maxLength = NULL) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, $label);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addText($name, $label, $cols, $maxLength);
            if ($action == 'read') {
                $item->omitted = true;
                $item->disabled = true;
            }
            return $item;
        }
        return NULL;
    }

    /**
     * Add classic select input with ACL mapping
     * @param string $name
     * @param string $label
     * @param type $items
     * @param type $size
     * @return input
     */
    public function addSelectAcl($name, $label = NULL, $items = NULL, $size = NULL) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, $label);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addSelect($name, $label, $items, $size);
            if ($action == 'read') {
                $item->omitted = true;
                $item->disabled = true;
            }
            return $item;
        }
        return NULL;
    }

    /**
     * Add multipleselect input with ACL mapping
     * @param string $name
     * @param string $label
     * @param type $items
     * @param type $size
     * @return input
     */
    public function addMultiSelectAcl($name, $label = NULL, $items = NULL, $size = NULL) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, $label);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addMultiSelect($name, $label, $items, $size);
            if ($action == 'read') {
                $item->omitted = true;
                $item->disabled = true;
            }
            return $item;
        }
        return NULL;
    }

    /**
     * Add classic checkbox input with ACL mapping
     * @param string $name
     * @param type $caption
     * @return input
     */
    public function addCheckboxAcl($name, $caption = NULL) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, $caption);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addCheckbox($name, $caption);
            if ($action == 'read') {
                $item->omitted = true;
                $item->disabled = true;
            }
            return $item;
        }
        return NULL;
    }

    /**
     * Add classic checkbox list input with ACL mapping
     * @param string $name
     * @param string $label
     * @param array $items
     * @return input
     */
    public function addCheckboxListAcl($name, $label = NULL, array $items = NULL) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, $label);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addCheckboxList($name, $label, $items);
            if ($action == 'read') {
                $item->omitted = true;
                $item->disabled = true;
            }
            return $item;
        }
        return NULL;
    }

    /**
     * Add classic email input with ACL mapping
     * @param string $name
     * @param string $label
     * @return input
     */
    public function addEmailAcl($name, $label = NULL) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, $label);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addEmail($name, $label);
            if ($action == 'read') {
                $item->omitted = true;
                $item->disabled = true;
            }
            return $item;
        }
        return NULL;
    }

    /**
     * Add classic hidden input with ACL mapping
     * @param string $name
     * @param type $default
     * @return input
     */
    public function addHiddenAcl($name, $default = NULL) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, $default);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addHidden($name, $default);
            if ($action == 'read') {
                $item->omitted = true;
                $item->disabled = true;
            }
            return $item;
        }
        return NULL;
    }

    /**
     * Add classic image input with ACL mapping
     * @param string $name
     * @param string $src
     * @param string $alt
     * @return input
     */
    public function addImageAcl($name, $src = NULL, $alt = NULL) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, $alt);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addImage($name, $src, $alt);
            if ($action == 'read') {
                $item->omitted = true;
                $item->disabled = true;
            }
            return $item;
        }
        return NULL;
    }

    /**
     * Add classic integer input with ACL mapping
     * @param string $name
     * @param string $label
     * @return input
     */
    public function addIntegerAcl($name, $label = NULL) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, $label);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addInteger($name, $label);
            if ($action == 'read') {
                $item->omitted = true;
                $item->disabled = true;
            }
            return $item;
        }
        return NULL;
    }

    /**
     * Add classic pass input with ACL mapping
     * @param string $name
     * @param string $label
     * @param int $cols
     * @param int $maxLength
     * @return input
     */
    public function addPasswordAcl($name, $label = NULL, $cols = NULL, $maxLength = NULL) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, $label);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addPassword($name, $label, $cols, $maxLength);
            if ($action == 'read') {
                $item->omitted = true;
                $item->disabled = true;
            }
            return $item;
        }
        return NULL;
    }

    /**
     * Add classic textarea field without editor and with ACL mapping
     * @param string $name
     * @param string $label
     * @param int $cols
     * @param int $rows
     * @return textarea
     */
    public function addTextAreaAcl($name, $label = NULL, $cols = NULL, $rows = NULL) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, $label);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addTextArea($name, $label, $cols, $rows);
            if ($action == 'read') {
                $item->omitted = true;
                $item->disabled = true;
            }
            return $item;
        }
        return NULL;
    }

    /**
     * Add classic upload input with ACL mapping
     * @param string $name
     * @param string $label
     * @param type $multiple
     * @return \Nette\Forms\Controls\UploadControl|null
     */
    public function addUploadAcl($name, $label = NULL, $multiple = FALSE) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, $label);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addUpload($name, $label, $multiple);
            if ($action == 'read') {
                $item->omitted = true;
                $item->disabled = true;
            }
            return $item;
        }
        return NULL;
    }

    public function addEditorAcl($name, $label = NULL) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, $label);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addTextArea($name, $label);
            $item->setAttribute('class', 'ckEditor');
            if ($action == 'read') {
                $item->omitted = true;
                $item->disabled = true;
            }
            return $item;
        }
        return NULL;
    }

    public function addSubmitAcl($name, $caption = NULL) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, 'Tlačítko: ' . $caption);

        if ($action == 'write' || $action == 'read') {
            return parent::addSubmit($name, $caption);
        }
        return NULL;
    }

    /**
     * Add hidden input for autocomplete render with ACL mapping
     * @param string $name
     * @param type $default
     * @return input
     */
    public function addAutocomplete($name, $title, $default = NULL) {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameForm, $name, $default);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addHidden($name, $default);
            $item->setAttribute('class', 'autocomplete-input');
            $item->setAttribute('data-toggle', 'completer');
            $item->setAttribute('autocomplete', 'true');
            $item->setAttribute('title', isset($componentInfo['title']) ? $componentInfo['title'] : '');
            if ($action == 'read') {
                $item->omitted = true;
                $item->disabled = true;
            }
            return $item;
        }
        return NULL;
    }

    /**
     * Set messages on success form - succ save and err save
     * @param array $messageOk [text, type]
     * @param array $messageEr [text, type]
     */
    public function setMessages($messageOk, $messageEr) {
        $this->messageOk = $messageOk;
        $this->messageEr = $messageEr;
    }

    /**
     * Set messages on success form - succ save and err save
     * @param string $target redirect target
     */
    public function setRedirect($target, $targetPar = NULL) {
        $this->target = $target;
        $this->targetPar = $targetPar;
    }

    /**
     * Set value for autocomplete field
     * @param string $name
     * @param string $value
     */
    public function setAutocmp($name, $value) {
        if ($value)
            $this->components[$name]->setAttribute('value-autocmp', $value);
    }

    /**
     * Set attribute name -> value to html element
     * @param string $name
     * @param string $nameAttr
     * @param string $valueAttr
     */
    public function setAttr($name, $nameAttr, $valueAttr) {
        $this->components[$name]->setAttribute($nameAttr, $valueAttr);
    }

}
