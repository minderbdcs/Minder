<?php

class Minder_Cache_Backend_Session extends Zend_Cache_Backend implements Zend_Cache_Backend_Interface {

    const MODIFIED_TIME = 'mtime';
    const EXPIRE = 'expire';

    protected $_storage = null;

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * Note : return value is always "string" (unserialization is done by the core not by the backend)
     *
     * @param  string $id Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|false cached datas
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        if (!($this->_test($id, $doNotTestCacheValidity))) {
            // The cache is not hit !
            return false;
        }

        $storage = $this->_getStorage();
        $data = isset($storage->data) ? $storage->data : array();
        return isset($data[$id]) ? $data[$id] : false;
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $id cache id
     * @return mixed|false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function test($id)
    {
        return $this->_test($id, false);
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string $data Datas to cache
     * @param  string $id Cache id
     * @param  array $tags Array of strings, the cache record will be tagged by each string entry
     * @param  int $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean true if no problem
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        $storage = $this->_getStorage();
        $storedMetadata = isset($storage->metadata) ? $storage->metadata : array();
        $storedData = isset($storage->data) ? $storage->data : array();

        $storedMetadata[$id] = array(
            static::MODIFIED_TIME => time(),
            static::EXPIRE => $this->_expireTime($this->getLifetime($specificLifetime))
        );

        $storedData[$id] = $data;
        $storage->metadata = $storedMetadata;
        $storage->data = $storedData;

        return true;
    }

    /**
     * Remove a cache record
     *
     * @param  string $id Cache id
     * @return boolean True if no problem
     */
    public function remove($id)
    {
        $storage = $this->_getStorage();
        $storedMetadata = isset($storage->metadata) ? $storage->metadata : array();
        $storedData = isset($storage->data) ? $storage->data : array();

        if (isset($storedMetadata[$id])) { unset($storedMetadata[$id]); }
        if (isset($storedData[$id])) { unset($storedData[$id]); }

        $storage->metadata = $storedMetadata;
        $storage->data = $storedData;

        return true;
    }

    /**
     * Clean some cache records
     *
     * Available modes are :
     * Zend_Cache::CLEANING_MODE_ALL (default)    => remove all cache entries ($tags is not used)
     * Zend_Cache::CLEANING_MODE_OLD              => remove too old cache entries ($tags is not used)
     * Zend_Cache::CLEANING_MODE_MATCHING_TAG     => remove cache entries matching all given tags
     *                                               ($tags can be an array of strings or a single string)
     * Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG => remove cache entries not {matching one of the given tags}
     *                                               ($tags can be an array of strings or a single string)
     * Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG => remove cache entries matching any given tags
     *                                               ($tags can be an array of strings or a single string)
     *
     * @param  string $mode Clean mode
     * @param  array $tags Array of tags
     * @return boolean true if no problem
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
        switch ($mode) {
            case Zend_Cache::CLEANING_MODE_ALL:
                return $this->_cleanAll();
            case Zend_Cache::CLEANING_MODE_OLD:
                return $this->_cleanOld();
            default:
                Zend_Cache::throwException('Invalid mode for clean() method');
                break;
        }

        return false;
    }

    protected function _cleanAll() {
        $storage = $this->_getStorage();
        $storage->metadata = array();
        $storage->data = array();

        return true;

    }

    protected function _cleanOld() {
        $storage = $this->_getStorage();
        $storedMetadata = isset($storage->metadata) ? $storage->metadata : array();
        $storedData = isset($storage->data) ? $storage->data : array();

        foreach ($storedMetadata as $id => $metadata) {
            if (time() > $metadata[static::EXPIRE]) {
                unset($storedMetadata[$id]);
                if (isset($storedData[$id])) {
                    unset($storedData[$id]);
                }
            }
        }

        $storage->metadata = $storedMetadata;
        $storage->data = $storedData;

        return true;
    }

    protected function _test($id, $doNotTestCacheValidity = false) {
        $metadata = $this->_getMetadata($id);
        if ($doNotTestCacheValidity || time() < $metadata[static::EXPIRE]) {
            return $metadata[static::MODIFIED_TIME];
        }

        return false;
    }

    protected function _getMetadata($id) {
        $emptyMetadata = array(
            static::MODIFIED_TIME => 0,
            static::EXPIRE => 0
        );

        $storage = $this->_getStorage();
        $metadata = isset($storage->metadata) ? $storage->metadata : array();
        return isset($metadata[$id]) ? $metadata[$id] : $emptyMetadata;
    }

    protected function _getStorage() {
        if (is_null($this->_storage)) {
            $this->_storage = new Zend_Session_Namespace(__CLASS__);
        }

        return $this->_storage;
    }

    /**
     * Compute & return the expire time
     *
     * @param $lifetime
     * @return int expire time (unix timestamp)
     */
    protected function _expireTime($lifetime)
    {
        if ($lifetime === null) {
            return 9999999999;
        }
        return time() + $lifetime;
    }
}