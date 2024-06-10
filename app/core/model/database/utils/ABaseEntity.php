<?php

namespace App\Core\Model\Database\Utils;

use App\Core\Model\Database\Utils\EntitySetData;
use App\Core\Model\Database\Utils\EntityToArray;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

abstract class ABaseEntity extends BaseEntity {

    /**
     * @param mixed $data
     */
    public function __construct($data = null) {
        if ($data !== null) {
            $this->data($data);
        }
    }

    /**
     * @param mixed $data
     *
     * @return $this
     */
    public function data($data) {
        return EntitySetData::set($data, $this);
    }

    /**
     * @return array
     */
    public function toArray() {
        return EntityToArray::get($this);
    }

}
