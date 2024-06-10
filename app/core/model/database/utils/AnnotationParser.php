<?php

namespace App\Core\Model\Database\Utils;

/**
 * Description of AnnotationParser
 *
 * @author Jan Jindra
 */
class AnnotationParser {

    /**
     * Get comments from property of class
     * @param mixed $class define of php class
     * $param string $prefix prefix of anotation
     * @return array of nameproperty => array(comments)
     */
    static function getClassPropertyAnnotations($class, $prefix) {
        $r = new \ReflectionClass($class);
        $property = $r->getProperties();
        $annotations = [];
        foreach ($property as $item) {
            $i = NULL;
            preg_match_all('#' . $prefix . '(.*?)\n#s', $item->getDocComment(), $i);
            $annotations[$item->name] = $i[1];
        }
        return $annotations;
    }

    /**
     * Get comments from method of class
     * @param mixed $class define of php class
     * $param string $prefix prefix of anotation
     * @return array of nameproperty => array(comments)
     */
    static function getMethodPropertyAnnotations($class, $function, $prefix) {
        $r = new \ReflectionClass($class);
        $property = NULL;
        preg_match_all('#' . $prefix . '(.*?)\n#s', $r->getMethod($function)->getDocComment(), $property);
        return $property[1];
    }

    /**
     *
     * @param type $value
     * @return type
     */
    static function parseArray($value) {
        $e = explode('|', $value);
        $array = [];
        foreach ($e as $item) {
            $item = str_replace("]", "", $item);
            $item = str_replace('[', "", $item);
            $item = str_replace("'", "", $item);
            $item = str_replace('"', "", $item);
            $item = trim($item);
            $a = explode('>', $item);

            if (!isset($a[1]))
                $array[] = trim($a[0]);
            else
                $array[trim($a[0])] = trim($a[1]);
        }
        return $array;
    }

    /**
     * Helper method for clean annotation text
     * @param string $a annotation
     * @return array $a clean annotation
     */
    static function cleanAnnotation($a, $explode = '=') {
        $a = explode($explode, $a);
        $a[0] = str_replace("'", "", $a[0]);
        $a[0] = str_replace('"', "", $a[0]);
        $a[0] = trim($a[0]);
        $a[0] = strtolower($a[0]);
        $a[1] = str_replace("'", "", $a[1]);
        $a[1] = str_replace('"', "", $a[1]);
        $a[1] = trim($a[1]);
        return $a;
    }

    /**
     * Helper method for convert input string to array|string for array
     * @param string $item string from annotation - format [2, 3, ..], if only one value - return string, another array
     * @return array|string depend on entry
     */
    static function createAndCleanArg($item, $oneReturnString = true) {
        $arg = NULL;

        $item = str_replace("[", "", $item);
        $item = str_replace("]", "", $item);
        $item = str_replace("(", "", $item);
        $item = str_replace(")", "", $item);
        $item = str_replace("/", "", $item);
        $item = str_replace("\\", "", $item);
        $item = str_replace("'", "", $item);
        $item = str_replace('"', "", $item);

        $item = explode(',', $item);

        $arg = [];
        foreach ($item as $i) {
            $arg[] = trim($i);
        }

        if (count($arg) == 1 && $oneReturnString) {
            return $arg[0];
        }

        return $arg;
    }

    /**
     * Return all properties of class
     * @param type $class
     */
    static function getPropertiesOfClass($class) {
        $reflect = new \ReflectionClass($class);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
        $arr = [];
        foreach ($props as $item) {
            $arr[] = $item->name;
        }
        return $arr;
    }

    /**
     * Return OneToMany properties of class
     * @param string $class
     */
    static function getOneToManyPropertiesOfClass($class) {
        $properties = self::getClassPropertyAnnotations($class, '@ORM\\\OneToMany');
        $items = [];
        if ($properties) {
            foreach($properties as $k => $p) {
                if (isset($p[0])) {
                    $anotation = explode(',', trim($p[0]));
                    foreach ($anotation as $a) {
                        $objects = explode('=', $a);
                        $objects[0] = trim(str_replace(['"', '(', ')', ' '], '', $objects[0]));
                        $objects[1] = trim(str_replace(['"', '(', ')', ' '], '', $objects[1]));
                        if (in_array($objects[0], ['targetEntity', 'mappedBy'])) {
                            if ($objects[0] == 'targetEntity') {
                                $arr = explode('\\', $class);
                                $objects[1] = implode('\\', array_slice($arr, 0, count($arr) - 1)).'\\'.$objects[1];
                            }
                            $items[$k][$objects[0]] = $objects[1];
                        }
                    }
                }
            }
        }
        return $items;
    }
}
