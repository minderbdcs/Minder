<?php

class Minder2_Model_Mapper_Abstract {
    const FETCH_MODE_ARRAY = 'FETCH_MODE_ARRAY';
    const FETCH_MODE_MODEL = 'FETCH_MODE_MODEL';

    /**
     * @var Minder
     */
    protected $_minder = null;

    /**
     * @return Minder
     */
    protected function _getMinder() {
        if (is_null($this->_minder))
            $this->_minder = Minder::getInstance();

        return $this->_minder;
    }
}