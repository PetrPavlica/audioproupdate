<?php

namespace App\Core\Model;

use Doctrine\ORM\EntityManager;
use Nette\Security\Identity;
use Nette\Caching\IStorage;
use App\Core\Model\Database\Utils\AnnotationParser;
use App\Core\Model\Database\Entity\PermisionItem;
use App\Core\Model\Database\Entity\PermisionRule;

class ACLMapper {

    /** Prefix for anotation */
    const PREFIX = 'ACL';

    /** Admin Group id - for allow all permision */
    const ADMIN_ID = 1;

    /** Default message for denit access */
    const DEFAULT_MESSAGE = 'K této akci nemáte přístup';

    /** @var EntityManager */
    private $em;

    /** @var IStorage */
    private $storage;

    /** $var Array */
    private $aclList;

    public function __construct(EntityManager $em, IStorage $storage) {
        $this->em = $em;
        $this->storage = $storage;
        $this->recreateAclList();
    }

    /**
     * Recreate ACL list
     */
    private function recreateAclList($total = false) {
        if ($total) { // clearn cash
            $this->storage->write('ACLPermisionList', null, []);
        }

        $list = $this->storage->read('ACLPermisionList');
        if ($list == NULL) { // if null, create and cash
            $tmp = $this->em->getRepository(PermisionItem::class)->findAll();
            $list = [];
            foreach ($tmp as $item) {
                $list[$item->name] = $item;
            }
            $this->storage->write('ACLPermisionList', $list, []);
        }
        $this->aclList = $list;
    }

    /**
     * Mapping function - map and secure access to function
     * @param Presenter $presenter
     * @param Identity $user
     * @param string $class
     * @param string $function
     * @param PermisionItem $type type of function PRESENTER|METHOD|FORM|ACTION
     */
    public function mapFunction($presenter, $user, $class, $function, $type = PermisionItem::TYPE_METHOD) {
        if ($type == PermisionItem::TYPE_PRESENTER)
            $name = $class;
        else
            $name = $class . "__" . $function;
        $name = str_replace('\\', '_', $name);
        $name = str_replace('App_Presenters_', '', $name);

        $annotation = AnnotationParser::getMethodPropertyAnnotations($class, $function, self::PREFIX);
        $annotationInfo = [];
        foreach ($annotation as $item) {
            $item = AnnotationParser::cleanAnnotation($item);
            $annotationInfo[$item[0]] = $item[1];
        }

        // Check if exist function in ACL list. If not, add function and recreate list
        if (!isset($this->aclList[$name])) {
            $item = new PermisionItem();
            $item->setName($name);
            if (!isset($annotationInfo['name'])) {
                throw new \Exception('Missing ACL annotation "name", method: ' . $function . ', class: ' . $class);
            }
            $item->setCaption($annotationInfo['name']);
            $item->setType($type);

            try {
                $this->em->persist($item);
                $this->em->flush();
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                \Tracy\Debugger::log(new \Exception('Error in mapping. Mapper try save existing Permision Item - function: mapFunction. Name: ' . $annotationInfo['name'] . ', method: ' . $function . ', class: ' . $class));
                return;
            }
            $this->recreateAclList(true);
        }

        /** For admin allow all */
        if ($user->identity->group === self::ADMIN_ID)
            return;

        /* If presenter not set - only mapping */
        if (!isset($presenter))
            return;

        $presenterName = str_replace('\\', '_', get_class($presenter));
        $presenterName = str_replace('App_Presenters_', '', $presenterName);
        // If user have role for presenter - return it
        if (isset($user->identity->roles[$presenterName])) {
            if ($user->identity->roles[$presenterName] == PermisionRule::ACTION_ALL)
                return;
            if ($user->identity->roles[$presenterName] == PermisionRule::ACTION_READ)
                return;
        }

        /* Check if user have anything role - manage acces on page */
        if (isset($user->identity->roles[$name])) {
            return;
        } else {
            if (isset($annotationInfo['rejection'])) {
                $presenter->flashMessage($annotationInfo['rejection'], 'warning');
            } else {
                $presenter->flashMessage(self::DEFAULT_MESSAGE, 'warning');
            }
            if (isset($annotationInfo['back-url'])) {
                $presenter->redirect($annotationInfo['back-url']);
            } else {
                $presenter->redirect(":Homepage:empty");
            }
            die;
        }
    }

