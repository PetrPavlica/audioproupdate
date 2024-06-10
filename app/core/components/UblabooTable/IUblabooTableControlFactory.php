<?php

namespace App\Core\Components\UblabooTable;

use App\Core\Components\UblabooTable\Model\ACLGrid;

interface IUblabooTableControlFactory {

    /** @return UblabooTableControl */
    function create(ACLGrid $grid);
}
