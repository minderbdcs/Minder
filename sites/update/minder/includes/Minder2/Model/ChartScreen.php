<?php

/**
 * @property string $SS_NAME
 * @property int $SS_SEQUENCE
 * @property string $chartUrl
 */
class Minder2_Model_ChartScreen extends Minder2_Model {

    const CHART_RENDERER = 'CHART_RENDERER';

    /**
     * @return string
     */
    function getName()
    {
        return $this->SS_NAME;
    }

    /**
     * @return string
     */
    function getStateId()
    {
        return 'CHART_SCREEN-' . $this->SS_NAME;
    }

    protected function _getPluginLoader($type = null)
    {
        $type = strtoupper($type);
        if (!isset($this->_loaders[$type])) {
            switch ($type) {
                case self::CHART_RENDERER:
                    $this->_loaders[$type] = new Zend_Loader_PluginLoader(
                        array('Minder_ChartRenderer_' => 'Minder/ChartRenderer/')
                    );
                    break;
                default:
                    return parent::_getPluginLoader($type);
            }

        }

        return $this->_loaders[$type];
    }

    public function addPrefixPath($prefix, $path, $type)
    {
        $type = strtoupper($type);
        switch ($type) {
            case self::CHART_RENDERER:
                $loader = $this->_getPluginLoader($type);
                $loader->addPrefixPath($prefix, $path);
                return $this;
            default:
                return parent::addPrefixPath($prefix, $path, $type);
        }
    }

    protected function getChartRenderer($name) {
        $className = $this->_getPluginLoader(self::CHART_RENDERER)->load($name);

        return new $className;
    }

    protected function _formatChartRendererClassName($ssName) {
        $nameArray = explode('_', strtolower($ssName));

        foreach ($nameArray as &$namePart)
            $namePart = ucfirst($namePart);

        return implode('', $nameArray);
    }

    function getChart() {
        $chartRenderer = $this->getChartRenderer($this->_formatChartRendererClassName($this->SS_NAME));

        return $chartRenderer->render();
    }

    /**
     * @return int
     */
    function getOrder()
    {
        return $this->SS_SEQUENCE;
    }


}