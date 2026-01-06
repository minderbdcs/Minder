<?php

abstract class Minder_SysScreen_Legacy_AbstractManager {
    protected $_cache;

    /**
     * @return Zend_Cache_Core
     */
    protected function _getCacheStorage() {
        if (empty($this->_cache)) {
            $this->_cache = Zend_Cache::factory(
                'Class',
                new Minder_Cache_Backend_Session(),
                array(
                    'cached_entity' => $this->_getCachedEntry(),
                    'cache_id_prefix' => $this->_getPrefix(),
                    'automatic_serialization' => true,
                )
            );
        }

        return $this->_cache;
    }

    protected abstract function _getCachedEntry();

    protected abstract function _getPrefix();

    protected function _flushData() {
        $this->_getCacheStorage()->clean();
        return $this;
    }

}