    /**
     * Mapping input of form - map and secur access to input.
     * @param type $user
     * @param type $name
     * @param type $label
     * @return string - type of access
     */
    public function mapInput($user, $presenter, $nameForm, $nameElement, $label) {
        $name = $presenter . '__' . $nameForm . '__' . $nameElement;
        $name = str_replace('\\', '_', $name);
        $name = str_replace('App_Presenters_', '', $name);
        // Check if input is in ACL list
        if (!isset($this->aclList[$name])) {
            $item = new PermisionItem();
            $item->setName($name);
            if (!isset($label)) {
                $label = $name;
            }
            $item->setCaption($label);
            $item->setType(PermisionItem::TYPE_FORM_ELEMENT);

            try {
                $this->em->persist($item);
                $this->em->flush();
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                \Tracy\Debugger::log(new \Exception('Error in mapping. Mapper try save existing Permision Item - function: maxInput. Name: ' . $name));
                return;
            }
            $this->recreateAclList(true);
        }

        // For admin allow all to write
        if ($user->identity->group === self::ADMIN_ID)
            return PermisionRule::ACTION_WRITE;

        $presenter = str_replace('App\\Presenters\\', '', $presenter);
        // If user have role for presenter
        if (isset($user->identity->roles[$presenter])) {
            if ($user->identity->roles[$presenter] == PermisionRule::ACTION_ALL)
                return PermisionRule::ACTION_WRITE;
            if ($user->identity->roles[$presenter] == PermisionRule::ACTION_READ)
                return PermisionRule::ACTION_READ;
        }

        // If user have role for form
        $fullNameForm = $presenter . '__' . $nameForm;
        if (isset($user->identity->roles[$fullNameForm])) {
            if ($user->identity->roles[$fullNameForm] == PermisionRule::ACTION_ALL)
                return PermisionRule::ACTION_WRITE;
            if ($user->identity->roles[$fullNameForm] == PermisionRule::ACTION_READ)
                return PermisionRule::ACTION_READ;
        }

        return isset($user->identity->roles[$name]) ? $user->identity->roles[$name] : NULL;
    }

    /**
     * Mapping html element - map and security who can show and use element
     * @param type $user
     * @param type $presenter
     * @param type $nameElement
     * @param type $caption
     * @return PermisionRule
     */
    public function mapHtmlControl($user, $presenter, $nameElement, $caption, $type) {
        if ($type == PermisionItem::TYPE_FORM) {
            //@TODO cash zpracovaných anotací.
            $annotation = AnnotationParser::getMethodPropertyAnnotations($presenter, $nameElement, self::PREFIX);
            $annotationInfo = [];
            foreach ($annotation as $item) {
                $item = AnnotationParser::cleanAnnotation($item);
                $annotationInfo[$item[0]] = $item[1];
            }
            if (!isset($annotationInfo['name'])) {
                throw new \Exception('Missing ACL annotation "name", method: ' . $nameElement . ', class: ' . $presenter);
            }
            $caption = $annotationInfo['name'];
        }
        $presenter = str_replace('App_Presenters_', '', $presenter);
        $presenter = str_replace('App\\Presenters\\', '', $presenter);
        $name = $presenter . '__' . $nameElement;
        $name = str_replace('\\', '_', $name);

        // Check if input is in ACL list
        if (!isset($this->aclList[$name])) {
            $item = new PermisionItem();
            $item->setName($name);
            $item->setCaption($caption);
            if (!$type)
                $type = PermisionItem::TYPE_ELEMENT;
            $item->setType($type);

            try {
                $this->em->persist($item);
                $this->em->flush();
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                if (isset($annotationInfo['name'])) {
                    \Tracy\Debugger::log(new \Exception('Error in mapping. Mapper try save existing Permision Item - function: maxInput. Name: ' . $annotationInfo['name'] . ', method: ' . $name));
                } else {
                    \Tracy\Debugger::log(new \Exception('Error in mapping. Mapper try save existing Permision Item - function: maxInput. Name: ' .  $name));
                }
                return;
            }
            $this->recreateAclList(true);
        }
        /** For admin allow all to write */
        if ($user->identity->group === self::ADMIN_ID)
            return PermisionRule::ACTION_SHOW;


        // If user have role for presenter
        if (isset($user->identity->roles[$presenter])) {
            if ($user->identity->roles[$presenter] == PermisionRule::ACTION_ALL)
                return PermisionRule::ACTION_SHOW;
            if ($user->identity->roles[$presenter] == PermisionRule::ACTION_READ)
                return PermisionRule::ACTION_SHOW;
        }
        return isset($user->identity->roles[$name]) ? $user->identity->roles[$name] : 'NULL';
    }

}
