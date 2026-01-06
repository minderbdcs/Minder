<?php

class SearchFieldState_Collection extends ArrayObject {
    protected $_wasSearch = false;

    /**
     * @param string $index
     * @return SearchFieldState
     */
    public function offsetGet($index)
    {
        if ($this->offsetExists($index))
            return parent::offsetGet($index);

        return new SearchFieldState($index);
    }

    public function wasSearch() {
        return $this->_wasSearch;
    }

    public function setWasSearch($was) {
        $this->_wasSearch = (boolean)$was;
    }
}