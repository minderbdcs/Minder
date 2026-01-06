<?php

class Minder_Page_Builder {
    /**
     * @var Zend_Loader_PluginLoader
     */
    protected static $_builderLoader = null;

    /**
     * @return Zend_Loader_PluginLoader
     */
    protected function _getBuilderLoader() {
        if (is_null(self::$_builderLoader))
            self::$_builderLoader = new Zend_Loader_PluginLoader(array('Minder_Page_Builder' => 'Minder/Page/Builder'));

        return self::$_builderLoader;
    }

    protected function _formatClassName($menuId) {
        $parts = explode('_', strtolower($menuId));
        foreach ($parts as &$part)
            ucfirst($part);
        return implode('', $parts);
    }

    /**
     * @param string $menuId
     * @return Minder_Page_Builder_Default
     */
    protected function _getConcreteBuilder($menuId) {
        $builderLoader = $this->_getBuilderLoader();
        if (false === ($className = $builderLoader->load($this->_formatClassName($menuId), false)))
            return new Minder_Page_Builder_Default($menuId);

        return new $className($menuId);
    }

    public function build($menuId) {
        return $this->_getConcreteBuilder($menuId)->build();
    }
}