<?php

class Minder2_SysScreen_BuilderLoader {
    /**
     * @var Zend_Loader_PluginLoader
     */
    static $_pluginLoader = null;

    /**
     * @static
     * @return Zend_Loader_PluginLoader
     */
    protected  static function _getPluginLoader() {
        if (is_null(self::$_pluginLoader))
            self::$_pluginLoader = new Zend_Loader_PluginLoader(
                        array('Minder2_SysScreen_Builder_' => 'Minder2/SysScreen/Builder/')
                    );

        return self::$_pluginLoader;
    }

    /**
     * @static
     * @param $prefix
     * @param $path
     * @return void
     */
    public static function addPrefixPath($prefix, $path) {
        self::_getPluginLoader()->addPrefixPath($prefix, $path);
    }

    /**
     * @static
     * @param $ssName
     * @return string
     */
    static protected function _formatBuilderClassName($ssName) {
        $nameArray = explode('_', strtolower($ssName));

        foreach ($nameArray as &$namePart)
            $namePart = ucfirst($namePart);

        return implode('', $nameArray);
    }

    /**
     * @static
     * @param $ssName
     * @return Minder2_SysScreen_Builder_Interface
     */
    public static function getScreenBuilder($ssName) {
        $builderClass = self::_getPluginLoader()->load($ssName, false);

        if (false === $builderClass)
            $builderClass = self::_getPluginLoader()->load('Default');

        return new $builderClass();
    }
}