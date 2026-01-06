<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Filter;

class FilterManager {
    /**
     * @var Filter\Factory
     */
    private $_factory;

    /**
     * @var Filter\Field[]
     */
    private $_filter;

    /**
     * @var Filter\Field[]
     */
    private $_filterNew;

    /**
     * FilterManager constructor.
     * @param Filter\Factory $factory
     */
    public function __construct(Filter\Factory $factory)
    {
        $this->_factory = $factory;
    }

    /**
     * @param Field $field
     * @return Filter\Field
     */
    public function getFieldFilter(Field $field) {
        if (empty($this->_filter[$field->getId()])) {
            $this->_filter[$field->getId()] = $this->_createFieldFilter($field->SSV_ALIAS, $field->METHOD->filter);
        }

        return $this->_filter[$field->getId()];
    }

    /**
     * @param Field $field
     * @return Filter\Field
     */
    public function getFieldFilterNew(Field $field) {
        if (empty($this->_filterNew[$field->getId()])) {
            $this->_filterNew[$field->getId()] = $this->_createFieldFilter($field->SSV_ALIAS, $field->METHOD_NEW->filter);
        }

        return $this->_filterNew[$field->getId()];

    }

    /**
     * @param $alias
     * @param array $filter
     * @return Filter\Field
     */
    private function _createFieldFilter($alias, array $filter) {

        try {
            $filter = $this->_factory->buildFilterChain($filter);
        } catch (\Exception $e) {
            user_error('Cannot create field filter: ' . $e->getMessage(), E_USER_WARNING);
            $filter = $this->_factory->buildFilterChain(array());
        }

        return new Filter\Field($alias, $filter);
    }
}