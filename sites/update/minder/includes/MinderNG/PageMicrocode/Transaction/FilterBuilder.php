<?php

namespace MinderNG\PageMicrocode\Transaction;

use MinderNG\Filter;

class FilterBuilder {
    private $_factory;

    private function _getFilterFactory() {
        if (empty($this->_factory)) {
            $this->_factory = new Filter\Factory();
        }

        return $this->_factory;
    }

    /**
     * @param $expression
     * @return Filter\Chain
     */
    public function buildFilter($expression)
    {
        return $this->_getFilterFactory()->buildFilterChain(json_decode($expression, true));
    }
}