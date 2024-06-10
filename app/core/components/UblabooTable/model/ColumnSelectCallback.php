<?php

namespace App\Core\Components\UblabooTable\Model;

use Ublaboo\DataGrid\Column\ColumnStatus;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Nette\Utils\DateTime;
use Ublaboo\DataGrid\Row;

class ColumnSelectCallback extends ColumnStatus {

    /**
     * @var string
     */
    public $dateFormat = 'j.n.Y';

    public function __construct(DataGrid $grid, $key, $column, $name) {
        parent::__construct($grid, $key, $column, $name);

        $this->setTemplate(__DIR__ . '/../templates/column_statusCallback.latte');
    }

    /**
     * Add option to status select
     * @param mixed $value
     * @param string $text
     * @param DateTime $start
     * @param DateTime $end
     * @return Option
     * @throws DataGridColumnStatusException
     */
    public function addOptionDateRange($value, $text, $start, $end) {
        if (!is_scalar($value)) {
            throw new DataGridColumnStatusException('Option value has to be scalar');
        }

        $option = new OptionRange($this, $value, $text);
        $option->setDateTimeRange($start, $end);
        $this->options[] = $option;

        return $option;
    }

    /**
     * Add null option to status select
     * @param mixed $value
     * @param string $text
     * @return Option
     * @throws DataGridColumnStatusException
     */
    public function addOptionNull($value, $text) {
        if (!is_scalar($value)) {
            throw new DataGridColumnStatusException('Option value has to be scalar');
        }
        $option = new OptionNull($this, $value, $text);
        $this->options[] = $option;

        return $option;
    }

    /**
     * Add prompt option to status select
     * @param string $text
     * @return Option
     * @throws DataGridColumnStatusException
     */
    public function addPrompt($text) {
        $option = new OptionPrompt($this, NULL, $text);
        $this->options[] = $option;
        return $option;
    }

    /**
     * Add option to status select
     * @param mixed $value
     * @param string $text
     * @return Option
     * @throws DataGridColumnStatusException
     */
    public function addOption($value, $text) {
        if ($value === NULL)
            throw new Exception("Value cannot be NULL, for null option use method addOptionNull");
        parent::addOption($value, $text);
    }

    public function getCurrentOption(Row $row) {
        foreach ($this->getOptions() as $option) {

            if ($option->getValue() === NULL) {
                continue;
            }
            if ($option instanceof OptionNull) {
                if ($row->getValue($this->getColumn()) === NULL) {
                    return $option;
                }
            }
            if ($option instanceof OptionRange) {
                $date = $row->getValue($this->getColumn());

                if ($option->rangeStart <= $date && $option->rangeEnd >= $date)
                    return $option;
            }
            if ($option instanceof Option) {

                if (is_numeric($option->getValue())) {
                    if (!is_numeric($row->getValue($this->getColumn())))
                        continue;
                }
                if ($option->getValue() == $row->getValue($this->getColumn())) {
                    return $option;
                }
            }
        }
        return NULL;
    }

    /**
     * Get prompt option to select
     * @param Row $row
     * @return option
     */
    public function getPromptOption(Row $row) {
        foreach ($this->getOptions() as $option) {
            if ($option->getValue() === NULL) {
                return $option;
            }
        }
        return NULL;
    }

    /**
     * Set output DateTime format
     * @param string $format
     */
    public function setDateFormat($format) {
        $this->dateFormat = $format;
    }

}
