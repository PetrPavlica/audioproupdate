<?php

namespace App\Core\Model\Database\Utils;

use Nette\Utils\DateTime;
use Nette\Utils\Strings;

class EntityToArray {

    /**
     * @param ABaseEntity $entity
     *
     * @return array
     */
    public static function get($entity) {
        $data = [];

        $propertyAnnotation = AnnotationParser::getClassPropertyAnnotations(get_class($entity), 'FORM');

        /**
         * @var ClassType $reflection
         */
        $reflection = new \ReflectionObject($entity);
        foreach ($reflection->getProperties() as $property) {
            $propertyname = $property->getName();
            $methodname = 'get' . Strings::firstUpper($propertyname);
            $annotation = [];
            if (isset($propertyAnnotation[$propertyname])) {
                foreach ($propertyAnnotation[$propertyname] as $item) {
                    $item = AnnotationParser::cleanAnnotation($item);
                    $annotation[$item[0]] = $item[1];
                }
            }

            if ($reflection->hasMethod($methodname)) {
                $method = $reflection->getMethod($methodname);
                if ($method->isPublic() && !$property->isStatic()) {
                    $data[$propertyname] = call_user_func_array([ $entity, $methodname], []);
                }
            } else {
                if (!$property->isPrivate() && !$property->isStatic()) {
                    if (is_object($entity->$propertyname) && stripos(get_class($entity->$propertyname), 'DateTime') !== false) {
                        if (isset($annotation['type']) && $annotation['type'] == 'datetime') {
                            $data[$propertyname] = $entity->$propertyname->format('d. m. Y H:i');
                        } elseif (isset($annotation['type']) && $annotation['type'] == 'date') {
                            $data[$propertyname] = $entity->$propertyname->format('d. m. Y');
                        } else {
                            $data[$propertyname] = $entity->$propertyname->format('H:i');
                        }
                    } else if (is_object($entity->$propertyname) && $entity->$propertyname->reflection->hasProperty('id')) {
                        $data[$propertyname] = $entity->$propertyname->id;
                    } else if (is_array($entity->$propertyname)) {
                        $list = [];
                        foreach ($entity->$propertyname as $collection) {
                            $list[] = $collection->id;
                        }
                        $data[$propertyname] = $list;
                    } else {
                        $data[$propertyname] = $entity->$propertyname;
                    }
                }
            }
        }

        return $data;
    }

}
