<?php

class Minder_Registry implements Minder_RegistryInterface {
    public static function get($index) {
        return Zend_Registry::get($index);
    }

    public static function set($index, $value) {
        Zend_Registry::set($index, $value);
    }

    /**
     * Set the default registry instance to a specified instance.
     *
     * @param Zend_Registry $registry An object instance of type Minder_RegistryInterface,
     *   or a subclass.
     * @return void
     * @throws Zend_Exception if registry is already initialized.
     */
    public static function setInstance(Zend_Registry $registry)
    {
        Zend_Registry::setInstance($registry);
    }

    /**
     * Set the class name to use for the default registry instance.
     * Does not affect the currently initialized instance, it only applies
     * for the next time you instantiate.
     *
     * @param string $registryClassName
     * @return void
     * @throws Zend_Exception if the registry is initialized or if the
     *   class name is not valid.
     */
    public static function setClassName($registryClassName = 'Zend_Registry')
    {
        Zend_Registry::setClassName($registryClassName);
    }

    /**
     * Returns TRUE if the $index is a named value in the registry,
     * or FALSE if $index was not found in the registry.
     *
     * @param  string $index
     * @return boolean
     */
    public static function isRegistered($index)
    {
        return Zend_Registry::isRegistered($index);
    }

    /**
     * Unset the default registry instance.
     * Primarily used in tearDown() in unit tests.
     * @returns void
     */
    public static function _unsetInstance()
    {
        Zend_Registry::_unsetInstance();
    }

    /**
     * @return Minder_Log
     */
    public static function getLogger() {
        return static::get('logger');
    }
}