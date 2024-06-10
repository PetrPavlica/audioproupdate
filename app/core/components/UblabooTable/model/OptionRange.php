<?php

namespace App\Core\Components\UblabooTable\Model;

use Nette;
use Ublaboo\DataGrid\Status\Option;
use Nette\Utils\DateTime;

class OptionRange extends Option {

    /**
     * @var DateTime
     */
    public $rangeStart;

    /**
     * @var DateTime
     */
    public $rangeEnd;

    /**
     * Set DateTime range to option
     * @param DateTime $rangeStart
     * @param DateTime $rangeEnd
     */
    public function setDateTimeRange($rangeStart, $rangeEnd) {
        if ($rangeStart instanceof DateTime) {
            $this->rangeStart = $rangeStart;
        } else {
            throw new Exception("Range Start musí být objekt typu DateTime!");
        }
        if ($rangeEnd instanceof DateTime) {
            $this->rangeEnd = $rangeEnd;
        } else {
            throw new Exception("Range End musí být objekt typu DateTime!");
        }
    }

}
