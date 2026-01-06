<?php

namespace MinderNG\PageMicrocode\Component\SearchSpecification;

use MinderNG\Collection\Model;

class DataSet extends Model {

    public function addRow($row) {
        $tmpArray = isset($this->rows) ? $this->rows : array();
        $tmpArray[] = $row;
        $this->rows = $tmpArray;

        return $this;
    }

    /**
     * @return array
     */
    public function getRows() {
        $this->rows = isset($this->rows) ? $this->rows : array();
        return $this->rows;
    }
}