<?php

namespace App\Core\Model\Database\Utils;

use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Reflection\ClassType;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;

class EntitySetData {

    /**
     * @param mixed      $data
     * @param BaseEntity $entity
     */
    public static function set($data, $entity) {
        if (!$entity instanceof BaseEntity) {
            throw new Exception("Object isn't instance of \\App\\Module\\Database\\Entity\\BaseEntity.");
        }

        $data = ArrayHash::from($data);
        $propertyAnnotation = AnnotationParser::getClassPropertyAnnotations(get_class($entity), 'FORM');

        /**
         * @var ClassType $reflection
         */
        $reflection = $entity->reflection;

        foreach ($data as $key => $value) {

            if (isset($propertyAnnotation[$key])) {
                foreach ($propertyAnnotation[$key] as $item) {
                    $item = AnnotationParser::cleanAnnotation($item);
                    $annotation[$item[0]] = $item[1];
                }
            }
            if (isset($annotation['type']) && $annotation['type'] == 'time') {
                $value = date_create_from_format('H:i', $value);
                if (!$value)
                    $value = NULL;
            } elseif (isset($annotation['type']) && $annotation['type'] == 'date') {
                $value = date_create_from_format('d. m. Y', $value);
                if (!$value)
                    $value = NULL;
            } elseif (isset($annotation['type']) && $annotation['type'] == 'datetime') {
                if (!$value instanceof \DateTime) {
                    $value = date_create_from_format('d. m. Y H:i', $value);
                    if (!$value)
                        $value = NULL;
                }
            }
            //@TODO dalo by se zde i pomocí anotací uložit i cizí entitu
            $methodname = 'set' . Strings::firstUpper($key);
            if ($reflection->hasMethod($methodname)) {
                $method = $reflection->getMethod($methodname);
                if ($method->isPublic()) {
                    call_user_func_array([ $entity, $methodname], [ $value]);
                }
            } else if ($reflection->hasProperty($key)) {
                $property = $reflection->getProperty($key);
                if (!$property->isPrivate()) {
                    $entity->$key = $value;
                }
            }
        }

        return $entity;
    }

}
